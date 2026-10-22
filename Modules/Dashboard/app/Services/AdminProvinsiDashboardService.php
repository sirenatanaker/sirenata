<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Creasi\Nusa\Models\Regency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Models\RencanaTenagaKerja;
use Modules\User\Models\UserScope;

class AdminProvinsiDashboardService
{
    /**
     * Get aggregated dashboard data for Admin Provinsi.
     */
    public function getDashboardData(
        User $user,
        ?int $selectedSdmYear = null,
        string|int $selectedRtkYear = 'all',
        string|int $selectedRtkEndYear = 'all'
    ): array {
        $userScope = UserScope::where('user_id', $user->id)->first();
        $provinceCode = $userScope ? $userScope->province_code : null;

        $currentYear = (int) date('Y');
        $selectedSdmYear = $selectedSdmYear ?: $currentYear;

        // SDM Years options
        $sdmYears = [];
        for ($y = $currentYear; $y >= $currentYear - 15; $y--) {
            $sdmYears[] = $y;
        }

        // 1. SDM per Kab/Kota
        $sdmPerKabKota = $this->getSdmPerKabKota($provinceCode, $selectedSdmYear);

        // 2. Gender stats di provinsi
        $genders = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('roles.name', 'user')
            ->where('user_scopes.province_code', $provinceCode)
            ->select('user_profiles.gender', DB::raw('count(*) as total'))
            ->groupBy('user_profiles.gender')
            ->pluck('total', 'gender');

        $genderMale = $genders->get('male', 0);
        $genderFemale = $genders->get('female', 0);

        // 3. Masa Aktif RTK per Kab/Kota
        $availableRtkYears = RencanaTenagaKerja::where('type', TypeRtk::KAB_KOTA->value)
            ->where('province_code', $provinceCode)
            ->berlaku()
            ->pluck('start_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaAktifPerKabKota = $this->getRtkMasaAktifPerKabKota($provinceCode, $selectedRtkYear);

        // 4. Masa Berakhir RTK per Kab/Kota
        $availableRtkEndYears = RencanaTenagaKerja::where('type', TypeRtk::KAB_KOTA->value)
            ->where('province_code', $provinceCode)
            ->berlaku()
            ->pluck('end_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaBerakhirPerKabKota = $this->getRtkMasaBerakhirPerKabKota($provinceCode, $selectedRtkEndYear);

        // 5. Status Distribusi RTK di provinsi ini
        $rtkStatusDistribution = DB::table('rencana_tenaga_kerjas')
            ->where('province_code', $provinceCode)
            ->where('type', TypeRtk::KAB_KOTA->value)
            ->where(function ($query) {
                $query->where('status_verification', '!=', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status_verification', 'pending')
                            ->where('is_active', 1);
                    });
            })
            ->select('status_verification as status', DB::raw('count(*) as total'))
            ->groupBy('status_verification')
            ->pluck('total', 'status');

        // 6. Year Filter Options
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

        // 7. Data Perlu Tindakan — RTK Kab/Kota
        $allPendingApprovals = $this->getAllPendingApprovals($provinceCode);

        return [
            'user' => $user,
            'sdmPerKabKota' => $sdmPerKabKota,
            'sdmMonthlyTrend' => $this->getSdmMonthlyTrend($provinceCode, $selectedSdmYear),
            'genderMale' => $genderMale,
            'genderFemale' => $genderFemale,
            'rtkMasaAktifPerKabKota' => $rtkMasaAktifPerKabKota,
            'rtkMasaBerakhirPerKabKota' => $rtkMasaBerakhirPerKabKota,
            'rtkStatusDistribution' => $rtkStatusDistribution,
            'sdmYears' => $sdmYears,
            'selectedSdmYear' => $selectedSdmYear,
            'selectedRtkYear' => $selectedRtkYear,
            'selectedRtkEndYear' => $selectedRtkEndYear,
            'rtkYearsOptions' => $rtkYearsOptions,
            'rtkEndYearsOptions' => $rtkEndYearsOptions,
            'initialPendingApprovals' => $allPendingApprovals->slice(0, 10)->values(),
            'hasMorePendingApprovals' => $allPendingApprovals->count() > 10,
            'totalPendingApprovals' => $allPendingApprovals->count(),
        ];
    }

    /**
     * Get SDM per Kab/Kota for a province.
     */
    public function getSdmPerKabKota(?string $provinceCode, int $selectedSdmYear): Collection
    {
        $userCountsByRegency = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->where('user_scopes.province_code', $provinceCode)
            ->whereYear('users.created_at', $selectedSdmYear)
            ->whereNotNull('user_scopes.regency_code')
            ->select('user_scopes.regency_code', DB::raw('count(*) as total'))
            ->groupBy('user_scopes.regency_code')
            ->get();

        $regencyCodes = $userCountsByRegency->pluck('regency_code')->unique()->toArray();
        $regencies = Regency::whereIn('code', $regencyCodes)->pluck('name', 'code');

        return $userCountsByRegency->map(function ($row) use ($regencies) {
            $code = $row->regency_code;
            return (object) [
                'regency_code' => $code,
                'regency_name' => collect(explode(' ', $regencies[$code] ?? 'Unknown (' . $code . ')'))
                    ->map(fn($w) => ucfirst(strtolower($w)))
                    ->join(' '),
                'total' => (int) $row->total,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * Get monthly SDM registration trend (Jan - Dec) for a year in a province.
     */
    public function getSdmMonthlyTrend(?string $provinceCode, int $selectedSdmYear): array
    {
        $monthlyCounts = DB::table('users')
            ->join('user_scopes', 'users.id', '=', 'user_scopes.user_id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->where('user_scopes.province_code', $provinceCode)
            ->whereYear('users.created_at', $selectedSdmYear)
            ->selectRaw('MONTH(users.created_at) as month, count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = (int) ($monthlyCounts[$m] ?? 0);
        }
        return $months;
    }

    /**
     * Get RTK Masa Aktif per Kab/Kota.
     */
    public function getRtkMasaAktifPerKabKota(?string $provinceCode, string|int $selectedRtkYear): Collection
    {
        $queryRtk = RencanaTenagaKerja::where('type', TypeRtk::KAB_KOTA->value)
            ->where('province_code', $provinceCode)
            ->berlaku();

        if ($selectedRtkYear !== 'all') {
            $queryRtk->where('end_date', '>=', (int) $selectedRtkYear);
        }

        $rtkKabKota = $queryRtk->get();
        $rtkRegencyCodes = $rtkKabKota->pluck('regency_code')->toArray();
        $rtkRegencyNames = Regency::whereIn('code', $rtkRegencyCodes)->pluck('name', 'code');

        return $rtkKabKota->map(function ($rtk) use ($rtkRegencyNames) {
            $rawName = $rtkRegencyNames[$rtk->regency_code] ?? 'Unknown';
            return (object) [
                'regency_name' => collect(explode(' ', $rawName))->map(fn($w) => ucfirst(strtolower($w)))->join(' '),
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => $rtk->start_date,
                'end_date' => $rtk->end_date,
            ];
        })->sortBy('regency_name')->values();
    }

    /**
     * Get RTK Masa Berakhir per Kab/Kota.
     */
    public function getRtkMasaBerakhirPerKabKota(?string $provinceCode, string|int $selectedRtkEndYear): Collection
    {
        $queryRtkEnd = RencanaTenagaKerja::where('type', TypeRtk::KAB_KOTA->value)
            ->where('province_code', $provinceCode)
            ->berlaku();

        if ($selectedRtkEndYear !== 'all') {
            $queryRtkEnd->where('end_date', (int) $selectedRtkEndYear);
        }

        $rtkKabKotaEnd = $queryRtkEnd->get();
        $rtkEndRegencyCodes = $rtkKabKotaEnd->pluck('regency_code')->toArray();
        $rtkEndRegencyNames = Regency::whereIn('code', $rtkEndRegencyCodes)->pluck('name', 'code');

        return $rtkKabKotaEnd->map(function ($rtk) use ($rtkEndRegencyNames) {
            $rawName = $rtkEndRegencyNames[$rtk->regency_code] ?? 'Unknown';
            return (object) [
                'regency_name' => collect(explode(' ', $rawName))->map(fn($w) => ucfirst(strtolower($w)))->join(' '),
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => $rtk->start_date,
                'end_date' => $rtk->end_date,
            ];
        })->sortBy('regency_name')->values();
    }

    /**
     * Get All Pending Approvals for Admin Provinsi.
     */
    public function getAllPendingApprovals(?string $provinceCode): Collection
    {
        return RencanaTenagaKerja::query()
            ->where('province_code', $provinceCode)
            ->where('type', TypeRtk::KAB_KOTA->value)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('status_verification', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::APPROVED->value)
                            ->where('status_document', '!=', 'valid');
                    });
            })
            ->with(['regency'])
            ->latest()
            ->get()
            ->map(function ($item) {
                $regencyName = $item->regency?->name ?? 'Kabupaten/Kota';
                $isVerificationPending = $item->status_verification === RTKStatusVerification::PENDING;

                return [
                    'id' => $item->id,
                    'category' => 'RTK Kab/Kota',
                    'title' => $item->name,
                    'subtitle' => $regencyName,
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at ? $item->created_at->diffForHumans() : '-',
                    'type' => 'rtk',
                    'verification_label' => $isVerificationPending ? 'Belum diverifikasi' : 'Sudah diverifikasi',
                    'verification_color' => $isVerificationPending
                        ? 'bg-amber-50 text-amber-700 border border-amber-200'
                        : 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    'document_label' => $item->status_document === 'valid' ? 'Valid' : 'Menunggu persetujuan',
                    'document_color' => $item->status_document === 'valid'
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',
                    'badge_color' => 'bg-[#13416B] text-white border-transparent',
                    'action_label' => $isVerificationPending ? 'Tinjau Verifikasi' : 'Tinjau Dokumen',
                    'url' => $item->regency_code
                        ? route('admin-province.laporan.show-regency', $item->regency_code)
                        : route('admin-province.laporan.index'),
                ];
            })
            ->values();
    }

    /**
     * Get paginated pending approvals for AJAX.
     */
    public function getPendingApprovalsPage(?string $provinceCode, int $page, int $perPage = 10): array
    {
        $all = $this->getAllPendingApprovals($provinceCode);
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
