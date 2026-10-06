<?php

use App\Models\User;

test('authenticated users see dashboard actions instead of registration actions on the landing page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('landingpage.index'))
        ->assertOk()
        ->assertSee('Buka Dashboard')
        ->assertDontSee('Daftar Gratis')
        ->assertDontSee('Daftar Sekarang')
        ->assertDontSee('Daftar Gratis Sekarang');
});
