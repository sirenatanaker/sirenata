<?php

namespace Modules\LMS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LMS\Models\GradingBatch;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\Grading\AiGradingService;
use Modules\LMS\Services\Grading\DocumentExtractorService;
use Modules\LMS\Services\Grading\Exceptions\GeminiRateLimitException;
use Modules\LMS\Services\Grading\GradingBatchService;
use Modules\LMS\Services\Grading\TextPreprocessorService;

class ProcessSubmissionGradingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 1 eksekusi awal + 3 kali retry (total 4 percobaan).
     */
    public int $tries = 4;

    public int $timeout = 90;

    public function __construct(
        public readonly string $resultId,
        public readonly string $batchId,
    ) {}

    public function handle(
        AiGradingService $aiGradingService,
        GradingBatchService $batchService,
        DocumentExtractorService $extractor,
        TextPreprocessorService $preprocessor,
    ): void {
        $batch = GradingBatch::find($this->batchId);
        $submission = PostTestResult::with('postTest.questions')->find($this->resultId);

        if (!$batch || !$submission || !$submission->postTest) {
            return;
        }

        // 1. Cek status batch: jika PAUSED_QUOTA atau CANCELLED, kembalikan ke PENDING & lewati
        if (in_array($batch->status, [GradingBatch::STATUS_PAUSED_QUOTA, GradingBatch::STATUS_CANCELLED], true)) {
            if (in_array($submission->grading_status, [PostTestResult::GRADING_QUEUED, PostTestResult::GRADING_PROCESSING], true)) {
                $submission->update(['grading_status' => PostTestResult::GRADING_PENDING]);
            }

            return;
        }

        // 2. Idempotensi: jika sudah DRAFT_GRADED atau PUBLISHED, hentikan job segera
        if (in_array($submission->grading_status, [PostTestResult::GRADING_DRAFT_GRADED, PostTestResult::GRADING_PUBLISHED], true)) {
            return;
        }

        // 3. Tandai status ke PROCESSING
        $submission->update([
            'grading_status'     => PostTestResult::GRADING_PROCESSING,
            'grading_batch_id'   => $batch->id,
            'last_error_message' => null,
        ]);

        $postTest = $submission->postTest;
        $gradingType = $postTest->resolvedGradingType();
        $rubricSnapshot = is_array($batch->rubric_snapshot_json) ? $batch->rubric_snapshot_json : [];
        $passingGrade = (float) ($rubricSnapshot['passing_grade'] ?? $postTest->effectivePassingGrade());

        try {
            // 4. Panggil Gemini API sesuai tipe tugas
            if ($gradingType === PostTest::GRADING_AUTO_AI_ESSAY) {
                $answers = is_array($submission->answers) ? $submission->answers : [];
                $aiResult = $aiGradingService->gradeEssay($postTest, $answers, $rubricSnapshot);
            } elseif ($gradingType === PostTest::GRADING_AUTO_AI_REPORT) {
                $cleanText = trim((string) ($submission->extracted_text ?? ''));
                $isTruncated = (bool) $submission->is_truncated;

                // Ekstraksi on-demand bila submission lama belum memiliki extracted_text
                if ($cleanText === '' && !empty($submission->file_path)) {
                    $extracted = $extractor->extractFromPublicDisk((string) $submission->file_path);
                    $preprocessed = $preprocessor->preprocess($extracted['text'], $postTest->template_extracted_text);
                    $cleanText = $preprocessed['text'];
                    $isTruncated = $preprocessed['is_truncated'];
                    $submission->update([
                        'extracted_text' => $cleanText,
                        'is_truncated'   => $isTruncated,
                    ]);
                }

                $aiResult = $aiGradingService->gradeReport($postTest, $cleanText, $rubricSnapshot);
            } else {
                // Jangan kirim tipe lain (misal Excel) ke API AI
                $submission->update([
                    'grading_status' => PostTestResult::GRADING_MANUAL_REVIEW,
                    'needs_manual_review' => true,
                ]);
                $batch->increment('processed_count');
                $batchService->refreshBatchStatus($batch->fresh());

                return;
            }

            // 6. Simpan hasil sebagai draf (DRAFT_GRADED) + Auto-Triage
            $score = (float) $aiResult['score'];
            $triage = $aiGradingService->evaluateAutoTriage(
                $score,
                $passingGrade,
                $aiResult,
                (bool) $submission->is_truncated
            );

            $feedbackPayload = array_merge($aiResult, [
                'grader'         => $gradingType,
                'passing_grade'  => $passingGrade,
                'triage_reasons' => $triage['reasons'],
            ]);

            DB::transaction(function () use ($submission, $batch, $score, $triage, $feedbackPayload) {
                $submission->update([
                    'ai_score'            => $score,
                    'final_score'         => $score,
                    'ai_feedback_json'    => $feedbackPayload,
                    'needs_manual_review' => $triage['needs_manual_review'],
                    'grading_status'      => PostTestResult::GRADING_DRAFT_GRADED,
                    'last_error_message'  => null,
                    'graded_at'           => now(),
                ]);

                // Reset counter 429 beruntun tiap kali 1 job sukses
                GradingBatch::where('id', $batch->id)->update([
                    'consecutive_429_count' => 0,
                    'processed_count'       => DB::raw('processed_count + 1'),
                ]);
            });

            $batchService->refreshBatchStatus($batch->fresh());

            // Jeda throttling 6-8 detik antar job pada worker sekuensial (lewati saat sync/testing)
            $this->applyThrottleDelay();
        } catch (GeminiRateLimitException $e) {
            $this->handleRateLimitException($submission, $batch, $batchService, $e);
        } catch (\Throwable $e) {
            $this->handleNonQuotaFailure($submission, $batch, $batchService, $e);
        } finally {
            \App\Support\SmartCache::bump([\App\Support\SmartCache::DOMAIN_LMS_EVENTS]);
        }
    }

    private function handleRateLimitException(
        PostTestResult $submission,
        GradingBatch $batch,
        GradingBatchService $batchService,
        GeminiRateLimitException $e
    ): void {
        $maxRetries = (int) config('grading.queue.max_retries', 3);
        $backoffSchedule = config('grading.queue.backoff_seconds', [60, 180, 300]);
        $currentRetry = (int) $submission->retry_count;

        Log::warning("ProcessSubmissionGradingJob rate-limit on submission {$submission->id} (retry {$currentRetry}/{$maxRetries}): {$e->getMessage()}");

        // Jika masih dalam batas retry dan berjalan lewat queue (bukan sync), lepas kembali dengan exponential backoff
        if ($currentRetry < $maxRetries && $this->job && config('grading.queue.connection') !== 'sync' && !app()->runningUnitTests()) {
            $delay = (int) ($backoffSchedule[$currentRetry] ?? 300);
            $submission->update([
                'retry_count'        => $currentRetry + 1,
                'grading_status'     => PostTestResult::GRADING_QUEUED,
                'last_error_message' => $e->getMessage(),
            ]);
            $this->release($delay);

            return;
        }

        // Retry habis akibat 429/503 -> naikkan consecutive_429_count, kembalikan ke PENDING (bukan FAILED)
        $threshold = (int) config('grading.queue.consecutive_429_pause_threshold', 3);

        DB::transaction(function () use ($submission, $batch, $threshold, $e) {
            $submission->update([
                'retry_count'        => $submission->retry_count + 1,
                'grading_status'     => PostTestResult::GRADING_PENDING,
                'last_error_message' => $e->getMessage(),
            ]);

            $lockedBatch = GradingBatch::lockForUpdate()->find($batch->id);
            if (!$lockedBatch) {
                return;
            }

            $newConsecutive = $lockedBatch->consecutive_429_count + 1;
            $updates = ['consecutive_429_count' => $newConsecutive];

            if ($newConsecutive >= $threshold) {
                $updates['status'] = GradingBatch::STATUS_PAUSED_QUOTA;

                // Kembalikan sisa submission berstatus QUEUED pada batch ini ke PENDING agar siap dilanjutkan
                PostTestResult::where('grading_batch_id', $lockedBatch->id)
                    ->where('grading_status', PostTestResult::GRADING_QUEUED)
                    ->update(['grading_status' => PostTestResult::GRADING_PENDING]);
            }

            $lockedBatch->update($updates);
        });

        $batchService->refreshBatchStatus($batch->fresh());
    }

    private function handleNonQuotaFailure(
        PostTestResult $submission,
        GradingBatch $batch,
        GradingBatchService $batchService,
        \Throwable $e
    ): void {
        $maxRetries = (int) config('grading.queue.max_retries', 3);
        $currentRetry = (int) $submission->retry_count;

        Log::error("ProcessSubmissionGradingJob error on submission {$submission->id}: {$e->getMessage()}");

        if ($currentRetry < $maxRetries && $this->job && config('grading.queue.connection') !== 'sync' && !app()->runningUnitTests()) {
            $submission->update([
                'retry_count'        => $currentRetry + 1,
                'grading_status'     => PostTestResult::GRADING_QUEUED,
                'last_error_message' => $e->getMessage(),
            ]);
            $this->release(30);

            return;
        }

        DB::transaction(function () use ($submission, $batch, $e) {
            $submission->update([
                'grading_status'      => PostTestResult::GRADING_FAILED,
                'needs_manual_review' => true,
                'last_error_message'  => $e->getMessage(),
            ]);

            GradingBatch::where('id', $batch->id)->update([
                'failed_count' => DB::raw('failed_count + 1'),
            ]);
        });

        $batchService->refreshBatchStatus($batch->fresh());
    }

    private function applyThrottleDelay(): void
    {
        if (app()->runningUnitTests() || config('grading.queue.connection') === 'sync') {
            return;
        }

        $delaySec = max(0, (int) config('grading.queue.rate_limit_delay_sec', 7));
        if ($delaySec > 0) {
            sleep($delaySec);
        }
    }
}
