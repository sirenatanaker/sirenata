<?php

// Penjaga panduan interaktif (driver.js): memastikan hook tur tetap ada
// di halaman, karena tur rusak diam-diam bila target dihapus.

test('course player page exposes guided tour hooks', function () {
    $source = file_get_contents(
        base_path('Modules/LMS/resources/views/user/course/my-course-detail.blade.php')
    );

    foreach (['data-tour="progres"', 'data-tour="modul"', 'data-tour="evaluasi"', 'data-tour="sertifikat"', 'startCoursePlayerTour', 'guide-assets'] as $needle) {
        expect($source)->toContain($needle);
    }
});

test('content reader page exposes guided tour hooks', function () {
    $source = file_get_contents(
        base_path('Modules/LMS/resources/views/user/course/content-show.blade.php')
    );

    foreach (['data-tour="materi"', 'data-tour="aksi"', 'startContentReaderTour', 'guide-assets'] as $needle) {
        expect($source)->toContain($needle);
    }
});

test('tour assets partial loads driver.js with shared helpers', function () {
    $source = file_get_contents(
        base_path('Modules/LMS/resources/views/user/course/partials/guide-assets.blade.php')
    );

    foreach (['driver.js@1/dist/driver', 'window.SirenataTour', 'autoStart', 'markSeen', 'Lanjut', 'Selesai'] as $needle) {
        expect($source)->toContain($needle);
    }
});

test('admin course detail exposes guided tour hooks', function () {
    $source = file_get_contents(
        base_path('Modules/LMS/resources/views/admin-pusat/course/show.blade.php')
    );

    foreach (['data-tour="kurikulum"', 'data-tour="evaluasi"', 'startCourseDetailTour', 'guide-tour-detail'] as $needle) {
        expect($source)->toContain($needle);
    }
});
