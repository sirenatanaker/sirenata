<?php

use App\Livewire\Dashboard\SuperAdmin\UserManagementCreate;
use App\Livewire\Dashboard\SuperAdmin\UserManagementEdit;
use App\Models\User;
use Livewire\Livewire;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::create(['name' => 'user', 'guard_name' => 'web']);
});

test('super-admin user creation form renders', function () {
    Livewire::test(UserManagementCreate::class)
        ->assertOk()
        ->assertSee('Informasi Pribadi', false);
});

test('super-admin user creation form renders in SSO mode', function () {
    Livewire::test(UserManagementCreate::class)
        ->set('isSsoMode', true)
        ->assertOk()
        ->assertSee('Mode SSO aktif', false);
});

test('super-admin user edit form renders', function () {
    $user = User::create([
        'name' => 'User Contoh',
        'email' => 'contoh@kemnaker.go.id',
        'password' => 'password',
    ]);

    Livewire::test(UserManagementEdit::class, ['user' => $user])
        ->assertOk()
        ->assertSee('Informasi Pribadi', false);
});
