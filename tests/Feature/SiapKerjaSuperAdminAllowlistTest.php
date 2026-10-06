<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Modules\Roles\Models\Role;

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
