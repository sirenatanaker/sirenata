<?php

use App\Models\User;
use Modules\LMS\Models\Category;
use Modules\LMS\Models\Course;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);
});

test('user profile page has no password section', function () {
    $user = User::create(['name' => 'Profil', 'email' => 'profil@kemnaker.go.id', 'password' => 'password']);
    $user->assignRole('user');

    $this->actingAs($user)->get(route('user.profile'))
        ->assertOk()
        ->assertSee('Informasi Saya', false)
        ->assertSee('Sertifikat Saya', false)
        ->assertDontSee('Ubah Password', false)
        ->assertDontSee('current_password', false);
});

test('user profile lists earned certificates', function () {    $user = User::create(['name' => 'Sertifikat', 'email' => 'sertifikat@kemnaker.go.id', 'password' => 'password']);
    $user->assignRole('user');

    $category = Category::create(['name' => 'Perencanaan', 'slug' => 'perencanaan']);
    $course = Course::create([
        'category_id' => $category->id,
        'name' => 'Kursus Sertifikat',
        'slug' => 'kursus-sertifikat',
        'description' => 'Deskripsi.',
    ]);
    $user->enrolledCourses()->attach($course->id, [
        'status' => 'completed',
        'certificate_code' => 'CERT-ABC-123',
        'certificate_issued_at' => now(),
    ]);

    $this->actingAs($user)->get(route('user.profile'))
        ->assertOk()
        ->assertSee('CERT-ABC-123', false)
        ->assertSee('Kursus Sertifikat', false);
});

test('profile can be partially updated without filling phone', function () {
    $user = User::create(['name' => 'Parsial', 'email' => 'parsial@kemnaker.go.id', 'password' => 'password']);
    $user->assignRole('user');
    $user->profile()->create(['full_name' => 'Parsial', 'unit_kerja' => 'Lama']);

    $this->actingAs($user)->post(route('user.profile.update'), [
        'full_name' => 'Parsial',
        'phone' => '',
        'gender' => 'male',
        'instansi' => 'Kementerian Ketenagakerjaan',
        'unit_kerja' => 'Baru',
    ])->assertRedirect(route('user.profile'));

    $profile = $user->fresh()->profile;

    expect($profile->unit_kerja)->toBe('Baru')
        ->and($profile->phone)->toBeNull();
});

test('sso users see a locked name field on profile', function () {
    $user = User::create([
        'name' => 'SSO Asli',
        'email' => 'sso@kemnaker.go.id',
        'password' => 'password',
        'siapkerja_id' => 'sso-id-1',
    ]);
    $user->assignRole('user');

    $this->actingAs($user)->get(route('user.profile'))
        ->assertOk()
        ->assertSee('Mengikuti nama akun SIAPKerja', false);
});
