@props([
    'courses' => collect(),
])

<section id="courses"
    class="py-12 sm:py-18 lg:py-16 px-4 sm:px-6 lg:px-8 bg-slate-50/60 border-t border-slate-200 relative overflow-hidden">
    <div
        class="mx-auto grid max-w-7xl grid-cols-1 items-stretch gap-8 sm:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] sm:gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)] lg:gap-16">

        <!-- Kiri: Kursus -->
        <div class="order-2 min-w-0 sm:order-1">
            @if (isset($courses) && $courses->count() > 0)
                <!-- Grid Kursus Satu Kolom -->
                <div
                    class="custom-scrollbar h-[520px] max-h-[520px] scroll-smooth overflow-y-auto overscroll-contain pr-2 sm:h-[500px] sm:max-h-[500px] md:h-[620px] md:max-h-[620px] lg:mt-2 lg:h-[780px] lg:max-h-[780px]">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-5">
                        @foreach ($courses as $course)
                            <x-landingpage::course-card :course="$course" />
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Empty state: katalog kursus belum ada -->
                <div class="flex h-full min-h-[320px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center lg:mt-2 lg:min-h-[420px]">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#13416B]/10 text-[#13416B] mb-4">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 lg:text-lg mb-2">Katalog kursus segera hadir</h3>
                    <p class="text-sm text-slate-500 max-w-sm leading-relaxed">Tim kami sedang menyiapkan modul pelatihan untuk aparatur daerah. Silakan kembali lagi nanti.</p>
                </div>
            @endif
        </div>

        <!-- Kanan: Ilustrasi LMS -->
        <div class="order-1 min-w-0 sm:order-2 sm:sticky sm:top-24 sm:pt-4 md:pt-6 lg:pt-8">
            <div class="mb-4 max-w-xl text-left reveal-right sm:mb-5 lg:mb-0">
                <div class="mb-3 flex items-center gap-3">
                    <span class="font-kalam text-lg font-bold text-[#13416B]">
                        LMS Terintegrasi
                    </span>
                    <div class="h-[2px] w-16 bg-[#13416B]/30"></div>
                </div>

                <h2 class="section-title text-slate-900">
                    Tingkatkan Kapasitas <span class="text-[#13416B]">Aparatur Daerah</span>
                </h2>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 md:mt-4 lg:text-lg">
                    Ikuti pelatihan daring yang terstruktur untuk memperkuat kompetensi dan memperluas
                    pengetahuan ketenagakerjaan secara mandiri.
                </p>
            </div>

            <div class="relative hidden h-[320px] items-center justify-center sm:flex sm:h-[380px] lg:h-[520px] reveal-right">
                <div class="absolute left-1/2 top-1/2 h-[190px] w-[190px] -translate-x-1/2 -translate-y-[52%] bg-gradient-to-tr from-amber-400 via-amber-300 to-amber-200 opacity-65 animate-blob lg:h-[340px] lg:w-[340px]"
                    style="border-radius: 42% 58% 63% 37% / 48% 40% 60% 52%;">
                </div>

                <div
                    class="absolute left-1/2 top-1/2 h-[220px] w-[220px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-[#13416B]/15 border-dashed animate-[spin_45s_linear_infinite] lg:h-[390px] lg:w-[390px]">
                </div>

                <svg class="absolute inset-0 h-full w-full opacity-50" viewBox="0 0 560 620" fill="none"
                    aria-hidden="true">
                    <path d="M72 430C128 205 267 111 469 188" stroke="#13416B" stroke-width="1.5"
                        stroke-dasharray="5 8" />
                    <circle cx="72" cy="430" r="5" fill="#F59E0B" />
                    <circle cx="469" cy="188" r="5" fill="#13416B" />
                </svg>

                <div
                    class="absolute bottom-2 left-3 z-20 w-32 rounded-lg border border-white/80 bg-white/95 p-2.5 shadow-xl animate-card-float-2 md:w-32 md:p-2.5 lg:bottom-8 lg:left-6 lg:w-44 lg:rounded-2xl lg:p-4">
                    <div class="mb-2 flex items-center justify-between lg:mb-3">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 lg:text-[10px]">Progres
                            Kursus</span>
                        <i class="fas fa-chart-line text-xs text-emerald-500 lg:text-sm"></i>
                    </div>
                    <div class="mb-2 flex items-end justify-between">
                        <strong class="text-xl font-extrabold text-[#13416B] lg:text-2xl">78%</strong>
                        <span class="text-[9px] font-bold text-emerald-500 lg:text-[10px]">+12%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[78%] rounded-full bg-emerald-400"></div>
                    </div>
                </div>

                <!-- CHAT MESSAGE CARD: Sertifikat Diperoleh (Bentuk message) -->
                <div
                    class="absolute bottom-18 right-2 sm:bottom-24 sm:right-3 lg:bottom-30 lg:right-4 z-20 flex items-center gap-2 rounded-xl border border-white/80 bg-[#13416B] px-2.5 py-2 text-white shadow-xl animate-card-float-1 md:gap-2.5 md:px-3 md:py-2.5 lg:gap-3.5 lg:px-4 lg:py-3 transition-all duration-300 hover:scale-105"
                    style="border-radius: 18px 18px 18px 3px;">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-400 text-[#13416B] shrink-0 md:h-8 md:w-8 lg:h-10 lg:w-10">
                        <i class="fas fa-award text-sm lg:text-base"></i>
                    </span>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-white/60 lg:text-[10px] mb-0.5">Kursus selesai</p>
                        <p class="text-[10px] font-bold text-white lg:text-sm mb-0">Sertifikat diperoleh</p>
                    </div>
                </div>

                <div class="relative z-10 w-full max-w-[430px] lg:max-w-[485px] animate-float drop-shadow-2xl">
                    <div class="relative">
                        <img src="{{ asset('images/LMS.webp') }}"
                            alt="Aparatur sedang belajar menggunakan laptop"
                            class="relative z-10 w-full object-contain"
                            style="-webkit-mask-image: linear-gradient(to bottom, black 86%, transparent 100%); mask-image: linear-gradient(to bottom, black 86%, transparent 100%);">
                        <div
                            class="pointer-events-none absolute inset-x-[-8%] bottom-0 h-16 bg-gradient-to-t from-slate-50/90 via-slate-50/45 to-transparent blur-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
