<?php

use Laravel\Socialite\Facades\Socialite;

test('a failed SSO callback uses the default server error response', function () {
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
        ->assertHeaderMissing('Location');
});
