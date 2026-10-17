<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginateResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\Api\CourseStudentService;
use Modules\LMS\Transformers\Api\CourseStudentResource;
use Modules\LMS\Transformers\Api\MyCourseResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class CourseStudentController extends Controller
{
    public function __construct(
        private readonly CourseStudentService $courseStudentService
    ) {}

    /**
     * List semua student yang enroll / terdaftar (admin only)
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        try {
            $row_per_page = $request->input('row_per_page', 20);
            $students = $this->courseStudentService->getStudents(
                slug: $slug,
                perPage: $row_per_page
            );

            return ResponseHelper::success(
                status: true,
                message: 'Students retrieved successfully',
                result: PaginateResource::make($students, CourseStudentResource::class),
                statusCode: 200
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                message: $th->getMessage(),
                statusCode: 500
            );
        }
    }
    
    /**
     *  Menambahkan user ke course 
     * 
     * menambahkan user menjadi terdaftar di course yang sedang user login berdasarkan user_id user login
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function enroll(string $slug): JsonResponse
    {
        try {
            $result = $this->courseStudentService->enroll($slug);

            if (! $result['enrolled']) {
                return ResponseHelper::error(
                    message: $result['message'],
                    statusCode: 422
                );
            }

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
                result: null,
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
     *  Update status student di course
     * 
     * @authenticated
     * @role:admin-pusat
     */
    public function updateStatus(\Modules\LMS\Http\Requests\Api\UpdateCourseStudentStatusRequest $request, string $slug, string $userId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->courseStudentService->updateStatus(
                slug: $slug,
                userId: $userId,
                status: $validated['status']
            );

            if (! $result['success']) {
                return ResponseHelper::error(
                    message: $result['message'],
                    statusCode: $result['code']
                );
            }

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
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
     * MyCourse User Login
     * 
     * 
     * Course yang diikuti oleh user yang login
     * 
     * @authenticated
     * @role:user
     */
    public function myCourses(Request $request): JsonResponse
    {
        try {
            $row_per_page = $request->input('row_per_page', 12);
            $status = $request->input('status', 'semua');
            $studentCourses = $this->courseStudentService->myCourses(
                perPage: $row_per_page,
                status: $status
            );
            
            return ResponseHelper::success(
                status: true,
                message: 'My courses retrieved successfully',
                result: PaginateResource::make($studentCourses, MyCourseResource::class),
                auth: [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
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
     *  Unenroll dari course
     * 
     * @authenticated
     * 
     */
    public function unenroll(string $slug): JsonResponse
    {
        try {
            $result = $this->courseStudentService->unenroll($slug);

            if (! $result['success']) {
                return ResponseHelper::error(
                    message: $result['message'],
                    statusCode: $result['code']
                );
            }

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
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
