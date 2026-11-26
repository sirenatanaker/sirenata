<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\LMS\Models\Course;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create([
        'name' => 'admin-pusat',
        'guard_name' => 'web',
    ]);

    $this->actingAs(User::factory()->create()->assignRole('admin-pusat'));
});

test('the master course form does not include category fields', function () {
    $this->get(route('admin-pusat.management-course.courses.create'))
        ->assertOk()
        ->assertSee('Master Kursus')
        ->assertSee('Peruntukan Course')
        ->assertDontSee('category_id')
        ->assertDontSee('Kelola Kategori');
});

test('the master course listing displays courses without category and student columns', function () {
    Course::create([
        'name' => 'Kursus Perencanaan SDM',
        'description' => 'Deskripsi kursus untuk tampilan tabel.',
        'audience' => 'instansi',
    ]);

    $this->get(route('admin-pusat.management-course.courses.index'))
        ->assertOk()
        ->assertSee('<table', false)
        ->assertSee('Master Kursus')
        ->assertSee('Nama Kursus')
        ->assertSee('Kursus Perencanaan SDM')
        ->assertSee('Kelola')
        ->assertSee('Hapus')
        ->assertDontSee('Kategori Course')
        ->assertDontSee('>Kategori<', false)
        ->assertDontSee('>Peserta<', false);
});

test('admin pusat can update master course from the detail modal and return to detail page', function () {
    $course = Course::create([
        'name' => 'Kursus Awal',
        'description' => 'Deskripsi awal.',
        'audience' => 'instansi',
    ]);

    $this->get(route('admin-pusat.management-course.courses.show', $course->slug))
        ->assertOk()
        ->assertSee('Edit Informasi Kursus')
        ->assertSee('edit-course-' . $course->slug, false);

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
        ->put(route('admin-pusat.management-course.courses.update', $course->slug), [
            'name' => 'Kursus Diperbarui',
            'audience' => 'swasta',
            'description' => 'Deskripsi baru.',
            'redirect_to' => 'show',
        ])
        ->assertRedirect(route('admin-pusat.management-course.courses.show', 'kursus-diperbarui'));

    $fresh = $course->fresh();
    expect($fresh->name)->toBe('Kursus Diperbarui')
        ->and($fresh->audience)->toBe('swasta')
        ->and($fresh->description)->toBe('Deskripsi baru.');
});

test('legacy course categories table, column, and routes no longer exist', function () {
    expect(Schema::hasTable('categories'))->toBeFalse()
        ->and(Schema::hasColumn('courses', 'category_id'))->toBeFalse()
        ->and(Route::has('admin-pusat.management-course.categories.store'))->toBeFalse();
});
