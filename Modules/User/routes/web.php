<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserManagementController;


Route::prefix('super-admin')->middleware(['auth', 'role:super-admin'])->name('super-admin.')->group(function () {
    Route::prefix('user-management')->name('user-management.')->controller(UserManagementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::delete('/{user}/destroy', 'destroy')->name('destroy');
        Route::get('/{user}/show', 'show')->name('show');
    });
});