<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Models\SectionContent;
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
            status: self::IN_PROGRESS,
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
            status: self::COMPLETED,
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

        return view('lms::user.course.my-course-detail', [
            'course'  => $course,
            'success' => $result['success'],
            'message' => $result['message'],
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

        $result = $this->courseProgressService->completeContent($content, $request->user()->id);

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
        $content = $this->courseService->getStudentContentForShow($contentId);

        if (!$this->courseService->canAccessContent($content)) {
            ToastMagic::error('Topik ini masih terkunci. Selesaikan topik sebelumnya terlebih dahulu.');

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        return view('lms::user.course.content-show', [
            'course'  => $result['data'] ?? null,
            'content' => $content,
            'slug'    => $slug,
        ]);
    }

    public function submitTest(Request $request, string $slug, string $postTestId): RedirectResponse
    {
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