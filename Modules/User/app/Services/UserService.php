<?php

namespace Modules\User\Services;

use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Permission\Models\Permission;
use Modules\Roles\Models\Role;
use Spatie\Activitylog\Models\Activity;

class UserService
{
    public function getFilteredQueryUsers(?string $search = null, string $sortBy = 'desc')
    {
        return User::query()->with(['roles', 'permissions','scopeArea.province','scopeArea.regency'])
            ->when($search, function ($q) use ($search) {
                $q->search($search);
            })
            ->orderBy('created_at', $sortBy);
    }

    public function paginateFilteredUsers(?string $search = null, string $sortBy = 'desc', $limit = 10)
    {
        return $this->getFilteredQueryUsers($search, $sortBy)->paginate($limit)->withQueryString();
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $oldRoles = $user->getRoleNames()->toArray();

            $user->update([
                'name'  => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ]);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $data['full_name'] ?? $user->profile?->full_name,
                    'instansi'   => $data['instansi'] ?? $user->profile?->instansi,
                    'phone'     => $data['phone'] ?? $user->profile?->phone,
                ]
            );

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
                'instansi'   => $data['instansi'] ?? null,
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
