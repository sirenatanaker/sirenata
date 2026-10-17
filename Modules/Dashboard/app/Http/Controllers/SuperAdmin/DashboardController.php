<?php

namespace Modules\Dashboard\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\DashboardService;
use Modules\Dashboard\Services\SuperAdminDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private SuperAdminDashboardService $superAdminDashboardService,
        private DashboardService $dashboardService,
    ) {}

    /**
     * Display Super Admin Dashboard.
     */
    public function index(Request $request): View
    {
        $data = $this->superAdminDashboardService->getDashboardData($request->user());

        return view('dashboard::pages.super-admin.index', $data);
    }

    /**
     * Super Admin Profile Page.
     */
    public function profile(Request $request): View
    {
        return view('dashboard::pages.super-admin.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update Super Admin Profile.
     */
    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $this->dashboardService->updateProfile($request->user(), $request->validated());

        ToastMagic::success('Profile berhasil diupdate!');
        return to_route('super-admin.profile');
    }
}
