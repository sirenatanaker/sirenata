<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Modules\LMS\Models\Course;
use Modules\User\Enums\InstitutionType;
use Modules\User\Models\UserProfile;
use Modules\User\Models\UserScope;
use Modules\User\Traits\HasScopeAccess;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\Notifications\Auth\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuids, LogsActivity, HasScopeAccess;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'siapkerja_id',
        'siapkerja_token',
        'siapkerja_refresh_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']);
    }

    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new ResetPassword($token));
        $this->notify(new ResetPasswordNotification($token));
    }

    #[Scope]
    protected function search(Builder $query, string $keyword): void
    {
        $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhereHas('profile', fn($sub) => $sub->where('instansi', 'like', "%{$keyword}%"));
        });
    }

    #[Scope]
    protected function inProvince(Builder $query, string $provinceCode): void
    {
        $query->whereHas('scopeArea', fn($q) => $q->where('province_code', $provinceCode));
    }

    #[Scope]
    protected function inRegency(Builder $query, string $regencyCode): void
    {
        $query->whereHas('scopeArea', fn($q) => $q->where('regency_code', $regencyCode));
    }

    #[Scope]
    protected function provinceInstitution(Builder $query): void
    {
        $query->whereHas('profile', fn($q) => $q->where('institution_type', InstitutionType::PROVINSI));
    }

    #[Scope]
    protected function regencyInstitution(Builder $query): void
    {
        $query->whereHas('profile', fn($q) => $q->where('institution_type', InstitutionType::KAB_KOTA));
    }

    #[Scope]
    protected function hasEnrolledCourses(Builder $query, string $search = ''): void
    {
        $query->whereHas('enrolledCourses', function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'like', "%{$search}%");
            }
        });
    }

    public function getRedirectRoute(): string
    {
        return match (true) {
            $this->hasRole('super-admin') => 'super-admin.dashboard',
            $this->hasRole('admin-pusat') => 'admin-pusat.dashboard',
            $this->hasRole('admin-province') => 'admin-province.dashboard',
            $this->hasRole('admin-kab-kota') => 'admin-kab-kota.dashboard',
            $this->hasRole('user') => 'user.dashboard',
            default => 'portal-dashboard',
        };
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function scopeArea()
    {
        return $this->hasOne(UserScope::class);
    }

    /**
     * Sektor pengguna yang dipilih: 'instansi', 'swasta', atau null jika belum memilih.
     */
    public function userSector(): ?string
    {
        $rawType = $this->profile?->institution_type;
        $type = $rawType instanceof InstitutionType ? $rawType->value : (string) $rawType;

        if ($type === InstitutionType::SWASTA->value) {
            return 'swasta';
        }

        if (in_array($type, [
            InstitutionType::INSTANSI->value,
            InstitutionType::PUSAT->value,
            InstitutionType::PROVINSI->value,
            InstitutionType::KAB_KOTA->value,
            'kabkota',
        ], true)) {
            return 'instansi';
        }

        return null;
    }

    public function isSwastaUser(): bool
    {
        return $this->userSector() === 'swasta';
    }

    public function isInstansiUser(): bool
    {
        return $this->userSector() === 'instansi';
    }

    /**
     * Aturan akses kursus berdasarkan sektor:
     * - Instansi / Lembaga: dapat mengakses kursus Instansi DAN kursus Swasta.
     * - Swasta: hanya dapat mengakses kursus Swasta.
     */
    public function canAccessCourseAudience(?string $audience): bool
    {
        $normalizedAudience = $audience ?: 'instansi';

        if ($this->isSwastaUser()) {
            return $normalizedAudience === 'swasta';
        }

        return true;
    }

    /**
     * Daftar informasi pribadi / instansi yang masih wajib dilengkapi di Profil
     * sebelum peserta dapat mengklaim sertifikat kelulusan.
     *
     * @return list<string>
     */
    public function missingCertificateProfileFields(): array
    {
        $profile = $this->profile;
        $scope = $this->scopeArea;
        $missing = [];

        $effectiveName = $this->siapkerja_id
            ? trim((string) $this->name)
            : trim((string) ($profile?->full_name ?? ''));

        if ($effectiveName === '') {
            $missing[] = 'Nama Lengkap';
        }

        $rawType = $profile?->institution_type;
        $type = $rawType instanceof InstitutionType ? $rawType->value : (string) $rawType;
        if ($type === 'kabkota') {
            $type = InstitutionType::KAB_KOTA->value;
        }

        if ($type === '') {
            $missing[] = 'Asal Pengguna (Instansi/Lembaga atau Swasta)';
            $missing[] = 'Nama Instansi / Perusahaan';

            return $missing;
        }

        if ($type === InstitutionType::SWASTA->value) {
            if (trim((string) ($profile?->instansi ?? '')) === '') {
                $missing[] = 'Nama Perusahaan / Lembaga Swasta';
            }
            if (empty($scope?->province_code)) {
                $missing[] = 'Provinsi Domisili';
            }
            if (empty($scope?->regency_code)) {
                $missing[] = 'Kabupaten/Kota Domisili';
            }

            return $missing;
        }

        // Instansi / Lembaga (pusat, provinsi, kab_kota)
        if (!in_array($type, [
            InstitutionType::PUSAT->value,
            InstitutionType::PROVINSI->value,
            InstitutionType::KAB_KOTA->value,
        ], true)) {
            $missing[] = 'Tingkat Asal Instansi (Pusat/Provinsi/Kab/Kota)';
        }

        if (trim((string) ($profile?->instansi ?? '')) === '') {
            $missing[] = 'Nama Instansi / Kementerian / Lembaga';
        }

        if (trim((string) ($profile?->unit_kerja ?? '')) === '') {
            $missing[] = 'Unit Kerja';
        }

        if (in_array($type, [InstitutionType::PROVINSI->value, InstitutionType::KAB_KOTA->value], true) && empty($scope?->province_code)) {
            $missing[] = 'Provinsi Instansi';
        }

        if ($type === InstitutionType::KAB_KOTA->value && empty($scope?->regency_code)) {
            $missing[] = 'Kabupaten/Kota Instansi';
        }

        return $missing;
    }

    public function hasCompleteCertificateProfile(): bool
    {
        return empty($this->missingCertificateProfileFields());
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student')
            ->withPivot([
                'status',
                'progress',
                'completed_at',
                'certificate_code',
                'certificate_file',
                'certificate_issued_at',
            ])
            ->withTimestamps();
    }
}
