<?php

namespace Modules\LMS\Services\Grading;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Jobs\ProcessSubmissionGradingJob;
use Modules\LMS\Models\GradingBatch;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\CourseService;

class GradingBatchService
{
    public function __construct(
        private ExcelGraderService $excelGrader,
        private DocumentExtractorService $extractor,
        private TextPreprocessorService $preprocessor,
        private AiGradingService $aiGradingService,
        private CourseService $courseService,
    ) {}

    /**
     * Fase 3: Trigger batch penilaian otomatis oleh Admin.
     * 1. Freeze pengumpulan (is_submission_locked = true)
     * 2. Buat record grading_batches dengan snapshot rubrik
     * 3. Jalankan Local Pre-Filter (Excel 100% lokal / kosong -> 0 kuota AI)
     * 4. Dispatch sisa target ke queue 'ai-grading'
     */
    public function startBatch(
        PostTest $postTest,
        ?string $bimtekEventId,
        int|string|null $triggeredBy
    ): GradingBatch {
        $postTest->loadMissing('questions');

        // 1. Freeze pengumpulan tugas
        $postTest->update(['is_submission_locked' => true]);

        $targets = $this->queryTargetResults($postTest->id, $bimtekEventId, [
            PostTestResult::GRADING_PENDING,
        ]);

        $rubricSnapshot = $this->buildRubricSnapshot($postTest);

        // 2. Buat record batch
        $batch = GradingBatch::create([
            'post_test_id'          => $postTest->id,
            'bimtek_event_id'       => $bimtekEventId,
            'triggered_by'          => $triggeredBy,
            'rubric_snapshot_json'  => $rubricSnapshot,
            'total_targets'         => $targets->count(),
            'processed_count'       => 0,
            'failed_count'          => 0,
            'consecutive_429_count' => 0,
            'status'                => GradingBatch::STATUS_RUNNING,
            'started_at'            => now(),
        ]);

        if ($targets->isEmpty()) {
            $batch->update([
                'status'      => GradingBatch::STATUS_COMPLETED,
                'finished_at' => now(),
            ]);

            return $batch;
        }

        $gradingType = $postTest->resolvedGradingType();
        $passingGrade = $postTest->effectivePassingGrade();
        $queueTargets = [];

        // 3. Local Pre-Filter (0 Kuota AI)
        foreach ($targets as $submission) {
            if ($gradingType === PostTest::GRADING_AUTO_EXCEL) {
                $this->processLocalExcel($submission, $postTest, $batch, $passingGrade);
                continue;
            }

            if ($gradingType === PostTest::GRADING_AUTO_AI_REPORT) {
                if ($this->shouldSkipEmptyReportToManual($submission, $postTest)) {
                    $this->markManualReviewPreFilter(
                        $submission,
                        $batch,
                        'Teks laporan kosong atau berada di bawah batas panjang minimal pada tahap pre-filter lokal (0 kuota AI).'
                    );
                    continue;
                }
                $queueTargets[] = $submission;
                continue;
            }

            if ($gradingType === PostTest::GRADING_AUTO_AI_ESSAY) {
                if ($this->shouldSkipEmptyEssayToManual($submission)) {
                    $this->markManualReviewPreFilter(
                        $submission,
                        $batch,
                        'Seluruh jawaban uraian kosong atau berada di bawah batas karakter minimal pada tahap pre-filter lokal (0 kuota AI).'
                    );
                    continue;
                }
                $queueTargets[] = $submission;
                continue;
            }

            // Fallback MANUAL
            $this->markManualReviewPreFilter(
                $submission,
                $batch,
                'Tipe evaluasi disetel sebagai penilaian manual.'
            );
        }

        // 4. Dispatch target yang lolos pre-filter ke antrean ai-grading
        $this->dispatchQueuedTargets($queueTargets, $batch);

        return $this->refreshBatchStatus($batch->fresh());
    }

    /**
     * Lanjutkan batch berstatus PAUSED_QUOTA — hanya memproses sisa peserta berstatus PENDING.
     */
    public function resumeBatch(GradingBatch $batch): GradingBatch
    {
        if ($batch->status !== GradingBatch::STATUS_PAUSED_QUOTA) {
            return $batch;
        }

        $batch->update([
            'status'                => GradingBatch::STATUS_RUNNING,
            'consecutive_429_count' => 0,
            'finished_at'           => null,
        ]);

        $pending = $this->queryTargetResults($batch->post_test_id, $batch->bimtek_event_id, [
            PostTestResult::GRADING_PENDING,
        ]);

        $this->dispatchQueuedTargets($pending->all(), $batch);

        return $this->refreshBatchStatus($batch->fresh());
    }

    /**
     * Ulangi hanya peserta yang berstatus FAILED (idempoten, tidak menyentuh DRAFT_GRADED / PUBLISHED).
     */
    public function retryFailed(GradingBatch $batch): GradingBatch
    {
        $failed = PostTestResult::where('grading_batch_id', $batch->id)
            ->where('grading_status', PostTestResult::GRADING_FAILED)
            ->get();

        if ($failed->isEmpty()) {
            return $this->refreshBatchStatus($batch);
        }

        $retryCount = $failed->count();
        $batch->update([
            'status'                => GradingBatch::STATUS_RUNNING,
            'failed_count'          => max(0, $batch->failed_count - $retryCount),
            'consecutive_429_count' => 0,
            'finished_at'           => null,
        ]);

        foreach ($failed as $sub) {
            $sub->update([
                'retry_count'        => 0,
                'last_error_message' => null,
            ]);
        }

        $this->dispatchQueuedTargets($failed->all(), $batch);

        return $this->refreshBatchStatus($batch->fresh());
    }

    /**
     * Publikasikan draf nilai (DRAFT_GRADED / MANUAL_REVIEW yang sudah memiliki nilai) ke PUBLISHED.
     * Menyelaraskan nilai akhir ke peserta dan memperbarui progres kursus.
     *
     * @param  array<int, string>|null  $submissionIds
     */
    public function publishGrades(
        PostTest $postTest,
        ?string $bimtekEventId = null,
        ?array $submissionIds = null
    ): int {
        $postTest->loadMissing(['course.sections.contents', 'section.course.sections.contents']);
        $passingGrade = $postTest->effectivePassingGrade();

        $query = PostTestResult::where('post_test_id', $postTest->id)
            ->whereIn('grading_status', [
                PostTestResult::GRADING_DRAFT_GRADED,
                PostTestResult::GRADING_MANUAL_REVIEW,
            ])
            ->where(fn($q) => $q->whereNotNull('final_score')->orWhereNotNull('ai_score'));

        if ($bimtekEventId) {
            $participantIds = DB::table('course_student')
                ->where('bimtek_event_id', $bimtekEventId)
                ->pluck('user_id');
            $query->whereIn('user_id', $participantIds);
        }

        if (!empty($submissionIds)) {
            $query->whereIn('id', $submissionIds);
        }

        $results = $query->get();
        $publishedCount = 0;
        $course = $postTest->course ?? $postTest->section?->course;

        foreach ($results as $result) {
            $finalScore = (float) ($result->final_score ?? $result->ai_score ?? 0);
            $intScore = (int) round($finalScore);
            $isPassed = $finalScore >= $passingGrade;

            $feedbackSummary = trim((string) ($result->ai_feedback_json['overall_feedback'] ?? ''));
            $notes = $feedbackSummary !== '' ? $feedbackSummary : null;

            $result->update([
                'final_score'         => $finalScore,
                'score'               => $intScore,
                'is_passed'           => $isPassed,
                'status'              => 'graded',
                'grading_status'      => PostTestResult::GRADING_PUBLISHED,
                'needs_manual_review' => false,
                'notes'               => $notes !== '' ? $notes : null,
                'graded_at'           => $result->graded_at ?? now(),
            ]);

            if ($course && $result->user_id) {
                $newProgress = $this->courseService->calculateDynamicProgress($course, $result->user_id);
                DB::table('course_student')
                    ->where('course_id', $course->id)
                    ->where('user_id', $result->user_id)
                    ->update([
                        'progress'     => $newProgress,
                        'status'       => $newProgress >= 100 ? 'completed' : 'in_progress',
                        'completed_at' => $newProgress >= 100 ? now() : null,
                        'updated_at'   => now(),
                    ]);
            }

            $publishedCount++;
        }

        return $publishedCount;
    }

    /**
     * Periksa apakah batch telah memproses seluruh targetnya.
     */
    public function refreshBatchStatus(GradingBatch $batch): GradingBatch
    {
        if ($batch->status !== GradingBatch::STATUS_RUNNING) {
            return $batch;
        }

        $done = $batch->processed_count + $batch->failed_count;
        if ($done >= $batch->total_targets) {
            $finalStatus = $batch->failed_count > 0
                ? GradingBatch::STATUS_COMPLETED_WITH_ERRORS
                : GradingBatch::STATUS_COMPLETED;

            $batch->update([
                'status'      => $finalStatus,
                'finished_at' => now(),
            ]);
        }

        return $batch->fresh();
    }

    /**
     * Snapshot rubrik saat batch dimulai agar standar penilaian konsisten dari awal hingga akhir.
     *
     * @return array<string, mixed>
     */
    public function buildRubricSnapshot(PostTest $postTest): array
    {
        $postTest->loadMissing('questions');
        $gradingType = $postTest->resolvedGradingType();
        $passingGrade = $postTest->effectivePassingGrade();

        $snapshot = [
            'grading_type'       => $gradingType,
            'passing_grade'      => $passingGrade,
            'key_extracted_text' => $postTest->key_extracted_text,
            'raw_rubric_json'    => $postTest->rubric_json,
        ];

        if ($gradingType === PostTest::GRADING_AUTO_AI_ESSAY) {
            $questions = $postTest->questions->values();
            $count = $questions->count();
            $defaultWeight = $count > 0 ? round(100 / $count, 2) : 0.0;
            $rubricByQ = [];

            foreach (($postTest->rubric_json ?? []) as $rItem) {
                if (!empty($rItem['question_id'])) {
                    $rubricByQ[(string) $rItem['question_id']] = $rItem;
                }
            }

            $essayItems = [];
            foreach ($questions as $idx => $q) {
                $rMeta = $rubricByQ[(string) $q->id] ?? ($postTest->rubric_json[$idx] ?? []);
                $essayItems[] = [
                    'question_id' => (string) $q->id,
                    'question'    => (string) $q->question,
                    'answer_key'  => (string) ($q->answer_key ?? ''),
                    'rubric'      => (string) ($rMeta['description'] ?? $rMeta['rubric'] ?? ''),
                    'max_score'   => (float) ($rMeta['max_score'] ?? ($rMeta['weight'] ?? $defaultWeight)),
                ];
            }
            $snapshot['essay_items'] = $essayItems;
        } elseif ($gradingType === PostTest::GRADING_AUTO_AI_REPORT && is_array($postTest->rubric_json)) {
            $snapshot['criteria'] = $postTest->rubric_json;
        }

        return $snapshot;
    }

    private function processLocalExcel(
        PostTestResult $submission,
        PostTest $postTest,
        GradingBatch $batch,
        float $passingGrade
    ): void {
        $disk = Storage::disk('public');

        if (
            empty($postTest->answer_key_file_path)
            || !$disk->exists($postTest->answer_key_file_path)
            || empty($submission->file_path)
            || !$disk->exists($submission->file_path)
        ) {
            $this->markManualReviewPreFilter(
                $submission,
                $batch,
                'Berkas Excel peserta atau kunci jawaban tidak ditemukan di storage.'
            );

            return;
        }

        try {
            $templatePath = ($postTest->admin_file_path && $disk->exists($postTest->admin_file_path))
                ? $disk->path($postTest->admin_file_path)
                : null;

            $eval = $this->excelGrader->grade(
                $disk->path($postTest->answer_key_file_path),
                $disk->path($submission->file_path),
                $templatePath,
                is_array($postTest->rubric_json) ? $postTest->rubric_json : [],
                $passingGrade
            );

            $triage = $this->aiGradingService->evaluateAutoTriage(
                $eval['score'],
                $passingGrade,
                $eval['feedback_json'],
                false
            );

            $feedbackJson = array_merge($eval['feedback_json'], [
                'passing_grade'  => $passingGrade,
                'triage_reasons' => $triage['reasons'],
            ]);

            $submission->update([
                'grading_batch_id'    => $batch->id,
                'ai_score'            => $eval['score'],
                'final_score'         => $eval['score'],
                'ai_feedback_json'    => $feedbackJson,
                'needs_manual_review' => $triage['needs_manual_review'],
                'grading_status'      => PostTestResult::GRADING_DRAFT_GRADED,
                'last_error_message'  => null,
                'graded_at'           => now(),
            ]);

            $batch->increment('processed_count');
        } catch (\Throwable $e) {
            $submission->update([
                'grading_batch_id'    => $batch->id,
                'grading_status'      => PostTestResult::GRADING_FAILED,
                'needs_manual_review' => true,
                'last_error_message'  => 'Gagal menilai Excel lokal: ' . $e->getMessage(),
            ]);
            $batch->increment('failed_count');
        }
    }

    private function shouldSkipEmptyReportToManual(PostTestResult $submission, PostTest $postTest): bool
    {
        $minChars = (int) config('grading.preprocessing.pdf_min_chars', 100);
        $cleanText = trim((string) ($submission->extracted_text ?? ''));

        if ($cleanText === '' && !empty($submission->file_path)) {
            $extracted = $this->extractor->extractFromPublicDisk((string) $submission->file_path);
            $preprocessed = $this->preprocessor->preprocess($extracted['text'], $postTest->template_extracted_text);
            $cleanText = $preprocessed['text'];

            $submission->update([
                'extracted_text' => $cleanText,
                'is_truncated'   => $preprocessed['is_truncated'],
            ]);
        }

        return mb_strlen($cleanText) < $minChars;
    }

    private function shouldSkipEmptyEssayToManual(PostTestResult $submission): bool
    {
        $minChars = (int) config('grading.preprocessing.essay_min_chars', 15);
        $answers = is_array($submission->answers) ? $submission->answers : [];
        $joined = trim(implode(' ', array_map(fn($a) => trim((string) $a), $answers)));

        return mb_strlen($joined) < $minChars;
    }

    private function markManualReviewPreFilter(PostTestResult $submission, GradingBatch $batch, string $reason): void
    {
        $submission->update([
            'grading_batch_id'    => $batch->id,
            'ai_score'            => 0,
            'final_score'         => 0,
            'ai_feedback_json'    => [
                'grader'           => 'LOCAL_PREFILTER',
                'overall_feedback' => $reason,
                'triage_reasons'   => [$reason],
            ],
            'needs_manual_review' => true,
            'grading_status'      => PostTestResult::GRADING_MANUAL_REVIEW,
            'graded_at'           => now(),
        ]);

        $batch->increment('processed_count');
    }

    /**
     * @param  array<int, PostTestResult>  $targets
     */
    private function dispatchQueuedTargets(array $targets, GradingBatch $batch): void
    {
        $connection = (string) config('grading.queue.connection', 'database');
        $queueName = (string) config('grading.queue.name', 'ai-grading');
        $delaySec = max(0, (int) config('grading.queue.rate_limit_delay_sec', 7));
        $useDelay = ($connection !== 'sync' && !app()->runningUnitTests() && $delaySec > 0);

        foreach (array_values($targets) as $index => $submission) {
            $submission->update([
                'grading_batch_id' => $batch->id,
                'grading_status'   => PostTestResult::GRADING_QUEUED,
            ]);

            $pendingDispatch = ProcessSubmissionGradingJob::dispatch($submission->id, $batch->id)
                ->onConnection($connection)
                ->onQueue($queueName);

            if ($useDelay && $index > 0) {
                $pendingDispatch->delay(now()->addSeconds($index * $delaySec));
            }
        }
    }

    /**
     * @param  array<int, string>  $statuses
     * @return Collection<int, PostTestResult>
     */
    private function queryTargetResults(string $postTestId, ?string $bimtekEventId, array $statuses): Collection
    {
        $query = PostTestResult::where('post_test_id', $postTestId)
            ->whereIn('grading_status', $statuses);

        if ($bimtekEventId) {
            $participantIds = DB::table('course_student')
                ->where('bimtek_event_id', $bimtekEventId)
                ->pluck('user_id');
            $query->whereIn('user_id', $participantIds);
        }

        return $query->get();
    }
}
