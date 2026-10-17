<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Creasi\Nusa\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Project\Models\Project;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\RTK\Enums\StatusDocument;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Models\RencanaTenagaKerja;
use Modules\User\Models\UserProfile;
use Spatie\Activitylog\Models\Activity;

class AdminPusatDashboardService
{
    /**
     * Get aggregated dashboard data for Admin Pusat.
     */
    public function getDashboardData(
        User $user,
        ?int $selectedSdmYear = null,
        string|int $selectedRtkYear = 'all',
        string|int $selectedRtkEndYear = 'all'
    ): array {
        $currentYear = (int) date('Y');
        $selectedSdmYear = $selectedSdmYear ?: $currentYear;

        // 1. Stats Admin & Activity
        $totalAdminPusat = User::role('admin-pusat')->count();
        $adminAktif = User::role(['admin-province', 'admin-kab-kota'])->count();
        $aktivitasAdmin = Activity::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 2. Gender distribution
        $genderMale = UserProfile::where('gender', 'male')->count();
        $genderFemale = UserProfile::where('gender', 'female')->count();

        // 3. SDM Years options
        $sdmYears = [];
        for ($y = $currentYear; $y >= $currentYear - 15; $y--) {
            $sdmYears[] = $y;
        }

        // 4. SDM per Provinsi
        $sdmPerProvinsi = $this->getSdmPerProvinsi($selectedSdmYear);

        // 5. Masa Aktif RTK per Provinsi
        $availableRtkYears = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)
            ->berlaku()
            ->pluck('start_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaAktifPerProvinsi = $this->getRtkMasaAktifPerProvinsi($selectedRtkYear);

        // 6. Masa Berakhir RTK per Provinsi
        $availableRtkEndYears = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)
            ->berlaku()
            ->pluck('end_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaBerakhirPerProvinsi = $this->getRtkMasaBerakhirPerProvinsi($selectedRtkEndYear);

        // 7. Status Distribusi RTK
        $rtkStatusDistribution = DB::table('rencana_tenaga_kerjas')
            ->select('status_verification as status', DB::raw('count(*) as total'))
            ->where(function ($query) {
                $query->where('status_verification', '!=', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::PENDING->value)
                            ->where('is_active', true)
                            ->whereIn('type', [
                                TypeRtk::PROVINSI->value,
                                TypeRtk::KAB_KOTA->value,
                            ]);
                    });
            })
            ->groupBy('status_verification')
            ->pluck('total', 'status');

        // 8. Year Filter Options
        $maxOptionYear = !empty($availableRtkEndYears) ? max($availableRtkEndYears) : $currentYear;
        $minOptionYear = $currentYear;
        $rtkYearsOptions = [];
        for ($y = $maxOptionYear; $y >= $minOptionYear; $y--) {
            $rtkYearsOptions[] = $y;
        }

        $minAvailableEndYear = !empty($availableRtkEndYears) ? min($availableRtkEndYears) : $currentYear;
        $maxAvailableEndYear = !empty($availableRtkEndYears) ? max($availableRtkEndYears) : $currentYear;
        $rtkEndYearsOptions = [];
        for ($y = $maxAvailableEndYear; $y >= $minAvailableEndYear; $y--) {
            $rtkEndYearsOptions[] = $y;
        }

        // 9. Pending Approvals
        $allPendingApprovals = $this->getAllPendingApprovals();
        $initialPendingApprovals = $allPendingApprovals->slice(0, 10)->values();
        $hasMorePendingApprovals = $allPendingApprovals->count() > 10;

        return [
            'user' => $user,
            'totalAdminPusat' => $totalAdminPusat,
            'adminAktif' => $adminAktif,
            'aktivitasAdmin' => $aktivitasAdmin,
            'genderMale' => $genderMale,
            'genderFemale' => $genderFemale,
            'sdmPerProvinsi' => $sdmPerProvinsi,
            'rtkMasaAktifPerProvinsi' => $rtkMasaAktifPerProvinsi,
            'rtkMasaBerakhirPerProvinsi' => $rtkMasaBerakhirPerProvinsi,
            'rtkStatusDistribution' => $rtkStatusDistribution,
            'sdmYears' => $sdmYears,
            'selectedSdmYear' => $selectedSdmYear,
            'selectedRtkYear' => $selectedRtkYear,
            'selectedRtkEndYear' => $selectedRtkEndYear,
            'rtkYearsOptions' => $rtkYearsOptions,
            'rtkEndYearsOptions' => $rtkEndYearsOptions,
            'initialPendingApprovals' => $initialPendingApprovals,
            'hasMorePendingApprovals' => $hasMorePendingApprovals,
            'totalPendingApprovals' => $allPendingApprovals->count(),
        ];
    }

    /**
     * Get SDM per Provinsi grouped by gender.
     */
    public function getSdmPerProvinsi(int $selectedSdmYear): Collection
    {
        $userCountsByProvince = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->whereYear('users.created_at', $selectedSdmYear)
            ->whereNotNull('user_scopes.province_code')
            ->select('user_scopes.province_code', 'user_profiles.gender', DB::raw('count(*) as total'))
            ->groupBy('user_scopes.province_code', 'user_profiles.gender')
            ->get();

        $provinceCodes = $userCountsByProvince->pluck('province_code')->unique()->toArray();
        $provinces = Province::whereIn('code', $provinceCodes)->pluck('name', 'code');

        return collect($provinceCodes)->map(function ($code) use ($userCountsByProvince, $provinces) {
            $provinceData = $userCountsByProvince->where('province_code', $code);
            $maleCount = $provinceData->where('gender', 'male')->first()->total ?? 0;
            $femaleCount = $provinceData->where('gender', 'female')->first()->total ?? 0;

            return (object) [
                'province_code' => $code,
                'province_name' => collect(explode(' ', $provinces[$code] ?? 'Unknown (' . $code . ')'))
                    ->map(fn($w) => ucfirst(strtolower($w)))
                    ->join(' '),
                'male' => $maleCount,
                'female' => $femaleCount,
                'total' => $maleCount + $femaleCount,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * Get RTK Masa Aktif per Provinsi.
     */
    public function getRtkMasaAktifPerProvinsi(string|int $selectedRtkYear): Collection
    {
        $queryRtk = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)->berlaku();

        if ($selectedRtkYear !== 'all') {
            $queryRtk->where('end_date', '>=', (int) $selectedRtkYear);
        }

        $rtkProvinsi = $queryRtk->get();
        $rtkProvinceCodes = $rtkProvinsi->pluck('province_code')->toArray();
        $rtkProvinceNames = Province::whereIn('code', $rtkProvinceCodes)->pluck('name', 'code');

        return $rtkProvinsi->groupBy('province_code')->map(function ($items, $code) use ($rtkProvinceNames) {
            $rtk = $items->first();
            return (object) [
                'province_name' => $rtkProvinceNames[$code] ?? 'Unknown',
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => (int) $rtk->start_date,
                'end_date' => (int) $rtk->end_date,
                'total' => $items->count(),
            ];
        })->sortBy('province_name')->values();
    }

    /**
     * Get RTK Masa Berakhir per Provinsi.
     */
    public function getRtkMasaBerakhirPerProvinsi(string|int $selectedRtkEndYear): Collection
    {
        $queryRtkEnd = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)->berlaku();

        if ($selectedRtkEndYear !== 'all') {
            $queryRtkEnd->where('end_date', (int) $selectedRtkEndYear);
        }

        $rtkProvinsiEnd = $queryRtkEnd->get();
        $rtkEndProvinceCodes = $rtkProvinsiEnd->pluck('province_code')->toArray();
        $rtkEndProvinceNames = Province::whereIn('code', $rtkEndProvinceCodes)->pluck('name', 'code');

        return $rtkProvinsiEnd->groupBy('province_code')->map(function ($items, $code) use ($rtkEndProvinceNames) {
            $rtk = $items->first();
            return (object) [
                'province_name' => $rtkEndProvinceNames[$code] ?? 'Unknown',
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => (int) $rtk->start_date,
                'end_date' => (int) $rtk->end_date,
                'total' => $items->count(),
            ];
        })->sortBy('province_name')->values();
    }

    /**
     * Get all pending approvals (Projects + RTK).
     */
    public function getAllPendingApprovals(): Collection
    {
        $pendingProjects = Project::whereIn(DB::raw('LOWER(status)'), ['draft'])
            ->get()
            ->map(function ($item) {
                $regionName = 'Daerah';
                $rtkData = \Modules\Project\Models\ProjectRtkData::where('project_id', $item->id)->first();

                if ($rtkData && !empty($rtkData->nama_daerah)) {
                    $regionName = $rtkData->nama_daerah;
                } else {
                    $userId = $item->created_by ?? $item->team_leader;
                    if ($userId) {
                        $scope = DB::table('user_scopes')->where('user_id', $userId)->first();
                        if ($scope) {
                            if (!empty($scope->regency_code)) {
                                $regionName = \Modules\MasterData\Models\Regency::where('code', $scope->regency_code)->value('name') ?? $regionName;
                            } elseif (!empty($scope->province_code)) {
                                $regionName = \Modules\MasterData\Models\Province::where('code', $scope->province_code)->value('name') ?? $regionName;
                            }
                        }
                    }
                }

                return [
                    'id' => $item->id,
                    'category' => 'Projek (' . ucfirst($item->type ?? 'Daerah') . ')',
                    'title' => $item->name,
                    'subtitle' => $regionName,
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at ? $item->created_at->diffForHumans() : '-',
                    'type' => 'project',
                    'badge_color' => 'bg-[#547996] text-white border-transparent',
                    'url' => route('admin-pusat.project.show', $item->id),
                ];
            });

        $pendingRtk = RencanaTenagaKerja::with(['province'])
            ->where('is_active', true)
            ->where('type', TypeRtk::PROVINSI->value)
            ->where(function ($query) {
                $query->where('status_verification', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::APPROVED->value)
                            ->where('status_document', '!=', StatusDocument::VALID->value);
                    });
            })
            ->latest()
            ->get()
            ->unique('province_code')
            ->map(function ($item) {
                $provinceCode = $item->province_code ?? $item->province?->code;
                $statusVerifVal = is_object($item->status_verification) ? $item->status_verification->value : $item->status_verification;
                $statusDocVal = is_object($item->status_document) ? $item->status_document->value : $item->status_document;

                $isVerifApproved = ($statusVerifVal === RTKStatusVerification::APPROVED->value || $statusVerifVal === 'approved');
                $isDocValid = ($statusDocVal === StatusDocument::VALID->value || $statusDocVal === 'valid');

                return [
                    'id' => $item->id,
                    'category' => 'RTK Provinsi',
                    'title' => $item->name,
                    'subtitle' => $item->province?->name ?? 'Provinsi',
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at ? $item->created_at->diffForHumans() : '-',
                    'type' => 'rtk',
                    'url' => $provinceCode
                        ? route('admin-pusat.rtkd.show-province', $provinceCode)
                        : route('admin-pusat.rtkd.index'),
                    'verification_label' => $isVerifApproved ? 'Sudah diverifikasi' : 'Menunggu diverifikasi',
                    'verification_color' => $isVerifApproved
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',
                    'document_label' => $isDocValid ? 'Valid' : 'Menunggu persetujuan',
                    'document_color' => $isDocValid
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',
                    'action_label' => $isVerifApproved ? 'Tinjau Dokumen' : 'Tinjau Verifikasi',
                ];
            });

        return $pendingRtk
            ->concat($pendingProjects)
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Get paginated pending approvals for AJAX infinite scroll.
     */
    public function getPendingApprovalsPage(int $page, int $perPage = 10): array
    {
        $all = $this->getAllPendingApprovals();
        $pagedItems = $all->slice(($page - 1) * $perPage, $perPage)->values();
        $hasMore = $all->count() > ($page * $perPage);

        return [
            'data' => $pagedItems,
            'has_more' => $hasMore,
            'page' => $page,
            'total' => $all->count(),
        ];
    }
}
