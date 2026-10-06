<?php

use App\Models\User;

test('logout clears the authenticated session and returns to the landing page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession([
            'api_token' => 'api-token',
            'access_token' => 'siapkerja-access-token',
            'refresh_token' => 'siapkerja-refresh-token',
        ])
        ->post(route('logout'))
        ->assertRedirect(route('landingpage.index'));

    $this->assertGuest();
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
