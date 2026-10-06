<?php

use Laravel\Socialite\Facades\Socialite;

test('a failed SSO callback displays an error instead of restarting authorization', function () {
    $provider = Mockery::mock();
    $provider->shouldReceive('user')
        ->once()
        ->andThrow(new RuntimeException('Invalid state'));

    Socialite::shouldReceive('driver')
        ->once()
        ->with('siapkerja')
        ->andReturn($provider);

    $this->get(route('siapkerja.callback'))
        ->assertStatus(500)
        ->assertSee('Autentikasi gagal diproses')
        ->assertHeaderMissing('Location');
});
