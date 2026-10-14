<?php

// Regression test temuan Qualys WAS (Mei 2026): route scaffold tanpa
// middleware auth pernah mengekspos daftar user + activity log ke publik.
// Route tersebut sudah dihapus; test ini memastikan tidak muncul lagi.

test('public user listing route does not exist', function () {
    $this->get('/users')->assertNotFound();
});

test('public activity log route does not exist', function () {
    $this->get('/activity')->assertNotFound();
});

test('named legacy user routes are not registered', function () {
    foreach (['user.index', 'user.show', 'user.edit', 'user.update', 'user.destroy', 'user.create', 'user.store'] as $name) {
        expect(\Illuminate\Support\Facades\Route::has($name))->toBeFalse();
    }
});
