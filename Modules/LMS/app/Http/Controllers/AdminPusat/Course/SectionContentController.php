<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Http\Requests\DeleteSectionContentWebRequest;
use Modules\LMS\Http\Requests\StoreSectionContentWebRequest;
use Modules\LMS\Http\Requests\UpdateSectionContentWebRequest;
use Modules\LMS\Http\Requests\UploadSectionVideoRequest;
use Modules\LMS\Services\SectionContentService;

class SectionContentController extends Controller
{
    public function __construct(private SectionContentService $sectionContentService) {}

    public function index(): View
    {
        return view('lms::index');
    }

    public function create(Request $request): View
    {
        $data = $this->sectionContentService->getCreateData(
            (string) $request->query('course_slug'),
            (string) $request->query('section_id'),
        );

        return view('lms::admin-pusat.section-content.create', $data);
    }

    public function store(StoreSectionContentWebRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $files = $request->file('documents') ?? ($request->file('document') ? [$request->file('document')] : []);
            $result = $this->sectionContentService->storeSectionContent($validated, $files);

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

    public function show(Request $request, string $id): View
    {
        $courseSlug = $request->query('course_slug');
        if (!$courseSlug) {
            abort(404, 'Course Slug tidak ditemukan.');
        }

        return view('lms::admin-pusat.section-content.show', $this->sectionContentService->getCourseAndContent($courseSlug, $id));
    }

    public function edit(Request $request, string $id): View
    {
        $courseSlug = $request->query('course_slug');
        if (!$courseSlug) {
            abort(404, 'Course Slug tidak ditemukan.');
        }

        return view('lms::admin-pusat.section-content.edit', $this->sectionContentService->getCourseAndContent($courseSlug, $id));
    }

    public function update(UpdateSectionContentWebRequest $request, string $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $files = $request->file('documents') ?? ($request->file('document') ? [$request->file('document')] : []);
            $result = $this->sectionContentService->updateContent($id, $validated, $files);

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

    public function uploadVideoEditor(UploadSectionVideoRequest $request): JsonResponse
    {
        $url = $this->sectionContentService->uploadEditorVideo($request->file('video'));

        return response()->json([
            'success' => true,
            'url'     => $url,
        ]);
    }

    public function destroy(DeleteSectionContentWebRequest $request, string $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->sectionContentService->deleteContent($id);

            if (!$result['success']) {
                ToastMagic::error($result['message']);

                return redirect()->back();
            }

            ToastMagic::success($result['message']);

            return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
        } catch (\Exception $e) {
            ToastMagic::error('Terjadi kesalahan saat menghapus materi: ' . $e->getMessage());

            return redirect()->back();
        }
    }
}
