<?php

namespace App\Livewire\Dashboard\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\Roles\Models\Role;
use Modules\User\Services\UserService;


class UserManagementTable extends Component
{
    use WithPagination;

    #[Url]
    public $limit = 10;
    #[Url]
    public $search = '';
    #[Url]
    public $orderBy = 'desc';
    #[Url]
    public ?string $role = null;
    #[Url]
    public ?string $sector = null;
    #[Url]
    public ?string $provinceCode = null;
    #[Url]
    public ?string $regencyCode = null;
    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>|null
     */
    public $user;

    protected UserService $userService;
    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function resetFilter()
    {
        $this->reset(['search', 'limit', 'provinceCode', 'regencyCode']);
        $this->resetPage();
    }

    public function selectRole(?string $role): void
    {
        $this->role = $role ?: null;
        $this->sector = null;
        $this->provinceCode = null;
        $this->regencyCode = null;
        $this->reset(['search']);
        $this->resetPage();
    }

    public function selectSector(?string $sector): void
    {
        $this->sector = $sector ?: null;
        $this->provinceCode = null;
        $this->regencyCode = null;
        $this->reset(['search']);
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLimit(): void
    {
        $this->resetPage();
    }

    public function updatedProvinceCode(): void
    {
        $this->regencyCode = null;
        $this->resetPage();
    }

    public function updatedRegencyCode(): void
    {
        $this->resetPage();
    }

    public function bulkDelete(array $ids)
    {
        $currentUserId = Auth::id();
        $idsToDelete = collect($ids)->reject(fn ($id) => $id == $currentUserId)->all();
        $selfDeleteAttempt = in_array($currentUserId, $ids);

        if (!empty($idsToDelete)) {
            User::whereIn('id', $idsToDelete)->delete();
            $message = count($idsToDelete) . ' user berhasil dihapus.';
            if ($selfDeleteAttempt) {
                $message .= ' Anda tidak dapat menghapus akun Anda sendiri.';
            }
            session()->flash('success', $message);
        } elseif ($selfDeleteAttempt) {
            session()->flash('warning', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->dispatch('bulk-cleared');
        
    }

    public function bulkActivate(array $ids)
    {
        User::whereIn('id', $ids)->update(['is_active' => true]);

        $this->dispatch('bulk-cleared');
        session()->flash('success', count($ids) . ' user berhasil diaktifkan.');
    }

    public function bulkDeactivate(array $ids)
    {
        $currentUserId = Auth::id();
        $idsToDeactivate = collect($ids)->reject(fn ($id) => $id == $currentUserId)->all();
        $selfDeactivationAttempt = in_array($currentUserId, $ids);

        if (!empty($idsToDeactivate)) {
            User::whereIn('id', $idsToDeactivate)->update(['is_active' => false]);
            $message = count($idsToDeactivate) . ' user berhasil dinonaktifkan.';
            if ($selfDeactivationAttempt) {
                $message .= ' Anda tidak dapat menonaktifkan akun Anda sendiri.';
            }
            session()->flash('success', $message);
        } elseif ($selfDeactivationAttempt) {
            session()->flash('warning', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $this->dispatch('bulk-cleared');
    }

    public function render()
    {
        $roleOrder = ['super-admin', 'admin-pusat', 'admin-province', 'admin-kab-kota', 'user'];

        $roleOptions = Role::withCount('users')->get()
            ->sortBy(fn ($role) => ($pos = array_search($role->name, $roleOrder, true)) === false ? 99 : $pos)
            ->values();

        $validRole = $roleOptions->pluck('name')->contains($this->role) ? $this->role : null;

        // Drill-down khusus role user: Swasta / Instansi Provinsi /
        // Instansi Kab-Kota / Lembaga Pusat (seperti rekapitulasi).
        $sectorTypes = [
            'swasta' => 'swasta',
            'instansi-provinsi' => 'provinsi',
            'instansi-kabkota' => 'kab_kota',
            'lembaga-pusat' => 'pusat',
        ];
        $validSector = ($validRole === 'user' && isset($sectorTypes[$this->sector ?? '']))
            ? $this->sector
            : null;
        $institutionType = $validSector ? $sectorTypes[$validSector] : null;

        if ($validRole === 'user') {
            $needsRegion = in_array($validSector, ['instansi-provinsi', 'instansi-kabkota'], true);
            $needsRegency = $validSector === 'instansi-kabkota';
            $showSuratTugas = false;
            $showSektor = false;
            $showProvinceColumn = $needsRegion;
            $showRegencyColumn = $needsRegency;
            $tableReady = (bool) $validSector;
        } else {
            $needsRegion = in_array($validRole, ['admin-province', 'admin-kab-kota'], true);
            $needsRegency = $validRole === 'admin-kab-kota';
            $showSuratTugas = (bool) $validRole;
            $showSektor = (bool) $validRole;
            $showProvinceColumn = $showRegencyColumn = $needsRegion;
            $tableReady = (bool) $validRole;
        }

        $sectorOptions = [];
        if ($validRole === 'user') {
            $countFor = fn (string $type) => User::whereHas('roles', fn ($q) => $q->where('name', 'user'))
                ->whereHas('profile', fn ($q) => $q->where('institution_type', $type))
                ->count();
            $sectorOptions = [
                ['key' => 'swasta', 'label' => 'Swasta', 'desc' => 'Pengguna dari perusahaan', 'icon' => 'fas fa-briefcase', 'count' => $countFor('swasta')],
                ['key' => 'instansi-provinsi', 'label' => 'Instansi Provinsi', 'desc' => 'Hanya kolom provinsi', 'icon' => 'fas fa-map-location-dot', 'count' => $countFor('provinsi')],
                ['key' => 'instansi-kabkota', 'label' => 'Instansi Kab/Kota', 'desc' => 'Kolom provinsi + kab/kota', 'icon' => 'fas fa-location-dot', 'count' => $countFor('kab_kota')],
                ['key' => 'lembaga-pusat', 'label' => 'Lembaga Pusat', 'desc' => 'Lembaga + unit kerja saja', 'icon' => 'fas fa-building-columns', 'count' => $countFor('pusat')],
            ];
        }

        $users = $tableReady
            ? $this->userService->paginateFilteredUsers(
                $this->search,
                $this->orderBy,
                $this->limit,
                $validRole,
                $needsRegion ? $this->provinceCode : null,
                $needsRegency ? $this->regencyCode : null,
                $institutionType
            )
            : null;

        return view('livewire.dashboard.super-admin.user-management-table', [
            'users' => $users,
            'roleOptions' => $roleOptions,
            'selectedRole' => $validRole,
            'sectorOptions' => $sectorOptions,
            'selectedSector' => $validSector,
            'needsRegion' => $needsRegion,
            'needsRegency' => $needsRegency,
            'showSuratTugas' => $showSuratTugas,
            'showSektor' => $showSektor,
            'showProvinceColumn' => $showProvinceColumn,
            'showRegencyColumn' => $showRegencyColumn,
            'provinces' => $needsRegion
                ? Province::select('code', 'name')->orderBy('name', 'asc')->get()
                : collect(),
            'regencies' => ($needsRegency && $this->provinceCode)
                ? Regency::where('province_code', $this->provinceCode)->select('code', 'name')->orderBy('name', 'asc')->get()
                : collect(),
        ]);
    }
}
