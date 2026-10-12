<?php

use App\Livewire\Dashboard\SuperAdmin\UserManagementCreate;
use App\Livewire\Dashboard\SuperAdmin\UserManagementEdit;
use App\Models\User;
use Livewire\Livewire;
use Modules\MasterData\Models\Institution;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::create(['name' => 'user', 'guard_name' => 'web']);
});

test('super-admin user creation form renders', function () {
    Livewire::test(UserManagementCreate::class)
        ->assertOk()
        ->assertSee('Role dan Izin Akses', false)
        ->assertDontSee('Simpan User', false);
});

test('super-admin user creation form renders in SSO mode', function () {
    $userRole = Role::where('name', 'user')->first();

    Livewire::test(UserManagementCreate::class)
        ->set('isSsoMode', true)
        ->set('roleId', $userRole->uuid)
        ->call('goToStep2')
        ->assertOk()
        ->assertSee('Informasi Pribadi', false)
        ->assertDontSee('Nomor Telepon', false);
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

test('create form reveals region fields step by step for kabkota role', function () {
    $kab = Role::create(['name' => 'admin-kab-kota', 'guard_name' => 'web']);

    Livewire::test(UserManagementCreate::class)
        ->set('roleId', $kab->uuid)
        ->call('goToStep2')
        ->assertSee('Provinsi', false)
        ->assertSee('Pilih provinsi terlebih dahulu', false)
        ->set('province', '32')
        ->assertSee('Pilih kabupaten/kota', false)
        ->set('regency', '3273')
        ->assertSee('Lainnya', false);
});

test('creation wizard blocks moving to step two without a role', function () {
    Livewire::test(UserManagementCreate::class)
        ->call('goToStep2')
        ->assertHasErrors(['roleId'])
        ->assertSee('Role dan Izin Akses', false);
});

test('user role is not offered on the creation form', function () {
    $names = Livewire::test(UserManagementCreate::class)
        ->viewData('roles')
        ->pluck('name')
        ->all();

    expect($names)->not->toContain('user')
        ->and($names)->not->toBeEmpty();
});

test('super-admin can create a pusat user with scoped lembaga', function () {
    Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);
    Institution::create([
        'name' => 'Kementerian Ketenagakerjaan',
        'type' => 'pusat',
        'is_active' => true,
    ]);

    $roleUuid = Role::where('name', 'admin-pusat')->first()->uuid;

    Livewire::test(UserManagementCreate::class)
        ->set('name', 'Pusat Baru')
        ->set('full_name', 'Pusat Baru Lengkap')
        ->set('email', 'pusat.baru@kemnaker.go.id')
        ->set('phone', '081234567890')
        ->set('roleId', $roleUuid)
        ->set('instansiOption', 'Kementerian Ketenagakerjaan')
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('super-admin.user-management.index'));

    $user = User::where('email', 'pusat.baru@kemnaker.go.id')->first();

    expect($user)->not->toBeNull()
        ->and($user->profile->instansi)->toBe('Kementerian Ketenagakerjaan')
        ->and($user->profile->institution_type)->toBe('pusat')
        ->and($user->hasRole('admin-pusat'))->toBeTrue();
});

test('super-admin can create a user with a manually added institution', function () {
    Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);

    $roleUuid = Role::where('name', 'admin-pusat')->first()->uuid;

    Livewire::test(UserManagementCreate::class)
        ->set('name', 'Lembaga Baru')
        ->set('full_name', 'Lembaga Baru Lengkap')
        ->set('email', 'lembaga.baru@kemnaker.go.id')
        ->set('phone', '081234567891')
        ->set('roleId', $roleUuid)
        ->set('instansiOption', '__custom')
        ->set('instansiCustom', 'Badan Baru')
        ->call('store')
        ->assertRedirect(route('super-admin.user-management.index'));

    expect(Institution::where('name', 'Badan Baru')->where('type', 'pusat')->exists())->toBeTrue()
        ->and(User::where('email', 'lembaga.baru@kemnaker.go.id')->first()->profile->instansi)->toBe('Badan Baru');
});
