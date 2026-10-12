<?php

namespace Modules\User\Services;

use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Permission\Models\Permission;
use Modules\Roles\Models\Role;
use Spatie\Activitylog\Models\Activity;

class UserService
{
    public function getFilteredQueryUsers(?string $search = null, string $sortBy = 'desc', ?string $role = null, ?string $provinceCode = null, ?string $regencyCode = null)
    {
        return User::query()->with(['roles', 'permissions', 'profile', 'scopeArea.province', 'scopeArea.regency'])
            ->when($search, function ($q) use ($search) {
                $q->search($search);
            })
            ->when($role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->when($provinceCode, fn ($q) => $q->whereHas('scopeArea', fn ($s) => $s->where('province_code', $provinceCode)))
            ->when($regencyCode, fn ($q) => $q->whereHas('scopeArea', fn ($s) => $s->where('regency_code', $regencyCode)))
            ->orderBy('created_at', $sortBy);
    }

    public function paginateFilteredUsers(?string $search = null, string $sortBy = 'desc', $limit = 10, ?string $role = null, ?string $provinceCode = null, ?string $regencyCode = null)
    {
        return $this->getFilteredQueryUsers($search, $sortBy, $role, $provinceCode, $regencyCode)->paginate($limit)->withQueryString();
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $oldRoles = $user->getRoleNames()->toArray();

            $user->update([
                'name'  => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ]);

            $oldSuratTugas = $user->profile?->surat_tugas;

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $data['full_name'] ?? $user->profile?->full_name,
                    'instansi'   => $data['instansi'] ?? $user->profile?->instansi,
                    'phone'     => $data['phone'] ?? $user->profile?->phone,
                    'surat_tugas' => $data['surat_tugas'] ?? $user->profile?->surat_tugas,
                ]
            );

            if (! empty($data['surat_tugas']) && $oldSuratTugas && $data['surat_tugas'] !== $oldSuratTugas) {
                Storage::disk('public')->delete($oldSuratTugas);
            }

            $user->scopeArea()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'province_code' => $data['province'] ?: null,
                    'regency_code'  => $data['regency'] ?: null,
                ]
            );

            if (!empty($data['password'])) {
                $user->update(['password' => bcrypt($data['password'])]);
            }

            $roles = Role::whereIn('uuid', $data['roles'])->get();
            $newRoles = $roles->pluck('name')->toArray();

            if (array_diff($oldRoles, $newRoles) || array_diff($newRoles, $oldRoles)) {
                $user->syncRoles($newRoles);
                activity('user-role')
                    ->causedBy(Auth::user())
                    ->performedOn($user)
                    ->withProperties([
                        'old' => $oldRoles,
                        'attributes' => $newRoles,
                    ])
                    ->log('User role updated');
            }
            ToastMagic::success("User {$user->name} updated successfully!");

            return $user;
        });
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'] ?? Str::before($data['email'], '@'),
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->profile()->create([
                'full_name' => $data['full_name'] ?? null,
                'institution_type' => $data['institution_type'] ?? null,
                'instansi'   => $data['instansi'] ?? null,
                'unit_kerja' => $data['unit_kerja'] ?? null,
                'surat_tugas' => $data['surat_tugas'] ?? null,
                'phone'     => $data['phone'] ?? null,
            ]);

            $user->scopeArea()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'province_code' => $data['province'] ?: null,
                    'regency_code'  => $data['regency'] ?: null,
                ]
            );

            $roles = Role::whereIn('uuid', $data['roles'])->get();
            $user->syncRoles($roles);

            ToastMagic::success("User {$user->name} created successfully!");
            return $user;
        });
    }


    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            $user->syncPermissions([]);
            $user->delete();
        });
    }
}
