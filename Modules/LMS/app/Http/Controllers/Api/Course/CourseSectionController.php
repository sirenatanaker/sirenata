<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\Api\StoreCourseSectionRequest;
use Modules\LMS\Http\Requests\Api\UpdateCourseSectionRequest;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Services\Api\CourseSectionService;
use Modules\LMS\Transformers\Api\CourseSectionResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class CourseSectionController extends Controller
{
    public function __construct(
        private readonly CourseSectionService $courseSectionService
    ) {}

    /**
     * List Course section berdasarkan slug course
     * 
     * List Course section berdasarkan slug course
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function index(string $slug): JsonResponse
    {
        try {
            $sections = $this->courseSectionService->getSectionsBySlug(slug: $slug);
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: CourseSectionResource::collection($sections),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: 'Gagal mengambil data section',
                statusCode: 500
            );
        }
    }

    /**
     * Tambah Course section
     * 
     * Tambah Course section berdasarkan slug course
     * 
     * @body CourseSection
     * @authenticated
     * @role:admin-pusat
     */
    public function store(StoreCourseSectionRequest $request, string $slug): JsonResponse
    {
        try {
            $section = $this->courseSectionService->createSection(
                slug: $slug,
                data: $request->validated()
            );

            return ResponseHelper::success(
                status: true,
                message: 'Course Section berhasil dibuat',
                result: new CourseSectionResource($section),
                statusCode: 201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Update Course section
     * 
     * Update Course section berdasarkan id CourseSection
     * 
     * @body CourseSection
     * @authenticated
     * @role:admin-pusat
     */
    public function update(UpdateCourseSectionRequest $request, CourseSection $section): JsonResponse
    {
        try {
            $section = $this->courseSectionService->updateSection(
                section: $section,
                data: $request->validated()
            );

            return ResponseHelper::success(
                status: true,
                message: 'Course Section berhasil diupdate',
                result: new CourseSectionResource($section),
                statusCode: 200
            );
        } catch (\Throwable $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Hapus section
     * 
     * Hapus section berdasarkan id CourseSection
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function destroy(CourseSection $section): JsonResponse
    {
        try {
            $this->courseSectionService->deleteSection($section);

            return ResponseHelper::success(
                status: true,
                message: 'Course Section berhasil dihapus',
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
     * Ubah urutan Course section
     * 
     * Ubah urutan Course section berdasarkan slug course
     * 
     * @body CourseSection
     * @authenticated
     * @role:admin-pusat
     */
    public function reorder(\Modules\LMS\Http\Requests\Api\ReorderCourseSectionRequest $request, string $slug): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->courseSectionService->reorderSections(
                slug: $slug,
                sections: $validated['sections']
            );

            return ResponseHelper::success(
                status: true,
                message: 'Urutan section berhasil diupdate',
                result: null,
                statusCode: 200
            );
        } catch (\Throwable $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500
            );
        }
    }
}
