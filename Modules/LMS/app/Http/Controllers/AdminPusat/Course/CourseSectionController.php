<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LMS\Services\CourseSectionService;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class CourseSectionController extends Controller
{
    public function __construct(private CourseSectionService $courseSectionService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('lms::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lms::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_slug' => 'required|string',
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $payload = [
                'slug'        => $validated['course_slug'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ];

            // Panggil service tanpa menggunakan token API sama sekali
            $result = $this->courseSectionService->storeCourseSection($payload);
            
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

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('lms::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('lms::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id) 
    {
        try {
            $courseSlug = $request->input('course_slug');
            
            $result = $this->courseSectionService->deleteCourseSection($id);
            
            if (!$result['success']) {
                ToastMagic::error($result['message']);
                return redirect()->back();
            }

            ToastMagic::success($result['message']);
            
            // Redirect kembali ke halaman detail course jika slug tersedia
            if ($courseSlug) {
                return redirect()->route('admin-pusat.management-course.courses.show', $courseSlug);
            }
            
            return redirect()->back();
            
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat menghapus bagian kursus: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}