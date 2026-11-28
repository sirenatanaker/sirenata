<?php

use App\Models\User;
use Modules\LMS\Models\CertificateSetting;
use Modules\LMS\Models\Course;
use Modules\Roles\Models\Role;

// Anti-joki: nama sertifikat memakai identitas terverifikasi (SSO) dan
// sertifikat hanya diterbitkan sekali — penerbitan ulang selalu ditolak.

test('certificate cannot be reissued after it already exists', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $user = User::create(['name' => 'Lulus', 'email' => 'lulus@kemnaker.go.id', 'password' => 'password']);
    $user->assignRole('user');
    $user->profile()->create([
        'full_name' => 'Lulus Asli',
        'institution_type' => 'pusat',
        'instansi' => 'Kementerian Ketenagakerjaan',
        'unit_kerja' => 'Pusat Pasar Kerja',
    ]);

    $course = Course::create([
        'name' => 'Kursus Lulus',
        'slug' => 'kursus-lulus',
        'description' => 'Deskripsi.',
    ]);
    $user->enrolledCourses()->attach($course->id, ['status' => 'completed', 'progress' => 100]);

    CertificateSetting::create([
        'signer_name' => 'Pejabat',
        'signer_title' => 'Kepala',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('user.course.my-course.generate-certificate', ['slug' => $course->slug]))
        ->assertRedirect();

    $firstCode = $user->fresh()->enrolledCourses()->first()->pivot->certificate_code;
    $firstFile = $user->fresh()->enrolledCourses()->first()->pivot->certificate_file;
    expect($firstCode)->not->toBeNull()->and($firstFile)->not->toBeNull();

    // Penyerang mengganti nama profil lalu mencoba terbitkan ulang.
    $user->profile()->update(['full_name' => 'Nama Palsu']);

    $this->actingAs($user)
        ->post(route('user.course.my-course.generate-certificate', ['slug' => $course->slug]))
        ->assertRedirect();

    $pivot = $user->fresh()->enrolledCourses()->first()->pivot;

    expect($pivot->certificate_code)->toBe($firstCode)
        ->and($pivot->certificate_file)->toBe($firstFile);
});

test('certificate claim requires completed personal and institution profile', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $user = User::create(['name' => 'Baru', 'email' => 'baru@contoh.id', 'password' => 'password']);
    $user->assignRole('user');
    // User baru selesai quick onboarding sektor saja (belum isi detail profil)
    $user->profile()->create([
        'institution_type' => 'swasta',
    ]);

    $course = Course::create([
        'name' => 'Kursus Swasta',
        'slug' => 'kursus-swasta',
        'description' => 'Deskripsi.',
        'audience' => 'swasta',
    ]);
    $user->enrolledCourses()->attach($course->id, ['status' => 'completed', 'progress' => 100]);

    CertificateSetting::create([
        'signer_name' => 'Pejabat',
        'signer_title' => 'Kepala',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->post(route('user.course.my-course.generate-certificate', ['slug' => $course->slug]));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain(route('user.profile'));

    $pivot = $user->fresh()->enrolledCourses()->first()->pivot;
    expect($pivot->certificate_code)->toBeNull()
        ->and($pivot->certificate_file)->toBeNull();
});
