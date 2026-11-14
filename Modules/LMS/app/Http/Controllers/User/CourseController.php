<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekInstructorEvaluation;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Models\SectionZoomProof;
use Modules\LMS\Models\StudentContentProgress;
use Modules\LMS\Services\Api\CourseProgressService;
use Modules\LMS\Services\CertificateService;
use Modules\LMS\Services\CourseService;
use Modules\LMS\Services\PostTestService;

class CourseController extends Controller
{
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';

    public function __construct(
        private CourseService $courseService,
        private CertificateService $certificateService,
        private CourseProgressService $courseProgressService,
        private PostTestService $postTestService,
    ) {}

    public function allMyCourse(Request $request): View
    {
        $result = $this->courseService->myCourses(
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('row_per_page', 11),
            tab: 'all',
        );

        return view('lms::user.course.my-course', [
            'courses' => $result['data'],
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseProgress(Request $request): View
    {
        $result = $this->courseService->myCourses(
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('row_per_page', 11),
            tab: 'progress',
        );

        return view('lms::user.course.my-course-progress', [
            'courses' => $result['data'],
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseFinish(Request $request): View
    {
        $result = $this->courseService->myCourses(
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('row_per_page', 11),
            tab: 'finish',
        );

        return view('lms::user.course.my-course-completed', [
            'courses' => $result['data'],
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseDetail(string $slug): View
    {
        $result = $this->courseService->getCourseDetailSlug($slug);
        $course = $result['data'] ?? null;

        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        $userId = auth()->id();
        $sectionProofs = [];
        if ($userId) {
            $sectionProofs = SectionZoomProof::where('user_id', $userId)
                ->pluck('status', 'course_section_id')
                ->toArray();
        }

        return view('lms::user.course.my-course-detail', [
            'course'                       => $course,
            'activeBimtek'                 => $course->active_bimtek,
            'isBimtekEvent'                => $course->is_bimtek_event,
            'formattedPeriod'              => $course->formatted_period,
            'isWaitingForStart'            => $course->is_waiting_for_start,
            'canReviewAll'                 => $course->can_review_all,
            'isCourseAlreadyFinished'      => $course->is_course_already_finished,
            'isEventEnded'                 => $course->is_event_ended,
            'isZoomAttended'               => $course->is_zoom_attended,
            'hasJoinedZoom'                => $course->has_joined_zoom,
            'isZoomStarted'                => $course->is_zoom_started,
            'isSubmittedToPPSDM'           => $course->is_submitted_to_ppsdm,
            'isLastDayReached'             => $course->is_last_day_reached,
            'isEvaluasiNarasumberLocked'   => $course->is_evaluasi_narasumber_locked,
            'evaluasiNarasumberLockReason' => $course->evaluasi_narasumber_lock_reason,
            'isEvaluasiAkhirLocked'        => $course->is_evaluasi_akhir_locked,
            'firstIncompleteSectionId'     => $course->first_incomplete_section_id,
            'remainingItems'               => $course->remaining_items,
            'progressBarClass'             => $course->progress_bar_class,
            'progressTextClass'            => $course->progress_text_class,
            'totalContents'                => $course->total_contents,
            'completedContents'            => $course->completed_count,
            'areAllTopicsCompleted'        => $course->are_all_topics_completed,
            'totalTests'                   => $course->total_tests,
            'passedTests'                  => $course->passed_tests,
            'totalItems'                   => $course->total_items,
            'completedItems'               => $course->completed_items,
            'currentProgress'              => $course->current_progress,
            'isFullyCompleted'             => $course->is_fully_completed,
            'evaluasiAkhir'                => $course->evaluasi_akhir,
            'isEvaluasiAkhirCompleted'     => $course->is_evaluasi_akhir_completed,
            'isEvaluasiNarasumberCompleted' => $course->is_evaluasi_narasumber_completed,
            'existingEvaluasiNarasumber'   => $course->existing_evaluasi_narasumber,
            'sectionProofs'                => $sectionProofs,
            'success'                      => $result['success'],
            'message'                      => $result['message'],
        ]);
    }

    public function showSection(Request $request, string $slug, string $sectionId): View|RedirectResponse
    {
        $result = $this->courseService->getCourseDetailSlug($slug);
        $course = $result['data'] ?? null;

        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        $sections = collect(data_get($course, 'sections', []));
        $section = $sections->first(function ($s) use ($sectionId) {
            return (string) (data_get($s, 'id') ?? data_get($s, 'section_id')) === (string) $sectionId;
        });

        if (!$section) {
            abort(404, 'Modul tidak ditemukan');
        }

        $sectionIndex = $sections->search(function ($s) use ($sectionId) {
            return (string) (data_get($s, 'id') ?? data_get($s, 'section_id')) === (string) $sectionId;
        });

        $isLocked = data_get($section, 'is_locked', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);

        if ($isWaitingForStart) {
            ToastMagic::error('Kelas pelatihan belum dimulai. Modul akan terbuka sesuai jadwal pelaksanaan.');
            return redirect()->route('user.course.my-course.detail', $slug);
        }

        if ($isLocked) {
            ToastMagic::error('Modul ini masih terkunci. Selesaikan modul sebelumnya terlebih dahulu.');
            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $sectionDetail = $this->courseService->getSectionDetailForUser(
            $course,
            $section,
            $course->active_bimtek,
            $request->user(),
            $sectionIndex !== false ? (int) $sectionIndex : 0
        );

        return view('lms::user.course.section-show', array_merge([
            'course'       => $course,
            'section'      => $section,
            'sectionIndex' => $sectionIndex !== false ? (int) $sectionIndex : 0,
            'slug'         => $slug,
            'activeBimtek' => $course->active_bimtek,
        ], $sectionDetail));
    }

    public function submitAssignment(Request $request, string $slug, string $sectionId): RedirectResponse
    {
        $request->validate([
            'assignment_file' => 'required|file|mimes:pdf,docx,doc,zip,rar,jpg,png|max:10240',
            'notes'           => 'nullable|string|max:500',
        ], [
            'assignment_file.required' => 'Harap pilih berkas jawaban tugas yang ingin diunggah.',
            'assignment_file.mimes'    => 'Format berkas jawaban harus berupa PDF, DOCX, DOC, atau ZIP.',
            'assignment_file.max'      => 'Ukuran berkas jawaban maksimal 10 MB.',
        ]);

        $userId = $request->user()?->id;
        $file = $request->file('assignment_file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store("assignments/{$userId}", 'public');

        $postTest = PostTest::where('course_section_id', $sectionId)->first();
        if ($postTest && $userId) {
            \Modules\LMS\Models\PostTestResult::updateOrCreate(
                [
                    'post_test_id' => $postTest->id,
                    'user_id'      => $userId,
                ],
                [
                    'file_path'    => $path,
                    'file_name'    => $originalName,
                    'notes'        => $request->input('notes'),
                    'status'       => 'submitted',
                    'is_passed'    => true,
                    'score'        => 100,
                    'completed_at' => now(),
                ]
            );
        }

        ToastMagic::success('Berkas tugas evaluasi berhasil dikumpulkan!');
        return redirect()->back();
    }

    public function submitEssay(Request $request, string $slug, string $sectionId): RedirectResponse
    {
        $request->validate([
            'essay_answers'   => 'required|array|min:1',
            'essay_answers.*' => 'required|string|min:5',
        ], [
            'essay_answers.required'   => 'Harap isi seluruh jawaban uraian.',
            'essay_answers.*.required' => 'Setiap soal uraian wajib dijawab.',
            'essay_answers.*.min'      => 'Jawaban uraian minimal 5 karakter.',
        ]);

        $userId = $request->user()?->id;
        $postTest = PostTest::where('course_section_id', $sectionId)->first();
        if ($postTest && $userId) {
            \Modules\LMS\Models\PostTestResult::updateOrCreate(
                [
                    'post_test_id' => $postTest->id,
                    'user_id'      => $userId,
                ],
                [
                    'answers'      => $request->input('essay_answers'),
                    'status'       => 'submitted',
                    'is_passed'    => true,
                    'score'        => 85,
                    'completed_at' => now(),
                ]
            );

            $course = \Modules\LMS\Models\Course::where('slug', $slug)->first();
            if ($course) {
                $newProgress = $this->courseService->calculateDynamicProgress($course, $userId);
                DB::table('course_student')
                    ->where('course_id', $course->id)
                    ->where('user_id', $userId)
                    ->update([
                        'progress' => $newProgress,
                        'status'   => $newProgress >= 100 ? 'completed' : 'in_progress',
                    ]);
            }
        }

        ToastMagic::success('Jawaban uraian evaluasi berhasil dikirim dan menunggu penilaian pengajar!');
        return redirect()->back();
    }

    public function uploadZoomProof(Request $request, string $slug, string $sectionId): RedirectResponse
    {
        $request->validate([
            'proof_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp,docx,doc,zip,rar|max:10240',
            'drive_link' => 'nullable|url|max:1000',
            'notes'      => 'nullable|string|max:500',
        ], [
            'proof_file.mimes' => 'Format berkas harus berupa JPG, PNG, WEBP, PDF, DOCX, atau ZIP.',
            'proof_file.max'   => 'Ukuran berkas maksimal 10 MB.',
            'drive_link.url'   => 'Format tautan Google Drive harus berupa URL yang valid (diawali https://).',
        ]);

        if (!$request->hasFile('proof_file') && empty(trim((string) $request->input('drive_link'))) && empty(trim((string) $request->input('notes')))) {
            ToastMagic::error('Harap unggah berkas bukti atau isi keterangan konfirmasi.');
            return redirect()->back();
        }

        $userId = $request->user()->id;
        $zoomProof = SectionZoomProof::firstOrNew([
            'course_section_id' => $sectionId,
            'user_id'           => $userId,
        ]);

        if ($request->hasFile('proof_file')) {
            if ($zoomProof->file_path && Storage::disk('public')->exists($zoomProof->file_path)) {
                Storage::disk('public')->delete($zoomProof->file_path);
            }
            $filePath = $request->file('proof_file')->store("zoom-proofs/{$userId}", 'public');
            $zoomProof->file_path = $filePath;
        }

        if ($request->filled('drive_link')) {
            $zoomProof->drive_link = trim((string) $request->input('drive_link'));
        }

        if ($request->has('notes')) {
            $zoomProof->notes = $request->input('notes');
        }

        $zoomProof->status = 'submitted';
        $zoomProof->submitted_at = now();
        $zoomProof->save();

        ToastMagic::success('Bukti pendukung presensi berhasil disimpan!');
        return redirect()->back();
    }

    public function joinZoom(Request $request, string $slug): RedirectResponse
    {
        $course = \Modules\LMS\Models\Course::where('slug', $slug)->firstOrFail();
        $activeBimtek = \Modules\LMS\Models\BimtekEvent::with('sessions')->where('course_id', $course->id)->latest()->first();

        $sessionId = $request->query('session_id');
        $targetSession = null;
        if ($sessionId && $activeBimtek) {
            $targetSession = $activeBimtek->sessions->firstWhere('id', $sessionId);
        }

        $zoomUrl = $targetSession?->effective_zoom_url ?: $activeBimtek?->zoom_url;

        if (!$activeBimtek || empty($zoomUrl)) {
            ToastMagic::error('Tautan ruang Zoom belum tersedia.');
            return redirect()->back();
        }

        // Cek jika gelombang bimtek belum dibuka/dimulai
        if ($activeBimtek->start_date && now()->lessThan($activeBimtek->start_date)) {
            ToastMagic::error('Sesi Live Zoom belum dibuka. Pelatihan gelombang ini dimulai pada tanggal ' . $activeBimtek->start_date->translatedFormat('d F Y') . '.');
            return redirect()->back();
        }

        $zoomEnd = $targetSession?->end_time ?: $activeBimtek->zoom_end_at;
        if ($zoomEnd && now()->greaterThan($zoomEnd)) {
            ToastMagic::error('Sesi Live Zoom telah berakhir pada ' . $zoomEnd->translatedFormat('d F Y, H:i') . ' WIB.');
            return redirect()->back();
        }

        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'zoom_joined_at' => now(),
            ]);

        return redirect()->away($zoomUrl);
    }

    public function attendZoom(Request $request, string $slug): RedirectResponse
    {
        $course = \Modules\LMS\Models\Course::where('slug', $slug)->firstOrFail();
        $activeBimtek = \Modules\LMS\Models\BimtekEvent::with('sessions')->where('course_id', $course->id)->latest()->first();

        $enrollment = DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $now = now();
        $sessionId = $request->input('session_id');
        $targetSession = null;
        if ($sessionId && $activeBimtek) {
            $targetSession = $activeBimtek->sessions->firstWhere('id', $sessionId);
        }

        if ($targetSession) {
            // Validasi waktu presensi sesi harian tertentu
            $presensiStart = $targetSession->effective_attendance_start_at;
            $presensiEnd = $targetSession->effective_attendance_end_at;
            $sessionTitle = $targetSession->title ?: 'Sesi Zoom';

            if ($presensiStart && $now->lessThan($presensiStart)) {
                $formattedStart = $targetSession->attendance_schedule_formatted;
                ToastMagic::error("Presensi {$sessionTitle} belum dibuka. Konfirmasi dibuka pada {$formattedStart}.");
                return redirect()->back();
            }

            if ($presensiEnd && $now->greaterThan($presensiEnd)) {
                ToastMagic::error("Waktu presensi {$sessionTitle} telah berakhir pada " . $presensiEnd->translatedFormat('d F Y, H:i') . " WIB.");
                return redirect()->back();
            }

            // Catat kehadiran di tabel bimtek_session_attendances
            \Modules\LMS\Models\BimtekSessionAttendance::firstOrCreate([
                'bimtek_event_session_id' => $targetSession->id,
                'user_id'                 => $request->user()->id,
            ], [
                'bimtek_event_id'         => $activeBimtek->id,
                'course_id'               => $course->id,
                'attended_at'             => now(),
                'ip_address'              => $request->ip(),
                'user_agent'              => $request->userAgent(),
            ]);
        } else {
            // Fallback: Event level single-session
            $presensiStart = $activeBimtek?->zoom_attendance_start_at ?? $activeBimtek?->zoom_scheduled_at ?? $activeBimtek?->start_date;
            $presensiEnd = $activeBimtek?->zoom_attendance_end_at ?? $activeBimtek?->zoom_end_at;

            if ($presensiStart && $now->lessThan($presensiStart)) {
                $formattedStart = $activeBimtek?->attendance_schedule_formatted ?? $presensiStart->translatedFormat('d F Y, H:i') . ' WIB';
                ToastMagic::error('Presensi belum dibuka. Konfirmasi kehadiran dibuka pada jadwal presensi (' . $formattedStart . ').');
                return redirect()->back();
            }

            if ($presensiEnd && $now->greaterThan($presensiEnd)) {
                ToastMagic::error('Waktu presensi tatap muka telah berakhir pada ' . $presensiEnd->translatedFormat('d F Y, H:i') . ' WIB.');
                return redirect()->back();
            }
        }

        // Update status umum kehadiran Zoom pada pivot course_student
        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'zoom_joined_at'   => $enrollment?->zoom_joined_at ?: now(),
                'zoom_attended'    => true,
                'zoom_attended_at' => now(),
            ]);

        $newProgress = $this->courseService->calculateDynamicProgress($course, $request->user()->id);
        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'progress' => $newProgress,
                'status'   => $newProgress >= 100 ? 'completed' : 'in_progress',
            ]);

        $evaluasiAkhir = PostTest::where('course_id', $course->id)->whereNull('course_section_id')->first();
        $canTakeFinalTest = $evaluasiAkhir && $this->courseService->canAccessTest($evaluasiAkhir);

        $successMsg = $targetSession
            ? "Presensi {$targetSession->title} berhasil dikonfirmasi!"
            : "Presensi sesi tatap muka berhasil dikonfirmasi!";

        if ($canTakeFinalTest) {
            $successMsg .= " Tahap Evaluasi Akhir kini telah terbuka.";
        }

        ToastMagic::success($successMsg);

        return redirect()->back();
    }

    public function submitEvaluasiNarasumber(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'rating_materi'      => 'required|integer|min:1|max:5',
            'rating_penyampaian' => 'required|integer|min:1|max:5',
            'rating_fasilitasi'  => 'required|integer|min:1|max:5',
            'feedback'           => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu.',
            ], 401);
        }

        $course = Course::where('slug', $slug)->firstOrFail();

        // Cari enrollment peserta
        $enrollment = $course->students()->wherePivot('user_id', $user->id)->first();
        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum terdaftar pada pelatihan ini.',
            ], 403);
        }

        // Ambil event bimtek aktif atau dari pivot
        $bimtekEventId = $enrollment->pivot->bimtek_event_id
            ?? BimtekEvent::where('course_id', $course->id)->latest()->value('id');

        if (!$bimtekEventId) {
            return response()->json([
                'success' => false,
                'message' => 'Event Bimtek tidak ditemukan untuk pelatihan ini.',
            ], 404);
        }

        $evaluation = BimtekInstructorEvaluation::updateOrCreate(
            [
                'bimtek_event_id' => $bimtekEventId,
                'user_id'         => $user->id,
            ],
            [
                'course_id'          => $course->id,
                'rating_materi'      => $validated['rating_materi'],
                'rating_penyampaian' => $validated['rating_penyampaian'],
                'rating_fasilitasi'  => $validated['rating_fasilitasi'],
                'feedback'           => $validated['feedback'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Evaluasi narasumber & penyelenggaraan berhasil dikirimkan.',
            'data'    => [
                'id'                 => $evaluation->id,
                'rating_materi'      => $evaluation->rating_materi,
                'rating_penyampaian' => $evaluation->rating_penyampaian,
                'rating_fasilitasi'  => $evaluation->rating_fasilitasi,
                'feedback'           => $evaluation->feedback,
                'average'            => $evaluation->average_rating,
            ],
        ]);
    }

    public function generateCertificate(Request $request, string $slug): RedirectResponse
    {
        $result = $this->certificateService->generateStudentCertificate($request->user(), $slug);

        if ($result['success']) {
            ToastMagic::success($result['message']);
        } else {
            ToastMagic::error($result['message']);
        }

        return redirect()->back();
    }

    public function completeContent(Request $request, SectionContent $content): RedirectResponse
    {
        if (!$this->courseService->canAccessContent($content)) {
            ToastMagic::error('Selesaikan topik sebelumnya terlebih dahulu.');

            return redirect()->back();
        }

        $result = $this->courseProgressService->completeContent($content, (string) $request->user()->id);

        if (!$result['success']) {
            ToastMagic::error($result['message']);

            return redirect()->back();
        }

        ToastMagic::success(
            $result['is_completed']
                ? 'Selamat! Anda telah menyelesaikan semua materi kursus ini! 🎉'
                : 'Materi berhasil ditandai selesai. Progress: ' . $result['progress'] . '%'
        );

        return redirect()->back();
    }

    public function showContent(string $slug, string $contentId): View|RedirectResponse
    {
        $result = $this->courseService->getCourseDetailSlug($slug);
        $courseData = $result['data'] ?? null;
        $content = $this->courseService->getStudentContentForShow($contentId);

        if ($courseData && !empty($courseData->is_waiting_for_start)) {
            $startDateStr = $courseData->active_bimtek?->start_date
                ? $courseData->active_bimtek->start_date->translatedFormat('d F Y')
                : 'jadwal pelaksanaan';
            ToastMagic::error("Materi pembelajaran belum dapat diakses. Kelas pelatihan akan dibuka serentak pada {$startDateStr}.");

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        if (!$this->courseService->canAccessContent($content)) {
            ToastMagic::error('Topik ini masih terkunci. Selesaikan topik sebelumnya terlebih dahulu.');

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $nextUrl = $this->getNextItemRoute($content, (string) auth()->id());

        $backToSectionUrl = !empty(data_get($content, 'course_section_id'))
            ? route('user.course.section.show', ['slug' => $slug ?? data_get($courseData, 'slug', ''), 'sectionId' => data_get($content, 'course_section_id')])
            : route('user.course.my-course.detail', $slug ?? data_get($courseData, 'slug', ''));

        return view('lms::user.course.content-show', [
            'course'           => $result['data'] ?? null,
            'content'          => $content,
            'slug'             => $slug,
            'nextUrl'          => $nextUrl,
            'backToSectionUrl' => $backToSectionUrl,
        ]);
    }

    private function getNextItemRoute(SectionContent $content, string $userId): ?string
    {
        $content->loadMissing('section.course');
        $section = $content->section;
        $course = $section?->course;
        if (!$course || !$section) {
            return null;
        }

        $passedTestIds = DB::table('post_test_results')
            ->where('user_id', $userId)
            ->where('is_passed', 1)
            ->pluck('post_test_id')
            ->toArray();

        // 1. Cek topik berikutnya di section (modul) yang sama
        $contents = SectionContent::where('course_section_id', $section->id)
            ->orderBy('position', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $currentIndex = $contents->search(fn($item) => $item->id === $content->id);
        if ($currentIndex !== false && isset($contents[$currentIndex + 1])) {
            return route('user.course.content.show', [
                'slug'    => $course->slug,
                'content' => $contents[$currentIndex + 1]->id,
            ]);
        }

        // 2. Cek evaluasi bab (post test) modul ini jika ada dan belum lulus
        $sectionTest = PostTest::where('course_section_id', $section->id)->first();
        if ($sectionTest && !in_array($sectionTest->id, $passedTestIds)) {
            return route('user.course.test.show', [
                'slug'       => $course->slug,
                'postTestId' => $sectionTest->id,
            ]);
        }

        // 3. Cek modul (section) berikutnya
        $sections = CourseSection::where('course_id', $course->id)
            ->orderBy('position', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $currentSectionIndex = $sections->search(fn($item) => $item->id === $section->id);
        if ($currentSectionIndex !== false) {
            for ($i = $currentSectionIndex + 1; $i < $sections->count(); $i++) {
                $nextSec = $sections[$i];
                $firstContent = SectionContent::where('course_section_id', $nextSec->id)
                    ->orderBy('position', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($firstContent) {
                    return route('user.course.content.show', [
                        'slug'    => $course->slug,
                        'content' => $firstContent->id,
                    ]);
                }

                $nextSecTest = PostTest::where('course_section_id', $nextSec->id)->first();
                if ($nextSecTest && !in_array($nextSecTest->id, $passedTestIds)) {
                    return route('user.course.test.show', [
                        'slug'       => $course->slug,
                        'postTestId' => $nextSecTest->id,
                    ]);
                }
            }
        }

        // 4. Cek evaluasi akhir kursus jika ada, belum lulus, dan sudah memenuhi syarat akses (termasuk presensi Zoom)
        $evaluasiAkhir = PostTest::where('course_id', $course->id)->whereNull('course_section_id')->first();
        if ($evaluasiAkhir && !in_array($evaluasiAkhir->id, $passedTestIds) && $this->courseService->canAccessTest($evaluasiAkhir)) {
            return route('user.course.test.show', [
                'slug'       => $course->slug,
                'postTestId' => $evaluasiAkhir->id,
            ]);
        }

        // 5. Jika semua tahapan selesai, kembali ke detail kursus
        return route('user.course.my-course.detail', $course->slug);
    }

    public function submitTest(Request $request, string $slug, string $postTestId): RedirectResponse
    {
        $resultData = $this->courseService->getCourseDetailSlug($slug);
        $courseData = $resultData['data'] ?? null;
        if ($courseData && !empty($courseData->is_waiting_for_start)) {
            $startDateStr = $courseData->active_bimtek?->start_date
                ? $courseData->active_bimtek->start_date->translatedFormat('d F Y')
                : 'jadwal pelaksanaan';
            ToastMagic::error("Evaluasi belum dapat diakses. Kelas pelatihan akan dibuka serentak pada {$startDateStr}.");

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $postTest = $this->postTestService->findWithQuestionsOrFail($postTestId);

        if (!$this->courseService->canAccessTest($postTest)) {
            ToastMagic::error('Evaluasi masih terkunci. Selesaikan seluruh topik sebelumnya terlebih dahulu.');

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $userId = $request->user()->id;

        // 1. Submit Tipe Upload File / Penugasan Berkas
        if ($postTest->is_file_upload || $request->hasFile('assignment_file')) {
            $request->validate([
                'assignment_file' => 'required|file|mimes:pdf,docx,doc,zip,rar,jpg,png|max:10240',
                'notes'           => 'nullable|string|max:500',
            ], [
                'assignment_file.required' => 'Harap pilih berkas jawaban tugas yang ingin diunggah.',
                'assignment_file.mimes'    => 'Format berkas jawaban harus berupa PDF, DOCX, DOC, atau ZIP.',
                'assignment_file.max'      => 'Ukuran berkas jawaban maksimal 10 MB.',
            ]);

            $file = $request->file('assignment_file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store("assignments/{$userId}", 'public');

            \Modules\LMS\Models\PostTestResult::updateOrCreate(
                [
                    'post_test_id' => $postTest->id,
                    'user_id'      => $userId,
                ],
                [
                    'file_path'    => $path,
                    'file_name'    => $originalName,
                    'notes'        => $request->input('notes'),
                    'status'       => 'submitted',
                    'is_passed'    => true,
                    'score'        => 100,
                    'completed_at' => now(),
                ]
            );

            $course = \Modules\LMS\Models\Course::where('slug', $slug)->first();
            if ($course) {
                $newProgress = $this->courseService->calculateDynamicProgress($course, $userId);
                DB::table('course_student')
                    ->where('course_id', $course->id)
                    ->where('user_id', $userId)
                    ->update([
                        'progress' => $newProgress,
                        'status'   => $newProgress >= 100 ? 'completed' : 'in_progress',
                    ]);
            }

            ToastMagic::success('Berkas tugas evaluasi berhasil dikumpulkan!');
            return redirect()->route('user.course.test.show', ['slug' => $slug, 'postTestId' => $postTest->id]);
        }

        // 2. Submit Tipe Uraian / Essay
        if ($postTest->is_essay || $request->has('essay_answers')) {
            $request->validate([
                'essay_answers'   => 'required|array|min:1',
                'essay_answers.*' => 'required|string|min:5',
            ], [
                'essay_answers.required'   => 'Harap isi seluruh jawaban uraian.',
                'essay_answers.*.required' => 'Setiap soal uraian wajib dijawab.',
                'essay_answers.*.min'      => 'Jawaban uraian minimal 5 karakter.',
            ]);

            \Modules\LMS\Models\PostTestResult::updateOrCreate(
                [
                    'post_test_id' => $postTest->id,
                    'user_id'      => $userId,
                ],
                [
                    'answers'      => $request->input('essay_answers'),
                    'status'       => 'submitted',
                    'is_passed'    => true,
                    'score'        => 85,
                    'completed_at' => now(),
                ]
            );

            $course = \Modules\LMS\Models\Course::where('slug', $slug)->first();
            if ($course) {
                $newProgress = $this->courseService->calculateDynamicProgress($course, $userId);
                DB::table('course_student')
                    ->where('course_id', $course->id)
                    ->where('user_id', $userId)
                    ->update([
                        'progress' => $newProgress,
                        'status'   => $newProgress >= 100 ? 'completed' : 'in_progress',
                    ]);
            }

            ToastMagic::success('Jawaban uraian evaluasi berhasil dikirim dan menunggu penilaian pengajar!');
            return redirect()->route('user.course.test.show', ['slug' => $slug, 'postTestId' => $postTest->id]);
        }

        // 3. Submit Tipe CBT Pilihan Ganda Standar
        $result = $this->postTestService->submitStudentTest($postTest, $slug, $userId, $request->input('answers', []));

        if (!$result['has_questions']) {
            ToastMagic::error($result['message']);

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $result['is_passed'] ? ToastMagic::success($result['message']) : ToastMagic::error($result['message']);

        return redirect()->route('user.course.test.show', ['slug' => $slug, 'postTestId' => $postTest->id]);
    }

    public function showTest(Request $request, string $slug, string $postTestId): View|RedirectResponse
    {
        $resultData = $this->courseService->getCourseDetailSlug($slug);
        $courseData = $resultData['data'] ?? null;
        if ($courseData && !empty($courseData->is_waiting_for_start)) {
            $startDateStr = $courseData->active_bimtek?->start_date
                ? $courseData->active_bimtek->start_date->translatedFormat('d F Y')
                : 'jadwal pelaksanaan';
            ToastMagic::error("Evaluasi belum dapat diakses. Kelas pelatihan akan dibuka serentak pada {$startDateStr}.");

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $postTest = $this->postTestService->findWithQuestionsOrFail($postTestId);

        if (!$this->courseService->canAccessTest($postTest)) {
            ToastMagic::error('Evaluasi masih terkunci. Selesaikan seluruh topik sebelumnya terlebih dahulu.');

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $userId = $request->user()?->id;
        $evalResult = null;
        if ($userId) {
            $evalResult = \Modules\LMS\Models\PostTestResult::where('post_test_id', $postTest->id)
                ->where('user_id', $userId)
                ->first();
        }

        // Tentukan tipe evaluasi: 'essay' / 'uraian', 'file_upload' / 'upload', atau 'multiple_choice' / 'pilgan'
        $evalType = match ($postTest->type) {
            'essay'       => 'uraian',
            'file_upload' => 'upload',
            default       => 'pilgan',
        };

        $backToUrl = !empty(data_get($postTest, 'course_section_id'))
            ? route('user.course.section.show', ['slug' => $slug, 'sectionId' => data_get($postTest, 'course_section_id')])
            : route('user.course.my-course.detail', $slug);

        $isAssignmentDone = (bool) ($evalResult && ($evalResult->file_path || $evalResult->status === 'submitted' || $evalResult->completed_at));
        $isEssayDone = (bool) ($evalResult && ($evalResult->answers || $evalResult->status === 'submitted' || $evalResult->completed_at));

        return view('lms::user.course.test-show', [
            'course'           => $resultData['data'] ?? null,
            'slug'             => $slug,
            'postTest'         => $postTest,
            'evalResult'       => $evalResult,
            'evalType'         => $evalType,
            'result'           => $postTest->is_multiple_choice ? $this->postTestService->getStudentTestResult($userId, $postTestId, (bool) $request->query('retake')) : null,
            'backToUrl'        => $backToUrl,
            'isAssignmentDone' => $isAssignmentDone,
            'isEssayDone'      => $isEssayDone,
        ]);
    }
}