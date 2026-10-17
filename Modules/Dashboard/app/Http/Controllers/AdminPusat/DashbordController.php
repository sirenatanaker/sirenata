<?php

namespace Modules\Dashboard\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\AdminPusatDashboardService;
use Modules\Dashboard\Services\DashboardService;

class DashbordController extends Controller
{
    public function __construct(
        private AdminPusatDashboardService $adminPusatDashboardService,
        private DashboardService $dashboardService
    ) {}

    /**
     * Display listing / index for Admin Pusat Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 1. AJAX Handlers
        if ($request->ajax()) {
            if ($request->has('pending_page')) {
                $page = (int) $request->input('pending_page', 1);
                return response()->json($this->adminPusatDashboardService->getPendingApprovalsPage($page));
            }

            if ($request->has('rtk_end_year')) {
                return response()->json([
                    'rtkMasaBerakhirPerProvinsi' => $this->adminPusatDashboardService->getRtkMasaBerakhirPerProvinsi($request->input('rtk_end_year')),
                ]);
            }

            if ($request->has('rtk_year')) {
                return response()->json([
                    'rtkMasaAktifPerProvinsi' => $this->adminPusatDashboardService->getRtkMasaAktifPerProvinsi($request->input('rtk_year')),
                ]);
            }

            if ($request->has('sdm_year')) {
                return response()->json([
                    'sdmPerProvinsi' => $this->adminPusatDashboardService->getSdmPerProvinsi((int) $request->input('sdm_year')),
                ]);
            }

            return response()->json([
                'sdmPerProvinsi' => $this->adminPusatDashboardService->getSdmPerProvinsi((int) $request->input('sdm_year', (int) date('Y'))),
                'rtkMasaAktifPerProvinsi' => $this->adminPusatDashboardService->getRtkMasaAktifPerProvinsi($request->input('rtk_year', 'all')),
                'rtkMasaBerakhirPerProvinsi' => $this->adminPusatDashboardService->getRtkMasaBerakhirPerProvinsi($request->input('rtk_end_year', 'all')),
            ]);
        }

        // 2. Full Page View
        $selectedSdmYear = (int) $request->input('sdm_year', (int) date('Y'));
        $selectedRtkYear = $request->input('rtk_year', 'all');
        $selectedRtkEndYear = $request->input('rtk_end_year', 'all');

        $data = $this->adminPusatDashboardService->getDashboardData(
            $user,
            $selectedSdmYear,
            $selectedRtkYear,
            $selectedRtkEndYear
        );

        return view('dashboard::pages.admin-pusat.index', $data);
    }

    /**
     * Profile page.
     */
    public function profile(Request $request): View
    {
        return view('dashboard::pages.admin-pusat.profile', [
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
        return to_route('admin-pusat.profile');
    }
}
