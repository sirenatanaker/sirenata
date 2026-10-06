<?php

namespace Modules\Auth\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Roles\Models\Role;

class SiapKerjaSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $emails = array_map(
            static fn (string $email): string => mb_strtolower(trim($email)),
            config('services.siapkerja.super_admin_emails', [])
        );

        if ($emails === []) {
            return;
        }

        $role = Role::query()->where('name', 'super-admin')->firstOrFail();
        $users = User::query()
            ->whereIn(DB::raw('LOWER(TRIM(email))'), $emails)
            ->get();

        foreach ($users as $user) {
            $user->syncRoles($role);
        }

        $this->command?->info(sprintf(
            'Assigned the super-admin role to %d matching SIAPKerja account(s).',
            $users->count()
        ));
    }
}
