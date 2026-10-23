<section class="pt-32 pb-16 sm:pt-28 sm:pb-12 md:pt-28 md:pb-12 lg:pt-32 lg:pb-16 lg:min-h-screen flex items-center relative overflow-hidden bg-slate-50/50" id="home">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full grid grid-cols-1 sm:grid-cols-[1fr_1fr] md:grid-cols-2 lg:grid-cols-2 gap-8 sm:gap-4 md:gap-6 lg:gap-16 items-center relative z-10">

        <!-- Kiri: Teks & CTA -->
        <div class="reveal-left min-w-0">
            <!-- Title dengan Oswald -->
            <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-4 lg:mb-6"
                style="font-family: 'Oswald', sans-serif;">
                Rencanakan Lebih Tepat, <span class="text-[#13416B]">Kembangkan Lebih Hebat</span>.
            </h1>
            <p class="text-base lg:text-xl text-slate-600 mb-5 lg:mb-8 leading-relaxed max-w-lg">
                Platform terpadu untuk manajemen Rencana Tenaga Kerja, dilengkapi
                fasilitas pelatihan yang berkelanjutan dari pusat ke daerah.
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

        <!-- Kanan: Floating Cards & Orang (Dinaikkan sedikit lg:translate-y-3) -->
        <div class="relative hidden h-[360px] sm:block sm:h-[420px] md:h-[480px] lg:h-[560px] translate-y-0 sm:translate-y-0 lg:translate-y-3 reveal-right" style="transition-delay: 0.2s;">
            <!-- Lingkaran Garis Putar Dasar -->
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[240px] h-[240px] sm:w-[280px] sm:h-[280px] md:w-[330px] md:h-[330px] lg:w-[405px] lg:h-[405px] rounded-full border border-slate-200/60 animate-[spin_60s_linear_infinite] z-0">
            </div>

            <!-- BULAT ABSTRAK (KUNING STATIC) -->
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-[45%] w-[220px] h-[180px] sm:w-[250px] sm:h-[210px] md:w-[295px] md:h-[250px] lg:w-[360px] lg:h-[305px] bg-gradient-to-tr from-amber-400 to-amber-300 z-0 opacity-90 overflow-hidden"
                style="border-radius: 60% 40% 30% 70% / 60% 40% 30% 40%;">
            </div>

            <!-- ILUSTRASI ORANG DI TENGAH (z-20) -->
            <div
                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 z-20 w-full max-w-[270px] pointer-events-none drop-shadow-2xl sm:max-w-[300px] md:max-w-[350px] lg:max-w-[430px]">
                <div class="relative">
                    <img src="{{ asset('images/Header.webp') }}" alt="Ilustrasi Perencana"
                        class="relative z-10 w-full h-auto object-contain"
                        style="-webkit-mask-image: linear-gradient(to bottom, black 86%, transparent 100%); mask-image: linear-gradient(to bottom, black 86%, transparent 100%);">
                    <div
                        class="pointer-events-none absolute inset-x-[-8%] bottom-0 h-16 bg-gradient-to-t from-slate-50/90 via-slate-50/45 to-transparent blur-md">
                    </div>
                </div>
            </div>

            <!-- DEKORASI TITIK-TITIK MATRIX (SVG GRID PATTERN - Diturunkan) -->
            <div class="hidden lg:block absolute bottom-8 right-4 z-10 w-10 h-24 pointer-events-none opacity-40">
                <svg width="100%" height="100%">
                    <pattern id="dot-grid" x="0" y="0" width="12" height="12" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.5" class="fill-yellow-600" />
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#dot-grid)" />
                </svg>
            </div>

            <!-- CHAT BUBBLE 2: Kiri Atas - Indeks IPK -->
            <div
                class="hidden lg:flex items-start gap-3 absolute bottom-32 sm:bottom-36 lg:bottom-42 -left-3 sm:-left-5 lg:-left-10 w-[230px] sm:w-[245px] lg:w-[260px] bg-white/95 backdrop-blur-md border border-slate-200/80 shadow-lg shadow-slate-900/5 p-3 sm:p-3.5 z-20 transition-all duration-300 hover:scale-105 hover:shadow-xl"
                style="border-radius: 16px 16px 2px 16px;">
                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs shrink-0 shadow-sm mt-0.5">
                    <i class="fas fa-gauge-high text-[12px]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-xs sm:text-[13px] font-bold text-slate-800 truncate">Indeks IPK</span>
                        <span class="text-[9.5px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60 leading-none">Evaluasi</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-snug mb-0">
                        Pengukuran capaian pembangunan ketenagakerjaan daerah.
                    </p>
                </div>
            </div>

            <!-- CHAT BUBBLE 1: Kiri Bawah - RTK Makro -->
            <div
                class="hidden lg:flex items-start gap-3 absolute bottom-5 sm:bottom-7 lg:bottom-9 -left-2 sm:-left-3 lg:-left-6 w-[235px] sm:w-[250px] lg:w-[265px] bg-white/95 backdrop-blur-md border border-slate-200/80 shadow-xl shadow-slate-900/10 p-3 sm:p-3.5 z-30 transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                style="border-radius: 16px 16px 2px 16px;">
                <div class="w-8 h-8 rounded-full bg-[#13416B] text-white flex items-center justify-center text-xs shrink-0 shadow-sm mt-0.5">
                    <i class="fas fa-arrow-trend-up text-[12px]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-xs sm:text-[13px] font-bold text-slate-800 truncate">RTK Makro</span>
                        <span class="text-[9.5px] font-bold text-[#13416B] bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/60 leading-none">Pusat &amp; Daerah</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-snug mb-0">
                        Penyusunan proyeksi kebutuhan naker berbasis ekonomi makro.
                    </p>
                </div>
            </div>

            <!-- CHAT BUBBLE 3: Kanan - RTK Mikro (Digeser ke kiri sejajar dengan dekorasi titik-titik right-4) -->
            <div
                class="hidden lg:flex items-start gap-3 absolute bottom-32 sm:bottom-36 md:bottom-40 lg:bottom-44 right-2 sm:right-3 lg:right-4 w-[230px] sm:w-[245px] lg:w-[260px] bg-white/95 backdrop-blur-md border border-slate-200/80 shadow-lg shadow-slate-900/5 p-3 sm:p-3.5 z-20 transition-all duration-300 hover:scale-105 hover:shadow-xl"
                style="border-radius: 16px 16px 16px 2px;">
                <div class="w-8 h-8 rounded-full bg-[#507A9E] text-white flex items-center justify-center text-xs shrink-0 shadow-sm mt-0.5">
                    <i class="fas fa-users-gear text-[12px]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-xs sm:text-[13px] font-bold text-slate-800 truncate">RTK Mikro</span>
                        <span class="text-[9.5px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200/60 leading-none">Instansi</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-snug mb-0">
                        Perencanaan formasi dan analisis kebutuhan naker perusahaan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
