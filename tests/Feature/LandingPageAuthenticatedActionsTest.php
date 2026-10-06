<?php

use App\Models\User;

test('authenticated users see dashboard actions instead of registration actions on the landing page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('landingpage.index'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertDontSee('Buka Dashboard')
        ->assertDontSee('>Dashboard</a>', false)
        ->assertDontSee('Daftar Gratis')
        ->assertDontSee('Daftar Sekarang')
        ->assertDontSee('Daftar Gratis Sekarang');
});

test('landing page can be rendered repeatedly in one process', function () {
    $this->get(route('landingpage.index'))->assertOk();
    $this->get(route('landingpage.index'))->assertOk();
});
