<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\BodyParameter;
use Exception;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\Api\StoreCourseBenefitRequest;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseBenefits;
use Modules\LMS\Services\Api\CourseBenefitsService;
use Modules\LMS\Transformers\Api\CourseBenefitResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class CourseBenefitController extends Controller
{

    public function __construct(
        private readonly CourseBenefitsService $courseBenefitsService
    ) {}
    /**
     * List semua benefit
     * 
     * Menampilkan benefit berdasarkan slug course
     * 
     * @unauthenticated
     */
    public function index(string $slug): JsonResponse
    {
        try {
            $benefits = $this->courseBenefitsService->queryBenefits($slug);
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: CourseBenefitResource::collection($benefits),
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
     * Buat benefit baru
     * 
     * Buat benefit baru berdasarkan slug course. Hanya bisa diakses oleh admin-pusat.
     * 
     * @authenticated
     * @role:admin-pusat
     */
    #[BodyParameter('name', description: 'Name Benefit dari Course.', type: 'string', required: true, example: 'test-1')]
    public function store(StoreCourseBenefitRequest $request, string $slug): JsonResponse
    {
        try {
            $benefit = $this->courseBenefitsService->CourseBenefitStore(
                data: $request->validated(),
                slug: $slug
            );
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: new CourseBenefitResource($benefit),
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
     * Update benefit
     * 
     * Update benefit berdasarkan slug course. Hanya bisa diakses oleh admin-pusat.
     * 
     * @body CourseBenefits
     * @authenticated
     * @role:admin-pusat
     */
    #[BodyParameter('name', description: 'Name Benefit dari Course.', type: 'string', required: true, example: 'test-1')]
    public function update(StoreCourseBenefitRequest $request, CourseBenefits $benefitId): JsonResponse
    {
        $data   = $request->validated();

        try {
            $benefit = $this->courseBenefitsService->CourseBenefitUpdate(data: $data, benefitId: $benefitId);
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: new CourseBenefitResource($benefit),
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
     * Hapus benefit
     * 
     * Hapus benefit berdasarkan slug course. Hanya bisa diakses oleh admin-pusat.
     * 
     * @body CourseBenefits
     * @authenticated
     * @role:admin-pusat
     */
    public function destroy(CourseBenefits $benefitId): JsonResponse
    {
        try {
            $course = $benefitId->course->id;
            abort_if($benefitId->course_id !== $course, 404, 'Benefit tidak ditemukan');
            $benefit = $this->courseBenefitsService->CourseBenefitDelete(benefitId: $benefitId);
            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: null,
                statusCode: 200
            );
        } catch (Exception $e) {
            return ResponseHelper::error(
                message: $e->getMessage(),
                statusCode: 500
            );
        }
    }
}
