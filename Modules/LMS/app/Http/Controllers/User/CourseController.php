<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
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
            'course'         => $course,
            'sectionProofs'  => $sectionProofs,
            'success'        => $result['success'],
            'message'        => $result['message'],
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

        $activeBimtek = null;
        $enrollment = $request->user() ? $course->students()->wherePivot('user_id', $request->user()->id)->first() : null;
        if ($enrollment && !empty($enrollment->pivot->bimtek_event_id)) {
            $activeBimtek = \Modules\LMS\Models\BimtekEvent::find($enrollment->pivot->bimtek_event_id);
        }
        if (!$activeBimtek) {
            $activeBimtek = \Modules\LMS\Models\BimtekEvent::where('course_id', $course->id)->latest()->first();
        }

        $zoomProof = SectionZoomProof::where('course_section_id', $sectionId)
            ->where('user_id', $request->user()->id)
            ->first();

        return view('lms::user.course.section-show', [
            'course'         => $course,
            'section'        => $section,
            'sectionIndex'   => $sectionIndex !== false ? $sectionIndex : 0,
            'slug'           => $slug,
            'activeBimtek'   => $activeBimtek,
            'zoomProof'      => $zoomProof,
            'hasJoinedZoom'  => (bool) ($enrollment?->pivot?->zoom_joined_at),
            'isZoomAttended' => (bool) ($enrollment?->pivot?->zoom_attended),
        ]);
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

        ToastMagic::success('Bukti presensi / penugasan berhasil disimpan!');
        return redirect()->back();
    }

    public function joinZoom(Request $request, string $slug): RedirectResponse
    {
        $course = \Modules\LMS\Models\Course::where('slug', $slug)->firstOrFail();
        $activeBimtek = \Modules\LMS\Models\BimtekEvent::where('course_id', $course->id)->latest()->first();

        if (!$activeBimtek || empty($activeBimtek->zoom_url)) {
            ToastMagic::error('Tautan ruang Zoom belum tersedia.');
            return redirect()->back();
        }

        // Cek jika gelombang bimtek belum dibuka/dimulai
        if ($activeBimtek->start_date && now()->lessThan($activeBimtek->start_date)) {
            ToastMagic::error('Sesi Live Zoom belum dibuka. Pelatihan gelombang ini dimulai pada tanggal ' . $activeBimtek->start_date->translatedFormat('d F Y') . '.');
            return redirect()->back();
        }

        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'zoom_joined_at' => now(),
            ]);

        return redirect()->away($activeBimtek->zoom_url);
    }

    public function attendZoom(Request $request, string $slug): RedirectResponse
    {
        $course = \Modules\LMS\Models\Course::where('slug', $slug)->firstOrFail();
        $activeBimtek = \Modules\LMS\Models\BimtekEvent::where('course_id', $course->id)->latest()->first();

        $enrollment = DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->first();

        // Opsi 2a: Wajib klik "Masuk Ruang Zoom" terlebih dahulu
        if (empty($enrollment?->zoom_joined_at)) {
            ToastMagic::error('Silakan klik tombol "Masuk Ruang Zoom" terlebih dahulu untuk mengikuti sesi sebelum konfirmasi hadir.');
            return redirect()->back();
        }

        // Opsi 2b: Penguncian berdasarkan jadwal waktu Zoom dimulai
        if ($activeBimtek && $activeBimtek->zoom_scheduled_at && now()->lessThan($activeBimtek->zoom_scheduled_at)) {
            ToastMagic::error('Presensi belum dibuka. Konfirmasi kehadiran hanya dapat dilakukan saat jadwal Sesi Live Zoom dimulai (' . $activeBimtek->zoom_schedule_formatted . ').');
            return redirect()->back();
        }

        // Opsi 1: Validasi Kode Presensi / Token Zoom
        if ($activeBimtek && !empty($activeBimtek->zoom_attendance_code)) {
            $inputCode = strtoupper(trim((string) $request->input('attendance_code')));
            $expectedCode = strtoupper(trim((string) $activeBimtek->zoom_attendance_code));

            if ($inputCode === '' || $inputCode !== $expectedCode) {
                ToastMagic::error('Kode Presensi Zoom tidak sesuai. Masukkan kode presensi yang dibagikan panitia/narasumber saat sesi Zoom berlangsung.');
                return redirect()->back();
            }
        }

        DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->update([
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

        ToastMagic::success(
            $canTakeFinalTest
                ? 'Presensi Sesi Live Zoom berhasil dikonfirmasi! Tahap Evaluasi Akhir kini telah terbuka.'
                : 'Presensi Sesi Live Zoom berhasil dikonfirmasi! Selesaikan seluruh modul pembelajaran mandiri untuk membuka Evaluasi Akhir.'
        );

        return redirect()->back();
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

        return view('lms::user.course.content-show', [
            'course'  => $result['data'] ?? null,
            'content' => $content,
            'slug'    => $slug,
            'nextUrl' => $nextUrl,
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

        $result = $this->postTestService->submitStudentTest($postTest, $slug, $request->user()->id, $request->input('answers', []));

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

        return view('lms::user.course.test-show', [
            'course'   => $resultData['data'] ?? null,
            'slug'     => $slug,
            'postTest' => $postTest,
            'result'   => $this->postTestService->getStudentTestResult($request->user()->id, $postTestId, (bool) $request->query('retake')),
        ]);
    }
}