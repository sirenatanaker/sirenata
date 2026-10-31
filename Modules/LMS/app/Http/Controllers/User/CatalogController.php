<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Services\CategoryService;
use Modules\LMS\Services\CourseService;

class CatalogController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $selectedCategories = $request->get('categories', []);
        $programType = $request->get('type', 'all');

        $enrolledCourseIds = $this->courseService->getEnrolledCourseIds();
        $courses = $this->courseService->getCatalogCourses(12, $search, $selectedCategories, $programType);
        $categories = $this->categoryService->getCategoriesForCatalog($enrolledCourseIds);

        // Ambil ID Bimtek Event yang sudah diikuti oleh user yang sedang login
        $userBimtekEventIds = [];
        if (auth()->check()) {
            $userBimtekEventIds = \Illuminate\Support\Facades\DB::table('course_student')
                ->where('user_id', auth()->id())
                ->whereNotNull('bimtek_event_id')
                ->pluck('bimtek_event_id')
                ->toArray();
        }

        return view('lms::user.katalog.index', compact(
            'courses',
            'enrolledCourseIds',
            'userBimtekEventIds',
            'search',
            'categories',
            'selectedCategories',
            'programType'
        ));
    }

    public function enroll(Request $request, string $slug): RedirectResponse
    {
        $bimtekEventId = $request->input('bimtek_event_id');
        $result = $this->courseService->enrollUser($slug, $bimtekEventId);

        if ($result['success']) {
            ToastMagic::success($result['message']);

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        ToastMagic::error($result['message']);

        return redirect()->back();
    }
}