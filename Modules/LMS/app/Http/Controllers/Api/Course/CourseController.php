<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginateResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\Http\Requests\Api\StoreCourseRequest;
use Modules\LMS\Http\Requests\Api\UpdateCourseRequest;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\Api\CourseService;
use Modules\LMS\Transformers\Api\CourseResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courseService
    ) {}

    /**
     * List semua course
     * 
     * GET /api/courses
     * Menampilkan daftar course yang tersedia. Bisa difilter berdasarkan
     * category_id atau keyword pencarian.
     * 
     * @unauthenticated
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $row_per_page = $request->input('row_per_page', 10);
            $search = $request->input('search');
            $category_id = $request->input('category_id');

            $courses = $this->courseService->queryCourses(
                category_id: $category_id,
                search: $search,
            )->paginate($row_per_page);

            return ResponseHelper::success(
                status: true,
                message: 'Users retrieved successfully',
                result: PaginateResource::make($courses, CourseResource::class),
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
     * Detail course
     * 
     * GET /api/courses/{slug}
     * Menampilkan detail satu course berdasarkan slug, lengkap dengan info
     * category, mentor, benefits, dan testimonis.
     * 
     * @unauthenticated
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $course = $this->courseService->queryCourseDetail($slug);
            return ResponseHelper::success(
                status: true,
                message: 'Course retrieved successfully',
                result: new CourseResource($course),
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
     * Buat course baru
     * 
     * POST /api/courses
     * Membuat course baru. Hanya bisa diakses oleh admin-pusat.
     * 
     * @requestMediaType multipart/form-data
     * @authenticated
     * @role:admin-pusat
     */
    #[Endpoint(method: 'PATCH')]
    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $course = $this->courseService->CourseStore($data);

            return ResponseHelper::success(
                status: true,
                message: 'Course created successfully',
                result: new CourseResource($course->load('category', 'user')),
                statusCode: 201
            );
        } catch (\Exception $th) {
            return ResponseHelper::error(
                message: $th->getMessage(),
                statusCode: 500
            );
        }
    }

    /**
     * Update course
     * 
     * PUT /api/courses/{slug}
     * Mengupdate course berdasarkan slug. Hanya bisa diakses oleh admin-pusat.
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function update(UpdateCourseRequest $request, string $slug): JsonResponse
    {
        try {
            $course = $this->courseService->findBySlugOrFail($slug);
            $data   = $request->validated();
            $this->courseService->CourseUpdate(course: $course, data: $data);

            return ResponseHelper::success(
                status: true,
                message: 'Course updated successfully',
                result: new CourseResource($course->fresh(['category', 'user'])),
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
     * Hapus course
     * 
     * DELETE /api/courses/{slug}
     * Menghapus course berdasarkan slug. Hanya bisa diakses oleh admin-pusat.
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function destroy(string $slug): JsonResponse
    {
        try {
            $course = $this->courseService->findBySlug($slug);

            if (!$course) {
                return ResponseHelper::error(
                    message: 'Course not found',
                    statusCode: 404
                );
            }

            $this->courseService->CourseDelete($course);

            return ResponseHelper::success(
                status: true,
                message: 'Course deleted successfully',
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
}
