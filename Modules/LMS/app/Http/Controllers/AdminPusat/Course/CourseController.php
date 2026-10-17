<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\Api\StoreCourseRequest;
use Modules\LMS\Http\Requests\Api\UpdateCourseRequest;
use Modules\LMS\Services\CategoryService;
use Modules\LMS\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $result = $this->courseService->allCourses(
            page: (int) $request->get('page', 1),
            perPage: $request->integer('row_per_page', 12),
            search: $request->get('search'),
            categoryId: $request->get('category_id'),
        );

        return view('lms::admin-pusat.course.index', [
            'courses'    => collect(json_decode(json_encode($result['data']))),
            'categories' => $this->categoryService->getAllOrdered(),
            'meta'       => $result['meta'],
            'links'      => $result['links'],
            'success'    => $result['success'],
            'message'    => $result['message'],
        ]);
    }

    public function create()
    {
        $categories = $this->categoryService->getAllOrdered();

        return view('lms::admin-pusat.course.create', compact('categories'));
    }

    public function store(StoreCourseRequest $request)
    {
        try {
            $result = $this->courseService->storeCourse(
                data: $request->validated(),
                thumbnailFile: $request->file('thumbnail'),
            );

            if ($result['success']) {
                ToastMagic::success('Course berhasil ditambahkan!');

                return redirect()->route('admin-pusat.management-course.courses.index');
            }

            return back()->withInput()->with('error', $result['message']);
        } catch (\Exception $e) {
            ToastMagic::error($e->getMessage());

            return back()->withInput();
        }
    }

    public function show(string $slug)
    {
        $courseResponse = $this->courseService->getCourseBySlug($slug);
        $course = json_decode(json_encode($courseResponse['data']));

        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        return view('lms::admin-pusat.course.show', compact('course'));
    }

    public function edit(string $slug)
    {
        $courseResponse = $this->courseService->getCourseBySlug($slug);

        if (!$courseResponse['success'] || empty($courseResponse['data'])) {
            abort(404, 'Course tidak ditemukan di server');
        }

        $course = json_decode(json_encode($courseResponse['data']));
        $categories = $this->categoryService->getAllOrdered();

        return view('lms::admin-pusat.course.edit', compact('course', 'categories'));
    }

    public function update(UpdateCourseRequest $request, string $slug)
    {
        $payload = array_merge($request->validated(), ['slug' => $slug]);
        $result = $this->courseService->updateCourse(
            data: $payload,
            thumbnailFile: $request->file('thumbnail'),
        );

        if ($result['success']) {
            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function destroy(string $slug)
    {
        $result = $this->courseService->deleteCourse($slug);

        if ($result['success']) {
            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}