<?php

namespace Modules\LMS\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\User\Enums\InstitutionType;
use Modules\User\Models\UserScope;

class RekapitulasiService
{
    private const DEFAULT_SORT = 'desc';
    private const DEFAULT_LIMIT = 10;

    /**
     *  Query Rekapitulasi Province
     */
    public function queryRekapitulasiProvince(
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {

        // 1️⃣ Ambil Province (SQLite)
        $provinceQuery = $search
            ? Province::search($search)->orderBy('name')
            : Province::query()->orderBy('name');

        $provinces = $provinceQuery->paginate($limit)->withQueryString();
        $provinceCodes = $provinces->getCollection()
            ->pluck('code')
            ->toArray();

        if (!empty($provinceCodes)) {
            $userCounts = UserScope::query()
                ->select('province_code')
                ->selectRaw('COUNT(DISTINCT user_id) as total_users')
                ->whereIn('province_code', $provinceCodes)
                ->whereNotNull('province_code')
                ->groupBy('province_code')
                ->pluck('total_users', 'province_code');

            // 3️⃣ Attach total_users
            $provinces->getCollection()->transform(function ($province) use ($userCounts) {
                $province->total_users = $userCounts[$province->code] ?? 0;
                return $province;
            });
        }

        return $provinces;
    }

    /**
     *  Paginate Rekapitulasi Province
     */
    public function paginateFilteredRekapitulasiProvince(
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {
        return $this->queryRekapitulasiProvince(
            search: $search,
            limit: $limit
        );
    }

    /**
     *  Query Rekapitulasi Regency Kab/Kota
     */
    public function queryRekapitulasiRegency(string $provinceCode, ?string $search = null, int $limit = self::DEFAULT_LIMIT)
    {
        // 1️⃣ Regency (SQLite - nusa)
        $regencyQuery = $search
            ? Regency::search($search)->where('province_code', $provinceCode)->orderBy('name')
            : Regency::query()->where('province_code', $provinceCode)->orderBy('name');

        $regencies = $regencyQuery->paginate($limit)->withQueryString();

        $regencyCodes = $regencies->getCollection()->pluck('code')->toArray();

        if (empty($regencyCodes)) {
            return $regencies;
        }

        if (!empty($regencyCodes)) {
            $userCounts = UserScope::query()
                ->select('regency_code')
                ->selectRaw('COUNT(DISTINCT user_id) as total_users')
                ->whereIn('regency_code', $regencyCodes)
                ->whereNotNull('regency_code')
                ->groupBy('regency_code')
                ->pluck('total_users', 'regency_code');

            // 3️⃣ Attach total_users
            $regencies->getCollection()->transform(function ($regency) use ($userCounts) {
                $regency->total_users = $userCounts[$regency->code] ?? 0;
                return $regency;
            });
        }

        return $regencies;
    }

    /**
     *  Paginate Rekapitulasi Regency Kab/Kota
     */
    public function paginateFilteredRekapitulasiRegency(
        string $provinceCode,
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {
        return $this->queryRekapitulasiRegency(
            provinceCode: $provinceCode,
            search: $search,
            limit: $limit
        );
    }

    /**
     *  Query Rekapitulasi User
     */
    public function queryUsersByRegion(string $provinceCode, ?string $regencyCode = null, ?string $search = null)
    {
        return User::query()
            ->whereHas('scopeArea', function ($q) use ($provinceCode, $regencyCode) {
                $q->where('province_code', $provinceCode);

                if ($regencyCode) {
                    $q->where('regency_code', $regencyCode);
                }
            })
            ->when($search !== null, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->with('scopeArea.province', 'scopeArea.regency');
    }

    /**
     *  Paginate Rekapitulasi User
     */
    public function paginateFilteredRekapitulasiUser(
        string $provinceCode,
        ?string $regencyCode = null,
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {
        return $this->queryUsersByRegion($provinceCode, $regencyCode, $search)
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     *  Query Rekapitulasi Pusat (Group by instansi)
     */
    public function queryRekapitulasiPusat(?string $search = null)
    {
        return User::query()
            ->whereHas('profile', function ($q) use ($search) {
                $q->where('institution_type', InstitutionType::PUSAT);
                if ($search) {
                    $q->where('instansi', 'like', "%{$search}%");
                }
            })
            ->with('profile')
            ->orderBy('name');
    }

    /**
     *  Paginate Rekapitulasi Pusat (Group by instansi)
     */
    public function paginateFilteredRekapitulasiPusat(
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {
        // Group by instansi
        $query = DB::table('user_profiles')
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('user_profiles.institution_type', InstitutionType::PUSAT)
            ->whereNotNull('user_profiles.instansi')
            ->where('user_profiles.instansi', '!=', '')
            ->when($search, function ($q) use ($search) {
                $q->where('user_profiles.instansi', 'like', "%{$search}%");
            })
            ->select(
                'user_profiles.instansi',
                DB::raw('COUNT(DISTINCT users.id) as total_users')
            )
            ->groupBy('user_profiles.instansi')
            ->orderBy('user_profiles.instansi');

        return $query->paginate($limit)->withQueryString();
    }

    /**
     *  Query Users by Instansi (Pusat)
     */
    public function queryUsersByInstansi(string $instansi, ?string $search = null)
    {
        return User::query()
            ->whereHas('profile', function ($q) use ($instansi) {
                $q->where('institution_type', InstitutionType::PUSAT)
                    ->where('instansi', $instansi);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->with('profile', 'scopeArea.province', 'scopeArea.regency');
    }

    /**
     *  Paginate Users by Instansi (Pusat)
     */
    public function paginateFilteredUsersByInstansi(
        string $instansi,
        ?string $search = null,
        int $limit = self::DEFAULT_LIMIT
    ): LengthAwarePaginator {
        return $this->queryUsersByInstansi($instansi, $search)
            ->paginate($limit)
            ->withQueryString();
    }

    public function getProvinceByCode(?string $provinceCode): ?Province
    {
        return $provinceCode ? Province::find($provinceCode) : null;
    }

    public function getRegencyByCode(?string $regencyCode): ?Regency
    {
        return $regencyCode ? Regency::find($regencyCode) : null;
    }
}