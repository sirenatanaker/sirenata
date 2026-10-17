<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Requests\GetRegenciesRequest;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Http\Requests\UpdateWebInstansiRequest;
use Modules\Dashboard\Services\DashboardService;
use Modules\Dashboard\Services\UserDashboardService;

class UserDashboardController extends Controller
{
    public function __construct(
        private UserDashboardService $userDashboardService,
        private DashboardService $dashboardService
    ) {}

    /**
     * User learning dashboard index.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->userDashboardService->getDashboardData($user);

        return view('dashboard::pages.user.index', $data);
    }

    /**
     * Get regencies list via AJAX for province selector.
     */
    public function getRegencies(GetRegenciesRequest $request): JsonResponse
    {
        $regencies = $this->userDashboardService->getRegenciesByProvinceCode($request->validated('province_code'));

        return response()->json([
            'success' => true,
            'data'    => $regencies,
        ]);
    }

    /**
     * Update user instansi from web dashboard.
     */
    public function updateInstansi(UpdateWebInstansiRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->userDashboardService->updateInstansiWeb($user, $request->validated());

        ToastMagic::success("Data instansi berhasil disimpan!");
        return redirect()->route('user.dashboard')->with('success', 'Data instansi berhasil disimpan.');
    }

    /**
     * User profile page with certificates and enrollment stats.
     */
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->userDashboardService->getProfileData($user);

        return view('dashboard::pages.user.profile', $data);
    }

    /**
     * Update user profile information & password.
     */
    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->dashboardService->updateProfile($user, $request->validated());

        ToastMagic::success("Profile berhasil diupdate!");
        return to_route('user.profile');
    }

    /**
     * Search suggestions for navbar global search.
     */
    public function searchSuggest(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $suggestions = $this->userDashboardService->getSearchSuggestions($user, $request->input('q'));

        return response()->json($suggestions);
    }
}
