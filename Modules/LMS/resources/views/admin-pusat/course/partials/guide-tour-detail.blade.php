{{-- Tur panduan halaman detail course admin (struktur kurikulum).
    Membutuhkan hook data-tour: kurikulum, evaluasi. --}}
@push('scripts')
    @include('lms::user.course.partials.guide-assets')
    <script>
        (function () {
            const PAGE = 'course_detail_admin';
            const STEPS = [
                {
                    element: '[data-tour="kurikulum"]',
                    popover: {
                        title: 'Struktur kurikulum',
                        description: 'Tambah Bagian untuk membuat bab, lalu isi tiap bab dengan materi. Setiap materi yang selesai membuka tombol tambah post test di bab tersebut.',
                    },
                },
                {
                    element: '[data-tour="evaluasi"]',
                    popover: {
                        title: 'Evaluasi akhir',
                        description: 'Ujian penentu kelulusan peserta. Buat atau ubah di sini; peserta baru bisa lulus setelah menyelesaikannya.',
                    },
                },
                {
                    popover: {
                        title: 'Alur lengkap',
                        description: 'Bagian → materi → post test per bab → evaluasi akhir → sertifikat. Tur ini bisa dibuka lagi lewat tombol Panduan.',
                    },
                },
            ];

            window.startCourseDetailTour = function () {
                window.SirenataTour.start(PAGE, STEPS);
            };

            document.addEventListener('DOMContentLoaded', function () {
                window.SirenataTour.autoStart(PAGE, STEPS);
            });
        })();
    </script>
@endpush
