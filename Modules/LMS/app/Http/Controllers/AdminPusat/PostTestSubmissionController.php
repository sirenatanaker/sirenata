<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\CourseService;
use Modules\LMS\Services\PostTestService;

class PostTestSubmissionController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private PostTestService $postTestService,
    ) {}

    /**
     * Alur 3 langkah pengelolaan jawaban evaluasi Admin Pusat:
     * 1. Pilih Event Bimtek
     * 2. Pilih Agenda Harian atau Evaluasi Akhir (jika sudah ada data)
     * 3. Lihat & nilai hasil evaluasi peserta pada agenda/evaluasi terpilih
     */
    public function index(Request $request): View
    {
        $bimtekEventId = $request->query('bimtek_event_id');
        $postTestId    = $request->query('post_test_id');
        $courseId      = $request->query('course_id');
        $status        = $request->query('status', 'all'); // 'all' | 'submitted' | 'graded'
        $search        = trim((string) $request->query('search', ''));

        $manualTypes = ['essay', 'uraian', 'file_upload', 'upload'];

        // Resolusi otomatis jika diakses lewat pintasan Detail Kursus (?course_id=...&post_test_id=...)
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
                    ->get(['post_test_id', 'user_id', 'status'])
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
                        && ($r->status === 'submitted' || is_null($r->status));
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

        // Ambil user IDs peserta yang terdaftar pada event terpilih
        $eventParticipantIds = DB::table('course_student')
            ->where('bimtek_event_id', $selectedEvent->id)
            ->pluck('user_id');

        $eventParticipantsCount = $eventParticipantIds->count();

        // =========================================================================
        // LANGKAH 2: PILIH AGENDA HARIAN / EVALUASI AKHIR PADA EVENT TERPILIH
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
                    ->get(['post_test_id', 'status'])
                    ->groupBy('post_test_id')
                : collect();

            $dailyAgendas = $sections->values()->map(function ($section, $index) use ($sectionTests, $resultsByTest, $selectedEvent, $manualTypes, $eventParticipantsCount) {
                $pt = $sectionTests->get($section->id);
                $rows = $pt ? $resultsByTest->get($pt->id, collect()) : collect();
                $isManual = $pt && in_array($pt->type, $manualTypes, true);

                $submittedCount = $rows->count();
                $pendingCount = $isManual
                    ? $rows->filter(fn($r) => $r->status === 'submitted' || is_null($r->status))->count()
                    : 0;
                $gradedCount = $isManual
                    ? $rows->filter(fn($r) => $r->status === 'graded')->count()
                    : $submittedCount;

                $cleanTitle = trim((string) preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $section->name));
                $dayDate = $selectedEvent->start_date
                    ? $selectedEvent->start_date->copy()->addDays($index)->translatedFormat('d M Y')
                    : null;

                return (object) [
                    'day_number'        => $index + 1,
                    'day_date'          => $dayDate,
                    'section'           => $section,
                    'title'             => $cleanTitle !== '' ? $cleanTitle : $section->name,
                    'post_test'         => $pt,
                    'format_label'      => match ($pt?->type) {
                        'essay', 'uraian'       => 'Uraian',
                        'file_upload', 'upload' => 'Upload Berkas',
                        default                 => $pt ? 'Pilihan Ganda' : 'Belum Ada Evaluasi',
                    },
                    'submitted_count'   => $submittedCount,
                    'pending_count'     => $pendingCount,
                    'graded_count'      => $gradedCount,
                    'total_participants'=> $eventParticipantsCount,
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
                    ? $finalRows->filter(fn($r) => $r->status === 'submitted' || is_null($r->status))->count()
                    : 0,
                'graded_count'       => $finalIsManual
                    ? $finalRows->filter(fn($r) => $r->status === 'graded')->count()
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
        // LANGKAH 3: DAFTAR HASIL & PENILAIAN EVALUASI PADA AGENDA / EVALUASI AKHIR
        // =========================================================================
        $isManualTest = in_array($selectedPostTest->type, $manualTypes, true);

        $submissionsQuery = PostTestResult::query()
            ->with(['user.profile'])
            ->where('post_test_id', $selectedPostTest->id);

        if ($eventParticipantIds->isNotEmpty()) {
            $submissionsQuery->whereIn('user_id', $eventParticipantIds);
        } else {
            $submissionsQuery->whereRaw('1 = 0');
        }

        if ($isManualTest) {
            $submissionsQuery
                ->when($status === 'submitted', fn($q) => $q->where(fn($sq) => $sq->where('status', 'submitted')->orWhereNull('status')))
                ->when($status === 'graded', fn($q) => $q->where('status', 'graded'));
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
            ->orderByRaw("CASE WHEN status = 'graded' THEN 1 ELSE 0 END ASC")
            ->orderByDesc('completed_at')
            ->orderByDesc('updated_at');

        $submissions = $submissionsQuery->paginate(15)->withQueryString();

        $pendingCount = ($isManualTest && $eventParticipantIds->isNotEmpty())
            ? PostTestResult::where('post_test_id', $selectedPostTest->id)
                ->whereIn('user_id', $eventParticipantIds)
                ->where(fn($q) => $q->where('status', 'submitted')->orWhereNull('status'))
                ->count()
            : 0;

        return view('lms::admin-pusat.post-test-submissions.index', [
            'step'             => 'results',
            'events'           => null,
            'selectedEvent'    => $selectedEvent,
            'selectedPostTest' => $selectedPostTest,
            'dailyAgendas'     => collect(),
            'finalEvaluation'  => null,
            'submissions'      => $submissions,
            'isManualTest'     => $isManualTest,
            'pendingCount'     => $pendingCount,
            'search'           => $search,
            'status'           => $status,
        ]);
    }

    /**
     * Simpan nilai, status kelulusan, dan catatan instruktur untuk jawaban evaluasi peserta.
     */
    public function grade(Request $request, string $id): RedirectResponse
    {
        $result = PostTestResult::with(['user.profile', 'postTest.course', 'postTest.section.course'])->findOrFail($id);
        $postTest = $result->postTest;

        $validated = $request->validate([
            'score'     => 'required|integer|min:0|max:100',
            'is_passed' => 'required|boolean',
            'notes'     => 'nullable|string|max:1000',
        ], [
            'score.required'     => 'Nilai evaluasi wajib diisi.',
            'score.integer'      => 'Nilai evaluasi harus berupa angka bulat.',
            'score.min'          => 'Nilai minimal adalah 0.',
            'score.max'          => 'Nilai maksimal adalah 100.',
            'is_passed.required' => 'Status kelulusan wajib ditentukan.',
        ]);

        $feedback = trim((string) ($validated['notes'] ?? ''));

        $result->update([
            'score'     => (int) $validated['score'],
            'is_passed' => (bool) $validated['is_passed'],
            'notes'     => $feedback !== '' ? $feedback : ($result->status === 'graded' ? null : $result->notes),
            'status'    => 'graded',
        ]);

        $course = $postTest?->course ?? $postTest?->section?->course;
        $this->syncStudentCourseProgress($course, $result->user_id);

        $studentName = $result->user?->profile?->full_name ?? $result->user?->name ?? 'Peserta';
        ToastMagic::success('Penilaian dan umpan balik evaluasi peserta berhasil disimpan.');

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Penilaian Berhasil Disimpan',
            'method'       => 'Penilaian Manual',
            'student_name' => $studentName,
            'score'        => (int) $validated['score'],
            'is_passed'    => (bool) $validated['is_passed'],
            'message'      => 'Nilai dan umpan balik evaluasi peserta berhasil disimpan.',
        ]);
    }

    /**
     * Jalankan koreksi otomatis sistem untuk satu hasil evaluasi peserta.
     */
    public function autoGrade(string $id): RedirectResponse
    {
        $result = PostTestResult::with([
            'user.profile',
            'postTest.questions',
            'postTest.course',
            'postTest.section.course',
        ])->findOrFail($id);

        $eval = $this->runAutoGradeOnResult($result);

        if (!$eval) {
            ToastMagic::error('Koreksi otomatis tidak dapat dijalankan karena kunci jawaban belum tersedia atau berkas tidak ditemukan.');

            return redirect()->back();
        }

        $studentName = $result->user?->profile?->full_name ?? $result->user?->name ?? 'Peserta';
        $statusLabel = $eval['is_passed'] ? 'Lulus' : 'Perlu Revisi';
        ToastMagic::success("Koreksi otomatis berhasil: Skor {$eval['score']} ({$statusLabel}).");

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Koreksi Otomatis Berhasil',
            'method'       => 'Koreksi Otomatis Sistem',
            'student_name' => $studentName,
            'score'        => (int) $eval['score'],
            'is_passed'    => (bool) $eval['is_passed'],
            'message'      => $eval['notes'] ?? 'Jawaban peserta berhasil dikoreksi otomatis oleh sistem.',
        ]);
    }

    /**
     * Jalankan koreksi otomatis secara massal untuk seluruh jawaban yang belum dinilai pada evaluasi terpilih.
     */
    public function autoGradeBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bimtek_event_id' => 'required|string',
            'post_test_id'    => 'required|string',
        ]);

        $eventParticipantIds = DB::table('course_student')
            ->where('bimtek_event_id', $validated['bimtek_event_id'])
            ->pluck('user_id');

        if ($eventParticipantIds->isEmpty()) {
            ToastMagic::info('Tidak ada peserta terdaftar pada event ini.');

            return redirect()->back();
        }

        $pendingResults = PostTestResult::with([
            'postTest.questions',
            'postTest.course',
            'postTest.section.course',
        ])
            ->where('post_test_id', $validated['post_test_id'])
            ->whereIn('user_id', $eventParticipantIds)
            ->where(fn($q) => $q->where('status', 'submitted')->orWhereNull('status'))
            ->get();

        if ($pendingResults->isEmpty()) {
            ToastMagic::info('Tidak ada jawaban peserta yang menunggu penilaian.');

            return redirect()->back();
        }

        $gradedCount = 0;
        $passedCount = 0;
        $revisiCount = 0;

        foreach ($pendingResults as $res) {
            $eval = $this->runAutoGradeOnResult($res);
            if ($eval) {
                $gradedCount++;
                if ($eval['is_passed']) {
                    $passedCount++;
                } else {
                    $revisiCount++;
                }
            }
        }

        if ($gradedCount === 0) {
            ToastMagic::error('Koreksi otomatis tidak dapat dijalankan karena kunci jawaban belum dikonfigurasi pada evaluasi ini.');

            return redirect()->back();
        }

        ToastMagic::success("Koreksi otomatis selesai untuk {$gradedCount} peserta ({$passedCount} Lulus, {$revisiCount} Perlu Revisi).");

        return redirect()->back()->with('grading_popup', [
            'title'        => 'Koreksi Otomatis Massal Selesai',
            'method'       => 'Koreksi Otomatis Sistem',
            'student_name' => "{$gradedCount} Peserta",
            'score'        => null,
            'is_passed'    => null,
            'summary'      => "{$passedCount} Lulus, {$revisiCount} Perlu Revisi",
            'message'      => "Seluruh {$gradedCount} jawaban peserta yang menunggu penilaian telah berhasil dikoreksi otomatis oleh sistem.",
        ]);
    }

    private function runAutoGradeOnResult(PostTestResult $result): ?array
    {
        $postTest = $result->postTest;
        if (!$postTest) {
            return null;
        }

        if ($postTest->is_file_upload) {
            $eval = $this->postTestService->autoGradeFileUpload($postTest, (string) $result->file_path);
        } elseif ($postTest->is_essay) {
            $eval = $this->postTestService->autoGradeEssay($postTest, is_array($result->answers) ? $result->answers : []);
        } else {
            return null;
        }

        if (empty($eval['auto_graded'])) {
            return null;
        }

        $existingNote = trim((string) ($result->notes ?? ''));
        $participantNote = '';
        if ($existingNote !== '') {
            if (preg_match('/Catatan Peserta:\s*(.+)$/s', $existingNote, $matches)) {
                $participantNote = trim($matches[1]);
            } elseif (!str_contains($existingNote, '[Koreksi Otomatis Sistem]')) {
                $participantNote = $existingNote;
            }
        }

        $finalNotes = trim(($eval['notes'] ?? '') . ($participantNote !== '' ? "\nCatatan Peserta: " . $participantNote : ''));

        $result->update([
            'score'     => (int) $eval['score'],
            'is_passed' => (bool) $eval['is_passed'],
            'notes'     => $finalNotes !== '' ? $finalNotes : null,
            'status'    => 'graded',
        ]);

        $course = $postTest->course ?? $postTest->section?->course;
        $this->syncStudentCourseProgress($course, $result->user_id);

        return $eval;
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
