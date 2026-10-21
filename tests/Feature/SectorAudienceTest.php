<?php

use App\Livewire\Dashboard\SuperAdmin\UserManagementTable;
use App\Models\User;
use Livewire\Livewire;
use Modules\LMS\Models\Category;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\CourseService;
use Modules\Roles\Models\Role;

test('courses default to instansi audience', function () {
    $category = Category::create(['name' => 'Perencanaan', 'slug' => 'perencanaan']);

    $result = app(CourseService::class)->storeCourse([
        'category_id' => $category->id,
        'name' => 'Kursus A',
        'description' => 'Deskripsi kursus A.',
    ]);

    expect($result['success'])->toBeTrue()
        ->and(Course::first()->audience)->toBe('instansi');
});

test('courses can target swasta audience', function () {
    $category = Category::create(['name' => 'Praktik', 'slug' => 'praktik']);

    $result = app(CourseService::class)->storeCourse([
        'category_id' => $category->id,
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
