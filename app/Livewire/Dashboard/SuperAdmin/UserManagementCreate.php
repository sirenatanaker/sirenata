<?php

namespace App\Livewire\Dashboard\SuperAdmin;


use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Permission\Models\Permission;
use Modules\Roles\Models\Role;
use Modules\User\Services\UserService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Modules\MasterData\Models\Institution;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\User\Enums\InstitutionType;
use Illuminate\Support\Str;

class UserManagementCreate extends Component
{
    use WithFileUploads;

    public const CUSTOM_INSTITUTION = '__custom';
    public const PUSAT_INSTITUTION = 'Kementerian Ketenagakerjaan';

    public $name;
    public $full_name;
    public $email;
    public $phone;
    public $join_date;
    public $province;
    public $regency;
    public $instansiOption;
    public $instansiCustom;
    public $unitKerja;
    public $suratTugas = null;
    public ?string $roleId = null;
    public bool $isSsoMode = false;
    public int $currentStep = 1;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount()
    {
        // Mode SSO (production): nama & telepon diisi otomatis dari
        // Siapkerja, password tidak dikelola manual.
        $this->isSsoMode = app()->isProduction();

        // Preset dari tombol kontekstual ("Tambah <Role>" di tabel role):
        // role langsung terpilih dan form dibuka di tahap 2.
        $preset = strtolower(trim((string) request()->query('role', '')));
        if ($preset !== '' && $preset !== 'user') {
            $role = Role::where('name', $preset)->first();
            if ($role) {
                $this->roleId = $role->uuid;
                $this->currentStep = 2;
            }
        }
    }

    protected function rules(): array
    {
        $personal = $this->isSsoMode ? 'nullable' : 'required';
        $scope = $this->scopeLevel();
        $needsRegion = $scope === 'kabkota' || $scope === 'provinsi';
        // Akun pusat tidak memilih lembaga — instansi diisi mandiri.
        $needsInstansi = $needsRegion;

        return [
            'name' => $personal.'|string|max:255',
            'full_name' => $personal.'|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => $personal.'|string|max:20',
            'roleId' => 'required|string|exists:roles,uuid',
            'province' => ($needsRegion ? 'required' : 'nullable').'|string|size:2',
            'regency'  => ($scope === 'kabkota' ? 'required' : 'nullable').'|string|max:5',
            'instansiOption' => ($needsInstansi ? 'required' : 'nullable').'|string|max:255',
            'instansiCustom' => 'required_if:instansiOption,'.self::CUSTOM_INSTITUTION.'|nullable|string|max:255',
            'unitKerja' => ($scope ? 'required' : 'nullable').'|string|max:255',
            'suratTugas' => ($scope ? 'required' : 'nullable').'|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'roleId' => 'role',
            'instansiOption' => 'instansi',
            'instansiCustom' => 'nama instansi',
            'unitKerja' => 'unit kerja',
            'suratTugas' => 'surat tugas',
        ];
    }

    /**
     * Lingkup wilayah mengikuti role yang dipilih. Role user tidak
     * ditawarkan di form ini (diisi mandiri saat onboarding).
     */
    public function scopeLevel(): ?string
    {
        if (empty($this->roleId)) {
            return null;
        }

        $name = strtolower((string) Role::where('uuid', $this->roleId)->value('name'));

        return match ($name) {
            'admin-kab-kota' => 'kabkota',
            'admin-province' => 'provinsi',
            'super-admin', 'admin-pusat' => 'pusat',
            default => null,
        };
    }

    public function goToStep2(): void
    {
        $this->validate([
            'roleId' => 'required|string|exists:roles,uuid',
        ]);

        $this->currentStep = 2;
    }

    public function goToStep1(): void
    {
        $this->currentStep = 1;
    }

    public function store()
    {
        try {
            $this->validate();

            $scope = $this->scopeLevel();
            $needsInstansi = $scope === 'kabkota' || $scope === 'provinsi';
            $needsDokumen = $scope !== null;

            $suratTugasPath = null;
            if ($needsDokumen && $this->suratTugas) {
                $suratTugasPath = $this->suratTugas->store('surat-tugas', 'public');
            }

            if ($scope === 'pusat') {
                // Akun pusat otomatis terafiliasi Kementerian Ketenagakerjaan.
                Institution::firstOrCreate(
                    ['name' => self::PUSAT_INSTITUTION, 'type' => 'pusat'],
                    ['is_active' => true]
                );
            }

            $instansi = $needsInstansi && $this->instansiOption === self::CUSTOM_INSTITUTION
                ? trim((string) $this->instansiCustom)
                : ($needsInstansi ? $this->instansiOption : ($scope === 'pusat' ? self::PUSAT_INSTITUTION : null));

            if ($needsInstansi && $this->instansiOption === self::CUSTOM_INSTITUTION && $instansi !== '') {
                // Samakan dengan onboarding: instansi baru dicatat ke master.
                Institution::firstOrCreate(
                    [
                        'name' => $instansi,
                        'type' => $scope === 'pusat' ? 'pusat' : 'daerah',
                        'province_code' => $scope === 'pusat' ? null : $this->province,
                        'regency_code' => $scope === 'kabkota' ? $this->regency : null,
                    ],
                    ['is_active' => true]
                );
            }

            $data = [
                'name'        => $this->name,
                'email'       => $this->email,
                'full_name'   => $this->full_name,
                'phone'       => $this->phone,
                'instansi'     => $instansi,
                'unit_kerja'   => $this->unitKerja,
                'surat_tugas'  => $suratTugasPath,
                'institution_type' => match ($scope) {
                    'pusat' => InstitutionType::PUSAT->value,
                    'provinsi' => InstitutionType::PROVINSI->value,
                    'kabkota' => InstitutionType::KAB_KOTA->value,
                    default => null,
                },
                'roles'        => $this->roleId ? [$this->roleId] : [],
                'province'    => $this->province,
                'regency'     => $this->regency,
                // Mode SSO: password acak tak terpakai (login via Siapkerja).
                // Lokal: default 'password' agar bisa login form.
                'password'    => $this->isSsoMode ? Str::random(32) : 'password',
            ];

            $this->userService->createUser($data);

            return redirect()->route('super-admin.user-management.index');
        } catch (\Exception $e) {
            ToastMagic::error($e->getMessage());
            throw $e;
        }
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

    #[Computed()]
    public function institutions()
    {
        $scope = $this->scopeLevel();
        $query = Institution::where('is_active', true)->orderBy('name', 'asc');

        if ($scope === 'provinsi' && ! empty($this->province)) {
            return $query->where('type', 'daerah')
                ->where('province_code', $this->province)
                ->where(fn ($q) => $q->whereNull('regency_code')->orWhere('regency_code', ''))
                ->select('id', 'name')->get();
        }

        if ($scope === 'kabkota' && ! empty($this->regency)) {
            return $query->where('type', 'daerah')
                ->where('regency_code', $this->regency)
                ->select('id', 'name')->get();
        }

        return collect();
    }

    public function updatedRoleId()
    {
        $this->resetRegion();
    }

    public function updatedProvince($value)
    {
        $this->regency = null;
        $this->instansiOption = null;
        $this->instansiCustom = null;
    }

    public function updatedRegency($value)
    {
        $this->instansiOption = null;
        $this->instansiCustom = null;
    }

    private function resetRegion(): void
    {
        $this->province = null;
        $this->regency = null;
        $this->instansiOption = null;
        $this->instansiCustom = null;
        $this->suratTugas = null;
    }

    public function render()
    {
        return view('livewire.dashboard.super-admin.user-management-create', [
            'roles'       => Role::where('name', '!=', 'user')->select('uuid', 'name')->orderBy('name', 'asc')->get(),
        ]);
    }
}
