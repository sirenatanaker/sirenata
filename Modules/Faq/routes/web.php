<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // JSON untuk modal Pusat Bantuan (pengganti halaman bantuan per role).
    Route::get('/help/faqs', [\Modules\Faq\Http\Controllers\HelpFaqController::class, 'index'])->name('help.faqs');

    // Manajemen FAQ hanya di super-admin (satu pintu untuk semua target role).
    Route::prefix('super-admin')->middleware(['role:super-admin'])->name('super-admin.')->group(function () {
        Route::get('faqs/export', [\Modules\Faq\Http\Controllers\SuperAdmin\FaqController::class, 'export'])->name('faq.export');
        Route::resource('faqs', \Modules\Faq\Http\Controllers\SuperAdmin\FaqController::class)->names('faq');
    });
});
