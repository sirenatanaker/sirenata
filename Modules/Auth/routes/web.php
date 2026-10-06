<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\PasswordResetController;
use Modules\Auth\Http\Controllers\RegisterController;
use Modules\Auth\Http\Controllers\SiapKerjaController;

Route::prefix("auth")->group(function () {

    Route::middleware("guest")->group(function () {
        Route::get("/login", [LoginController::class, "login"])->name("login");
        Route::post("/login", [LoginController::class, "authenticate"])->name("authenticate");

        Route::get("/register", [RegisterController::class, "register"])->name("register");
        Route::post("/register", [RegisterController::class, "store"])->name("register.store");

        Route::get("/forgot-password", [PasswordResetController::class, "showForgotForm"])->name("forgot-password");
        Route::post("/forgot-password", [PasswordResetController::class, "sendResetLink"])->name("password.email");
        Route::get("/reset-password", [PasswordResetController::class, "showResetForm"])->name("password.reset");
        Route::post("/reset-password", [PasswordResetController::class, "resetPassword"])->name("password.update");
    });

    Route::post("/logout", [LoginController::class, "logout"])
        ->middleware("auth")
        ->name("logout");


    Route::get('/redirect', [SiapKerjaController::class, 'redirect'])->name('siapkerja.redirect');
    Route::get('/callback', [SiapKerjaController::class, 'callback'])->name('siapkerja.callback');
    Route::post('/siapkerja/logout', [LoginController::class, 'logout'])
        ->middleware('auth')
        ->name('siapkerja.logout');
});
