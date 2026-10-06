<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\Api\StoreCourseRequest;
use Modules\LMS\Http\Requests\Api\UpdateCourseRequest;
use Modules\LMS\Models\Category;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Log;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page       = $request->get('page', 1);
        $perPage    = $request->integer('row_per_page', 12);
        $categoryId = $request->get('category_id');
        $search     = $request->get('search');

        // Panggil service tanpa token
        $result = $this->courseService->allCourses(page: $page, perPage: $perPage, search: $search, categoryId: $categoryId);
        $courses = collect(json_decode(json_encode($result['data'])));

        $categories = Category::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('lms::admin-pusat.course.index', [
            'courses'    => $courses,
            'categories' => $categories,
            'meta'       => $result['meta'],
            'links'      => $result['links'],
            'success'    => $result['success'],
            'message'    => $result['message'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'name')->orderBy('name', 'asc')->get();
        return view('lms::admin-pusat.course.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        try {
            $validated = $request->validated();

          $payload = [
                'category_id' => $request->input('category_id'),
                'name'        => $request->input('name'),
                'description' => $request->input('description'),
                'bg_color'    => $request->input('bg_color'), 
            ];

            $thumbnailFile = $request->file('thumbnail');

            // Panggil service tanpa token
            $result = $this->courseService->storeCourse(data: $payload, thumbnailFile: $thumbnailFile);

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

    /**
     * Show the specified resource.
     */
    public function show(string $slug)
    {
        // Panggil service tanpa token
        $courseResponse = $this->courseService->getCourseBySlug($slug);
        $course = json_decode(json_encode($courseResponse['data']));
        
        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        return view('lms::admin-pusat.course.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $slug)
    {
        // Panggil service tanpa token
        $courseResponse = $this->courseService->getCourseBySlug($slug);

        if (!$courseResponse['success'] || empty($courseResponse['data'])) {
            abort(404, 'Course tidak ditemukan di server');
        }

        $course = json_decode(json_encode($courseResponse['data']));
        $categories = Category::select('id', 'name')->orderBy('name', 'asc')->get();

        return view('lms::admin-pusat.course.edit', compact('course', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, string $slug)
    {
        $validated = $request->validated();

        $payload = [
            'slug'        => $slug,
            'category_id' => $request->input('category_id'),
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
        ];

        $thumbnailFile = $request->file('thumbnail');

        // Panggil service tanpa token
        $result = $this->courseService->updateCourse(data: $payload, thumbnailFile: $thumbnailFile);

        if ($result['success']) {
            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        // Panggil service tanpa token
        $result = $this->courseService->deleteCourse($slug);

        if ($result['success']) {
            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}