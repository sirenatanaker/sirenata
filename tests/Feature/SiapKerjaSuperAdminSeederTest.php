<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\SiapKerjaSuperAdminSeeder;
use Modules\Roles\Models\Role;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('the SIAPKerja super-admin seeder promotes existing allowlisted accounts', function () {
    config([
        'services.siapkerja.super_admin_emails' => [
            'pusat.ptk@gmail.com',
            'prastyoguntur982@gmail.com',
        ],
    ]);

    Role::create([
        'name' => 'super-admin',
        'guard_name' => 'web',
    ]);
    $userRole = Role::create([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $allowlistedUsers = collect([
        User::factory()->create(['email' => 'pusat.ptk@gmail.com']),
        User::factory()->create(['email' => 'prastyoguntur982@gmail.com']),
    ]);
    $otherUser = User::factory()->create(['email' => 'other@example.test']);
    $otherUser->assignRole($userRole);

    app(SiapKerjaSuperAdminSeeder::class)->run();

    foreach ($allowlistedUsers as $user) {
        expect($user->fresh()->hasRole('super-admin'))->toBeTrue();
    }

    expect($otherUser->fresh()->hasRole('user'))->toBeTrue()
        ->and($otherUser->fresh()->hasRole('super-admin'))->toBeFalse()
        ->and(User::role('super-admin')->count())->toBe(2);
});

test('allowlisted SIAPKerja accounts receive the super-admin role when they first log in', function () {
    config([
        'services.siapkerja.super_admin_emails' => ['pusat.ptk@gmail.com'],
    ]);

    Role::create([
        'name' => 'super-admin',
        'guard_name' => 'web',
    ]);
    Role::create([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $socialUser = (new SocialiteUser())
        ->setRaw([])
        ->map([
            'id' => 'siapkerja-super-admin-id',
            'name' => 'Super Admin',
            'email' => 'PUSAT.PTK@gmail.com',
        ])
        ->setToken('access-token')
        ->setRefreshToken('refresh-token');

    $provider = Mockery::mock();
    $provider->shouldReceive('user')->once()->andReturn($socialUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('siapkerja')
        ->andReturn($provider);

    $this->get(route('siapkerja.callback'))
        ->assertRedirect(route('super-admin.dashboard'));

    expect(User::where('siapkerja_id', 'siapkerja-super-admin-id')->first()->hasRole('super-admin'))
        ->toBeTrue();
});
