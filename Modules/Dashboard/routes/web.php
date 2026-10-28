<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\NotificationController;
use Modules\Dashboard\Http\Controllers\PortalDashboardController;
use Modules\Dashboard\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Modules\Dashboard\Http\Controllers\SuperAdmin\ActivityLogController;
use Modules\Dashboard\Http\Controllers\AdminPusat\DashbordController as AdminPusatDashboardController;
use Modules\Dashboard\Http\Controllers\AdminProvinsi\DashboardController as AdminProvinsiDashboardController;
use Modules\Dashboard\Http\Controllers\AdminKabKota\DashboardController as AdminKabKotaDashboardController;

use Modules\Dashboard\Http\Controllers\UserDashboardController;

Route::get('/portal-dashboard', [PortalDashboardController::class, 'index'])->middleware(['auth'])->name('portal-dashboard');

Route::prefix('user')->middleware(['auth', 'role:user'])->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/instansi', [UserDashboardController::class, 'updateInstansi'])->name('update-instansi');
    Route::get('/dashboard/get-regencies', [UserDashboardController::class, 'getRegencies'])->name('get-regencies');

    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserDashboardController::class, 'storeOrUpdateProfile'])->name('profile.update');
    
    Route::get('/search', [UserDashboardController::class, 'search'])->name('search');
    Route::get('/search-suggest', [UserDashboardController::class, 'searchSuggest'])->name('search.suggest');
});

Route::prefix('super-admin')->middleware(['auth', 'role:super-admin'])->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [SuperAdminDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [SuperAdminDashboardController::class, 'storeOrUpdateProfile'])->name('profile.update');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
});

Route::prefix('admin-pusat')->middleware(['auth', 'role:admin-pusat'])->name('admin-pusat.')->group(function () {
    Route::get('/dashboard', [AdminPusatDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AdminPusatDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminPusatDashboardController::class, 'storeOrUpdateProfile'])->name('profile.update');
});

Route::prefix('admin-provinsi')->middleware(['auth', 'role:admin-province'])->name('admin-province.')->group(function () {
    Route::get('/dashboard', [AdminProvinsiDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AdminProvinsiDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminProvinsiDashboardController::class, 'storeOrUpdateProfile'])->name('profile.update');
});

Route::prefix('admin-kab-kota')->middleware(['auth', 'role:admin-kab-kota'])->name('admin-kab-kota.')->group(function () {
    Route::get('/dashboard', [AdminKabKotaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AdminKabKotaDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminKabKotaDashboardController::class, 'storeOrUpdateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Notifikasi (Admin Provinsi & Admin Kab/Kota)
|--------------------------------------------------------------------------
| Notifikasi dibuat oleh listener saat kejadian (persetujuan/verifikasi)
| terjadi; endpoint di bawah hanya untuk membaca & membuang.
*/
Route::prefix('notifications')
    ->name('notifications.')
    ->middleware(['auth', 'role:user|admin-province|admin-kab-kota'])
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
    });
