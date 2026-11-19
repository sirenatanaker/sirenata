<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\GradingBatch;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\CourseService;
use Modules\LMS\Services\Grading\AiGradingService;
use Modules\LMS\Services\Grading\DocumentExtractorService;
use Modules\LMS\Services\Grading\ExcelGraderService;
use Modules\LMS\Services\Grading\GradingBatchService;
use Modules\LMS\Services\Grading\TextPreprocessorService;

class PostTestSubmissionController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private GradingBatchService $gradingBatchService,
        private ExcelGraderService $excelGrader,
        private AiGradingService $aiGradingService,
        private DocumentExtractorService $documentExtractor,
        private TextPreprocessorService $textPreprocessor,
    ) {}

    /**
     * Redirect tautan lama Jawaban Evaluasi langsung ke Pusat Kendali Periode Event (Tab Penilaian Evaluasi).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $bimtekEventId = $request->query('bimtek_event_id');
        $postTestId    = $request->query('post_test_id');
        $courseId      = $request->query('course_id');

        if ($bimtekEventId) {
            return redirect()->route('admin-pusat.management-course.bimtek-events.show', array_filter([
                'bimtek_event' => $bimtekEventId,
                'tab'          => 'evaluation',
                'post_test_id' => $postTestId,
                'eval_search'  => $request->query('search'),
                'eval_status'  => $request->query('status'),
            ], fn($v) => $v !== null && $v !== ''));
        }

        if ($postTestId || $courseId) {
            $pt = $postTestId ? PostTest::with('section')->find($postTestId) : null;
            $resolvedCourseId = $pt?->course_id ?? $pt?->section?->course_id ?? $courseId;
            $resolvedEvent = $resolvedCourseId
                ? BimtekEvent::where('course_id', $resolvedCourseId)
                    ->orderByRaw("FIELD(status, 'ongoing', 'completed', 'open_registration', 'draft')")
                    ->latest('start_date')
                    ->first()
                : null;

            if ($resolvedEvent) {
                return redirect()->route('admin-pusat.management-course.bimtek-events.show', array_filter([
                    'bimtek_event' => $resolvedEvent->id,
                    'tab'          => 'evaluation',
                    'post_test_id' => $postTestId,
                ], fn($v) => $v !== null && $v !== ''));
            }
        }

        return redirect()->route('admin-pusat.management-course.bimtek-events.index');
    }

    /**
     * Legacy fallback unused: kept clean.
     */
    private function unusedLegacyIndex(Request $request): View
    {
        $bimtekEventId = $request->query('bimtek_event_id');
        $postTestId    = $request->query('post_test_id');
        $courseId      = $request->query('course_id');
        $status        = (string) $request->query('status', 'all');
        $search        = trim((string) $request->query('search', ''));

        $manualTypes = ['essay', 'uraian', 'file_upload', 'upload'];

        $selectedPostTest = $postTestId
            ? PostTest::with(['questions', 'section', 'course'])->find($postTestId)
            : null;

        if ($bimtekEventId) {
            $selectedEvent = BimtekEvent::with([
                'course.sections' => fn($q) => $q->orderBy('position')->orderBy('created_at'),
            ])->withCount('participants')->find($bimtekEventId);
        } elseif ($selectedPostTest || $courseId) {
            $resolvedCourseId = $selectedPostTest?->course_id
                ?? $selectedPostTest?->section?->course_id
                ?? $courseId;

            $selectedEvent = $resolvedCourseId
                ? BimtekEvent::with([
                    'course.sections' => fn($q) => $q->orderBy('position')->orderBy('created_at'),
                ])->withCount('participants')
                    ->where('course_id', $resolvedCourseId)
                    ->orderByRaw("FIELD(status, 'ongoing', 'completed', 'open_registration', 'draft')")
                    ->latest('start_date')
                    ->first()
                : null;
        } else {
            $selectedEvent = null;
        }

        // =========================================================================
        // LANGKAH 1: PILIH EVENT BIMTEK
        // =========================================================================
        if (!$selectedEvent) {
            $events = BimtekEvent::with('course:id,name,slug')
                ->withCount('participants')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('batch_name', 'like', "%{$search}%")
                            ->orWhere('batch_code', 'like', "%{$search}%")
                            ->orWhereHas('course', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
                    });
                })
                ->orderByRaw("FIELD(status, 'ongoing', 'completed', 'open_registration', 'draft')")
                ->orderByDesc('start_date')
                ->paginate(12)
                ->withQueryString();

            $eventIds = $events->getCollection()->pluck('id');
            $courseIds = $events->getCollection()->pluck('course_id')->filter()->unique();

            $participantsByEvent = DB::table('course_student')
                ->whereIn('bimtek_event_id', $eventIds)
                ->select('bimtek_event_id', 'user_id')
                ->get()
                ->groupBy('bimtek_event_id')
                ->map(fn($rows) => $rows->pluck('user_id')->all());

            $postTestsByCourse = PostTest::whereIn('course_id', $courseIds)
                ->orWhereHas('section', fn($q) => $q->whereIn('course_id', $courseIds))
                ->with('section:id,course_id')
                ->get(['id', 'course_id', 'course_section_id', 'type'])
                ->groupBy(fn($pt) => $pt->course_id ?? $pt->section?->course_id);

            $allEventUserIds = $participantsByEvent->flatten()->unique()->values();
            $allPostTestIds  = $postTestsByCourse->flatten()->pluck('id')->unique()->values();

            $resultStats = ($allEventUserIds->isNotEmpty() && $allPostTestIds->isNotEmpty())
                ? PostTestResult::whereIn('user_id', $allEventUserIds)
                    ->whereIn('post_test_id', $allPostTestIds)
                    ->get(['post_test_id', 'user_id', 'status', 'grading_status'])
                : collect();

            $events->getCollection()->transform(function ($ev) use ($participantsByEvent, $postTestsByCourse, $resultStats, $manualTypes) {
                $evUserIds = $participantsByEvent->get($ev->id, []);
                $evTests   = $postTestsByCourse->get($ev->course_id, collect());
                $evTestIds = $evTests->pluck('id')->all();
                $manualTestIds = $evTests->whereIn('type', $manualTypes)->pluck('id')->all();

                $matching = $resultStats->filter(fn($r) => in_array($r->user_id, $evUserIds, true) && in_array($r->post_test_id, $evTestIds, true));

                $ev->submissions_count = $matching->count();
                $ev->pending_count = $matching->filter(function ($r) use ($manualTestIds) {
                    return in_array($r->post_test_id, $manualTestIds, true)
                        && $r->grading_status !== PostTestResult::GRADING_PUBLISHED;
                })->count();

                return $ev;
            });

            return view('lms::admin-pusat.post-test-submissions.index', [
                'step'             => 'events',
                'events'           => $events,
                'selectedEvent'    => null,
                'selectedPostTest' => null,
                'dailyAgendas'     => collect(),
                'finalEvaluation'  => null,
                'submissions'      => null,
                'search'           => $search,
                'status'           => $status,
            ]);
        }

        $eventParticipantIds = DB::table('course_student')
            ->where('bimtek_event_id', $selectedEvent->id)
            ->pluck('user_id');

        $eventParticipantsCount = $eventParticipantIds->count();

        // =========================================================================
        // LANGKAH 2: PILIH AGENDA HARIAN / EVALUASI AKHIR
        // =========================================================================
        if (!$selectedPostTest) {
            $sections = $selectedEvent->course?->sections ?? collect();
            $sectionIds = $sections->pluck('id');

            $sectionTests = $sectionIds->isNotEmpty()
                ? PostTest::whereIn('course_section_id', $sectionIds)->get()->keyBy('course_section_id')
                : collect();

            $finalTest = $selectedEvent->course_id
                ? PostTest::where('course_id', $selectedEvent->course_id)->whereNull('course_section_id')->first()
                : null;

            $relevantTestIds = $sectionTests->pluck('id');
            if ($finalTest) {
                $relevantTestIds = $relevantTestIds->push($finalTest->id);
            }

            $resultsByTest = ($eventParticipantIds->isNotEmpty() && $relevantTestIds->isNotEmpty())
                ? PostTestResult::whereIn('user_id', $eventParticipantIds)
                    ->whereIn('post_test_id', $relevantTestIds)
                    ->get(['post_test_id', 'status', 'grading_status'])
                    ->groupBy('post_test_id')
                : collect();

            $dailyAgendas = $sections->values()->map(function ($section, $index) use ($sectionTests, $resultsByTest, $selectedEvent, $manualTypes, $eventParticipantsCount) {
                $pt = $sectionTests->get($section->id);
                $rows = $pt ? $resultsByTest->get($pt->id, collect()) : collect();
                $isManual = $pt && in_array($pt->type, $manualTypes, true);

                $submittedCount = $rows->count();
                $pendingCount = $isManual
                    ? $rows->filter(fn($r) => $r->grading_status !== PostTestResult::GRADING_PUBLISHED)->count()
                    : 0;
                $gradedCount = $isManual
                    ? $rows->filter(fn($r) => $r->grading_status === PostTestResult::GRADING_PUBLISHED)->count()
                    : $submittedCount;

                $cleanTitle = trim((string) preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $section->name));
                $dayDate = $selectedEvent->start_date
                    ? $selectedEvent->start_date->copy()->addDays($index)->translatedFormat('d M Y')
                    : null;

                return (object) [
                    'day_number'         => $index + 1,
                    'day_date'           => $dayDate,
                    'section'            => $section,
                    'title'              => $cleanTitle !== '' ? $cleanTitle : $section->name,
                    'post_test'          => $pt,
                    'format_label'       => match ($pt?->type) {
                        'essay', 'uraian'       => 'Uraian',
                        'file_upload', 'upload' => 'Upload Berkas',
                        default                 => $pt ? 'Pilihan Ganda' : 'Belum Ada Evaluasi',
                    },
                    'submitted_count'    => $submittedCount,
                    'pending_count'      => $pendingCount,
                    'graded_count'       => $gradedCount,
                    'total_participants' => $eventParticipantsCount,
                ];
            });

            $finalRows = $finalTest ? $resultsByTest->get($finalTest->id, collect()) : collect();
            $finalSubmittedCount = $finalRows->count();
            $finalIsManual = $finalTest && in_array($finalTest->type, $manualTypes, true);

            $finalEvaluation = (object) [
                'title'              => $finalTest?->title ?? ('Evaluasi Akhir: ' . ($selectedEvent->course?->name ?? 'Pelatihan')),
                'post_test'          => $finalTest,
                'format_label'       => match ($finalTest?->type) {
                    'essay', 'uraian'       => 'Uraian',
                    'file_upload', 'upload' => 'Upload Berkas',
                    default                 => $finalTest ? 'Pilihan Ganda (CBT)' : 'Belum Ada Evaluasi',
                },
                'submitted_count'    => $finalSubmittedCount,
                'pending_count'      => $finalIsManual
                    ? $finalRows->filter(fn($r) => $r->grading_status !== PostTestResult::GRADING_PUBLISHED)->count()
                    : 0,
                'graded_count'       => $finalIsManual
                    ? $finalRows->filter(fn($r) => $r->grading_status === PostTestResult::GRADING_PUBLISHED)->count()
                    : $finalSubmittedCount,
                'total_participants' => $eventParticipantsCount,
            ];

            return view('lms::admin-pusat.post-test-submissions.index', [
                'step'             => 'agenda',
                'events'           => null,
                'selectedEvent'    => $selectedEvent,
                'selectedPostTest' => null,
                'dailyAgendas'     => $dailyAgendas,
                'finalEvaluation'  => $finalEvaluation,
                'submissions'      => null,
                'search'           => $search,
                'status'           => $status,
            ]);
        }

        // =========================================================================
        // LANGKAH 3: DASHBOARD TRIAGE, BATCH PROGRESS & PUBLIKASI
        // =========================================================================
        $isManualTest = in_array($selectedPostTest->type, $manualTypes, true);

        $baseScopeQuery = PostTestResult::query()
            ->where('post_test_id', $selectedPostTest->id);

        if ($eventParticipantIds->isNotEmpty()) {
            $baseScopeQuery->whereIn('user_id', $eventParticipantIds);
        } else {
            $baseScopeQuery->whereRaw('1 = 0');
        }

        $triageRows = (clone $baseScopeQuery)->get(['grading_status', 'needs_manual_review', 'ai_score', 'final_score']);
        $triageCounts = [
            'all'          => $triageRows->count(),
            'pending'      => $triageRows->whereIn('grading_status', [PostTestResult::GRADING_PENDING, PostTestResult::GRADING_QUEUED, PostTestResult::GRADING_PROCESSING])->count(),
            'pure_pending' => $triageRows->where('grading_status', PostTestResult::GRADING_PENDING)->count(),
            'needs_review' => $triageRows->filter(fn($r) => $r->grading_status === PostTestResult::GRADING_MANUAL_REVIEW || ($r->needs_manual_review && $r->grading_status === PostTestResult::GRADING_DRAFT_GRADED))->count(),
            'failed'       => $triageRows->where('grading_status', PostTestResult::GRADING_FAILED)->count(),
            'draft_ready'  => $triageRows->filter(fn($r) => in_array($r->grading_status, [PostTestResult::GRADING_DRAFT_GRADED, PostTestResult::GRADING_MANUAL_REVIEW], true) && (!is_null($r->final_score) || !is_null($r->ai_score)))->count(),
            'published'    => $triageRows->where('grading_status', PostTestResult::GRADING_PUBLISHED)->count(),
        ];

        $submissionsQuery = (clone $baseScopeQuery)->with(['user.profile']);

        if ($isManualTest) {
            match ($status) {
                'pending', 'submitted' => $submissionsQuery->whereIn('grading_status', [
                    PostTestResult::GRADING_PENDING,
                    PostTestResult::GRADING_QUEUED,
                    PostTestResult::GRADING_PROCESSING,
                ]),
                'needs_review' => $submissionsQuery->where(function ($q) {
                    $q->where('grading_status', PostTestResult::GRADING_MANUAL_REVIEW)
                        ->orWhere(fn($sq) => $sq->where('grading_status', PostTestResult::GRADING_DRAFT_GRADED)->where('needs_manual_review', true));
                }),
                'failed' => $submissionsQuery->where('grading_status', PostTestResult::GRADING_FAILED),
                'draft_graded' => $submissionsQuery->where('grading_status', PostTestResult::GRADING_DRAFT_GRADED),
                'published', 'graded' => $submissionsQuery->where('grading_status', PostTestResult::GRADING_PUBLISHED),
                default => null,
            };
        }

        $submissionsQuery
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn($pq) => $pq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('instansi', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE
                WHEN grading_status = 'FAILED' THEN 0
                WHEN grading_status = 'MANUAL_REVIEW' OR (grading_status = 'DRAFT_GRADED' AND needs_manual_review = 1) THEN 1
                WHEN grading_status IN ('PENDING', 'QUEUED', 'PROCESSING') THEN 2
                WHEN grading_status = 'DRAFT_GRADED' THEN 3
                ELSE 4 END ASC")
            ->orderByDesc('completed_at')
            ->orderByDesc('updated_at');

        $submissions = $submissionsQuery->paginate(15)->withQueryString();

        $activeBatch = GradingBatch::where('post_test_id', $selectedPostTest->id)
            ->when($selectedEvent->id, fn($q) => $q->where(fn($sq) => $sq->where('bimtek_event_id', $selectedEvent->id)->orWhereNull('bimtek_event_id')))
            ->latest('started_at')
            ->first();

        return view('lms::admin-pusat.post-test-submissions.index', [
            'step'             => 'results',
            'events'           => null,
            'selectedEvent'    => $selectedEvent,
            'selectedPostTest' => $selectedPostTest,
            'dailyAgendas'     => collect(),
            'finalEvaluation'  => null,
            'submissions'      => $submissions,
            'isManualTest'     => $isManualTest,
            'pendingCount'     => $triageCounts['pure_pending'],
            'triageCounts'     => $triageCounts,
            'activeBatch'      => $activeBatch,
            'search'           => $search,
            'status'           => $status,
        ]);
    }

    /**
     * Fase 3: Admin trigger batch penilaian otomatis ("Mulai Penilaian Otomatis").
     */
    public function startBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bimtek_event_id' => 'required|string',
            'post_test_id'    => 'required|string',
        ]);

        $postTest = PostTest::with('questions')->findOrFail($validated['post_test_id']);
        $gradingType = $postTest->resolvedGradingType();

        if (in_array($gradingType, [PostTest::GRADING_AUTO_AI_ESSAY, PostTest::GRADING_AUTO_AI_REPORT], true)
            && trim((string) config('grading.gemini.api_key', '')) === '') {
            ToastMagic::error('GEMINI_API_KEY belum diisi di .env. Silakan konfigurasi API key Gemini terlebih dahulu untuk penilaian otomatis AI.');

            return redirect()->back();
        }

        $batch = $this->gradingBatchService->startBatch(
            $postTest,
            $validated['bimtek_event_id'],
            $request->user()?->id
        );

        if ($batch->total_targets === 0) {
            ToastMagic::info('Tidak ada jawaban peserta berstatus menunggu penilaian (PENDING).');

            return redirect()->back();
        }

        $isExcel = $gradingType === PostTest::GRADING_AUTO_EXCEL;
        $title = $isExcel ? 'Penilaian Otomatis Selesai' : 'Penilaian Otomatis Sedang Diproses';
        $message = $isExcel
            ? "Seluruh {$batch->processed_count} berkas jawaban Excel sudah selesai diperiksa dan disimpan sebagai draf. Silakan tinjau hasilnya terlebih dahulu, lalu klik tombol Publikasikan Semua Nilai agar nilai tampil di halaman peserta."
            : "Pengumpulan tugas telah dikunci sementara agar jawaban tidak berubah. Sebanyak {$batch->total_targets} jawaban peserta sedang diperiksa secara bertahap di latar belakang. Pantau progresnya di halaman ini, lalu tinjau draf nilai sebelum dipublikasikan ke peserta.";

        ToastMagic::success($title);

        return redirect()->back()->with('grading_popup', [
            'title'        => $title,
            'method'       => 'Penilaian Otomatis',
            'student_name' => "{$batch->total_targets} Peserta",
            'score'        => null,
            'is_passed'    => null,
            'summary'      => $isExcel
                ? "{$batch->processed_count} dari {$batch->total_targets} selesai (Draf)"
                : 'Sedang diproses di latar belakang',
            'message'      => $message,
        ]);
    }

    /**
     * Fase 5: Polling status batch (ringan, tiap 5–10 detik).
     */
    public function batchStatus(string $batchId): JsonResponse
    {
        $batch = GradingBatch::findOrFail($batchId);

        return response()->json([
            'id'                    => $batch->id,
            'status'                => $batch->status,
            'total_targets'         => $batch->total_targets,
            'processed_count'       => $batch->processed_count,
            'failed_count'          => $batch->failed_count,
            'consecutive_429_count' => $batch->consecutive_429_count,
            'progress_percent'      => $batch->progress_percent,
            'started_at'            => $batch->started_at?->toIso8601String(),
            'finished_at'           => $batch->finished_at?->toIso8601String(),
        ]);
    }

    /**
     * Fase 5: Lanjutkan batch berstatus PAUSED_QUOTA (hanya memproses sisa PENDING).
     */
    public function resumeBatch(string $batchId): RedirectResponse
    {
        $batch = GradingBatch::findOrFail($batchId);
        $updated = $this->gradingBatchService->resumeBatch($batch);

        ToastMagic::success('Penilaian otomatis berhasil dilanjutkan.');

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Penilaian Otomatis Dilanjutkan',
            'method'       => 'Penilaian Otomatis',
            'student_name' => "{$updated->total_targets} Peserta",
            'score'        => null,
            'is_passed'    => null,
            'summary'      => "{$updated->processed_count} dari {$updated->total_targets} selesai",
            'message'      => 'Sisa jawaban peserta yang belum dinilai telah dimasukkan kembali ke antrean dan sedang diproses otomatis di latar belakang.',
        ]);
    }

    /**
     * Fase 5: Ulangi hanya peserta yang berstatus FAILED (idempoten).
     */
    public function retryFailed(string $batchId): RedirectResponse
    {
        $batch = GradingBatch::findOrFail($batchId);
        $updated = $this->gradingBatchService->retryFailed($batch);

        ToastMagic::success('Jawaban yang gagal telah dimasukkan ulang untuk dinilai.');

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Penilaian Ulang Dimulai',
            'method'       => 'Penilaian Otomatis',
            'student_name' => "{$updated->total_targets} Peserta",
            'score'        => null,
            'is_passed'    => null,
            'summary'      => "{$updated->processed_count} dari {$updated->total_targets} selesai",
            'message'      => 'Hanya jawaban peserta yang sebelumnya gagal diperiksa yang sedang dinilai ulang. Nilai peserta lain yang sudah berhasil tidak berubah.',
        ]);
    }

    /**
     * Fase 5: Publikasikan semua draf nilai ke peserta (PUBLISHED) + perbarui progres kursus.
     */
    public function publishGrades(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bimtek_event_id' => 'required|string',
            'post_test_id'    => 'required|string',
        ]);

        $postTest = PostTest::findOrFail($validated['post_test_id']);
        $publishedCount = $this->gradingBatchService->publishGrades($postTest, $validated['bimtek_event_id']);

        if ($publishedCount === 0) {
            ToastMagic::info('Tidak ada draf nilai yang siap dipublikasikan.');

            return redirect()->back();
        }

        ToastMagic::success("Berhasil mempublikasikan nilai untuk {$publishedCount} peserta.");

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Nilai Berhasil Dipublikasikan',
            'method'       => 'Publikasi Nilai',
            'student_name' => "{$publishedCount} Peserta",
            'score'        => null,
            'is_passed'    => null,
            'summary'      => 'Sudah tampil di halaman peserta',
            'message'      => "Nilai akhir untuk {$publishedCount} peserta kini sudah dapat dilihat oleh masing-masing peserta dan otomatis memperbarui progres kelulusan pelatihan.",
        ]);
    }

    /**
     * Kunci / buka kunci pengumpulan peserta pada evaluasi ini.
     */
    public function toggleLock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'post_test_id' => 'required|string',
        ]);

        $postTest = PostTest::findOrFail($validated['post_test_id']);
        $postTest->update(['is_submission_locked' => !$postTest->is_submission_locked]);

        $msg = $postTest->is_submission_locked
            ? 'Pengumpulan peserta dikunci.'
            : 'Pengumpulan peserta dibuka kembali.';
        ToastMagic::success($msg);

        return redirect()->back();
    }

    /**
     * Edit nilai manual / review triage oleh Admin (simpan sebagai draf atau langsung publikasikan).
     */
    public function grade(Request $request, string $id): RedirectResponse
    {
        $result = PostTestResult::with(['user.profile', 'postTest.course', 'postTest.section.course'])->findOrFail($id);
        $postTest = $result->postTest;

        $validated = $request->validate([
            'score'       => 'required|numeric|min:0|max:100',
            'is_passed'   => 'required|boolean',
            'notes'       => 'nullable|string|max:2000',
            'publish_now' => 'nullable|boolean',
        ], [
            'score.required'     => 'Nilai evaluasi wajib diisi.',
            'score.numeric'      => 'Nilai evaluasi harus berupa angka.',
            'score.min'          => 'Nilai minimal adalah 0.',
            'score.max'          => 'Nilai maksimal adalah 100.',
            'is_passed.required' => 'Status kelulusan wajib ditentukan.',
        ]);

        $publishNow = $request->boolean('publish_now', true);
        $finalScore = round((float) $validated['score'], 2);
        $feedback = trim((string) ($validated['notes'] ?? ''));

        $existingFeedbackJson = is_array($result->ai_feedback_json) ? $result->ai_feedback_json : [];
        if ($feedback !== '') {
            $existingFeedbackJson['overall_feedback'] = $feedback;
        }

        $updates = [
            'final_score'         => $finalScore,
            'ai_feedback_json'    => !empty($existingFeedbackJson) ? $existingFeedbackJson : $result->ai_feedback_json,
            'needs_manual_review' => false,
            'graded_at'           => now(),
        ];

        if ($publishNow) {
            $finalNote = $feedback !== '' ? $feedback : trim((string) ($existingFeedbackJson['overall_feedback'] ?? ''));
            $updates['score'] = (int) round($finalScore);
            $updates['is_passed'] = (bool) $validated['is_passed'];
            $updates['status'] = 'graded';
            $updates['grading_status'] = PostTestResult::GRADING_PUBLISHED;
            $updates['notes'] = $finalNote !== '' ? $finalNote : null;
        } else {
            $updates['grading_status'] = PostTestResult::GRADING_DRAFT_GRADED;
        }

        $result->update($updates);

        if ($publishNow) {
            $course = $postTest?->course ?? $postTest?->section?->course;
            $this->syncStudentCourseProgress($course, $result->user_id);
        }

        $studentName = $result->user?->profile?->full_name ?? $result->user?->name ?? 'Peserta';
        $title = $publishNow ? 'Penilaian Disimpan & Dipublikasikan' : 'Draf Penilaian Disimpan';
        ToastMagic::success($title);

        return redirect()->back()->with('grading_popup', [
            'title'        => $title,
            'method'       => 'Penilaian Manual Instruktur',
            'student_name' => $studentName,
            'score'        => $finalScore,
            'is_passed'    => (bool) $validated['is_passed'],
            'message'      => $publishNow
                ? 'Nilai akhir peserta berhasil disimpan dan dipublikasikan ke halaman peserta.'
                : 'Draf nilai peserta berhasil disimpan. Klik Publikasikan Nilai saat siap ditampilkan ke peserta.',
        ]);
    }

    /**
     * Koreksi otomatis satuan oleh Admin (hasil disimpan sebagai DRAFT_GRADED untuk triage).
     */
    public function autoGrade(string $id): RedirectResponse
    {
        $result = PostTestResult::with([
            'user.profile',
            'postTest.questions',
            'postTest.course',
            'postTest.section.course',
        ])->findOrFail($id);

        $postTest = $result->postTest;
        if (!$postTest) {
            ToastMagic::error('Data evaluasi tidak ditemukan.');

            return redirect()->back();
        }

        $gradingType = $postTest->resolvedGradingType();
        $passingGrade = $postTest->effectivePassingGrade();

        try {
            if ($gradingType === PostTest::GRADING_AUTO_EXCEL) {
                $disk = Storage::disk('public');
                if (empty($postTest->answer_key_file_path) || !$disk->exists($postTest->answer_key_file_path)
                    || empty($result->file_path) || !$disk->exists($result->file_path)) {
                    ToastMagic::error('Berkas Excel peserta atau kunci jawaban tidak ditemukan.');

                    return redirect()->back();
                }

                $templatePath = ($postTest->admin_file_path && $disk->exists($postTest->admin_file_path))
                    ? $disk->path($postTest->admin_file_path)
                    : null;

                $eval = $this->excelGrader->grade(
                    $disk->path($postTest->answer_key_file_path),
                    $disk->path($result->file_path),
                    $templatePath,
                    is_array($postTest->rubric_json) ? $postTest->rubric_json : [],
                    $passingGrade
                );

                $score = (float) $eval['score'];
                $triage = $this->aiGradingService->evaluateAutoTriage($score, $passingGrade, $eval['feedback_json'], false);
                $feedbackJson = array_merge($eval['feedback_json'], [
                    'passing_grade'  => $passingGrade,
                    'triage_reasons' => $triage['reasons'],
                ]);
                $methodLabel = 'Penilaian Otomatis';
                $summaryMessage = $eval['summary_note'] . ' Hasil disimpan sebagai draf, silakan tinjau lalu klik Publikasikan Nilai agar tampil ke peserta.';
            } elseif ($gradingType === PostTest::GRADING_AUTO_AI_ESSAY) {
                $snapshot = $this->gradingBatchService->buildRubricSnapshot($postTest);
                $aiResult = $this->aiGradingService->gradeEssay($postTest, is_array($result->answers) ? $result->answers : [], $snapshot);
                $score = (float) $aiResult['score'];
                $triage = $this->aiGradingService->evaluateAutoTriage($score, $passingGrade, $aiResult, false);
                $feedbackJson = array_merge($aiResult, [
                    'grader'         => $gradingType,
                    'passing_grade'  => $passingGrade,
                    'triage_reasons' => $triage['reasons'],
                ]);
                $methodLabel = 'Penilaian Otomatis';
                $summaryMessage = ($aiResult['overall_feedback'] ?: 'Jawaban uraian berhasil diperiksa secara otomatis.')
                    . ' Hasil disimpan sebagai draf, silakan tinjau sebelum dipublikasikan ke peserta.';
            } elseif ($gradingType === PostTest::GRADING_AUTO_AI_REPORT) {
                $cleanText = trim((string) ($result->extracted_text ?? ''));
                $isTruncated = (bool) $result->is_truncated;

                if ($cleanText === '' && !empty($result->file_path)) {
                    $extracted = $this->documentExtractor->extractFromPublicDisk((string) $result->file_path);
                    $preprocessed = $this->textPreprocessor->preprocess($extracted['text'], $postTest->template_extracted_text);
                    $cleanText = $preprocessed['text'];
                    $isTruncated = $preprocessed['is_truncated'];
                    $result->update(['extracted_text' => $cleanText, 'is_truncated' => $isTruncated]);
                }

                $snapshot = $this->gradingBatchService->buildRubricSnapshot($postTest);
                $aiResult = $this->aiGradingService->gradeReport($postTest, $cleanText, $snapshot);
                $score = (float) $aiResult['score'];
                $triage = $this->aiGradingService->evaluateAutoTriage($score, $passingGrade, $aiResult, $isTruncated);
                $feedbackJson = array_merge($aiResult, [
                    'grader'         => $gradingType,
                    'passing_grade'  => $passingGrade,
                    'triage_reasons' => $triage['reasons'],
                ]);
                $methodLabel = 'Penilaian Otomatis';
                $summaryMessage = ($aiResult['overall_feedback'] ?: 'Laporan peserta berhasil diperiksa secara otomatis.')
                    . ' Hasil disimpan sebagai draf, silakan tinjau sebelum dipublikasikan ke peserta.';
            } else {
                ToastMagic::error('Evaluasi ini disetel untuk penilaian manual.');

                return redirect()->back();
            }

            $result->update([
                'ai_score'            => $score,
                'final_score'         => $score,
                'ai_feedback_json'    => $feedbackJson,
                'needs_manual_review' => $triage['needs_manual_review'],
                'grading_status'      => PostTestResult::GRADING_DRAFT_GRADED,
                'last_error_message'  => null,
                'graded_at'           => now(),
            ]);

            $studentName = $result->user?->profile?->full_name ?? $result->user?->name ?? 'Peserta';
            $isPassed = $score >= $passingGrade;

            ToastMagic::success("Draf penilaian otomatis berhasil dibuat: Skor {$score}");

            return redirect()->back()->with('grading_popup', [
                'title'        => 'Penilaian Otomatis Selesai',
                'method'       => $methodLabel,
                'student_name' => $studentName,
                'score'        => $score,
                'is_passed'    => $isPassed,
                'message'      => $summaryMessage,
            ]);
        } catch (\Throwable $e) {
            $result->update([
                'grading_status'      => PostTestResult::GRADING_FAILED,
                'needs_manual_review' => true,
                'last_error_message'  => $e->getMessage(),
            ]);

            ToastMagic::error('Koreksi otomatis gagal: ' . $e->getMessage());

            return redirect()->back();
        }
    }

    private function syncStudentCourseProgress($course, int|string|null $userId): void
    {
        if (!$course || !$userId) {
            return;
        }

        $course->loadMissing('sections.contents');
        $newProgress = $this->courseService->calculateDynamicProgress($course, $userId);

        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->update([
                'progress'     => $newProgress,
                'status'       => $newProgress >= 100 ? 'completed' : 'in_progress',
                'completed_at' => $newProgress >= 100 ? now() : null,
                'updated_at'   => now(),
            ]);
    }
}
