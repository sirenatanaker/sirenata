<section class="pt-24 pb-10 md:pt-28 md:pb-12 lg:pt-32 lg:pb-16 lg:min-h-screen flex items-center relative overflow-hidden bg-slate-50/50" id="home">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full grid grid-cols-1 sm:grid-cols-[1.1fr_0.9fr] lg:grid-cols-2 gap-8 sm:gap-6 lg:gap-16 items-center relative z-10">

        <!-- Kiri: Teks & CTA -->
        <div class="reveal-left min-w-0">
            <!-- Title dengan Oswald -->
            <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-4 lg:mb-6"
                style="font-family: 'Oswald', sans-serif;">
                Rencanakan Lebih Tepat, <span class="text-[#13416B]">Kembangkan Lebih Hebat</span>.
            </h1>
            <p class="text-base lg:text-lg text-slate-600 mb-5 lg:mb-8 leading-relaxed max-w-lg">
                Platform terpadu untuk manajemen Rencana Tenaga Kerja, dilengkapi
                fasilitas pelatihan sebagai sarana transfer pengetahuan yang berkelanjutan dari pusat ke
                daerah.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 mb-6 lg:mb-10">
                @guest
                    @unless (session('logged_out'))
                        <a href="{{ config('services.siapkerja.register_url') }}"
                            class="inline-flex justify-center items-center px-8 py-3.5 rounded-full text-white font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                            style="background-color: #13416B;">
                            Daftar Sekarang
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex justify-center items-center px-8 py-3.5 rounded-full text-white font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                            style="background-color: #13416B;">
                            Masuk
                        </a>
                    @endunless
                @else
                    <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                        class="inline-flex justify-center items-center px-8 py-3.5 rounded-full text-white font-bold shadow-md hover:shadow-lg transition-all"
                        style="background-color: #13416B;">
                        Masuk
                    </a>
                @endguest
                <a href="#features"
                    class="inline-flex justify-center items-center px-8 py-3.5 rounded-full border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition-colors">
                    Pelajari Fitur
                </a>
            </div>

            <!-- Integrasi SIAPKerja -->
            <div class="pt-5 sm:pt-6 border-t border-slate-200/80 flex items-center gap-3">
                <img src="{{ asset('images/siapkerja.webp') }}" alt="SIAPKerja" class="h-7 sm:h-8 w-auto shrink-0">
                <div class="leading-tight">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Single Sign-On</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-800">Terintegrasi SIAPKerja</span>
                </div>
            </div>
        </div>

        <!-- Kanan: Floating Cards & Orang -->
        <div class="relative hidden h-[340px] sm:block sm:h-[370px] md:h-[400px] lg:h-[520px] reveal-right" style="transition-delay: 0.2s;">
            <!-- Lingkaran Garis Putar Dasar -->
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[260px] h-[260px] sm:w-[305px] sm:h-[305px] lg:w-[405px] lg:h-[405px] rounded-full border border-slate-200/60 animate-[spin_60s_linear_infinite] z-0">
            </div>

            <!-- BULAT ABSTRAK (KUNING STATIC) -->
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-[45%] w-[240px] h-[200px] sm:w-[275px] sm:h-[230px] lg:w-[360px] lg:h-[305px] bg-gradient-to-tr from-amber-400 to-amber-300 z-0 opacity-90 overflow-hidden"
                style="border-radius: 60% 40% 30% 70% / 60% 40% 30% 40%;">
            </div>

            <!-- ILUSTRASI ORANG DI TENGAH (z-20) -->
            <div
                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 z-20 w-full max-w-[290px] pointer-events-none drop-shadow-2xl sm:max-w-[315px] md:max-w-[340px] lg:max-w-[430px]">
                <div class="relative">
                    <img src="{{ asset('images/Header.webp') }}" alt="Ilustrasi Perencana"
                        class="relative z-10 w-full h-auto object-contain"
                        style="-webkit-mask-image: linear-gradient(to bottom, black 78%, transparent 100%); mask-image: linear-gradient(to bottom, black 78%, transparent 100%);">
                    <div
                        class="pointer-events-none absolute inset-x-[-8%] bottom-0 h-24 bg-gradient-to-t from-slate-50/90 via-slate-50/45 to-transparent blur-md">
                    </div>
                </div>
            </div>

            <!-- DEKORASI TITIK-TITIK MATRIX (SVG GRID PATTERN) -->
            <div class="hidden lg:block absolute bottom-24 right-4 z-10 w-10 h-24 pointer-events-none opacity-40">
                <svg width="100%" height="100%">
                    <pattern id="dot-grid" x="0" y="0" width="12" height="12" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.5" class="fill-yellow-600" />
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#dot-grid)" />
                </svg>
            </div>

            <!-- CARD 2: Kiri Atas - IPK (Di Atas, Ditumpuk di Belakang Makro, Terpotong, Digeser / Tidak Sejajar) -->
            <div
                class="hidden sm:block absolute bottom-32 sm:bottom-32 lg:bottom-36 -left-4 sm:-left-7 lg:-left-11 w-[195px] sm:w-[215px] bg-white rounded-xl shadow-md border border-slate-100 z-10 overflow-hidden animate-card-float-2">
                <div class="h-14 sm:h-16 bg-[#507A9E] flex items-center justify-center relative">
                    <h2 class="text-2xl sm:text-3xl font-medium text-white/95 leading-none"
                        style="font-family: Arial, sans-serif; letter-spacing: -1px;">
                        IK
                    </h2>
                </div>
                <div class="p-2.5 sm:p-3">
                    <h3 class="font-bold text-slate-800 text-xs mb-1">Indeks Pembangunan Ketenagakerjaan</h3>
                    <p class="text-[9px] text-slate-500 line-clamp-2 mb-0">
                        Pengukuran dan evaluasi 7 indikator utama ketenagakerjaan daerah.
                    </p>
                </div>
            </div>

            <!-- CARD 1: Kiri Bawah - Makro (Di Depan Laptop, Sejajar dengan Card Mikro di Kanan) -->
            <div
                class="hidden sm:block absolute bottom-6 -left-2 sm:-left-4 lg:-left-6 w-[210px] sm:w-[230px] bg-white rounded-xl shadow-xl border border-slate-100 z-30 overflow-hidden animate-card-float-1">
                <div class="h-16 sm:h-18 bg-[#81A9CA] flex items-center justify-center relative">
                    <h2 class="text-3xl sm:text-4xl font-medium text-white/95 leading-none"
                        style="font-family: Arial, sans-serif; letter-spacing: -2px;">
                        PM
                    </h2>
                </div>
                <div class="p-3 sm:p-4">
                    <h3 class="font-bold text-slate-800 text-xs mb-1 sm:mb-1.5">Perencanaan Tenaga Kerja Makro</h3>
                    <p class="text-[9px] text-slate-500 mb-0 line-clamp-2">
                        Penyusunan Rencana Tenaga Kerja dengan pendekatan makro ekonomi dan ketenagakerjaan.
                    </p>
                </div>
            </div>

            <!-- CARD 3: Kanan Atas - Mikro (Dibelakang Ilustrasi Orang) -->
            <div
                class="hidden sm:block absolute top-14 sm:top-20 lg:top-32 -right-2 sm:-right-4 lg:-right-6 w-[210px] sm:w-[230px] bg-white rounded-xl shadow-lg border border-slate-100 z-10 overflow-hidden animate-card-float-3">
                <div class="h-16 sm:h-18 bg-[#103F6E] flex items-center justify-center relative">
                    <h2 class="text-3xl sm:text-4xl font-medium text-white/95 leading-none"
                        style="font-family: Arial, sans-serif; letter-spacing: -1px;">
                        PM
                    </h2>
                </div>
                <div class="p-3 sm:p-4">
                    <h3 class="font-bold text-slate-800 text-xs mb-1 sm:mb-1.5">Perencanaan Tenaga Kerja Mikro</h3>
                    <p class="text-[9px] text-slate-500 line-clamp-2 mb-0">
                        Analisis kebutuhan tenaga kerja di tingkat instansi atau perusahaan secara terperinci.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
