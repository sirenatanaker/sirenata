<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the landing page shows the LMS section even with an empty catalog', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Katalog kursus segera hadir', false);
});
