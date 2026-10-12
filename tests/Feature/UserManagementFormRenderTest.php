<?php

use App\Livewire\Dashboard\SuperAdmin\UserManagementCreate;
use App\Livewire\Dashboard\SuperAdmin\UserManagementEdit;
use App\Livewire\Dashboard\SuperAdmin\UserManagementTable;
use App\Models\User;
use Livewire\Livewire;
use Modules\MasterData\Models\Institution;
use Modules\Roles\Models\Role;
use Modules\User\Models\UserScope;

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

test('user table shows a role picker before any role is chosen', function () {
    Livewire::test(UserManagementTable::class)
        ->assertOk()
        ->assertSee('Pilih Role', false)
        ->assertSee('Super Admin', false)
        ->assertDontSee('Daftar Super Admin', false);
});

test('user table lists only users of the chosen role', function () {
    Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);

    $pusat = User::create(['name' => 'Pusat', 'email' => 'pusat.x@kemnaker.go.id', 'password' => 'password']);
    $pusat->assignRole('admin-pusat');

    $biasa = User::create(['name' => 'Biasa', 'email' => 'biasa@kemnaker.go.id', 'password' => 'password']);
    $biasa->assignRole('user');

    Livewire::test(UserManagementTable::class)
        ->call('selectRole', 'admin-pusat')
        ->assertOk()
        ->assertSee('pusat.x@kemnaker.go.id', false)
        ->assertDontSee('biasa@kemnaker.go.id', false);
});

test('province filter narrows the admin-province table', function () {
    Role::create(['name' => 'admin-province', 'guard_name' => 'web']);

    $aceh = User::create(['name' => 'Aceh', 'email' => 'aceh@kemnaker.go.id', 'password' => 'password']);
    $aceh->assignRole('admin-province');
    UserScope::create(['user_id' => $aceh->id, 'province_code' => '11']);

    $jabar = User::create(['name' => 'Jabar', 'email' => 'jabar@kemnaker.go.id', 'password' => 'password']);
    $jabar->assignRole('admin-province');
    UserScope::create(['user_id' => $jabar->id, 'province_code' => '32']);

    Livewire::test(UserManagementTable::class)
        ->call('selectRole', 'admin-province')
        ->set('provinceCode', '11')
        ->assertOk()
        ->assertSee('aceh@kemnaker.go.id', false)
        ->assertDontSee('jabar@kemnaker.go.id', false);
});

test('super-admin can create a pusat user without picking lembaga', function () {
    Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);

    $roleUuid = Role::where('name', 'admin-pusat')->first()->uuid;

    Livewire::test(UserManagementCreate::class)
        ->set('name', 'Pusat Baru')
        ->set('full_name', 'Pusat Baru Lengkap')
        ->set('email', 'pusat.baru@kemnaker.go.id')
        ->set('phone', '081234567890')
        ->set('roleId', $roleUuid)
        ->call('goToStep2')
        ->assertDontSee('Kementerian/Lembaga', false)
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('super-admin.user-management.index'));

    $user = User::where('email', 'pusat.baru@kemnaker.go.id')->first();

    expect($user)->not->toBeNull()
        ->and($user->profile->instansi)->toBeNull()
        ->and($user->profile->institution_type)->toBe('pusat')
        ->and($user->hasRole('admin-pusat'))->toBeTrue();
});

test('super-admin can create a user with a manually added institution', function () {
    Role::create(['name' => 'admin-province', 'guard_name' => 'web']);

    $roleUuid = Role::where('name', 'admin-province')->first()->uuid;

    Livewire::test(UserManagementCreate::class)
        ->set('name', 'Prov Baru')
        ->set('full_name', 'Prov Baru Lengkap')
        ->set('email', 'prov.baru@kemnaker.go.id')
        ->set('phone', '081234567891')
        ->set('roleId', $roleUuid)
        ->set('province', '11')
        ->set('instansiOption', '__custom')
        ->set('instansiCustom', 'Dinas Baru')
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('super-admin.user-management.index'));

    expect(Institution::where('name', 'Dinas Baru')->where('type', 'daerah')->where('province_code', '11')->exists())->toBeTrue()
        ->and(User::where('email', 'prov.baru@kemnaker.go.id')->first()->profile->instansi)->toBe('Dinas Baru');
});
