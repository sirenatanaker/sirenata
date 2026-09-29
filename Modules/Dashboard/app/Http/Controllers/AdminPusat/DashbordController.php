<?php

namespace Modules\Dashboard\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Creasi\Nusa\Models\Province;
use Modules\User\Models\UserProfile;
use Spatie\Activitylog\Models\Activity;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Modules\RTK\Models\RencanaTenagaKerja;
use Modules\RTK\Models\RtkPemanfaatanSubmission;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\Project\Models\Project;
use Illuminate\Http\RedirectResponse;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\DashboardService;

class DashbordController extends Controller
{
    public function __construct(
        private DashboardService $dashbordService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Total Admin Pusat (users with role 'admin-pusat')
        $totalAdminPusat = User::role('admin-pusat')->count();

        // Admin Aktif (Admin Provinsi + Admin Kab/Kota)
        $adminAktif = User::role(['admin-province', 'admin-kab-kota'])->count();

        // Aktivitas Admin bulan ini (from activity_log table)
        $aktivitasAdmin = Activity::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Gender distribution from user_profiles
        $genderMale = UserProfile::where('gender', 'male')->count();
        $genderFemale = UserProfile::where('gender', 'female')->count();

        // Filter by Year for SDM
        $currentYear = (int) date('Y');
        $selectedSdmYear = (int) $request->input('sdm_year', $currentYear);

        $sdmYears = [];

        for ($y = $currentYear; $y >= $currentYear - 15; $y--) {
            $sdmYears[] = $y;
        }

        // SDM per Provinsi dengan Grouping Gender
        $userCountsByProvince = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join(
                'user_profiles',
                'users.id',
                '=',
                'user_profiles.user_id'
            )
            ->join('model_has_roles', function ($join) {
                $join->on(
                    'users.id',
                    '=',
                    'model_has_roles.model_uuid'
                )
                    ->where(
                        'model_has_roles.model_type',
                        '=',
                        'App\\Models\\User'
                    );
            })
            ->join(
                'roles',
                'model_has_roles.role_id',
                '=',
                'roles.uuid'
            )
            ->where('roles.name', 'user')
            ->whereYear('users.created_at', $selectedSdmYear)
            ->whereNotNull('user_scopes.province_code')
            ->select(
                'user_scopes.province_code',
                'user_profiles.gender',
                DB::raw('count(*) as total')
            )
            ->groupBy(
                'user_scopes.province_code',
                'user_profiles.gender'
            )
            ->get();

        $provinceCodes = $userCountsByProvince
            ->pluck('province_code')
            ->unique()
            ->toArray();

        $provinces = Province::whereIn('code', $provinceCodes)
            ->pluck('name', 'code');

        // Mengelompokkan ulang hasil query
        // agar bentuknya per provinsi berisi total male dan female
        $sdmPerProvinsi = collect($provinceCodes)
            ->map(function ($code) use (
                $userCountsByProvince,
                $provinces
            ) {
                $provinceData = $userCountsByProvince
                    ->where('province_code', $code);

                $maleCount = $provinceData
                    ->where('gender', 'male')
                    ->first()
                    ->total ?? 0;

                $femaleCount = $provinceData
                    ->where('gender', 'female')
                    ->first()
                    ->total ?? 0;

                return (object) [
                    'province_code' => $code,
                    'province_name' => collect(
                        explode(
                            ' ',
                            $provinces[$code] ?? 'Unknown (' . $code . ')'
                        )
                    )
                        ->map(
                            fn($w) => ucfirst(strtolower($w))
                        )
                        ->join(' '),
                    'male' => $maleCount,
                    'female' => $femaleCount,
                    'total' => $maleCount + $femaleCount,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // ================================================================
        // MASA AKTIF RTK PER PROVINSI
        // ================================================================

        $availableRtkYears = RencanaTenagaKerja::where(
            'type',
            TypeRtk::PROVINSI->value
        )
            ->berlaku()
            ->pluck('start_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $selectedRtkYear = $request->input('rtk_year', 'all');

        $queryRtk = RencanaTenagaKerja::where(
            'type',
            TypeRtk::PROVINSI->value
        )->berlaku();

        if ($selectedRtkYear !== 'all') {
            $queryRtk->where(
                'end_date',
                '>=',
                (int) $selectedRtkYear
            );
        }

        $rtkProvinsi = $queryRtk->get();

        $rtkProvinceCodes = $rtkProvinsi
            ->pluck('province_code')
            ->toArray();

        $rtkProvinceNames = Province::whereIn(
            'code',
            $rtkProvinceCodes
        )->pluck('name', 'code');

        $rtkMasaAktifPerProvinsi = $rtkProvinsi
            ->groupBy('province_code')
            ->map(function ($items, $code) use (
                $rtkProvinceNames
            ) {
                $rtk = $items->first();

                return (object) [
                    'province_name' => $rtkProvinceNames[$code] ?? 'Unknown',
                    'sisa_tahun' => max(
                        0,
                        (int) $rtk->end_date - (int) date('Y')
                    ),
                    'start_date' => (int) $rtk->start_date,
                    'end_date' => (int) $rtk->end_date,
                    'total' => $items->count(),
                ];
            })
            ->sortBy('province_name')
            ->values();

        // ================================================================
        // DATA RTK FILTERED BY END DATE
        // ================================================================

        $availableRtkEndYears = RencanaTenagaKerja::where(
            'type',
            TypeRtk::PROVINSI->value
        )
            ->berlaku()
            ->pluck('end_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $selectedRtkEndYear = $request->input(
            'rtk_end_year',
            'all'
        );

        $queryRtkEnd = RencanaTenagaKerja::where(
            'type',
            TypeRtk::PROVINSI->value
        )->berlaku();

        if ($selectedRtkEndYear !== 'all') {
            $queryRtkEnd->where(
                'end_date',
                (int) $selectedRtkEndYear
            );
        }

        $rtkProvinsiEnd = $queryRtkEnd->get();

        $rtkEndProvinceCodes = $rtkProvinsiEnd
            ->pluck('province_code')
            ->toArray();

        $rtkEndProvinceNames = Province::whereIn(
            'code',
            $rtkEndProvinceCodes
        )->pluck('name', 'code');

        $rtkMasaBerakhirPerProvinsi = $rtkProvinsiEnd
            ->groupBy('province_code')
            ->map(function ($items, $code) use (
                $rtkEndProvinceNames
            ) {
                $rtk = $items->first();

                return (object) [
                    'province_name' => $rtkEndProvinceNames[$code] ?? 'Unknown',
                    'sisa_tahun' => max(
                        0,
                        (int) $rtk->end_date - (int) date('Y')
                    ),
                    'start_date' => (int) $rtk->start_date,
                    'end_date' => (int) $rtk->end_date,
                    'total' => $items->count(),
                ];
            })
            ->sortBy('province_name')
            ->values();

        // ================================================================
        // STATUS DISTRIBUSI RTK
        // ================================================================

        $rtkStatusDistribution = DB::table(
            'rencana_tenaga_kerjas'
        )
            ->select(
                'status_verification as status',
                DB::raw('count(*) as total')
            )
            // RTK dikelompokkan per status_verification.
            //
            // Khusus status "pending" (Menunggu Persetujuan), berlaku aturan:
            //   is_active           = 1
            //   status_verification = pending
            //   type IN (Provinsi, Kab/Kota)
            //
            // is_active BUKAN penanda menunggu persetujuan. RTK yang sudah
            // approved + valid tetap is_active = 1, namun TIDAK dihitung di
            // sini. Penanda utamanya adalah status_verification = pending.
            ->where(function ($query) {
                $query
                    ->where(
                        'status_verification',
                        '!=',
                        RTKStatusVerification::PENDING->value
                    )
                    ->orWhere(function ($q) {
                        $q
                            ->where(
                                'status_verification',
                                RTKStatusVerification::PENDING->value
                            )
                            ->where('is_active', true)
                            ->whereIn('type', [
                                TypeRtk::PROVINSI->value,
                                TypeRtk::KAB_KOTA->value,
                            ]);
                    });
            })
            ->groupBy('status_verification')
            ->pluck('total', 'status');

        $maxOptionYear = !empty($availableRtkEndYears)
            ? max($availableRtkEndYears)
            : $currentYear;

        $minOptionYear = $currentYear;

        $rtkYearsOptions = [];

        for (
            $y = $maxOptionYear;
            $y >= $minOptionYear;
            $y--
        ) {
            $rtkYearsOptions[] = $y;
        }

        $minAvailableEndYear = !empty($availableRtkEndYears)
            ? min($availableRtkEndYears)
            : $currentYear;

        $maxAvailableEndYear = !empty($availableRtkEndYears)
            ? max($availableRtkEndYears)
            : $currentYear;

        $rtkEndYearsOptions = [];

        for (
            $y = $maxAvailableEndYear;
            $y >= $minAvailableEndYear;
            $y--
        ) {
            $rtkEndYearsOptions[] = $y;
        }

        // ====================================================================
        // DATA PERSETUJUAN
        // ====================================================================
        //
        // Yang ditampilkan pada card "Perlu Persetujuan":
        //
        // 1. Projek dengan status draft
        // 2. RTK Acuan yang:
        //    - status_verifikasi = PENDING
        //    - memiliki dokumen RTK
        //    - dokumen RTK tersebut bertipe PROVINSI
        //
        // RTK Provinsi biasa TIDAK dimasukkan ke card ini.
        // ====================================================================

        // ================================================================
        // 1. PROJEK PENDING / DRAFT
        // ================================================================

        $pendingProjects = Project::whereIn(
            DB::raw('LOWER(status)'),
            ['draft']
        )
            ->get()
            ->map(function ($item) {

                $regionName = 'Daerah';

                // a. Coba ambil dari tabel data RTK Projek
                $rtkData = \Modules\Project\Models\ProjectRtkData::where(
                    'project_id',
                    $item->id
                )->first();

                if (
                    $rtkData &&
                    !empty($rtkData->nama_daerah)
                ) {
                    $regionName = $rtkData->nama_daerah;
                } else {

                    // b. Jika belum ada, ambil dari user_scopes
                    $userId = $item->created_by
                        ?? $item->team_leader;

                    if ($userId) {

                        $scope = DB::table('user_scopes')
                            ->where('user_id', $userId)
                            ->first();

                        if ($scope) {

                            if (!empty($scope->regency_code)) {

                                $regionName =
                                    \Modules\MasterData\Models\Regency::where(
                                        'code',
                                        $scope->regency_code
                                    )->value('name')
                                    ?? $regionName;
                            } elseif (!empty($scope->province_code)) {

                                $regionName =
                                    \Modules\MasterData\Models\Province::where(
                                        'code',
                                        $scope->province_code
                                    )->value('name')
                                    ?? $regionName;
                            }
                        }
                    }
                }

                return [
                    'id' => $item->id,
                    'category' => 'Projek (' . ucfirst(
                        $item->type ?? 'Daerah'
                    ) . ')',
                    'title' => $item->name,
                    'subtitle' => $regionName,
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at
                        ? $item->created_at->diffForHumans()
                        : '-',
                    'type' => 'project',
                    'badge_color' => 'bg-[#547996] text-white border-transparent',
                    'url' => route(
                        'admin-pusat.project.show',
                        $item->id
                    ),
                ];
            });

        // ====================================================================
        // DATA PERSETUJUAN
        // ====================================================================

        // ================================================================
        // 1. PROJEK PENDING & DRAFT
        // ================================================================

        $pendingProjects = Project::whereIn(
            DB::raw('LOWER(status)'),
            ['draft']
        )
            ->get()
            ->map(function ($item) {

                $regionName = 'Daerah';

                // a. Coba ambil dari tabel data RTK Projek
                $rtkData = \Modules\Project\Models\ProjectRtkData::where(
                    'project_id',
                    $item->id
                )->first();

                if (
                    $rtkData &&
                    !empty($rtkData->nama_daerah)
                ) {
                    $regionName = $rtkData->nama_daerah;
                } else {

                    // b. Jika belum ada, ambil dari user_scopes
                    $userId = $item->created_by
                        ?? $item->team_leader;

                    if ($userId) {

                        $scope = DB::table('user_scopes')
                            ->where('user_id', $userId)
                            ->first();

                        if ($scope) {

                            if (!empty($scope->regency_code)) {

                                $regionName =
                                    \Modules\MasterData\Models\Regency::where(
                                        'code',
                                        $scope->regency_code
                                    )->value('name')
                                    ?? $regionName;
                            } elseif (!empty($scope->province_code)) {

                                $regionName =
                                    \Modules\MasterData\Models\Province::where(
                                        'code',
                                        $scope->province_code
                                    )->value('name')
                                    ?? $regionName;
                            }
                        }
                    }
                }

                return [
                    'id' => $item->id,
                    'category' => 'Projek (' . ucfirst(
                        $item->type ?? 'Daerah'
                    ) . ')',
                    'title' => $item->name,
                    'subtitle' => $regionName,
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at
                        ? $item->created_at->diffForHumans()
                        : '-',
                    'type' => 'project',
                    'badge_color' =>
                    'bg-[#547996] text-white border-transparent',
                    'url' => route(
                        'admin-pusat.project.show',
                        $item->id
                    ),
                ];
            });


        // ================================================================
        // 1. RTK PROVINSI
        // ================================================================
        $pendingRtk = RencanaTenagaKerja::with(['province'])
            ->where('is_active', true)
            ->where('type', TypeRtk::PROVINSI->value)
            ->where(function ($query) {
                $query->where('status_verification', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::APPROVED->value)
                            ->where('status_document', '!=', \Modules\RTK\Enums\StatusDocument::VALID->value);
                    });
            })
            ->latest()
            ->get()
            ->unique('province_code')
            ->map(function ($item) {
                $provinceCode = $item->province_code ?? $item->province?->code;

                $statusVerifVal = is_object($item->status_verification) ? $item->status_verification->value : $item->status_verification;
                $statusDocVal   = is_object($item->status_document) ? $item->status_document->value : $item->status_document;

                $isVerifApproved = ($statusVerifVal === RTKStatusVerification::APPROVED->value || $statusVerifVal === 'approved');
                $isDocValid      = ($statusDocVal === \Modules\RTK\Enums\StatusDocument::VALID->value || $statusDocVal === 'valid');

                return [
                    'id' => $item->id,
                    'category' => 'RTK Provinsi',
                    'title' => $item->name,
                    'subtitle' => $item->province?->name ?? 'Provinsi',
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at
                        ? $item->created_at->diffForHumans()
                        : '-',
                    'type' => 'rtk',

                    'url' => $provinceCode
                        ? route('admin-pusat.rtkd.show-province', $provinceCode)
                        : route('admin-pusat.rtkd.index'),

                    'verification_label' => $isVerifApproved
                        ? 'Sudah diverifikasi'
                        : 'Menunggu diverifikasi',

                    'verification_color' => $isVerifApproved
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',

                    'document_label' => $isDocValid
                        ? 'Valid'
                        : 'Menunggu persetujuan',

                    'document_color' => $isDocValid
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',

                    // Action
                    'action_label' => $isVerifApproved
                        ? 'Tinjau Dokumen'
                        : 'Tinjau Verifikasi',
                ];
            });

        $allPendingApprovals = $pendingRtk
            ->concat($pendingProjects)
            ->sortByDesc('created_at')
            ->values();

        // ================================================================
        // AJAX PAGINATION UNTUK INFINITE SCROLL
        // ================================================================

        if ($request->ajax()) {

            // ------------------------------------------------------------
            // Pagination Card Persetujuan
            // ------------------------------------------------------------

            if ($request->has('pending_page')) {

                $page = (int) $request->input(
                    'pending_page',
                    1
                );

                $perPage = 10;

                $pagedItems = $allPendingApprovals
                    ->slice(
                        ($page - 1) * $perPage,
                        $perPage
                    )
                    ->values();

                $hasMore =
                    $allPendingApprovals->count()
                    > ($page * $perPage);

                return response()->json([
                    'data' => $pagedItems,
                    'has_more' => $hasMore,
                    'page' => $page,
                    'total' => $allPendingApprovals->count(),
                ]);
            }

            // ------------------------------------------------------------
            // Filter RTK End Year
            // ------------------------------------------------------------

            if ($request->has('rtk_end_year')) {

                return response()->json([
                    'rtkMasaBerakhirPerProvinsi' =>
                    $rtkMasaBerakhirPerProvinsi,
                ]);
            }

            // ------------------------------------------------------------
            // Filter RTK Year
            // ------------------------------------------------------------

            if ($request->has('rtk_year')) {

                return response()->json([
                    'rtkMasaAktifPerProvinsi' =>
                    $rtkMasaAktifPerProvinsi,
                ]);
            }

            // ------------------------------------------------------------
            // Filter SDM Year
            // ------------------------------------------------------------

            if ($request->has('sdm_year')) {

                return response()->json([
                    'sdmPerProvinsi' =>
                    $sdmPerProvinsi,
                ]);
            }

            return response()->json([
                'sdmPerProvinsi' =>
                $sdmPerProvinsi,

                'rtkMasaAktifPerProvinsi' =>
                $rtkMasaAktifPerProvinsi,

                'rtkMasaBerakhirPerProvinsi' =>
                $rtkMasaBerakhirPerProvinsi,
            ]);
        }

        // ================================================================
        // INITIAL DATA CARD PERSETUJUAN
        // ================================================================

        $initialPendingApprovals = $allPendingApprovals
            ->slice(0, 10)
            ->values();

        $hasMorePendingApprovals =
            $allPendingApprovals->count() > 10;

        // ================================================================
        // VIEW
        // ================================================================

        return view(
            'dashboard::pages.admin-pusat.index',
            [
                'user' => $user,

                'totalAdminPusat' =>
                $totalAdminPusat,

                'adminAktif' =>
                $adminAktif,

                'aktivitasAdmin' =>
                $aktivitasAdmin,

                'genderMale' =>
                $genderMale,

                'genderFemale' =>
                $genderFemale,

                'sdmPerProvinsi' =>
                $sdmPerProvinsi,

                'rtkMasaAktifPerProvinsi' =>
                $rtkMasaAktifPerProvinsi,

                'rtkMasaBerakhirPerProvinsi' =>
                $rtkMasaBerakhirPerProvinsi,

                'rtkStatusDistribution' =>
                $rtkStatusDistribution,

                'sdmYears' =>
                $sdmYears,

                'selectedSdmYear' =>
                $selectedSdmYear,

                'selectedRtkYear' =>
                $selectedRtkYear,

                'selectedRtkEndYear' =>
                $selectedRtkEndYear,

                'rtkYearsOptions' =>
                $rtkYearsOptions,

                'rtkEndYearsOptions' =>
                $rtkEndYearsOptions,

                'initialPendingApprovals' =>
                $initialPendingApprovals,

                'hasMorePendingApprovals' =>
                $hasMorePendingApprovals,

                'totalPendingApprovals' =>
                $allPendingApprovals->count(),
            ]
        );
    }

    /**
     * Profile page.
     */
    public function profile(Request $request)
    {
        $user = Auth::user();

        return view(
            'dashboard::pages.admin-pusat.profile',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Store or update profile.
     */
    public function storeOrUpdateProfile(
        UpdateProfileRequest $request
    ): RedirectResponse {

        $user = Auth::user();

        $this->dashbordService->updateProfile(
            $user,
            $request->validated()
        );

        ToastMagic::success(
            "Profile berhasil diupdate!"
        );

        return to_route(
            'admin-pusat.profile'
        );
    }
}
