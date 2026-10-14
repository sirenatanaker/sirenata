<?php

use App\Models\User;
use Modules\Faq\Models\Faq;
use Modules\Roles\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'user', 'guard_name' => 'web']);
    Role::create(['name' => 'admin-pusat', 'guard_name' => 'web']);
    Role::create(['name' => 'admin-province', 'guard_name' => 'web']);
});

function makeFaq(string $question, array $roles, string $creatorId): Faq
{
    $faq = Faq::create([
        'question' => $question,
        'answer' => 'A',
        'created_by' => $creatorId,
    ]);
    $faq->roles()->sync(Role::whereIn('name', $roles)->pluck('uuid')->all());

    return $faq;
}

test('guest cannot access help faqs endpoint', function () {
    $this->getJson(route('help.faqs'))->assertUnauthorized();
});

test('help faqs endpoint returns only faqs targeted at the viewer role', function () {
    $user = User::create(['name' => 'Biasa', 'email' => 'biasa@kemnaker.go.id', 'password' => 'password']);
    $user->assignRole('user');

    makeFaq('Q User', ['user'], $user->id);
    makeFaq('Q Multi', ['user', 'admin-province'], $user->id);
    makeFaq('Q Admin Saja', ['admin-province'], $user->id);

    $this->actingAs($user)->getJson(route('help.faqs'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['question' => 'Q User'])
        ->assertJsonFragment(['question' => 'Q Multi'])
        ->assertJsonMissing(['question' => 'Q Admin Saja']);
});

test('help faqs endpoint adapts to newly added roles', function () {
    Role::create(['name' => 'operator-wilayah', 'guard_name' => 'web']);

    $operator = User::create(['name' => 'Op', 'email' => 'op@kemnaker.go.id', 'password' => 'password']);
    $operator->assignRole('operator-wilayah');

    makeFaq('Q Operator', ['operator-wilayah'], $operator->id);
    makeFaq('Q User', ['user'], $operator->id);

    $this->actingAs($operator)->getJson(route('help.faqs'))
        ->assertOk()
        ->assertJsonFragment(['question' => 'Q Operator'])
        ->assertJsonMissing(['question' => 'Q User']);
});

test('help faqs endpoint returns everything for pusat admins', function () {
    $admin = User::create(['name' => 'Pusat', 'email' => 'pusat@kemnaker.go.id', 'password' => 'password']);
    $admin->assignRole('admin-pusat');

    makeFaq('Q User', ['user'], $admin->id);
    makeFaq('Q Admin', ['admin-province'], $admin->id);

    $this->actingAs($admin)->getJson(route('help.faqs'))
        ->assertOk()
        ->assertJsonFragment(['question' => 'Q User'])
        ->assertJsonFragment(['question' => 'Q Admin']);
});

test('legacy help pages no longer exist', function () {
    $admin = User::create(['name' => 'Pusat', 'email' => 'pusat@kemnaker.go.id', 'password' => 'password']);
    $admin->assignRole('admin-pusat');

    $this->actingAs($admin)->get('/admin-pusat/help')->assertNotFound();

    foreach (['admin-pusat.help', 'admin-province.help', 'admin-kab-kota.help', 'user.help'] as $name) {
        expect(\Illuminate\Support\Facades\Route::has($name))->toBeFalse();
    }
});

test('super-admin can create faq with multiple target roles', function () {
    $super = User::create(['name' => 'SA', 'email' => 'sa@kemnaker.go.id', 'password' => 'password']);
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $super->assignRole('super-admin');

    $this->actingAs($super)->post(route('super-admin.faq.store'), [
        'question' => 'Q Baru',
        'answer' => 'Jawaban baru.',
        'roles' => ['user', 'admin-province'],
    ])->assertRedirect(route('super-admin.faq.index'));

    $faq = Faq::where('question', 'Q Baru')->first();

    expect($faq)->not->toBeNull()
        ->and($faq->roles->pluck('name')->sort()->values()->all())->toBe(['admin-province', 'user']);
});

test('super-admin faq store rejects disallowed roles', function () {
    $super = User::create(['name' => 'SA', 'email' => 'sa@kemnaker.go.id', 'password' => 'password']);
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $super->assignRole('super-admin');

    $this->actingAs($super)->post(route('super-admin.faq.store'), [
        'question' => 'Q Nakal',
        'answer' => 'Jawaban.',
        'roles' => ['super-admin'],
    ])->assertSessionHasErrors('roles.0');

    expect(Faq::where('question', 'Q Nakal')->exists())->toBeFalse();
});
