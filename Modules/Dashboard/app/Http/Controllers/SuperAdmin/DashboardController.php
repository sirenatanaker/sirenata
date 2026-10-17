<?php

namespace Modules\Dashboard\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\DashboardService;
use Modules\Dashboard\Services\SuperAdminDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private SuperAdminDashboardService $superAdminDashboardService,
        private DashboardService $dashboardService
    ) {}

    /**
     * Display Super Admin Dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->superAdminDashboardService->getDashboardData($user);

        return view('dashboard::pages.super-admin.index', $data);
    }

    /**
     * Super Admin Profile Page.
     */
    public function profile(Request $request)
    {
        return view('dashboard::pages.super-admin.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update Super Admin Profile.
     */
    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->dashboardService->updateProfile($user, $request->validated());

        ToastMagic::success("Profile berhasil diupdate!");
        return to_route('super-admin.profile');
    }
}
