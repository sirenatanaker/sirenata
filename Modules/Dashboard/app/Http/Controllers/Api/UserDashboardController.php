<?php

namespace Modules\Dashboard\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Requests\UpdateInstansiRequest;
use Modules\Dashboard\Services\DashboardService;

class UserDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get user profile
     * 
     * GET /api/v1/user/profile
     * 
     * @authenticated
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load(['profile', 'scopeArea.province', 'scopeArea.regency']);

        return ResponseHelper::success(
            status: true, 
            message: 'User Data Profile successfully', 
            result: new UserProfileResource($user), 
            statusCode: 200
        );
    }

    /**
     * Update user instansi
     * 
     * PATCH /api/v1/user/instansi
     * 
     * @authenticated
     */
    public function updateInstansi(UpdateInstansiRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user = $this->dashboardService->updateInstansi($user, $request->validated());
        $user->load(['profile', 'scopeArea.province', 'scopeArea.regency']);

        return ResponseHelper::success(
            status: true, 
            message: 'Data instansi berhasil disimpan', 
            result: new UserProfileResource($user), 
            statusCode: 200
        );
    }
}
