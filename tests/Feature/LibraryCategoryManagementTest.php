<?php

use App\Models\User;
use Modules\LMS\Models\Library;
use Modules\LMS\Models\LibraryCategory;
use Modules\Permission\Models\Permission;
use Modules\Roles\Models\Role;

beforeEach(function () {
    $permissions = collect(['library-view', 'library-create', 'library-edit', 'library-delete'])
        ->map(fn (string $name) => Permission::create(['name' => $name, 'guard_name' => 'web']));
    $role = Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);
    $role->givePermissionTo($permissions);

    $this->user = User::factory()->create();
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

test('the library collection form has inline category management and a single sidebar destination', function () {
    LibraryCategory::create(['name' => 'Peraturan']);

    $this->get(route('admin-pusat.libraries.index'))
        ->assertOk()
        ->assertSee('Kelola kategori koleksi')
        ->assertSee('Tipe Koleksi')
        ->assertDontSee('Tipe Materi')
        ->assertSee('Kelola Kategori Koleksi')
        ->assertSee('Peraturan')
        ->assertSee('Manajemen Perpustakaan')
        ->assertDontSee(route('admin-pusat.library-categories.index'));
});

test('external library covers are rendered as external URLs without a storage prefix', function () {
    $category = LibraryCategory::create(['name' => 'Peraturan']);
    $coverUrl = 'https://ui-avatars.com/api/?name=Undang-Undang+Cipta&background=13416B&color=fff&size=512&bold=true&length=2';

    Library::create([
        'library_category_id' => $category->id,
        'title' => 'Undang-Undang Cipta',
        'cover_image' => $coverUrl,
        'created_by' => $this->user->id,
    ]);

    $this->get(route('admin-pusat.libraries.index'))
        ->assertOk()
        ->assertSee($coverUrl)
        ->assertDontSee('/storage/https://ui-avatars.com');

    expect(Library::first()->cover_image_url)->toBe($coverUrl);
});

test('admin pusat can create and rename a library category from the collection form', function () {
    $createResponse = $this->postJson(route('admin-pusat.library-categories.inline.store'), [
        'name' => 'Panduan Kerja',
    ]);

    $createResponse
        ->assertCreated()
        ->assertJsonPath('category.name', 'Panduan Kerja');

    $category = LibraryCategory::findOrFail($createResponse->json('category.id'));

    $this->putJson(route('admin-pusat.library-categories.inline.update', $category), [
        'name' => 'Panduan Ketenagakerjaan',
    ])
        ->assertOk()
        ->assertJsonPath('category.name', 'Panduan Ketenagakerjaan');
});

test('admin pusat can delete an unused library category', function () {
    $category = LibraryCategory::create(['name' => 'Kategori Sementara']);

    $this->deleteJson(route('admin-pusat.library-categories.inline.destroy', $category))
        ->assertOk()
        ->assertJsonPath('message', 'Kategori berhasil dihapus.');

    $this->assertDatabaseMissing('library_categories', ['id' => $category->id]);
});

test('a library category used by a collection cannot be deleted', function () {
    $category = LibraryCategory::create(['name' => 'Kategori Terpakai']);
    Library::create([
        'library_category_id' => $category->id,
        'title' => 'Koleksi terkait kategori',
        'created_by' => $this->user->id,
    ]);

    $this->deleteJson(route('admin-pusat.library-categories.inline.destroy', $category))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Kategori tidak dapat dihapus karena masih digunakan oleh koleksi.');

    $this->assertDatabaseHas('library_categories', ['id' => $category->id]);
});

test('library category inline endpoints are restricted to admin pusat', function () {
    $this->postJson(route('admin-pusat.library-categories.inline.store'), [
        'name' => 'Kategori baru',
    ])->assertCreated();

    $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($userRole);

    $this->actingAs($user)
        ->postJson(route('admin-pusat.library-categories.inline.store'), [
            'name' => 'Kategori tidak diizinkan',
        ])
        ->assertForbidden();
});
