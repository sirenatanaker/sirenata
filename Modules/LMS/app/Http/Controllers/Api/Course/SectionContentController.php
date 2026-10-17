<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Http\Requests\Api\StoreSectionContentRequest;
use Modules\LMS\Http\Requests\Api\UpdateSectionContentRequest;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Services\Api\SectionContentService;
use Modules\LMS\Transformers\Api\SectionContentResource;

class SectionContentController extends Controller
{
    public function __construct(
        private SectionContentService $service
    ) {}

    /**
     * Menampilkan list konten dalam section
     * 
     * 
     * List semua konten dalam section (harus sudah enroll / terdaftar),
     * Menampilkan SectionContentCourse berdasarkan courseSection Id
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function index(CourseSection $courseSection): JsonResponse
    {
        try {
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: $this->service->getContents($courseSection),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * List konten dalam section detail
     * 
     * 
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function show(SectionContent $content): JsonResponse
    {


        try {
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: $this->service->getContent($content),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Menambahkan konten baru dalam section
     * 
     * Create SectionContentCourse berdasarkan courseSection Id
     * 
     * @requestMediaType multipart/form-data
     * @authenticated
     * @role:admin-pusat
     */
    public function store(StoreSectionContentRequest $request, CourseSection $courseSection): JsonResponse
    {
        try {
            $result = $this->service->store(
                validated: $request->validated(),
                courseSection: $courseSection,
                file: $request->file('document')
            );

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
                result: new SectionContentResource($result['content']),
                statusCode: 201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 400
            );
        }
    }

    /**
     * Update konten — bisa ganti nama
     * 
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function update(UpdateSectionContentRequest $request, SectionContent $content): JsonResponse
    {
        try {
            $result = $this->service->update(
                validated: $request->validated(),
                content: $content,
                file: $request->file('document')
            );

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
                result: new SectionContentResource($result['content']),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Delete SectionContent
     * 
     * delete SectionContentCourse berdasarkan Id
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function destroy(SectionContent $content): JsonResponse
    {
        try {
            $this->service->destroy($content);

            return ResponseHelper::success(
                status: true,
                message: 'Konten berhasil dihapus',
                result: null,
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Ubah urutan SectionContent
     * 
     * Ubah urutan SectionContent berdasarkan courseSection Id
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function reorder(\Modules\LMS\Http\Requests\Api\ReorderSectionContentRequest $request, CourseSection $courseSection): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->service->reorder($validated['contents'], $courseSection);

        return ResponseHelper::success(
            status: true,
            message: $result['message'],
            result: null,
            statusCode: 200
        );
    }
}
