@props([
    'courses' => collect(),
])

<section id="courses"
    class="py-12 sm:py-18 lg:py-16 px-4 sm:px-6 lg:px-8 bg-slate-50/60 border-t border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 items-start gap-8 sm:grid-cols-2 sm:gap-6 lg:gap-24">

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
            <div class="order-1 min-w-0 sm:order-2 sm:sticky sm:top-24">
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
                        Ikuti pelatihan daring yang terstruktur untuk memperkuat kompetensi dan memperluas pengetahuan ketenagakerjaan secara mandiri.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-2 sm:mt-8">
                        <span class="rounded-full bg-[#13416B]/5 px-3.5 py-1 text-xs font-semibold text-[#13416B]">Pelatihan Mandiri</span>
                        <span class="rounded-full bg-[#13416B]/5 px-3.5 py-1 text-xs font-semibold text-[#13416B]">Materi Terstruktur</span>
                        <span class="rounded-full bg-[#13416B]/5 px-3.5 py-1 text-xs font-semibold text-[#13416B]">Sertifikat Resmi</span>
                    </div>
                </div>

                <div class="relative hidden sm:block">
                    <img src="{{ asset('images/Library.webp') }}" alt="Ilustrasi LMS"
                        class="w-full h-auto max-h-[300px] object-contain drop-shadow-lg mx-auto" />
                </div>
            </div>

        </div>
    </div>
</section>
