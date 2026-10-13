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
        ->assertSee('Informasi Pribadi', false)
        ->assertSee('Sertifikat Saya', false)
        ->assertDontSee('Ubah Password', false)
        ->assertDontSee('current_password', false);
});

test('user profile lists earned certificates', function () {
    $user = User::create(['name' => 'Sertifikat', 'email' => 'sertifikat@kemnaker.go.id', 'password' => 'password']);
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
