<x-dashboard::layouts.dashboard title="Kursus Sedang Berlangsung | SIRENATA">
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    @endpush

    <div class="p-0 sm:p-6 lg:p-8 bg-slate-50 min-h-screen">
        <x-lms::user.course-banner illustration="images/modul_ilustrasi.webp" />

        <div>
            <x-lms::user.course-tabs active="progress" :total="$meta['total'] ?? 0" />

            <x-lms::user.infinite-course-grid
                :courses="$courses"
                :hasMore="$hasMore ?? false"
                :nextPage="$nextPage ?? null"
                :fetchUrl="route('user.course.my-course.progress')"
            >
                <x-slot name="empty">
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center bg-white dark:bg-slate-800/90 rounded-md border border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/60 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-600 text-slate-300 dark:text-slate-400">
                            <i class="fas fa-spinner text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">Tidak Ada Pelatihan Sedang Berlangsung</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-300 max-w-sm">Belum ada pelatihan dalam periode pelaksanaan atau evaluasi saat ini.</p>
                    </div>
                </x-slot>
            </x-lms::user.infinite-course-grid>
        </div>
    </div>
</x-dashboard::layouts.dashboard>