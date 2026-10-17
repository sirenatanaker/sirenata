<?php

use Illuminate\Support\Facades\Route;
use Modules\LandingPage\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('landingpage.index');
