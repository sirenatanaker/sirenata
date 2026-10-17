@props([
    'stats' => [],
])

<section id="cta" class="py-20 sm:py-16 md:py-28 lg:py-42 px-4 md:px-16 relative overflow-hidden"
    style="background-color: #13416B;">
    <!-- Efek Glow Latar Belakang -->
    <div class="absolute inset-0 bg-blue-400/20 blur-[120px] rounded-full w-[80%] h-[80%] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none z-0"></div>

    <!-- Ornamen SVG -->
    <div class="absolute -left-[50%] sm:-left-[20%] top-1/2 -translate-y-1/2 w-[1200px] h-[1200px] lg:w-[2200px] lg:h-[2200px] text-white opacity-[0.06] pointer-events-none z-0 transition-transform duration-1000">
        <svg viewBox="0 0 1600 1600" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
            <polyline points="300,200 900,800 300,1400" stroke="currentColor" stroke-width="260"
                stroke-linecap="round" stroke-linejoin="miter" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

            <!-- Kolom Kiri: Teks & Tombol -->
            <div class="text-left reveal-left lg:col-span-7 xl:col-span-8 lg:pr-10">
                <h2 class="section-title section-title-cta text-white mb-4 lg:mb-6 drop-shadow-sm">
                    Siap Memulai Perencanaan?
                </h2>
                <p class="text-slate-300 mb-6 lg:mb-10 max-w-2xl text-base lg:text-xl leading-relaxed">
                    Tingkatkan efisiensi dan akurasi data dengan bergabung bersama
                    <span class="text-yellow-300 font-extrabold stat-counter"
                        data-target="{{ $stats['regencies'] ?? 514 }}" data-suffix="+">0</span> daerah lain di
                    seluruh Indonesia menggunakan <span class="text-yellow-300 font-extrabold">SIRENATA</span>.
                </p>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-4 reveal-up"
                    style="transition-delay: 0.2s;">
                    @guest
                        @unless (session('logged_out'))
                            <a href="{{ config('services.siapkerja.register_url') }}"
                                class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 bg-white font-bold rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-sm lg:text-lg text-center"
                                style="color: #13416B;">
                                Daftar Gratis Sekarang
                            </a>
                        @endunless
                        <a href="{{ route('login') }}"
                            class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 font-bold rounded-full border-2 border-white/30 text-white transition-all hover:bg-white/10 text-sm lg:text-lg text-center">
                            Sudah Punya Akun? Masuk
                        </a>
                    @else
                        <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                            class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 bg-white font-bold rounded-full shadow-lg text-sm lg:text-lg text-center"
                            style="color: #13416B;">
                            Masuk
                        </a>
                    @endguest
                </div>
            </div>

        </div>
    </div>
</section>
