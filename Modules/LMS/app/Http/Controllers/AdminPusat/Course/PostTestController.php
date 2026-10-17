<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\StorePostTestRequest;
use Modules\LMS\Http\Requests\UpdatePostTestRequest;
use Modules\LMS\Http\Requests\UploadPostTestImageRequest;
use Modules\LMS\Services\PostTestService;

class PostTestController extends Controller
{
    public function __construct(private PostTestService $postTestService) {}

    public function create(Request $request)
    {
        $data = $this->postTestService->getCreateData(
            (string) $request->query('course_slug'),
            $request->query('section_id'),
        );

        return view('lms::admin-pusat.post-test.create', $data);
    }

    public function store(StorePostTestRequest $request)
    {
        $validated = $request->validated();
        $result = $this->postTestService->storePostTestWithQuestions($validated);

        if (!$result['success']) {
            ToastMagic::error($result['message']);

            return redirect()->back()->withInput();
        }

        ToastMagic::success($result['message']);

        return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
    }

    public function edit(Request $request, $id)
    {
        $data = $this->postTestService->getEditData(
            (string) $id,
            (string) $request->query('course_slug'),
        );

        return view('lms::admin-pusat.post-test.edit', $data);
    }

    public function update(UpdatePostTestRequest $request, $id)
    {
        $validated = $request->validated();
        $postTest = $this->postTestService->findOrFail((string) $id);
        $result = $this->postTestService->updatePostTestWithQuestions($postTest, $validated);

        if (!$result['success']) {
            ToastMagic::error($result['message']);

            return redirect()->back()->withInput();
        }

        ToastMagic::success($result['message']);

        return redirect()->route('admin-pusat.management-course.courses.show', $validated['course_slug']);
    }

    public function uploadImage(UploadPostTestImageRequest $request)
    {
        $url = $this->postTestService->uploadEditorImage($request->file('image'));

        return response()->json([
            'success' => true,
            'url'     => $url,
        ]);
    }
}