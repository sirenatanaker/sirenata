<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Http\Requests\StoreCourseSectionWebRequest;
use Modules\LMS\Services\CourseSectionService;

class CourseSectionController extends Controller
{
    public function __construct(private CourseSectionService $courseSectionService) {}

    public function index(): View
    {
        return view('lms::index');
    }

    public function create(): View
    {
        return view('lms::create');
    }

    public function store(StoreCourseSectionWebRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->courseSectionService->storeCourseSection([
                'slug'        => $validated['course_slug'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if (!$result['success']) {
                ToastMagic::error($result['message']);

                return redirect()->back()->withInput();
            }

            ToastMagic::success($result['message']);

            return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat membuat bagian kursus: ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function show($id): View
    {
        return view('lms::show');
    }

    public function edit($id): View
    {
        return view('lms::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        return redirect()->back();
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        try {
            $courseSlug = $request->input('course_slug');
            $result = $this->courseSectionService->deleteCourseSection($id);

            if (!$result['success']) {
                ToastMagic::error($result['message']);

                return redirect()->back();
            }

            ToastMagic::success($result['message']);

            return $courseSlug
                ? redirect()->route('admin-pusat.management-course.courses.show', $courseSlug)
                : redirect()->back();
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat menghapus bagian kursus: ' . $e->getMessage());

            return redirect()->back();
        }
    }
}