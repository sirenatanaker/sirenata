<?php

use App\Models\User;
use Modules\LMS\Models\Category;
use Modules\LMS\Models\Course;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create([
        'name' => 'admin-pusat',
        'guard_name' => 'web',
    ]);

    $this->actingAs(User::factory()->create()->assignRole('admin-pusat'));
});

test('the course form includes the category manager and existing category options', function () {
    Category::create(['name' => 'Perencanaan Tenaga Kerja']);

    $this->get(route('admin-pusat.management-course.courses.create'))
        ->assertOk()
        ->assertSee('Kelola kategori')
        ->assertSee('Perencanaan Tenaga Kerja')
        ->assertSee('Kelola Kategori Kursus');
});

test('the course management listing displays courses in a table', function () {
    $category = Category::create(['name' => 'Perencanaan']);
    Course::create([
        'category_id' => $category->id,
        'name' => 'Kursus Perencanaan SDM',
        'description' => 'Deskripsi kursus untuk tampilan tabel.',
    ]);

    $this->get(route('admin-pusat.management-course.courses.index'))
        ->assertOk()
        ->assertSee('<table', false)
        ->assertSee('Nama Kursus')
        ->assertSee('Peserta')
        ->assertSee('Kursus Perencanaan SDM')
        ->assertSee('Perencanaan');
});

test('admin pusat can create and rename a course category', function () {
    $createResponse = $this->postJson(route('admin-pusat.management-course.categories.store'), [
        'name' => 'Perencanaan Tenaga Kerja',
    ]);

    $createResponse
        ->assertCreated()
        ->assertJsonPath('category.name', 'Perencanaan Tenaga Kerja');

    $categoryId = $createResponse->json('category.id');
    $category = Category::findOrFail($categoryId);

    $this->putJson(route('admin-pusat.management-course.categories.update', $category), [
        'name' => 'Perencanaan SDM',
    ])
        ->assertOk()
        ->assertJsonPath('category.name', 'Perencanaan SDM');

    expect($category->fresh()->slug)->toBe('perencanaan-sdm');
});

test('admin pusat can delete an unused course category', function () {
    $category = Category::create(['name' => 'Kategori Sementara']);

    $this->deleteJson(route('admin-pusat.management-course.categories.destroy', $category))
        ->assertOk()
        ->assertJsonPath('message', 'Kategori berhasil dihapus.');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('a course category that is in use cannot be deleted', function () {
    $category = Category::create(['name' => 'Kategori Terpakai']);
    Course::create([
        'category_id' => $category->id,
        'name' => 'Kursus terkait kategori',
        'description' => 'Deskripsi kursus terkait kategori.',
    ]);

    $this->deleteJson(route('admin-pusat.management-course.categories.destroy', $category))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Kategori tidak dapat dihapus karena masih digunakan oleh kursus.');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('course category management is restricted to admin pusat', function () {
    Role::create([
        'name' => 'user',
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create()->assignRole('user');

    $this->actingAs($user)
        ->postJson(route('admin-pusat.management-course.categories.store'), [
            'name' => 'Kategori Tidak Diizinkan',
        ])
        ->assertForbidden();
});
