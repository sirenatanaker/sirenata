<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LMS\Services\SectionContentService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Log;

// Import Model yang dibutuhkan
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\SectionContent;

class SectionContentController extends Controller
{
    public function __construct(private SectionContentService $sectionContentService) {}

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
    public function create(Request $request)
    {
        $courseSlug = $request->query('course_slug');
        $sectionId = $request->query('section_id');

        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $section = CourseSection::findOrFail($sectionId);

        return view('lms::admin-pusat.section-content.create', compact('course', 'section'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_slug'       => 'required|string',
                'course_section_id' => 'required|string',
                'name'              => 'required|string|max:255',
                'video'             => 'nullable|string',
                'document'          => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'content_text'      => 'nullable|string',
            ]);

            $payload = [
                'course_section_id' => $validated['course_section_id'],
                'name'              => $validated['name'],
                'video'             => $validated['video'] ?? null,
                'content_text'      => $validated['content_text'] ?? null,
            ];

            $documentFile = $request->file('document');

            // Panggil service tanpa token API
            $result = $this->sectionContentService->storeSectionContent($payload, $documentFile);

            if (!$result['success']) {
                ToastMagic::error($result['message']);
                return redirect()->back()->withInput();
            }

            ToastMagic::success($result['message']);
            return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat membuat konten bagian: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $courseSlug = $request->query('course_slug');
        if (!$courseSlug) {
            abort(404, 'Course Slug tidak ditemukan.');
        }

        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $content = SectionContent::with('section')->findOrFail($id);

        return view('lms::admin-pusat.section-content.show', compact('course', 'content'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $courseSlug = $request->query('course_slug');
        if (!$courseSlug) {
            abort(404, 'Course Slug tidak ditemukan.');
        }

        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $content = SectionContent::with('section')->findOrFail($id);

        return view('lms::admin-pusat.section-content.edit', compact('course', 'content'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'course_slug'  => 'required|string',
                'name'         => 'required|string|max:255',
                'video'        => 'nullable|string',
                'document'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'content_text' => 'nullable|string',
            ]);

            $payload = [
                'name'         => $validated['name'],
                'video'        => $validated['video'] ?? null,
                'content_text' => $validated['content_text'] ?? null,
            ];

            $documentFile = $request->file('document');

            // Panggil service tanpa token API dan argumen parameter yang bersih
            $result = $this->sectionContentService->updateContent($id, $payload, $documentFile);

            Log::info('SectionContentController::update result', ['result' => $result]);

            if (!$result['success']) {
                ToastMagic::error($result['message']);
                return redirect()->back()->withInput();
            }

            ToastMagic::success($result['message']);
            return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function uploadVideoEditor(Request $request)
    {
       
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/webm,video/ogg|max:102400',
        ]);

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('course-videos', 'public');
            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 400);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $request->validate([
                'course_slug' => 'required|string'
            ]);

            // Panggil service tanpa token API
            $result = $this->sectionContentService->deleteContent($id);

            if (!$result['success']) {
                ToastMagic::error($result['message']);
                return redirect()->back();
            }

            ToastMagic::success($result['message']);

            return redirect()->route('admin-pusat.management-course.courses.show', $request->course_slug);
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat menghapus materi: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
