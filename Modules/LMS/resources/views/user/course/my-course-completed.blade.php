<x-dashboard::layouts.dashboard title="Kursus Saya | SIRENATA">

    {{-- Impor Font Kalam dan Oswald dari Google Fonts --}}
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Oswald:wght@600;700&display=swap"
            rel="stylesheet">
    @endpush
    <div class="p-0 md:p-6 lg:p-8 bg-slate-50 min-h-screen">

        {{-- ========================================== --}}
        {{-- HEADER KURSUS SAYA DENGAN ILUSTRASI GRUP   --}}
        {{-- ========================================== --}}
        <div
            class="relative bg-[#13416B] rounded-md p-6 sm:p-8 lg:p-10 mb-6 sm:mb-8 flex items-center justify-between border border-blue-900/20 shadow-lg overflow-hidden min-h-[280px] sm:min-h-[280px] lg:min-h-[320px]">

            <!-- Efek Dekoratif Bubbles Geometris Profesional -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_1.5px,transparent_1.5px)] [background-size:24px_24px]">
                </div>
                <div class="absolute -top-24 -right-16 w-80 h-80 border-[30px] border-white/5 rounded-full"></div>
                <div class="absolute -bottom-20 right-[10%] w-64 h-64 bg-white/5 rounded-full"></div>
                <div class="absolute top-[15%] right-[38%] w-24 h-24 border-[8px] border-amber-400/20 rounded-full">
                </div>
                <div class="absolute bottom-[30%] right-[45%] w-8 h-8 bg-blue-400/20 rounded-full"></div>
                <div class="absolute top-[20%] left-[45%] w-12 h-12 bg-white/5 rounded-full"></div>
                <div
                    class="absolute left-0 top-0 w-2/3 h-full bg-gradient-to-r from-[#13416B] via-[#13416B]/80 to-transparent z-10">
                </div>
            </div>

            <!-- Sisi Kiri: Teks Utama -->
            <div class="relative z-20 w-full sm:w-[60%] lg:w-[60%] text-left ">

                {{-- BADGE: FONT KALAM (Tanpa uppercase dan font-semibold) --}}
                <span
                    class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-md text-sm sm:text-base tracking-wider text-white border border-white/10 mb-4 shadow-sm"
                    style="font-family: 'Kalam', cursive;">
                    Ruang Belajar Pegawai
                </span>

                {{-- JUDUL: FONT OSWALD (Tanpa font-extrabold, tracking-tight, dan leading-tight) --}}
                <h1 class="text-2xl md:text-3xl lg:text-4xl text-white mb-4" style="font-family: 'Oswald', sans-serif;">
                    Pelatihan & Pengembangan Kompetensi
                </h1>

                <!-- Deskripsi Diperpanjang -->
                <p class="text-sm md:text-base text-blue-100/90 leading-relaxed max-w-xl lg:max-w-2xl font-medium">
                    Selesaikan modul pelatihan Anda, ikuti evaluasi dengan baik, dan raih sertifikat kelulusan untuk
                    mendukung perjalanan karier serta profesionalisme Anda.
                </p>
            </div>

            <!-- Sisi Kanan: Ilustrasi Pegawai Kemnaker -->
            <div
                class="hidden sm:flex absolute bottom-0 right-0 lg:right-5 z-10 w-[45%] lg:w-[35%] h-[90%] lg:h-[95%] pointer-events-none justify-end items-end">
                <img src="{{ asset('images/pegawai_kemnaker.webp') }}" alt="Pegawai Kemnaker"
                    class="w-full h-full object-contain object-bottom drop-shadow-[0_15px_25px_rgba(0,0,0,0.3)] relative z-20"
                    onerror="this.style.display='none'">
            </div>
        </div>


        <div>
            <div class="mb-6 sm:mb-8 border-b border-slate-200">
                <nav class="flex space-x-6 overflow-x-auto pb-[-1px]">
                    <a href="{{ route('user.course.my-course') }}"
                        class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium">
                        <span>Semua</span>
                    </a>
                    <a href="{{ route('user.course.my-course.progress') }}"
                        class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium">
                        <span>Belum Selesai</span>
                    </a>
                    <a href="{{ route('user.course.my-course.finish') }}"
                        class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors border-indigo-600 text-indigo-600 font-bold">
                        <span>Selesai ({{ $meta['total'] ?? 0 }})</span>
                    </a>
                </nav>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 px-0 sm:px-4 md:px-0">
                @forelse ($courses as $course)
                    <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                        class="group flex flex-col bg-white rounded-md border border-emerald-100 shadow-sm hover:shadow-xl hover:border-emerald-300 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                        <div class="relative h-24 sm:h-48 overflow-hidden bg-slate-100">
                            @if (!empty($course->thumbnail_url))
                                <img src="{{ $course->thumbnail_url }}"
                                    alt="{{ $course->course_name ?? $course->name }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-image text-2xl sm:text-4xl text-slate-300"></i>
                                </div>
                            @endif

                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 max-w-[60%] sm:max-w-none">
                                <span
                                    class="block truncate px-1.5 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-800 bg-white/90 backdrop-blur-sm rounded shadow-sm">
                                    {{ $course->category->name ?? 'Umum' }}
                                </span>
                            </div>

                            <div class="absolute top-2 right-2 sm:top-3 sm:right-3">
                                <span
                                    class="px-1.5 py-0.5 sm:px-2 sm:py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 border border-emerald-200 rounded shadow-sm flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> <span class="hidden sm:inline">Selesai</span>
                                </span>
                            </div>
                        </div>

                        <div class="p-3 sm:p-5 flex flex-col flex-1">
                            <h3
                                class="text-[10px] sm:text-base font-bold text-slate-800 leading-snug mb-1.5 sm:mb-2 group-hover:text-emerald-600 transition-colors line-clamp-2">
                                {{ $course->course_name ?? $course->name }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-2.5 sm:mb-3 text-[9px] sm:text-xs font-medium text-slate-500">
                                <span class="flex items-center gap-1 sm:gap-1.5 bg-slate-50 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded border border-slate-100">
                                    <i class="fas fa-layer-group text-slate-400"></i> {{ $course->total_modul ?? 0 }} Modul
                                </span>
                                <span class="flex items-center gap-1 sm:gap-1.5 bg-slate-50 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded border border-slate-100">
                                    <i class="fas fa-list-ul text-slate-400"></i> {{ $course->total_materi ?? 0 }} Topik
                                </span>
                                <span class="flex items-center gap-1 sm:gap-1.5 bg-[#13416B]/5 text-[#13416B] px-1.5 py-0.5 sm:px-2 sm:py-1 rounded border border-[#13416B]/10">
                                    <i class="fas fa-users"></i> {{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta
                                </span>
                            </div>

                            <p class="hidden sm:block text-xs text-slate-500 mb-5 line-clamp-2 leading-relaxed flex-1">
                                {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
                            </p>

                            <div class="mt-auto pt-3 sm:pt-4 border-t border-slate-100">
                                <div
                                    class="flex items-center justify-between mb-1.5 sm:mb-2 text-[9px] sm:text-[11px] font-bold uppercase tracking-wider">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="text-emerald-600">100%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-2.5 sm:mb-4">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: 100%"></div>
                                </div>

                                <div
                                    class="w-full py-1.5 sm:py-2.5 text-[10px] sm:text-xs font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600">
                                    <i class="fas fa-award mr-1"></i> Lihat Sertifikat
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div
                        class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                        <div
                            class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 text-slate-300">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Kursus Selesai</h3>
                        <p class="text-sm text-slate-500 max-w-sm">Anda belum menyelesaikan kursus apapun. Terus
                            tingkatkan progress belajar Anda.</p>
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