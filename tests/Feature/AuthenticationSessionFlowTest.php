<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Roles\Models\Role;

test('logout clears the authenticated session and returns to the landing page', function () {
    $user = User::factory()->create([
        'siapkerja_token' => 'persisted-siapkerja-access-token',
        'siapkerja_refresh_token' => 'persisted-siapkerja-refresh-token',
    ]);

    $this->actingAs($user)
        ->withSession([
            'api_token' => 'api-token',
            'access_token' => 'siapkerja-access-token',
            'refresh_token' => 'siapkerja-refresh-token',
        ])
        ->post(route('logout'))
        ->assertRedirect(route('landingpage.index'))
        ->assertSessionHas('logged_out', true);

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'siapkerja_token' => null,
        'siapkerja_refresh_token' => null,
    ]);

    $this->get(route('landingpage.index'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertDontSee('Daftar Gratis')
        ->assertDontSee('Daftar Gratis Sekarang');

    $this->get(route('landingpage.index'))
        ->assertSee('Daftar Gratis');
});

test('the legacy SIAPKerja logout route also clears the authenticated session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('siapkerja.logout'))
        ->assertRedirect(route('landingpage.index'));

    $this->assertGuest();
});

test('authenticated users can return to the landing page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('landingpage.index'))
        ->assertOk();
});

test('landing page registration links point to SIAPKerja while login keeps using the SSO route', function () {
    config(['app.env' => 'production']);

    $this->get(route('landingpage.index'))
        ->assertOk()
        ->assertSee('href="https://account.kemnaker.go.id/register"', false)
        ->assertSee('href="' . route('login') . '"', false);
});

test('local users can sign in with the built-in email and password form', function () {
    config(['app.env' => 'local']);

    $user = User::factory()->create([
        'password' => Hash::make('local-password'),
    ]);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke Akun Anda');

    $this->post(route('authenticate'), [
        'email' => $user->email,
        'password' => 'local-password',
    ])
        ->assertRedirect(route($user->getRedirectRoute()));

    $this->assertAuthenticatedAs($user);
});

test('local users can create an account with the built-in registration form', function () {
    config(['app.env' => 'local']);

    Role::create([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Daftar Akun Baru');

    $this->post(route('register.store'), [
        'name' => 'Local Test User',
        'email' => 'local-test@example.test',
        'password' => 'local-password',
        'password_confirmation' => 'local-password',
    ])
        ->assertRedirect(route('user.dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'local-test@example.test',
        'name' => 'Local Test User',
    ]);
});

test('non-local login and registration continue to use SIAPKerja', function () {
    config(['app.env' => 'production']);

    $this->get(route('login'))
        ->assertRedirect(route('siapkerja.redirect'));

    $this->get(route('register'))
        ->assertRedirect(route('siapkerja.redirect'));
});
