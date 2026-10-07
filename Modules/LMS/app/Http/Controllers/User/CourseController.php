<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\LMS\Services\CourseService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CertificateSetting;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Services\Api\CourseProgressService;

class CourseController extends Controller
{
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';

    public function __construct(
        private CourseService $courseService
    ) {}

    /**
     * Helper untuk kalkulasi progress dinamis secara real-time
     * (Logika perhitungan sama persis dengan halaman Detail)
     */
    private function calculateDynamicProgress($dbCourse)
    {
        $userId = Auth::id();
        $totalContents = 0;
        $completedContents = 0;
        $totalTests = 0;
        $passedTests = 0;

        foreach ($dbCourse->sections as $section) {
            $totalContents += $section->contents->count();

            // Hitung materi yang sudah selesai
            $completedContents += \Modules\LMS\Models\StudentContentProgress::where('user_id', $userId)
                ->whereIn('section_content_id', $section->contents->pluck('id'))
                ->count();

            // Hitung evaluasi bab
            $postTestBab = \Modules\LMS\Models\PostTest::where('course_section_id', $section->id)->first();
            if ($postTestBab) {
                $totalTests++;
                $isPassed = DB::table('post_test_results')
                    ->where('user_id', $userId)
                    ->where('post_test_id', $postTestBab->id)
                    ->where('is_passed', 1)
                    ->exists();
                if ($isPassed) $passedTests++;
            }
        }

        // Hitung evaluasi akhir
        $evaluasiAkhir = \Modules\LMS\Models\PostTest::where('course_id', $dbCourse->id)->whereNull('course_section_id')->first();
        if ($evaluasiAkhir) {
            $totalTests++;
            $isEvaluasiAkhirCompleted = DB::table('post_test_results')
                ->where('user_id', $userId)
                ->where('post_test_id', $evaluasiAkhir->id)
                ->where('is_passed', 1)
                ->exists();
            if ($isEvaluasiAkhirCompleted) $passedTests++;
        }

        $totalItems = $totalContents + $totalTests;
        $completedItems = $completedContents + $passedTests;

        return $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;
    }

    public function allMyCourse(Request $request)
    {
        $page   = $request->get('page', 1);
        $perPage = $request->get('row_per_page', 11);

        $result = $this->courseService->myCourses(page: $page, perPage: $perPage);

        $courses = collect($result['data'])->map(function ($item) {
            $courseObj = (object) $item;
            $slug = $courseObj->slug ?? null;

            $courseObj->total_modul = 0;
            $courseObj->total_materi = 0;

            if ($slug) {
                $dbCourse = Course::with(['category', 'sections.contents'])->where('slug', $slug)->first();
                if ($dbCourse) {
                    $courseObj->description = $dbCourse->description;
                    $courseObj->category = $dbCourse->category ? (object) $dbCourse->category->toArray() : null;

                    // Perbaikan penanganan URL thumbnail
                    if ($dbCourse->thumbnail) {
                        $courseObj->thumbnail_url = str_starts_with($dbCourse->thumbnail, 'http')
                            ? $dbCourse->thumbnail
                            : Storage::url($dbCourse->thumbnail);
                    } else {
                        $courseObj->thumbnail_url = null;
                    }

                    $courseObj->total_modul = $dbCourse->sections->count();
                    $courseObj->total_materi = $dbCourse->sections->sum(fn($s) => $s->contents->count());

                    $courseObj->progress = $this->calculateDynamicProgress($dbCourse);
                    $courseObj->status = $courseObj->progress >= 100 ? self::COMPLETED : self::IN_PROGRESS;
                }
            }
            return $courseObj;
        });

        return view('lms::user.course.my-course', [
            'courses' => $courses,
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseProgress(Request $request)
    {
        $page   = $request->get('page', 1);
        $perPage = $request->get('row_per_page', 11);

        $result = $this->courseService->myCourses(page: $page, perPage: $perPage, status: self::IN_PROGRESS);

        $courses = collect($result['data'])->map(function ($item) {
            $courseObj = (object) $item;
            $slug = $courseObj->slug ?? null;

            $courseObj->total_modul = 0;
            $courseObj->total_materi = 0;

            if ($slug) {
                $dbCourse = Course::with(['category', 'sections.contents'])->where('slug', $slug)->first();
                if ($dbCourse) {
                    $courseObj->description = $dbCourse->description;
                    $courseObj->category = $dbCourse->category ? (object) $dbCourse->category->toArray() : null;

                    // Perbaikan penanganan URL thumbnail
                    if ($dbCourse->thumbnail) {
                        $courseObj->thumbnail_url = str_starts_with($dbCourse->thumbnail, 'http')
                            ? $dbCourse->thumbnail
                            : Storage::url($dbCourse->thumbnail);
                    } else {
                        $courseObj->thumbnail_url = null;
                    }

                    $courseObj->total_modul = $dbCourse->sections->count();
                    $courseObj->total_materi = $dbCourse->sections->sum(fn($s) => $s->contents->count());

                    $courseObj->progress = $this->calculateDynamicProgress($dbCourse);
                    $courseObj->status = $courseObj->progress >= 100 ? self::COMPLETED : self::IN_PROGRESS;
                }
            }
            return $courseObj;
        });

        return view('lms::user.course.my-course-progress', [
            'courses' => $courses,
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseFinish(Request $request)
    {
        $page   = $request->get('page', 1);
        $perPage = $request->get('row_per_page', 11);

        $result = $this->courseService->myCourses(page: $page, perPage: $perPage, status: self::COMPLETED);

        $courses = collect($result['data'])->map(function ($item) {
            $courseObj = (object) $item;
            $slug = $courseObj->slug ?? null;

            $courseObj->total_modul = 0;
            $courseObj->total_materi = 0;

            if ($slug) {
                $dbCourse = Course::with(['category', 'sections.contents'])->where('slug', $slug)->first();
                if ($dbCourse) {
                    $courseObj->description = $dbCourse->description;
                    $courseObj->category = $dbCourse->category ? (object) $dbCourse->category->toArray() : null;

                    // Perbaikan penanganan URL thumbnail
                    if ($dbCourse->thumbnail) {
                        $courseObj->thumbnail_url = str_starts_with($dbCourse->thumbnail, 'http')
                            ? $dbCourse->thumbnail
                            : Storage::url($dbCourse->thumbnail);
                    } else {
                        $courseObj->thumbnail_url = null;
                    }

                    $courseObj->total_modul = $dbCourse->sections->count();
                    $courseObj->total_materi = $dbCourse->sections->sum(fn($s) => $s->contents->count());

                    $courseObj->progress = $this->calculateDynamicProgress($dbCourse);
                    $courseObj->status = $courseObj->progress >= 100 ? self::COMPLETED : self::IN_PROGRESS;
                }
            }
            return $courseObj;
        });

        return view('lms::user.course.my-course-completed', [
            'courses' => $courses,
            'meta'    => $result['meta'],
            'links'   => $result['links'],
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function myCourseDetail(string $slug)
    {
        $result = $this->courseService->getCourseDetailSlug($slug);
        $course = $result['data'] ?? null;

        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        $enrollment = $course->students()->wherePivot('user_id', Auth::id())->first();

        $course->course_name = $course->name;
        $course->thumbnail_url = $course->thumbnail ? Storage::url($course->thumbnail) : null;

        if ($enrollment) {
            $course->certificate_code = $enrollment->pivot->certificate_code;
            $course->certificate_issued_at = $enrollment->pivot->certificate_issued_at;
            $course->certificate_file = $enrollment->pivot->certificate_file
                ? Storage::url($enrollment->pivot->certificate_file)
                : null;
        }

        return view('lms::user.course.my-course-detail', [
            'course'  => $course,
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }
    public function generateCertificate(string $slug)
    {
        $user = Auth::user();
        $course = Course::where('slug', $slug)->firstOrFail();

        $enrollment = $course->students()->wherePivot('user_id', $user->id)->first();

        if (!$enrollment) {
            ToastMagic::error('Anda belum terdaftar di kursus ini.');
            return redirect()->back();
        }

        if ($enrollment->pivot->status !== 'completed' || $enrollment->pivot->progress < 100) {
            ToastMagic::error('Anda belum menyelesaikan kursus ini.');
            return redirect()->back();
        }

        $activeSetting = CertificateSetting::getActive();
        if (!$activeSetting) {
            ToastMagic::error('Pengaturan sertifikat aktif belum diatur oleh admin.');
            return redirect()->back();
        }

        $certificateCode = $enrollment->pivot->certificate_code;
        $issuedAt = $enrollment->pivot->certificate_issued_at ?? now();

        if (empty($certificateCode)) {
            do {
                $certificateCode = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(6));
                $codeExists = DB::table('course_student')
                    ->where('certificate_code', $certificateCode)
                    ->exists();
            } while ($codeExists);
            $issuedAt = now();
        }

        // Menggunakan Storage::url() alih-alih konversi base64
        $backgroundUrl = $activeSetting->background_image
            ? Storage::url($activeSetting->background_image)
            : null;

        $signatureUrl = $activeSetting->signature_image
            ? Storage::url($activeSetting->signature_image)
            : null;

        $completedDate = Carbon::parse($issuedAt)->translatedFormat('d F Y');
        $fullName = $user->profile?->full_name ?? $user->name;

        $pdf = Pdf::loadView('lms::admin-pusat.certificates.certificate-template', [
            'nama_peserta' => $fullName,
            'nama_kursus' => $course->name,
            'tanggal_selesai' => $completedDate,
            'nomor_sertifikat' => $certificateCode,
            'background_url' => $backgroundUrl,
            'signature_url' => $signatureUrl,
            'signer_name' => $activeSetting->signer_name,
            'signer_title' => $activeSetting->signer_title,
        ]);

        $pdf->setPaper('a5', 'landscape');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        $pdfFileName = 'certificates/pdfs/cert-' . $course->slug . '-' . $user->id . '.pdf';

        if (!Storage::disk('public')->exists('certificates/pdfs')) {
            Storage::disk('public')->makeDirectory('certificates/pdfs');
        }

        Storage::disk('public')->put($pdfFileName, $pdf->output());

        $course->students()->updateExistingPivot($user->id, [
            'certificate_code' => $certificateCode,
            'certificate_file' => $pdfFileName,
            'certificate_issued_at' => $issuedAt,
        ]);

        ToastMagic::success('Sertifikat Anda berhasil disiapkan dengan data terbaru!');
        return redirect()->back();
    }

    public function completeContent(SectionContent $content)
    {
        $progressService = app(CourseProgressService::class);
        $result = $progressService->completeContent($content);

        if (!$result['success']) {
            ToastMagic::error($result['message']);
            return redirect()->back();
        }

        if ($result['is_completed']) {
            ToastMagic::success('Selamat! Anda telah menyelesaikan semua materi kursus ini! 🎉');
        } else {
            ToastMagic::success('Materi berhasil ditandai selesai. Progress: ' . $result['progress'] . '%');
        }

        return redirect()->back();
    }

    public function showContent(string $slug, string $contentId)
    {
        $result = $this->courseService->getCourseDetailSlug($slug);
        $course = $result['data'] ?? null;

        if ($course) {
            $course->course_name = $course->name;
            $course->thumbnail_url = $course->thumbnail ? Storage::url($course->thumbnail) : null;
        }

        $content = SectionContent::findOrFail($contentId);

        return view('lms::user.course.content-show', [
            'course'  => $course,
            'content' => $content,
            'slug'    => $slug,
        ]);
    }

    public function submitTest(Request $request, string $slug, string $postTestId)
    {
        $postTest = \Modules\LMS\Models\PostTest::with('questions.choices')->findOrFail($postTestId);
        $userAnswers = $request->input('answers', []);

        $totalQuestions = $postTest->questions->count();
        if ($totalQuestions === 0) {
            ToastMagic::error('Tidak ada soal pada ujian ini.');
            return redirect()->route('user.course.my-course.detail', $slug);
        }

        $correctCount = 0;

        foreach ($postTest->questions as $question) {
            $userSelectedChoiceId = $userAnswers[$question->id] ?? null;
            if ($userSelectedChoiceId) {
                $correctChoice = $question->choices->where('is_correct', true)->first();
                if ($correctChoice && $correctChoice->id === $userSelectedChoiceId) {
                    $correctCount++;
                }
            }
        }

        $score = round(($correctCount / $totalQuestions) * 100);
        $isPassed = $score >= $postTest->passing_score;

        $userId = Auth::id();

        $existingRecord = DB::table('post_test_results')
            ->where('user_id', $userId)
            ->where('post_test_id', $postTest->id)
            ->first();

        if ($existingRecord) {
            DB::table('post_test_results')
                ->where('id', $existingRecord->id)
                ->update([
                    'score'        => $score,
                    'is_passed'    => $isPassed,
                    'completed_at' => $isPassed ? now() : null,
                    'updated_at'   => now(),
                ]);
        } else {
            DB::table('post_test_results')->insert([
                'id'           => Str::uuid()->toString(),
                'user_id'      => $userId,
                'post_test_id' => $postTest->id,
                'score'        => $score,
                'is_passed'    => $isPassed,
                'completed_at' => $isPassed ? now() : null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if (is_null($postTest->course_section_id) && $isPassed) {
            $course = Course::where('slug', $slug)->first();
            if ($course) {
                $course->students()->updateExistingPivot($userId, [
                    'status' => 'completed',
                    'progress' => 100,
                ]);
            }
        }

        if ($isPassed) {
            ToastMagic::success("Selamat! Anda lulus ujian dengan nilai {$score} (KKM: {$postTest->passing_score})");
        } else {
            ToastMagic::error("Nilai Anda {$score} belum mencapai KKM ({$postTest->passing_score}). Silakan pelajari materi kembali dan ulangi tes.");
        }

        return redirect()->route('user.course.test.show', [
            'slug' => $slug,
            'postTestId' => $postTest->id,
        ]);
    }

    public function showTest(\Illuminate\Http\Request $request, string $slug, string $postTestId)
    {
        $resultData = $this->courseService->getCourseDetailSlug($slug);
        $course = $resultData['data'] ?? null;

        if ($course) {
            $course->course_name = $course->name;
            $course->thumbnail_url = $course->thumbnail ? Storage::url($course->thumbnail) : null;
        }

        $postTest = \Modules\LMS\Models\PostTest::with('questions.choices')->findOrFail($postTestId);

        $result = DB::table('post_test_results')
            ->where('user_id', Auth::id())
            ->where('post_test_id', $postTestId)
            ->first();

        if ($request->query('retake')) {
            $result = null;
        }

        return view('lms::user.course.test-show', [
            'course'   => $course,
            'slug'     => $slug,
            'postTest' => $postTest,
            'result'   => $result,
        ]);
    }
}
