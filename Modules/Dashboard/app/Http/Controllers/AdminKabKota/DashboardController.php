<?php

namespace Modules\Dashboard\Http\Controllers\AdminKabKota;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\AdminKabKotaDashboardService;
use Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private AdminKabKotaDashboardService $adminKabKotaDashboardService,
        private DashboardService $dashboardService
    ) {}

    /**
     * Display listing / index for Admin Kab/Kota Dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $selectedYear = (int) $request->input('year', (int) date('Y'));

        $data = $this->adminKabKotaDashboardService->getDashboardData($user, $selectedYear);

        return view('dashboard::pages.admin-kab-kota.index', $data);
    }

    /**
     * Profile page.
     */
    public function profile(Request $request)
    {
        return view('dashboard::pages.admin-kab-kota.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Store or update profile.
     */
    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->dashboardService->updateProfile($user, $request->validated());

        ToastMagic::success("Profile berhasil diupdate!");
        return to_route('admin-kab-kota.profile');
    }
}