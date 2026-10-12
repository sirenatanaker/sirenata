<?php

namespace App\Livewire\Dashboard\SuperAdmin;

use App\Models\User;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Permission\Models\Permission;
use Modules\Roles\Models\Role;
use Modules\User\Services\UserService;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class UserManagementEdit extends Component
{
    public User $user;

    public $join_date;
    public $email;
    public $full_name;
    public $phone;
    public $instansi;
    public ?string $province = null;
    public ?string $regency  = null;

    public array $roleIds = [];
    public bool $isSsoMode = false;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }
    

    public function mount(User $user)
    {
        $this->isSsoMode = app()->isProduction();
        $user->load(['roles', 'profile', 'scopeArea']);

        $this->user = $user;

        $this->email     = $user->email;
        $this->full_name = $user->profile?->full_name;
        $this->phone     = $user->profile?->phone;
        $this->instansi   = $user->profile?->instansi;
        $this->join_date = $user->created_at->format('Y-m-d');

        $this->roleIds = $user->roles->pluck('uuid')->toArray();

        $this->province = $user->scopeArea?->province_code;
        $this->regency  = $user->scopeArea?->regency_code;
    }


    protected function rules(): array
    {
        $personal = $this->isSsoMode ? 'nullable' : 'required';

        return [
            'full_name' => $personal.'|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'phone' => $personal.'|string|max:20',
            'roleIds' => 'required|array',
            'roleIds.*' => 'required|exists:roles,uuid',
            'instansi' => 'required|string|max:255',
            'province' => 'nullable|string|size:2',
            'regency'  => 'nullable|string|max:5',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'roleIds' => 'role',
        ];
    }

    public function save()
    {
        try {
            $this->validate();
            $data = [
                'email'       => $this->email,
                'instansi'     => $this->instansi,
                'roles'       => $this->roleIds,
                'province'    => $this->province,
                'regency'     => $this->regency,
            ];

            if (! $this->isSsoMode) {
                $data['full_name'] = $this->full_name;
                $data['phone']     = $this->phone;
            }
            
            $this->userService->updateUser($this->user, $data);
            return redirect()->route('super-admin.user-management.index');

        } catch (\Exception $e) {
            ToastMagic::error($e->getMessage());
            throw $e;
        }
    }

    public function updatedProvince($value)             
    {
        // Ganti/clear provinsi selalu mereset kab/kota agar tidak
        // tersisa pilihan dari provinsi sebelumnya.
        $this->regency = null;
    }

    #[Computed()]
    public function provinces()
    {
        return Province::select('code', 'name')->lazy();
    }

    #[Computed()]
    public function regencies()
    {
        return Regency::where('province_code', $this->province)->select('code', 'name')->lazy();
    }

    public function render()
    {
        return view('livewire.dashboard.super-admin.user-management-edit', [
            'roles'       => Role::select('uuid', 'name')->get(),
        ]);
    }
}
