<?php

use App\Livewire\Dashboard\SuperAdmin\UserManagementTable;
use App\Models\User;
use Livewire\Livewire;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\CourseService;
use Modules\Roles\Models\Role;

test('courses default to instansi audience', function () {
    $result = app(CourseService::class)->storeCourse([
        'name' => 'Kursus A',
        'description' => 'Deskripsi kursus A.',
    ]);

    expect($result['success'])->toBeTrue()
        ->and(Course::first()->audience)->toBe('instansi');
});

test('courses can target swasta audience', function () {
    $result = app(CourseService::class)->storeCourse([
        'name' => 'Kursus B',
        'description' => 'Deskripsi kursus B.',
        'audience' => 'swasta',
    ]);

    expect($result['success'])->toBeTrue()
        ->and(Course::first()->audience)->toBe('swasta');
});

test('new users can register as swasta with a company name', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $user = User::create(['name' => 'Swasta', 'email' => 'swasta@contoh.id', 'password' => 'password']);
    $user->assignRole('user');

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
        ->actingAs($user)->post(route('user.update-instansi'), [
            'sector' => 'swasta',
            'instansi' => 'PT Maju Bersama',
            'province_code' => '11',
            'regency_code' => '1101',
        ])->assertRedirect(route('user.dashboard'));

    $profile = $user->fresh()->profile;

    expect($profile->institution_type)->toBe('swasta')
        ->and($profile->instansi)->toBe('PT Maju Bersama')
        ->and($profile->unit_kerja)->toBeNull()
        ->and($user->fresh()->scopeArea->province_code)->toBe('11')
        ->and($user->fresh()->scopeArea->regency_code)->toBe('1101');
});

test('user table shows sector badges', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $swasta = User::create(['name' => 'Swasta', 'email' => 'swasta@contoh.id', 'password' => 'password']);
    $swasta->assignRole('user');
    $swasta->profile()->create(['institution_type' => 'swasta', 'instansi' => 'PT Maju Bersama']);

    $asn = User::create(['name' => 'ASN', 'email' => 'asn@kemnaker.go.id', 'password' => 'password']);
    $asn->assignRole('user');
    $asn->profile()->create(['institution_type' => 'pusat', 'instansi' => 'Kementerian Ketenagakerjaan']);

    Livewire::test(UserManagementTable::class)
        ->call('selectRole', 'user')
        ->assertOk()
        ->assertSee('Swasta', false)
        ->assertSee('Instansi', false);
});

test('new users can complete quick onboarding by choosing only sector', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $instansiUser = User::create(['name' => 'ASN Baru', 'email' => 'asn.baru@kemnaker.go.id', 'password' => 'password']);
    $instansiUser->assignRole('user');

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
        ->actingAs($instansiUser)
        ->post(route('user.update-instansi'), ['sector' => 'instansi'])
        ->assertRedirect(route('user.dashboard'));

    expect($instansiUser->fresh()->profile->institution_type)->toBe('instansi')
        ->and($instansiUser->fresh()->profile->instansi)->toBeNull()
        ->and($instansiUser->fresh()->isInstansiUser())->toBeTrue()
        ->and($instansiUser->fresh()->hasCompleteCertificateProfile())->toBeFalse();

    $swastaUser = User::create(['name' => 'Swasta Baru', 'email' => 'swasta.baru@contoh.id', 'password' => 'password']);
    $swastaUser->assignRole('user');

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
        ->actingAs($swastaUser)
        ->post(route('user.update-instansi'), ['sector' => 'swasta'])
        ->assertRedirect(route('user.dashboard'));

    expect($swastaUser->fresh()->profile->institution_type)->toBe('swasta')
        ->and($swastaUser->fresh()->isSwastaUser())->toBeTrue()
        ->and($swastaUser->fresh()->hasCompleteCertificateProfile())->toBeFalse();
});

test('instansi users can access both instansi and swasta courses while swasta users only access swasta courses', function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $courseInstansi = Course::create([
        'name' => 'Kursus Khusus Instansi',
        'slug' => 'kursus-khusus-instansi',
        'description' => 'Untuk instansi.',
        'audience' => 'instansi',
    ]);
    BimtekEvent::create([
        'course_id' => $courseInstansi->id,
        'batch_name' => 'Gelombang 1 Instansi',
        'batch_code' => 'INST-001',
        'status' => 'open_registration',
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);

    $courseSwasta = Course::create([
        'name' => 'Kursus Terbuka Swasta',
        'slug' => 'kursus-terbuka-swasta',
        'description' => 'Untuk swasta.',
        'audience' => 'swasta',
    ]);
    BimtekEvent::create([
        'course_id' => $courseSwasta->id,
        'batch_name' => 'Gelombang 1 Swasta',
        'batch_code' => 'SWST-001',
        'status' => 'open_registration',
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);

    // 1. User Instansi bisa melihat & mendaftar kursus Instansi DAN Swasta
    $instansiUser = User::create(['name' => 'Pegawai Instansi', 'email' => 'pegawai@kemnaker.go.id', 'password' => 'password']);
    $instansiUser->assignRole('user');
    $instansiUser->profile()->create(['institution_type' => 'instansi']);

    $this->actingAs($instansiUser);
    $instansiCatalogIds = app(CourseService::class)->getCatalogCourses(20)->pluck('id')->all();
    expect($instansiCatalogIds)->toContain($courseInstansi->id, $courseSwasta->id);

    $enrollSwastaByInstansi = app(CourseService::class)->enrollUser($courseSwasta->slug);
    expect($enrollSwastaByInstansi['success'])->toBeTrue();

    // 2. User Swasta hanya melihat kursus Swasta & ditolak saat coba daftar kursus Instansi
    $swastaUser = User::create(['name' => 'Karyawan Swasta', 'email' => 'karyawan@perusahaan.id', 'password' => 'password']);
    $swastaUser->assignRole('user');
    $swastaUser->profile()->create(['institution_type' => 'swasta']);

    $this->actingAs($swastaUser);
    $swastaCatalogIds = app(CourseService::class)->getCatalogCourses(20)->pluck('id')->all();
    expect($swastaCatalogIds)->toContain($courseSwasta->id)
        ->not->toContain($courseInstansi->id);

    $enrollInstansiBySwasta = app(CourseService::class)->enrollUser($courseInstansi->slug);
    expect($enrollInstansiBySwasta['success'])->toBeFalse();

    $enrollSwastaBySwasta = app(CourseService::class)->enrollUser($courseSwasta->slug);
    expect($enrollSwastaBySwasta['success'])->toBeTrue();
});
