<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\Http\Controllers\AdminKabKota\RekapitulasiController as AdminKabKotaRekapitulasiController;
use Modules\LMS\Http\Controllers\AdminProvince\RekapitulasiController as AdminProvinceRekapitulasiController;
use Modules\LMS\Http\Controllers\AdminPusat\CertificateController as AdminPusatCertificateController;
use Modules\LMS\Http\Controllers\AdminPusat\Course\CourseController;
use Modules\LMS\Http\Controllers\AdminPusat\Course\CategoryController as AdminPusatCourseCategoryController;
use Modules\LMS\Http\Controllers\AdminPusat\Course\CourseSectionController;
use Modules\LMS\Http\Controllers\AdminPusat\Course\PostTestController;
use Modules\LMS\Http\Controllers\AdminPusat\Course\SectionContentController;
use Modules\LMS\Http\Controllers\AdminPusat\LibraryCategoryController as AdminPusatLibraryCategoryController;
use Modules\LMS\Http\Controllers\AdminPusat\LibraryController as AdminPusatLibraryController;
use Modules\LMS\Http\Controllers\AdminPusat\RekapitulasiController;
use Modules\LMS\Http\Controllers\User\CourseController as UserCourseController;
use Modules\LMS\Http\Controllers\User\LibraryController as UserLibraryController;

Route::prefix('admin-pusat')->middleware(['auth', 'role:admin-pusat'])->name('admin-pusat.')->group(function () {
    Route::prefix('rekapitulasi')->name('rekapitulasi.')->controller(RekapitulasiController::class)->group(function () {
        // Pusat (Instansi)
        Route::get('/pusat', 'pusat')->name('pusat');
        Route::get('/pusat/{instansi}', 'pusatUsers')->name('pusat.users');
        Route::get('/pusat/{instansi}/export', 'exportPusatUsers')->name('pusat.users.export');

        // Daerah (Provinsi → Kab/Kota → User)
        Route::get('/', 'index')->name('index');
        Route::get('/kab-kota/{provinceCode}', 'kabKota')->name('kab-kota');

        Route::get('/rekap-user-province/{provinceCode}', 'rekapUserProvince')->name('rekap-user-province');
        Route::get('/rekap-user-province/{provinceCode}/export', 'exportRekapUserProvince')->name('rekap-user-province.export');

        Route::get('/rekap-user-kab-kota/{regencyCode}', 'rekapUserKabKota')->name('rekap-user-kab-kota');
        Route::get('/rekap-user-kab-kota/{regencyCode}/export', 'exportRekapUserRegency')->name('rekap-user-kab-kota.export');
    });

    Route::prefix('management-course')->name('management-course.')->group(function () {
        Route::post('categories', [AdminPusatCourseCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [AdminPusatCourseCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AdminPusatCourseCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::resource('courses', CourseController::class);
        Route::resource('course-sections', CourseSectionController::class);
        Route::resource('course-sections-contents', SectionContentController::class);
        Route::prefix('post-test')->name('post-tests.')->group(function () {
            Route::get('/create', [PostTestController::class, 'create'])->name('create');
            Route::post('/store', [PostTestController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PostTestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PostTestController::class, 'update'])->name('update');

            Route::post('/upload-image', [PostTestController::class, 'uploadImage'])->name('upload-image');
        });
    });



    Route::post('library-categories-inline', [AdminPusatLibraryCategoryController::class, 'storeInline'])->name('library-categories.inline.store');
    Route::put('library-categories-inline/{libraryCategory}', [AdminPusatLibraryCategoryController::class, 'updateInline'])->name('library-categories.inline.update');
    Route::delete('library-categories-inline/{libraryCategory}', [AdminPusatLibraryCategoryController::class, 'destroyInline'])->name('library-categories.inline.destroy');
    Route::resource('library-categories', AdminPusatLibraryCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('libraries', AdminPusatLibraryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('certificates', AdminPusatCertificateController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('certificates/{certificate}/activate', [AdminPusatCertificateController::class, 'activate'])->name('certificates.activate');
});

Route::prefix('admin-province')->middleware(['auth', 'role:admin-province'])->name('admin-province.')->group(function () {
    Route::prefix('rekapitulasi')->name('rekapitulasi.')->controller(AdminProvinceRekapitulasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/rekap-user-kab-kota/{regencyCode}', 'rekapUserKabKota')->name('rekap-user-kab-kota');
        Route::get('/rekap-user-province', 'rekapUserProvince')->name('rekap-user-province');

        Route::get('/rekap-user-kab-kota/{regencyCode}/export', 'exportRekapUserRegency')->name('rekap-user-kab-kota.export');
        Route::get('/rekap-user-province/export', 'exportRekapUserProvince')->name('rekap-user-province.export');
    });
});

Route::prefix('admin-kab-kota')->middleware(['auth', 'role:admin-kab-kota'])->name('admin-kab-kota.')->group(function () {
    Route::prefix('rekapitulasi')->name('rekapitulasi.')->controller(AdminKabKotaRekapitulasiController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        Route::get('/export', 'exportRekapUserRegency')->name('rekap-user-regency.export');
    });
});

Route::prefix('user')->middleware(['auth', 'role:user'])->name('user.')->group(function () {
    
    // --> PENAMBAHAN ROUTE UNTUK FITUR TERAKHIR DILIHAT (LIBRARY) <--
    Route::get('/library', [UserLibraryController::class, 'index'])->name('library.index');
    Route::get('/library/{id}', [UserLibraryController::class, 'show'])->name('library.show');

    Route::post('/library/{id}/track', [UserLibraryController::class, 'track'])->name('library.track');

    Route::prefix('course')->name('course.')->controller(UserCourseController::class)->group(function () {
        Route::get('/my-course', 'allMyCourse')->name('my-course');
        Route::get('/my-course/progress', 'myCourseProgress')->name('my-course.progress');
        Route::get('/my-course/finish', 'myCourseFinish')->name('my-course.finish');
        Route::get('/my-course/{slug}', 'myCourseDetail')->name('my-course.detail');
        Route::post('/my-course/{slug}/generate-certificate', 'generateCertificate')->name('my-course.generate-certificate');
        Route::post('/content/{content}/complete', 'completeContent')->name('content.complete');
        Route::get('/my-course/{slug}/content/{content}', 'showContent')->name('content.show');
        Route::get('/my-course/{slug}/test/{postTestId}', 'showTest')->name('test.show');
        Route::post('/my-course/{slug}/test/{postTestId}/submit', 'submitTest')->name('test.submit');
    });

    Route::prefix('katalog')->name('catalog.')->controller(\Modules\LMS\Http\Controllers\User\CatalogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{slug}/enroll', 'enroll')->name('enroll');
    });
});