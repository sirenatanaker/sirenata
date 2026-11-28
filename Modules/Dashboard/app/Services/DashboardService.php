<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use App\Support\SmartCache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\MasterData\Models\Institution;
use Modules\User\Enums\InstitutionType;
use Modules\User\Models\UserProfile;
use Modules\User\Models\UserScope;

class DashboardService
{
    public function updateProfile($user, $validated)
    {
        $existingProfile = $user->profile;
        $rawExistingType = $existingProfile?->institution_type;
        $existingType = $rawExistingType instanceof InstitutionType ? $rawExistingType->value : (string) $rawExistingType;

        $sector = $validated['sector'] ?? null;
        $asalInstansi = $validated['asal_instansi'] ?? null;

        $institutionType = $existingType !== '' ? $existingType : null;
        if ($sector === 'swasta') {
            $institutionType = InstitutionType::SWASTA->value;
        } elseif ($sector === 'instansi' || !empty($asalInstansi)) {
            $institutionType = match ($asalInstansi) {
                'pusat' => InstitutionType::PUSAT->value,
                'provinsi' => InstitutionType::PROVINSI->value,
                'kabkota', 'kab_kota' => InstitutionType::KAB_KOTA->value,
                default => in_array($existingType, [
                    InstitutionType::PUSAT->value,
                    InstitutionType::PROVINSI->value,
                    InstitutionType::KAB_KOTA->value,
                ], true) ? $existingType : InstitutionType::INSTANSI->value,
            };
        }

        $instansiValue = $validated['instansi'] ?? $existingProfile?->instansi;
        if (($validated['instansi'] ?? null) === 'lainnya') {
            $customName = trim((string) ($validated['instansi_lainnya'] ?? ''));
            $instansiValue = $customName !== '' ? $customName : $existingProfile?->instansi;

            if ($customName !== '' && $institutionType !== InstitutionType::SWASTA->value) {
                Institution::firstOrCreate(
                    [
                        'name' => $customName,
                        'type' => $institutionType === InstitutionType::PUSAT->value ? 'pusat' : 'daerah',
                        'province_code' => $validated['province_code'] ?? null,
                        'regency_code' => $institutionType === InstitutionType::PROVINSI->value ? null : ($validated['regency_code'] ?? null),
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        } elseif (is_string($instansiValue)) {
            $instansiValue = trim($instansiValue);
        }

        $unitKerjaValue = array_key_exists('unit_kerja', $validated)
            ? (is_string($validated['unit_kerja']) ? trim($validated['unit_kerja']) : $validated['unit_kerja'])
            : $existingProfile?->unit_kerja;

        $profilePayload = [
            'full_name' => !empty($validated['full_name']) ? trim((string) $validated['full_name']) : $existingProfile?->full_name,
            'phone' => array_key_exists('phone', $validated) ? ($validated['phone'] ?: null) : $existingProfile?->phone,
            'gender' => $validated['gender'] ?? $existingProfile?->gender,
            'instansi' => $instansiValue !== '' ? $instansiValue : null,
            'unit_kerja' => $unitKerjaValue !== '' ? $unitKerjaValue : null,
        ];

        if ($institutionType !== null) {
            $profilePayload['institution_type'] = $institutionType;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profilePayload
        );

        $provinceCode = array_key_exists('province_code', $validated)
            ? ($validated['province_code'] ?: null)
            : $user->scopeArea?->province_code;
        $regencyCode = array_key_exists('regency_code', $validated)
            ? ($validated['regency_code'] ?: null)
            : $user->scopeArea?->regency_code;

        if ($institutionType === InstitutionType::PUSAT->value) {
            $provinceCode = null;
            $regencyCode = null;
        } elseif ($institutionType === InstitutionType::PROVINSI->value) {
            $regencyCode = null;
        }

        $user->scopeArea()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'province_code' => $provinceCode,
                'regency_code' => $regencyCode,
            ]
        );

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Password lama salah',
                ]);
            }
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        SmartCache::bump([
            SmartCache::userDomain($user->id),
        ]);

        return $user;
    }

    public function updateInstansi(User $user, array $data)
    {
        $profile = UserProfile::firstOrNew([
            'user_id' => $user->id
        ]);

        $institutionType = match ($data['asal_instansi']) {
            'pusat' => InstitutionType::PUSAT,
            'provinsi' => InstitutionType::PROVINSI,
            'kab_kota' => InstitutionType::KAB_KOTA,
        };

        // handle instansi "lainnya"
        $instansi = $data['instansi'];
        if ($instansi === 'lainnya' && !empty($data['instansi_lainnya'])) {
            $instansi = $data['instansi_lainnya'];
        }

        $profile->institution_type = $institutionType->value;
        $profile->instansi = $instansi;
        $profile->unit_kerja = $data['unit_kerja'];
        $profile->save();

        UserScope::updateOrCreate(
            ['user_id' => $user->id],
            [
                'province_code' => $data['province_code'] ?? null,
                'regency_code' => $data['regency_code'] ?? null,
            ]
        );

        return $user->load(['profile', 'scopeArea']);
    }
}
