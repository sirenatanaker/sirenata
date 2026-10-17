<?php

namespace Modules\Dashboard\Http\Controllers\AdminProvinsi;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\AdminProvinsiDashboardService;
use Modules\Dashboard\Services\DashboardService;
use Modules\User\Models\UserScope;

class DashboardController extends Controller
{
    public function __construct(
        private AdminProvinsiDashboardService $adminProvinsiDashboardService,
        private DashboardService $dashboardService
    ) {}

    /**
     * Display listing / index for Admin Provinsi Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $userScope = UserScope::where('user_id', $user->id)->first();
        $provinceCode = $userScope ? $userScope->province_code : null;

        // 1. AJAX Handlers
        if ($request->ajax()) {
            if ($request->has('pending_page')) {
                $page = (int) $request->input('pending_page', 1);
                return response()->json($this->adminProvinsiDashboardService->getPendingApprovalsPage($provinceCode, $page));
            }

            if ($request->has('rtk_end_year')) {
                return response()->json([
                    'rtkMasaBerakhirPerKabKota' => $this->adminProvinsiDashboardService->getRtkMasaBerakhirPerKabKota($provinceCode, $request->input('rtk_end_year')),
                ]);
            }

            if ($request->has('rtk_year')) {
                return response()->json([
                    'rtkMasaAktifPerKabKota' => $this->adminProvinsiDashboardService->getRtkMasaAktifPerKabKota($provinceCode, $request->input('rtk_year')),
                ]);
            }

            if ($request->has('sdm_year')) {
                return response()->json([
                    'sdmPerKabKota' => $this->adminProvinsiDashboardService->getSdmPerKabKota($provinceCode, (int) $request->input('sdm_year')),
                ]);
            }

            return response()->json([
                'sdmPerKabKota' => $this->adminProvinsiDashboardService->getSdmPerKabKota($provinceCode, (int) $request->input('sdm_year', (int) date('Y'))),
                'rtkMasaAktifPerKabKota' => $this->adminProvinsiDashboardService->getRtkMasaAktifPerKabKota($provinceCode, $request->input('rtk_year', 'all')),
                'rtkMasaBerakhirPerKabKota' => $this->adminProvinsiDashboardService->getRtkMasaBerakhirPerKabKota($provinceCode, $request->input('rtk_end_year', 'all')),
            ]);
        }

        // 2. Full Page View
        $selectedSdmYear = (int) $request->input('sdm_year', (int) date('Y'));
        $selectedRtkYear = $request->input('rtk_year', 'all');
        $selectedRtkEndYear = $request->input('rtk_end_year', 'all');

        $data = $this->adminProvinsiDashboardService->getDashboardData(
            $user,
            $selectedSdmYear,
            $selectedRtkYear,
            $selectedRtkEndYear
        );

        return view('dashboard::pages.admin-provinsi.index', $data);
    }

    /**
     * Profile page.
     */
    public function profile(Request $request): View
    {
        return view('dashboard::pages.admin-provinsi.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Store or update profile.
     */
    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->dashboardService->updateProfile($user, $request->validated());

        ToastMagic::success("Profile berhasil diupdate!");
        return to_route('admin-province.profile');
    }
}
