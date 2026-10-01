<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\PasswordResetController;
use Modules\Auth\Http\Controllers\RegisterController;
use Modules\Auth\Http\Controllers\SiapKerjaController;

Route::prefix("auth")->group(function () {

    Route::middleware("guest")->group(function () {
        Route::get("/login", [LoginController::class, "login"])->name("login");

        Route::get("/register", [RegisterController::class, "register"])->name("register");

        Route::get("/forgot-password", [PasswordResetController::class, "showForgotForm"])->name("forgot-password");
        Route::post("/forgot-password", [PasswordResetController::class, "sendResetLink"])->name("password.email");
        Route::get("/reset-password", [PasswordResetController::class, "showResetForm"])->name("password.reset");
        Route::post("/reset-password", [PasswordResetController::class, "resetPassword"])->name("password.update");
    });

    Route::post("/logout", [LoginController::class, "logout"])
        ->middleware("auth")
        ->name("logout");


    Route::get('/siapkerja/redirect', [SiapKerjaController::class, 'redirect'])->name('siapkerja.redirect');
    Route::get('/callback', [SiapKerjaController::class, 'callback'])->name('siapkerja.callback');
    Route::post('/siapkerja/logout', [SiapKerjaController::class, 'logout'])->name('siapkerja.logout');
});
