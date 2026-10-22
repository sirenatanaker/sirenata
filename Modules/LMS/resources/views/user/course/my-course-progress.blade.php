<x-dashboard::layouts.dashboard title="Kursus Saya | SIRENATA">
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    @endpush

    <div class="p-0 md:p-6 lg:p-8 bg-slate-50 min-h-screen">
        <x-lms::user.course-banner illustration="images/modul_ilustrasi.webp" />

        <div>
            <x-lms::user.course-tabs active="progress" :total="$meta['total'] ?? 0" />

            <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-y-6 gap-x-3 sm:gap-6 px-0 sm:px-4 md:px-0">
                @forelse ($courses as $course)
                    <x-lms::user.course-card :course="$course" />
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white dark:bg-slate-800/90 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/60 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-600 text-slate-300 dark:text-slate-400">
                            <i class="fas fa-check-double text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">Semua Kursus Selesai!</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-300 max-w-sm">Anda tidak memiliki kursus yang sedang berjalan.</p>
                    </div>
                @endforelse
            </div>

            @if (!empty($courses) && count($courses) > 0)
                <div class="mt-8 flex justify-center">
                    <x-api-pagination :meta="$meta" />
                </div>
            @endif
        </div>
    </div>
</x-dashboard::layouts.dashboard>