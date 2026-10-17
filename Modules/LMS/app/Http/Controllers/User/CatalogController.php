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

        $enrolledCourseIds = $this->courseService->getEnrolledCourseIds();
        $courses = $this->courseService->getCatalogCourses(12, $search, $selectedCategories);
        $categories = $this->categoryService->getCategoriesForCatalog($enrolledCourseIds);

        return view('lms::user.katalog.index', compact(
            'courses',
            'enrolledCourseIds',
            'search',
            'categories',
            'selectedCategories'
        ));
    }

    public function enroll(string $slug): RedirectResponse
    {
        $result = $this->courseService->enrollUser($slug);

        if ($result['success']) {
            ToastMagic::success($result['message']);

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        ToastMagic::error($result['message']);

        return redirect()->back();
    }
}