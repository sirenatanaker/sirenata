<x-landingpage::layouts.master title="SIRENATA - Sistem Informasi Perencanaan Ketenagakerjaan">

    {{-- CSS Kustom untuk Animasi, Custom Scrollbar & Font Kalam + Oswald --}}
    @push('styles')
        <style>
            /* Import Font Kalam & Oswald dari Google Fonts */
            @import url('https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&family=Oswald:wght@400;600;700&display=swap');

            .font-kalam {
                font-family: 'Kalam', cursive;
            }

            .section-title {
                font-family: 'Oswald', sans-serif;
                font-size: 1.5rem;
                font-weight: 800;
                line-height: 1.2;
            }

            @media (min-width: 640px) {
                .section-title {
                    font-size: 1.875rem;
                }
            }

            @media (min-width: 1024px) {
                .section-title {
                    font-size: 3rem;
                }

                .section-title-stat {
                    font-size: 1.875rem;
                }

                .section-title-cta {
                    font-size: 3.75rem;
                }
            }

            html {
                scroll-behavior: smooth;
            }

            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            /* Custom Scrollbar untuk area Kursus */
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }

            /* Floating Animasi */
            @keyframes float {

                0%,
                100% {
                    transform: translateY(0) scale(1);
                }

                50% {
                    transform: translateY(-15px) scale(1.01);
                }
            }

            @keyframes cardFloat {

                0%,
                100% {
                    transform: translateY(0) rotate(0deg);
                }

                33% {
                    transform: translateY(-8px) rotate(1deg);
                }

                66% {
                    transform: translateY(-4px) rotate(-1deg);
                }
            }

            /* ANIMASI BULAT ABSTRAK (MORPHING BLOB) */
            @keyframes morphBlob {

                0%,
                100% {
                    border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
                }

                50% {
                    border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
                }
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            .animate-card-float-1 {
                animation: cardFloat 5s ease-in-out infinite;
            }

            .animate-card-float-2 {
                animation: cardFloat 7s ease-in-out infinite 0.5s;
            }

            .animate-card-float-3 {
                animation: cardFloat 6s ease-in-out infinite 1.5s;
            }

            .course-scroll-container {
                cursor: grab;
                overscroll-behavior: contain;
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }

            .course-scroll-container.is-dragging {
                cursor: grabbing;
                user-select: none;
            }

            .animate-blob {
                animation: morphBlob 8s ease-in-out infinite;
            }

            /* --- KELAS ANIMASI REVEAL DARI SAMPING / BAWAH --- */
            .reveal-left {
                opacity: 0;
                transform: translateX(-50px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-right {
                opacity: 0;
                transform: translateX(50px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-up {
                opacity: 0;
                transform: translateY(40px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-left.active,
            .reveal-right.active,
            .reveal-up.active {
                opacity: 1;
                transform: translate(0, 0);
            }
        </style>
    @endpush

    <!-- Noise Overlay (Sangat Lembut) -->
    <div class="fixed inset-0 pointer-events-none z-[9999] opacity-[0.25] mix-blend-overlay"
        style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E&quot;);">
    </div>

    <!-- ========================================== -->
    <!-- NAVBAR (Sticky & Blur)                     -->
    <!-- ========================================== -->
    <nav x-data="{ isScrolled: false, mobileMenuOpen: false }" @scroll.window="isScrolled = (window.pageYOffset > 20)"
        :class="isScrolled ? 'bg-white/95 shadow-sm backdrop-blur-md' : 'bg-white/80 backdrop-blur-sm'"
        class="fixed top-0 left-0 right-0 z-[100] transition-all duration-300 border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

            <a href="{{ route('landingpage.index') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="SIRENATA Logo" class="h-8 sm:h-10 w-auto">
                <span class="text-lg sm:text-xl font-extrabold tracking-tight" style="color: #13416B;">SIRENATA</span>
            </a>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center gap-2">
                <a href="#features"
                    class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">Fitur</a>
                <a href="#courses"
                    class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">LMS</a>
                <a href="#faq"
                    class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">FAQ</a>
                <a href="#cta"
                    class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">CTA</a>
            </div>

            <!-- Auth Buttons (Normal Landing Page) -->
            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}"
                        class="px-5 py-2 font-bold text-slate-700 hover:bg-slate-100 rounded-full transition-colors">Masuk</a>
                    <a href="{{ route('login') }}"
                        class="px-6 py-2.5 font-bold text-white rounded-full shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                        style="background-color: #13416B;">Daftar Gratis</a>
                @else
                    <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                        class="px-6 py-2.5 font-bold text-white rounded-full shadow-md hover:shadow-lg transition-all"
                        style="background-color: #13416B;">Dashboard</a>
                @endguest
            </div>

            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 focus:outline-none">
                <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                <i class="fas fa-times text-xl" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-collapse
            class="md:hidden bg-white border-t border-slate-100 shadow-xl absolute w-full">
            <div class="p-4 space-y-2">

                <a href="#features" @click="mobileMenuOpen = false"
                    class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">Fitur</a>
                <a href="#courses" @click="mobileMenuOpen = false"
                    class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">LMS</a>
                <a href="#faq" @click="mobileMenuOpen = false"
                    class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">FAQ</a>
                <a href="#cta" @click="mobileMenuOpen = false"
                    class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">CTA</a>
                @guest
                    <div class="pt-4 mt-2 border-t border-slate-100 flex gap-3">
                        <a href="{{ route('login') }}"
                            class="flex-1 text-center py-3 rounded-xl font-bold bg-slate-100 text-slate-700">Masuk</a>
                        <a href="{{ route('login') }}" class="flex-1 text-center py-3 rounded-xl font-bold text-white"
                            style="background-color: #13416B;">Daftar</a>
                    </div>
                @else
                    <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                        class="block border-t border-slate-100 pt-4 mt-2 text-center py-3 rounded-xl font-bold text-white"
                        style="background-color: #13416B;">Buka Dashboard</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- HERO SECTION (Split Layout & Kalem)        -->
    <!-- ========================================== -->
    <section class="pt-28 pb-12 md:pt-28 md:pb-12 lg:min-h-screen lg:pt-28 lg:pb-16 flex items-center relative overflow-hidden bg-slate-50/50" id="home">


        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full grid grid-cols-1 sm:grid-cols-[1.1fr_0.9fr] lg:grid-cols-2 gap-8 sm:gap-6 lg:gap-16 items-center relative z-10">

            <!-- Kiri: Teks & CTA -->
            <div class="reveal-left min-w-0">

                <!-- Title dengan Oswald -->
                <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-4 lg:mb-6"
                    style="font-family: 'Oswald', sans-serif;">
                    Masa Depan <span class="text-[#13416B]">Ketenagakerjaan</span> Dimulai di Sini.
                </h1>
                <p class="text-base lg:text-lg text-slate-600 mb-5 lg:mb-8 leading-relaxed max-w-lg">
                    Platform terpadu untuk manajemen Rencana Tenaga Kerja (Makro & Mikro) dan evaluasi IPK, dilengkapi
                    fasilitas e-learning interaktif sebagai sarana transfer pengetahuan yang berkelanjutan dari pusat ke
                    daerah.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 mb-6 lg:mb-10">
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex justify-center items-center px-8 py-3.5 rounded-full text-white font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                            style="background-color: #13416B;">
                            Daftar Sekarang
                        </a>
                    @else
                        <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                            class="inline-flex justify-center items-center px-8 py-3.5 rounded-full text-white font-bold shadow-md hover:shadow-lg transition-all"
                            style="background-color: #13416B;">
                            Buka Dashboard
                        </a>
                    @endguest
                    <a href="#features"
                        class="inline-flex justify-center items-center px-8 py-3.5 rounded-full border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition-colors">
                        Pelajari Fitur
                    </a>
                </div>

                <!-- Avatar Social Proof -->
                <div class="flex items-center gap-4">
                    <div class="flex -space-x-3">
                        <img src="https://ui-avatars.com/api/?name=JD&background=random"
                            class="w-10 h-10 rounded-full border-2 border-white shadow-sm" alt="User">
                        <img src="https://ui-avatars.com/api/?name=FW&background=random"
                            class="w-10 h-10 rounded-full border-2 border-white shadow-sm" alt="User">
                        <img src="https://ui-avatars.com/api/?name=RM&background=random"
                            class="w-10 h-10 rounded-full border-2 border-white shadow-sm" alt="User">
                        <div
                            class="w-10 h-10 rounded-full border-2 border-white shadow-sm bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-600">
                            +1.2K</div>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Bergabung dengan pengguna lainnya.</p>
                </div>
            </div>

            <!-- Kanan: Floating Cards & Orang -->
            <div class="relative hidden h-[380px] sm:block sm:h-[460px] lg:h-[550px] reveal-right" style="transition-delay: 0.2s;">
                <!-- Lingkaran Garis Putar Dasar -->
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[250px] h-[250px] sm:w-[290px] sm:h-[290px] lg:w-[420px] lg:h-[420px] rounded-full border border-slate-200/60 animate-[spin_60s_linear_infinite] z-0">
                </div>

                <!-- BULAT ABSTRAK (KUNING STATIC) + GARIS GELOMBANG SESUAI GAMBAR -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-[45%] w-[230px] h-[190px] sm:w-[260px] sm:h-[220px] lg:w-[380px] lg:h-[320px] bg-gradient-to-tr from-amber-400 to-amber-300 z-0 opacity-90 overflow-hidden"
                    style="border-radius: 60% 40% 30% 70% / 60% 40% 30% 40%;">

                    <!-- Gelombang Garis / Pita Organik (Soft Overlay) -->
                    {{-- <svg class="absolute inset-0 w-full h-full opacity-30 pointer-events-none" viewBox="0 0 400 350"
                        fill="none" preserveAspectRatio="none">
                        <path d="M-50 80 C 80 180, 250 110, 450 140 L 450 200 C 250 170, 80 240, -50 140 Z"
                            fill="white" />
                        <path d="M-50 160 C 80 260, 250 190, 450 220 L 450 280 C 250 250, 80 320, -50 220 Z"
                            fill="white" />
                        <path d="M-50 0 C 80 100, 250 30, 450 60 L 450 110 C 250 80, 80 150, -50 50 Z" fill="white" />
                    </svg> --}}
                </div>

                <!-- ILUSTRASI ORANG DI TENGAH (z-20) -->
                <div
                    class="absolute bottom-0 left-1/2 transform -translate-x-1/2 z-20 w-full max-w-[420px] pointer-events-none drop-shadow-2xl">
                    <div class="relative">
                        <img src="{{ asset('images/Header.webp') }}" alt="Ilustrasi Perencana"
                            class="relative z-10 w-full h-auto object-contain"
                            style="-webkit-mask-image: linear-gradient(to bottom, black 78%, transparent 100%); mask-image: linear-gradient(to bottom, black 78%, transparent 100%);">
                        <div
                            class="pointer-events-none absolute inset-x-[-8%] bottom-0 h-24 bg-gradient-to-t from-slate-50/90 via-slate-50/45 to-transparent blur-md">
                        </div>
                    </div>
                </div>

                <!-- DEKORASI TITIK-TITIK MATRIX PROFESIONAL (SVG GRID PATTERN) -->
                <div class="hidden lg:block absolute bottom-24 right-4 z-10 w-10 h-24 pointer-events-none opacity-40">
                    <svg width="100%" height="100%">
                        <pattern id="dot-grid" x="0" y="0" width="12" height="12"
                            patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1.5" class="fill-yellow-600" />
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#dot-grid)" />
                    </svg>
                </div>

                <!-- CARD 1: Kanan Atas (Warna 1: Dark Navy Blue - #103F6E) -->
                <div
                    class="hidden lg:block absolute top-8 -right-4 w-[260px] bg-white rounded-xl shadow-lg border border-slate-100 animate-card-float-1 z-10 overflow-hidden">
                    <div class="h-24 bg-[#81A9CA] flex items-center justify-center relative">

                        <h2 class="text-[54px] font-medium text-white/95 leading-none"
                            style="font-family: Arial, sans-serif; letter-spacing: -2px;">
                            PM
                        </h2>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-slate-800 text-sm mb-1.5">Perencanaan Tenaga Kerja Makro</h3>
                        <p class="text-[10px] text-slate-500 mb-0 line-clamp-2">
                            Penyusunan Rencana Tenaga Kerja dengan pendekatan makro ekonomi dan ketenagakerjaan.
                        </p>
                    </div>
                </div>

                <!-- CARD 3: Kiri Atas (Warna 2: Medium Slate Blue - #507A9E) -->
                <div
                    class="hidden lg:block absolute top-40 -left-14 w-[240px] bg-white rounded-xl shadow-md border border-slate-100 animate-card-float-3 z-10 overflow-hidden">
                    <div class="h-16 bg-[#507A9E] flex items-center justify-center relative">

                        <h2 class="text-3xl font-medium text-white/95 leading-none"
                            style="font-family: Arial, sans-serif; letter-spacing: -1px;">
                            IK
                        </h2>
                    </div>
                    <div class="p-3">
                        <h3 class="font-bold text-slate-800 text-xs mb-1">Indeks Pembangunan Ketenagakerjaan</h3>
                        <p class="text-[9px] text-slate-500 line-clamp-2 mb-0">
                            Pengukuran dan evaluasi 7 indikator utama ketenagakerjaan daerah.
                        </p>
                    </div>
                </div>

                <!-- CARD 2: Kiri Bawah (Warna 3: Soft Blue - #81A9CA) -->
                <div
                    class="hidden lg:block absolute bottom-12 -left-10 w-[250px] bg-white rounded-xl shadow-xl border border-slate-100 animate-card-float-2 z-30 overflow-hidden">
                    <div class="h-20 bg-[#103F6E] flex items-center justify-center relative">

                        <h2 class="text-4xl font-medium text-white/95 leading-none"
                            style="font-family: Arial, sans-serif; letter-spacing: -1px;">
                            PM
                        </h2>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-slate-800 text-xs mb-1.5">Perencanaan Tenaga Kerja Mikro</h3>
                        <p class="text-[9px] text-slate-500 line-clamp-2 mb-0">
                            Analisis kebutuhan tenaga kerja di tingkat instansi atau perusahaan secara terperinci.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- STATS BANNER (Variasi Dot Besar & Kecil)   -->
    <!-- ========================================== -->
    <!-- ========================================== -->
    <!-- STATS BANNER                               -->
    <!-- ========================================== -->
    @php
        $parseStat = function ($val, $defaultSuffix = '') {
            $val = (string) $val;
            $num = floatval(preg_replace('/[^0-9.]/', '', $val));
            $suf = preg_replace('/[0-9.]/', '', $val);
            if (strpos($val, '+') !== false && strpos($suf, '+') === false) {
                $suf .= '+';
            }
            return ['num' => $num ? $num : 0, 'suf' => $suf ?: $defaultSuffix];
        };
        $sProv = $parseStat($stats['provinces'] ?? 38);
        $sReg = $parseStat($stats['regencies'] ?? 514);
        $sRtk = $parseStat($stats['rtk'] ?? '1.2K', '+');
        $sCourse = $parseStat($stats['courses'] ?? 15);

        // ---- Dekorasi halftone 1/4 elips (pusat di pojok kanan bawah, melebar ke kiri) ----
        $W = 760; // lebar kotak SVG (px) -> makin besar makin melebar ke kiri
        $H = 420; // tinggi kotak SVG (px)
        $step = 22; // jarak antar dot
        $rowH = $step * 0.866; // grid heksagonal (staggered)
        $Rx = $W - 10; // radius horizontal
        $Ry = $H - 10; // radius vertikal
        $dots = [];
        $rows = (int) ceil($H / $rowH);

        for ($j = 0; $j < $rows; $j++) {
            $y = $H - $step / 2 - $j * $rowH;
            $shift = $j % 2 ? $step / 2 : 0;
            for ($i = 0; $i <= (int) ceil($W / $step); $i++) {
                $x = $W - $step / 2 - $shift - $i * $step;
                if ($x < 0 || $y < 0) {
                    continue;
                }
                $t = sqrt((($W - $x) / $Rx) ** 2 + (($H - $y) / $Ry) ** 2);
                if ($t > 1) {
                    continue;
                }
                $r = round(0.6 + 3.8 * pow(1 - $t, 1.2), 2); // besar di pojok, kecil di ujung
                $o = round(pow(1 - $t, 1.3), 2); // makin redup ke ujung
                if ($o < 0.04) {
                    continue;
                }
                $dots[] = ['x' => round($x, 2), 'y' => round($y, 2), 'r' => $r, 'o' => $o];
            }
        }
    @endphp

    <section class="py-12 sm:py-18 lg:py-16 bg-slate-900 relative overflow-hidden">

        <!-- Dekorasi Halftone 1/4 Elips -->
        <div class="absolute bottom-0 right-0 w-[380px] h-[210px] sm:w-[760px] sm:h-[420px] pointer-events-none opacity-40"
            aria-hidden="true">
            <svg class="w-full h-full" viewBox="0 0 {{ $W }} {{ $H }}" fill="#ffffff"
                xmlns="http://www.w3.org/2000/svg">
                @foreach ($dots as $dot)
                    <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['r'] }}"
                        fill-opacity="{{ $dot['o'] }}" />
                @endforeach
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal-up">
            <!-- Header Kecil Section -->
            <div class="mb-10 text-left">
                <span class="text-xs font-semibold tracking-widest text-slate-400 uppercase block mb-1">Statistik
                </span>
                <h3 class="section-title section-title-stat text-white tracking-tight">
                    SIRENATA dalam Angka
                </h3>
            </div>

            <!-- Grid Statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Item 1 -->
                <div
                    class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                    <p class="text-xs font-semibold text-slate-400 mb-2">Jangkauan Wilayah</p>
                    <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                        data-target="{{ $sProv['num'] }}" data-suffix="{{ $sProv['suf'] }}"
                        style="font-family: 'Oswald', sans-serif;">0</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Provinsi yang telah terintegrasi dalam sistem
                        perencanaan.</p>
                </div>

                <!-- Item 2 -->
                <div
                    class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                    <p class="text-xs font-semibold text-slate-400 mb-2">Pemerintah Daerah</p>
                    <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                        data-target="{{ $sReg['num'] }}" data-suffix="{{ $sReg['suf'] }}"
                        style="font-family: 'Oswald', sans-serif;">0</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Kabupaten/Kota yang mengunggah dan menyusun
                        dokumen RTK.</p>
                </div>

                <!-- Item 3 -->
                <div
                    class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                    <p class="text-xs font-semibold text-slate-400 mb-2">Dokumen Terarsip</p>
                    <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                        data-target="{{ $sRtk['num'] }}" data-suffix="{{ $sRtk['suf'] }}"
                        style="font-family: 'Oswald', sans-serif;">0</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Total Rencana Tenaga Kerja yang
                        telah diverifikasi.</p>
                </div>

                <!-- Item 4 -->
                <div
                    class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                    <p class="text-xs font-semibold text-slate-400 mb-2">Peningkatan Kompetensi</p>
                    <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                        data-target="{{ $sCourse['num'] }}" data-suffix="{{ $sCourse['suf'] }}"
                        style="font-family: 'Oswald', sans-serif;">0</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Pelatihan aktif di e-learning LMS untuk aparatur
                        daerah.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- FITUR UTAMA                                -->
    <!-- ========================================== -->
    <section id="features" class="py-12 sm:py-18 lg:py-16 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 sm:gap-6 lg:gap-24 items-center">

                <!-- Kiri: Teks & Penjelasan -->
                <div class="lg:sticky lg:top-24 flex flex-col justify-center reveal-left">
                    <!-- Teks dengan Font Kalam & Tanpa Background Badge -->
                    <div class="flex items-center gap-4 mb-2">
                        {{-- <span class="text-[#13416B] font-kalam font-bold text-md lg:text-xl">
                            Fitur Unggulan
                        </span>
                        <div class="h-[2px] w-40 bg-[#13416B]/30"></div> --}}
                    </div>

                    <!-- FONT OSWALD UNTUK TITLE -->
                    <h2 class="section-title text-slate-900 mb-4 lg:mb-6">
                        Solusi Terpadu <span class="text-[#13416B]">Perencanaan Ketenagakerjaan</span>
                    </h2>
                    <p class="text-slate-600 text-base lg:text-lg leading-relaxed mb-5 lg:mb-8">
                        Aplikasi yang mendigitalkan pengumpulan data, perhitungan rencana tenaga kerja, dan pemantauan
                        capaian kinerja daerah secara terukur dan konsisten.
                    </p>

                    <ul class="space-y-3 lg:space-y-4 mb-2 text-sm lg:text-base">
                        <li class="flex items-start gap-3 text-slate-700 font-medium">
                            <i class="fas fa-check-circle text-emerald-500 opacity-80 mt-1"></i> Perhitungan Rencana
                            Tenaga Kerja (Makro & Mikro) secara otomatis dan akurat.
                        </li>
                        <li class="flex items-start gap-3 text-slate-700 font-medium">
                            <i class="fas fa-check-circle text-emerald-500 opacity-80 mt-1"></i> Fasilitas pelatihan
                            mandiri
                            berbasis LMS untuk aparatur daerah.
                        </li>
                        <li class="flex items-start gap-3 text-slate-700 font-medium">
                            <i class="fas fa-check-circle text-emerald-500 opacity-80 mt-1"></i> Pemantauan dokumen
                            RTKD
                            dan pelaporan
                            yang terintegrasi secara aman.
                        </li>
                    </ul>
                </div>

                <div class="reveal-right w-full h-full flex flex-col justify-center">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">

                        <!-- Card 1: Penghitungan RTK (Full Width di atas) -->
                        <div
                            class="md:col-span-2 bg-[#13416B] rounded-xl p-4 md:p-6 lg:p-8 relative overflow-hidden group hover:shadow-xl transition-all duration-500">
                            <!-- Decorative Glow -->
                            <div
                                class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 transition-transform duration-700 group-hover:scale-150">
                            </div>

                            <div
                                class="relative z-10 flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-5 lg:gap-6">
                                <div
                                    class="w-14 h-14 md:w-12 md:h-12 lg:w-16 lg:h-16 rounded-2xl bg-white/10 text-white flex items-center justify-center shrink-0 backdrop-blur-md border border-white/20 shadow-inner">
                                    <i class="fas fa-calculator text-2xl lg:text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-base md:text-sm lg:text-xl font-bold text-white mb-2 tracking-tight">
                                        Penghitungan RTK</h3>
                                    <p class="text-white/80 leading-relaxed text-sm md:text-xs lg:text-base">
                                        Alat bantu dalam perhitungan rencana tenaga kerja makro dan mikro sesuai kondisi
                                        daerah secara cepat dan presisi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: LMS Terintegrasi (Sejajar di kiri bawah, tanpa badge & dekorasi) -->
                        <div
                            class="bg-slate-50 rounded-xl p-5 md:p-3 lg:p-6 border border-slate-200 hover:border-[#13416B]/40 hover:shadow-lg hover:bg-white transition-all duration-300 group flex flex-col justify-between">
                            <div>
                                <div
                                    class="w-11 h-11 md:w-9 md:h-9 lg:w-12 lg:h-12 rounded-xl bg-white text-[#13416B] flex items-center justify-center shadow-sm border border-slate-200 mb-3 lg:mb-4 group-hover:bg-[#13416B] group-hover:text-white transition-colors duration-300">
                                    <i class="fas fa-graduation-cap text-lg lg:text-xl"></i>
                                </div>
                                <h3 class="text-sm md:text-xs lg:text-base font-bold text-slate-800 mb-1.5">LMS Terintegrasi</h3>
                                <p class="text-slate-600 leading-relaxed text-xs md:text-[10px] lg:text-sm">
                                    Transfer pengetahuan terstruktur melalui modul pelatihan, video pembelajaran, hingga
                                    sertifikasi kelulusan resmi.
                                </p>
                            </div>
                        </div>

                        <!-- Card 3: Pelaporan & Arsip (Sejajar di kanan bawah) -->
                        <div
                            class="bg-slate-50 rounded-xl p-5 md:p-3 lg:p-6 border border-slate-200 hover:border-[#13416B]/40 hover:shadow-lg hover:bg-white transition-all duration-300 group flex flex-col justify-between">
                            <div>
                                <div
                                    class="w-11 h-11 md:w-9 md:h-9 lg:w-12 lg:h-12 rounded-xl bg-white text-[#13416B] flex items-center justify-center shadow-sm border border-slate-200 mb-3 lg:mb-4 group-hover:bg-[#13416B] group-hover:text-white transition-colors duration-300">
                                    <i class="fas fa-file-invoice text-lg lg:text-xl"></i>
                                </div>
                                <h3 class="text-sm md:text-xs lg:text-base font-bold text-slate-800 mb-1.5">Pelaporan & Arsip</h3>
                                <p class="text-slate-600 leading-relaxed text-xs md:text-[10px] lg:text-sm">
                                    Pemantauan dokumen RTKD dan fitur sanggahan nilai dengan integrasi bukti pendukung
                                    yang aman.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- COURSES SECTION (LMS) - MASONRY CATALOG    -->
    <!-- ========================================== -->
    @if (isset($courses) && $courses->count() > 0)
        <section id="courses"
            class="py-12 sm:py-18 lg:py-16 px-4 sm:px-6 lg:px-8 bg-slate-50/60 border-t border-slate-200 relative overflow-hidden">
            <div
                class="mx-auto grid max-w-7xl grid-cols-1 items-start gap-8 sm:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] sm:gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)] lg:gap-16">

                <div class="order-2 min-w-0 sm:order-1">
                    <!-- Grid Kursus Satu Kolom -->
                    <div
                        class="custom-scrollbar h-[520px] max-h-[520px] scroll-smooth overflow-y-auto overscroll-contain pr-2 sm:h-[500px] sm:max-h-[500px] md:h-[620px] md:max-h-[620px] lg:mt-2 lg:h-[780px] lg:max-h-[780px]">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-5">
                            @foreach ($courses as $course)
                                @php
                                    // Penanganan URL Gambar Thumbnail
                                    $thumbUrl = $course->thumbnail
                                        ? (filter_var($course->thumbnail, FILTER_VALIDATE_URL)
                                            ? $course->thumbnail
                                            : Storage::url($course->thumbnail))
                                        : null;

                                    // Generate Inisial Teks untuk Fallback Thumbnail
                                    $initials = '';
                                    if (!$thumbUrl) {
                                        $words = explode(' ', trim($course->name));
                                        foreach (array_slice($words, 0, 2) as $w) {
                                            $initials .= strtoupper(substr($w, 0, 1));
                                        }
                                        if (strlen($initials) < 2) {
                                            $initials = substr(strtoupper($course->name), 0, 2);
                                        }
                                    }

                                    // Perhitungan Modul & Tinggi Thumbnail
                                    $modulesCount =
                                        $course->sections_count ??
                                        (isset($course->sections) ? collect($course->sections)->count() : 0);
                                    $thumbHeight =
                                        $loop->iteration % 3 === 0
                                            ? 'h-52'
                                            : ($loop->iteration % 2 === 0
                                                ? 'h-44'
                                                : 'h-40');
                                @endphp

                                <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                                    class="flex h-full w-full flex-col overflow-hidden rounded-xl border border-slate-200/80 bg-white transition-all duration-300 group hover:-translate-y-1 hover:border-[#13416B]/30 hover:shadow-xl">

                                    <!-- Thumbnail Kursus -->
                                    <div
                                        class="relative h-[140px] bg-[#184A78] flex items-center justify-center overflow-hidden lg:h-[180px]">
                                        @if ($thumbUrl)
                                            <img src="{{ $thumbUrl }}" alt="{{ $course->name }}"
                                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                loading="lazy">
                                        @else
                                            <h2
                                                class="text-3xl md:text-2xl lg:text-4xl font-extrabold text-white/90 leading-none tracking-wider select-none">
                                                {{ $initials }}
                                            </h2>
                                        @endif

                                        <!-- Badge Kategori -->
                                        @if ($course->category)
                                            <span
                                                class="absolute left-3 top-3 z-10 rounded-full border border-white/20 bg-slate-900/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                                                {{ $course->category->name }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Detail Konten -->
                                    <div class="flex min-h-[110px] flex-col justify-center p-3 md:p-2 lg:min-h-[150px] lg:p-5 text-left">
                                        <h3
                                            class="mb-1.5 line-clamp-2 font-bold text-slate-800 text-sm md:text-xs lg:mb-2 lg:text-base group-hover:text-[#13416B] transition-colors">
                                            {{ $course->name }}
                                        </h3>
                                        <p class="line-clamp-3 text-[11px] md:text-[10px] lg:text-xs leading-relaxed text-slate-500">
                                            {{ $course->description ?? "Modul pelatihan komprehensif untuk mendalami materi {$course->name} secara terstruktur." }}
                                        </p>
                                    </div>

                                    <!-- Card Footer -->
                                    <div
                                        class="flex items-center justify-between border-t border-slate-100 px-3 pb-3 pt-2 md:px-2 md:pb-2 lg:px-5 lg:pb-5 lg:pt-3 text-[11px] md:text-[10px] lg:text-xs font-medium text-slate-500">
                                        <span
                                            class="flex items-center gap-1 rounded-md border border-slate-100 bg-slate-50 px-1.5 py-1 lg:gap-1.5 lg:px-2 text-slate-600">
                                            <i class="fas fa-layer-group text-[#13416B]"></i>
                                            {{ $modulesCount }} Modul
                                        </span>

                                    </div>

                                </a>
                            @endforeach
                        </div>
                    </div>

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
                            Ikuti pelatihan daring yang terstruktur untuk memperkuat kompetensi dan memperluas
                            pengetahuan
                            ketenagakerjaan secara mandiri.
                        </p>
                    </div>

                    <div class="relative hidden h-[320px] items-center justify-center sm:flex sm:h-[380px] lg:h-[460px] reveal-right">
                        <div class="absolute left-1/2 top-1/2 h-[190px] w-[190px] -translate-x-1/2 -translate-y-1/2 bg-gradient-to-br from-[#E8F2F8] via-[#D5E8F3] to-amber-100 opacity-90 animate-blob lg:h-[330px] lg:w-[330px]"
                            style="border-radius: 42% 58% 63% 37% / 48% 40% 60% 52%;">
                        </div>

                        <div
                            class="absolute left-1/2 top-1/2 h-[220px] w-[220px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-[#13416B]/15 border-dashed animate-[spin_45s_linear_infinite] lg:h-[370px] lg:w-[370px]">
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

                        <div
                            class="absolute right-3 top-3 z-20 flex items-center gap-1.5 rounded-lg border border-white/80 bg-[#13416B] px-2 py-1.5 text-white shadow-xl animate-card-float-1 md:top-5 md:gap-1 md:px-1.5 md:py-1 lg:right-6 lg:top-12 lg:gap-3 lg:rounded-2xl lg:px-4 lg:py-3">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-400 text-[#13416B] md:h-7 md:w-7 lg:h-10 lg:w-10 lg:rounded-xl">
                                <i class="fas fa-award text-sm lg:text-base"></i>
                            </span>
                            <div>
                                <p class="text-[9px] font-bold uppercase tracking-wider text-white/60 lg:text-[10px]">Kursus selesai
                                </p>
                                <p class="text-[10px] font-bold lg:text-sm">Sertifikat diperoleh</p>
                            </div>
                        </div>

                        <div class="relative z-10 w-full max-w-[440px] animate-float">
                            <img src="{{ asset('images/LMS.webp') }}"
                                alt="Aparatur sedang belajar menggunakan laptop"
                                class="relative z-10 w-full object-contain drop-shadow-xl"
                                style="-webkit-mask-image: linear-gradient(to bottom, black 78%, transparent 100%); mask-image: linear-gradient(to bottom, black 78%, transparent 100%);">
                            <div
                                class="pointer-events-none absolute inset-x-[-8%] bottom-0 h-28 bg-gradient-to-t from-slate-50/90 via-slate-50/45 to-transparent blur-md">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    @endif

    <!-- ========================================== -->
    <!-- LMS STATS BANNER                           -->
    <!-- ========================================== -->
    @if (isset($courses) && $courses->count() > 0)
        @php
            $lmsCourseCount = $courses->count();
            $lmsModuleCount = $courses->sum(
                fn($course) => $course->sections_count ??
                    (isset($course->sections) ? collect($course->sections)->count() : 0),
            );
            $lmsCategoryCount = $courses->pluck('category_id')->filter()->unique()->count();
            $lmsParticipantCount = $stats['participants'] ?? 1200;

            // ---- Dekorasi halftone 1/4 elips (pusat di pojok kanan bawah) ----
            // Variabel diberi prefix "lms" supaya tidak bentrok dengan section Stats Banner di halaman yang sama
            $lmsW = 640;
            $lmsH = 260;
            $lmsStep = 22;
            $lmsRowH = $lmsStep * 0.866;
            $lmsRx = $lmsW - 10;
            $lmsRy = $lmsH - 10;
            $lmsDots = [];
            $lmsRows = (int) ceil($lmsH / $lmsRowH);

            for ($j = 0; $j < $lmsRows; $j++) {
                $y = $lmsH - $lmsStep / 2 - $j * $lmsRowH;
                $shift = $j % 2 ? $lmsStep / 2 : 0;
                for ($i = 0; $i <= (int) ceil($lmsW / $lmsStep); $i++) {
                    $x = $lmsW - $lmsStep / 2 - $shift - $i * $lmsStep;
                    if ($x < 0 || $y < 0) {
                        continue;
                    }
                    $t = sqrt((($lmsW - $x) / $lmsRx) ** 2 + (($lmsH - $y) / $lmsRy) ** 2);
                    if ($t > 1) {
                        continue;
                    }
                    $r = round(0.6 + 3.8 * pow(1 - $t, 1.2), 2);
                    $o = round(pow(1 - $t, 1.3), 2);
                    if ($o < 0.04) {
                        continue;
                    }
                    $lmsDots[] = ['x' => round($x, 2), 'y' => round($y, 2), 'r' => $r, 'o' => $o];
                }
            }
        @endphp

        <section class="relative overflow-hidden border-t border-slate-800 bg-slate-900 py-12 sm:py-18 lg:py-16">

            <!-- Dekorasi Halftone 1/4 Elips -->
            <div class="pointer-events-none absolute bottom-0 right-0 h-[130px] w-[320px] opacity-40 sm:h-[260px] sm:w-[640px]"
                aria-hidden="true">
                <svg class="h-full w-full" viewBox="0 0 {{ $lmsW }} {{ $lmsH }}" fill="#ffffff"
                    xmlns="http://www.w3.org/2000/svg">
                    @foreach ($lmsDots as $dot)
                        <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['r'] }}"
                            fill-opacity="{{ $dot['o'] }}" />
                    @endforeach
                </svg>
            </div>

            <div class="reveal-up relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-3 divide-x-0 sm:grid-cols-4 sm:gap-4 sm:divide-x sm:divide-slate-700 lg:gap-8">
                    <div class="p-4 text-center">
                        <h4 class="stat-counter mb-2 text-3xl font-extrabold text-white lg:text-4xl"
                            data-target="{{ $lmsCourseCount }}" data-suffix=""
                            style="font-family: 'Oswald', sans-serif;">0</h4>
                        <p class="text-sm font-medium text-slate-400">Kursus Tersedia</p>
                    </div>
                    <div class="p-4 text-center">
                        <h4 class="stat-counter mb-2 text-3xl font-extrabold text-white lg:text-4xl"
                            data-target="{{ $lmsModuleCount }}" data-suffix="+"
                            style="font-family: 'Oswald', sans-serif;">0</h4>
                        <p class="text-sm font-medium text-slate-400">Modul Pembelajaran</p>
                    </div>
                    <div class="p-4 text-center">
                        <h4 class="stat-counter mb-2 text-3xl font-extrabold text-white lg:text-4xl"
                            data-target="{{ $lmsCategoryCount }}" data-suffix=""
                            style="font-family: 'Oswald', sans-serif;">0</h4>
                        <p class="text-sm font-medium text-slate-400">Kategori Kursus</p>
                    </div>
                    <div class="p-4 text-center">
                        <h4 class="stat-counter mb-2 text-3xl font-extrabold text-white lg:text-4xl"
                            data-target="{{ $lmsParticipantCount / 1000 }}" data-suffix="K+"
                            style="font-family: 'Oswald', sans-serif;">0</h4>
                        <p class="text-sm font-medium text-slate-400">Peserta Terdaftar</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- ========================================== -->
    <!-- FAQ SECTION                                -->
    <!-- ========================================== -->
    <section id="faq" class="py-12 sm:py-18 lg:py-16 px-4 bg-white border-t border-slate-200 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 sm:gap-6 lg:gap-16 items-center">

                <!-- Kolom Kiri: Judul & Accordion -->
                <div class="order-2 reveal-left lg:order-2">
                    <div class="text-left mb-6 lg:mb-10">
                        <!-- FONT KALAM DIAPLIKASIKAN DISINI (Tanpa tracking-wide) -->
                        {{-- <span class="text-[#13416B] font-kalam font-bold text-md lg:text-xl mb-2 block">
                            Pusat Bantuan
                        </span> --}}
                        <!-- FONT OSWALD UNTUK TITLE -->
                        <h2 class="section-title text-slate-900 mb-4 lg:mb-6">
                            Pertanyaan yang Sering <span class="text-[#13416B]">Diajukan</span>
                        </h2>
                        <p class="text-slate-600 text-sm lg:text-lg leading-relaxed">
                            Temukan jawaban cepat untuk pertanyaan seputar penggunaan aplikasi SIRENATA atau kunjungi <a
                                href="https://bantuan.kemnaker.go.id/" target="_blank"
                                class="text-amber-500 font-bold hover:underline">Pusat Bantuan Kemnaker</a> untuk
                            kendala teknis lainnya.
                        </p>
                    </div>

                    <!-- Accordion FAQ menggunakan Alpine.js -->
                    <div x-data="{ activeAccordion: null }" class="space-y-3 lg:space-y-4">
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                            :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 1 }">
                            <button @click="activeAccordion = activeAccordion === 1 ? null : 1"
                                class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                    :class="{ 'text-[#13416B]': activeAccordion === 1 }">
                                    Siapa saja yang dapat menggunakan aplikasi SIRENATA?
                                </h3>
                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                    :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 1 }"></i>
                            </button>
                            <div x-show="activeAccordion === 1" x-collapse x-cloak>
                                <div
                                    class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                    Aplikasi ini ditujukan khusus bagi para pemangku kepentingan ketenagakerjaan,
                                    termasuk Super Admin, Admin Pusat, Admin Instansi Provinsi, Admin Instansi
                                    Kabupaten/Kota, dan Pengguna ASN (Aparatur Sipil Negara) selaku perencana di daerah.
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                            :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 2 }">
                            <button @click="activeAccordion = activeAccordion === 2 ? null : 2"
                                class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                    :class="{ 'text-[#13416B]': activeAccordion === 2 }">
                                    Bagaimana cara mendaftar atau masuk ke dalam aplikasi?
                                </h3>
                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                    :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 2 }"></i>
                            </button>
                            <div x-show="activeAccordion === 2" x-collapse x-cloak>
                                <div
                                    class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                    SIRENATA terintegrasi dengan sistem <strong>Single Sign-On (SSO)</strong> Kemnaker.
                                    Anda dapat langsung masuk menggunakan akun <strong>SIAPKerja ID</strong> yang telah
                                    terdaftar. Jika Anda mewakili instansi daerah, hubungi Admin Pusat untuk penyesuaian
                                    hak akses.
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                            :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 3 }">
                            <button @click="activeAccordion = activeAccordion === 3 ? null : 3"
                                class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                    :class="{ 'text-[#13416B]': activeAccordion === 3 }">
                                    Apa perbedaan RTK Makro, RTK Mikro, dan IPK?
                                </h3>
                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                    :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 3 }"></i>
                            </button>
                            <div x-show="activeAccordion === 3" x-collapse x-cloak>
                                <div
                                    class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                    <ul class="list-disc pl-5 space-y-2">
                                        <li><strong>RTK Makro:</strong> Proyeksi tenaga kerja di tingkat wilayah
                                            (Nasional/Provinsi/Kabupaten/Kota) berdasarkan ekonomi makro.</li>
                                        <li><strong>RTK Mikro:</strong> Analisis kebutuhan pegawai/tenaga kerja spesifik
                                            di dalam internal suatu instansi atau perusahaan.</li>
                                        <li><strong>IPK:</strong> Indeks Pembangunan Ketenagakerjaan, yaitu pengukuran
                                            capaian kinerja daerah berdasarkan 7 indikator utama ketenagakerjaan.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                            :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 4 }">
                            <button @click="activeAccordion = activeAccordion === 4 ? null : 4"
                                class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                    :class="{ 'text-[#13416B]': activeAccordion === 4 }">
                                    Apa fungsi fitur LMS Terintegrasi?
                                </h3>
                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                    :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 4 }"></i>
                            </button>
                            <div x-show="activeAccordion === 4" x-collapse x-cloak>
                                <div
                                    class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                    Fitur LMS difungsikan sebagai sarana transfer pengetahuan dari pusat ke daerah.
                                    Pengguna dapat mengikuti kursus interaktif secara mandiri untuk meningkatkan
                                    kompetensi terkait perencanaan, serta mendapatkan <strong>sertifikat resmi</strong>
                                    setelah lulus ujian (post-test).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Ilustrasi Bantuan -->
                <div
                    class="order-1 relative hidden h-[300px] items-center justify-center reveal-right sm:flex lg:h-full">
                    <div
                        class="absolute right-0 top-1/2 h-[240px] w-[240px] -translate-y-1/2 rounded-full border border-[#13416B]/15 border-dashed animate-[spin_55s_linear_infinite] lg:h-[420px] lg:w-[420px]">
                    </div>
                    <div class="absolute right-12 top-1/2 h-[210px] w-[210px] -translate-y-1/2 overflow-hidden bg-gradient-to-tr from-amber-400 to-yellow-200 opacity-90 lg:right-32 lg:h-[380px] lg:w-[380px]"
                        style="border-radius: 44% 56% 62% 38% / 52% 42% 58% 48%;">
                        <svg class="absolute inset-0 h-full w-full opacity-25" viewBox="0 0 380 380" fill="none"
                            preserveAspectRatio="none" aria-hidden="true">
                            <path d="M48 -24C96 72 10 130 58 222C88 282 38 336 60 404" stroke="white"
                                stroke-width="18" />
                            <path d="M132 -24C180 72 94 130 142 222C172 282 122 336 144 404" stroke="white"
                                stroke-width="18" />
                            <path d="M216 -24C264 72 178 130 226 222C256 282 206 336 228 404" stroke="white"
                                stroke-width="18" />
                            <path d="M300 -24C348 72 262 130 310 222C340 282 290 336 312 404" stroke="white"
                                stroke-width="18" />
                        </svg>
                    </div>
                    <img src="{{ asset('images/FAQ.webp') }}" alt="Pusat Bantuan Kemnaker"
                        class="relative z-10 w-full max-w-[300px] md:max-w-[360px] lg:max-w-[520px] h-auto object-contain drop-shadow-2xl animate-float mt-4 lg:mt-8"
                        style="-webkit-mask-image: linear-gradient(to bottom, black 87%, transparent 100%); mask-image: linear-gradient(to bottom, black 87%, transparent 100%);">
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- CTA SECTION                                -->
    <!-- ========================================== -->
    <section id="cta" class="py-20 sm:py-16 md:py-28 lg:py-36 px-4 md:px-16 relative overflow-hidden"
        style="background-color: #13416B;">
        <!-- Efek Glow Latar Belakang -->
        <div
            class="absolute inset-0 bg-blue-400/20 blur-[120px] rounded-full w-[80%] h-[80%] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none z-0">
        </div>

        <!-- Ornamen SVG -->
        <div
            class="absolute -left-[50%] sm:-left-[20%] top-1/2 -translate-y-1/2 w-[1200px] h-[1200px] lg:w-[2200px] lg:h-[2200px] text-white opacity-[0.06] pointer-events-none z-0 transition-transform duration-1000">
            <svg viewBox="0 0 1600 1600" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <polyline points="300,200 900,800 300,1400" stroke="currentColor" stroke-width="260"
                    stroke-linecap="round" stroke-linejoin="miter" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Kolom Kiri: Teks & Tombol -->
                <div class="text-left reveal-left lg:col-span-7 xl:col-span-8 lg:pr-10">
                    <!-- FONT OSWALD UNTUK TITLE -->
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
                            <a href="{{ route('login') }}"
                                class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 bg-white font-bold rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-sm lg:text-lg text-center"
                                style="color: #13416B;">
                                Daftar Gratis Sekarang
                            </a>
                            <a href="{{ route('login') }}"
                                class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 font-bold rounded-full border-2 border-white/30 text-white transition-all hover:bg-white/10 text-sm lg:text-lg text-center">
                                Sudah Punya Akun? Masuk
                            </a>
                        @else
                            <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                                class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 bg-white font-bold rounded-full shadow-lg text-sm lg:text-lg text-center"
                                style="color: #13416B;">
                                Buka Dashboard
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- Kolom Kanan: Ilustrasi CTA -->
                {{-- <div
                    class="relative hidden lg:col-span-5 xl:col-span-4 lg:flex h-full items-end justify-end reveal-right">
                    <div class="absolute right-10 bottom-10 h-[280px] w-[280px] rounded-full bg-white/5 blur-xl"></div>
                    <img src="{{ asset('images/cta_illustrasi.png') }}"
                        alt="Kepala Pusat Perencanaan Ketenagakerjaan"
                        class="relative z-10 w-full h-full  max-w-[780px] object-contain drop-shadow-2xl animate-float translate-y-8"
                        style="-webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%); mask-image: linear-gradient(to bottom, black 80%, transparent 100%);">
                </div> --}}

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- FOOTER                                     -->
    <!-- ========================================== -->
    <footer class="relative py-12 md:py-14 lg:py-20 px-4 overflow-hidden" style="background-color: #0b2641;">
        <!-- Dekorasi Latar Belakang Footer -->
        <div
            class="absolute top-0 right-0 -mt-20 -mr-20 w-[500px] h-[500px] rounded-full bg-blue-500/5 blur-[100px] pointer-events-none z-0">
        </div>

        <div class="max-w-7xl mx-auto relative z-10 reveal-up">
                <div class="flex flex-col sm:flex-row gap-6 sm:gap-8 lg:gap-24 mb-10 lg:mb-16">

                <!-- Kiri: Brand, Deskripsi & Sosial Media -->
                <div class="sm:w-5/12 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-4 mb-6">
                            <img src="{{ asset('images/logo.png') }}" alt="SIRENATA"
                                class="h-8 w-auto brightness-0 invert">
                            <span class="text-xl font-bold text-white tracking-tight">SIRENATA</span>
                        </div>
                        <p class="text-slate-400 leading-relaxed text-sm mb-10 max-w-md">
                            Aplikasi digital terpadu untuk kebutuhan penyusunan RTK Makro, RTK Mikro, dan pengukuran
                            Indeks Pembangunan Ketenagakerjaan.
                        </p>
                    </div>

                    <!-- Sosial Media -->
                    <div>
                        <h3 class="font-bold text-white mb-5 uppercase tracking-widest text-xs opacity-60">Terhubung
                            Bersama Kami</h3>
                        <div class="flex items-center gap-4">
                            <a href="#"
                                class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#"
                                class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                                <i class="fab fa-youtube text-xl"></i>
                            </a>
                            <!-- Ikon X Menggunakan Inline SVG agar pasti muncul -->
                            <a href="#"
                                class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Tautan & Hubungi Kami (Layout Kalem) -->
                <div class="sm:w-7/12 grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-16 pt-2">

                    <!-- Tautan Publik -->
                    <div>
                        <h3
                            class="font-bold text-white mb-6 uppercase tracking-widest text-xs border-b border-white/10 pb-4 inline-block">
                            Tautan Publik</h3>
                        <ul class="space-y-4 text-slate-400 text-sm">

                            <li>
                                <a href="https://kemnaker.go.id" target="_blank"
                                    class="hover:text-white transition-colors flex items-center gap-2 group">
                                    <i
                                        class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                    Kemnaker RI
                                </a>
                            </li>
                            <li>
                                <a href="https://pusren.kemnaker.go.id/" target="_blank"
                                    class="hover:text-white transition-colors flex items-center gap-2 group">
                                    <i
                                        class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                    Pusrenaker
                                </a>
                            </li>
                            <li>
                                <a href="https://siapkerja.kemnaker.go.id" target="_blank"
                                    class="hover:text-white transition-colors flex items-center gap-2 group">
                                    <i
                                        class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                    SIAPkerja Kemnaker
                                </a>
                            </li>
                            <li>
                                <a href="https://satudata.kemnaker.go.id" target="_blank"
                                    class="hover:text-white transition-colors flex items-center gap-2 group">
                                    <i
                                        class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                    Satu Data Ketenagakerjaan
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Hubungi Kami (Kalem & Rata Kiri) -->
                    <div>
                        <h3
                            class="font-bold text-white mb-6 uppercase tracking-widest text-xs border-b border-white/10 pb-4 inline-block">
                            Pusat Informasi</h3>

                        <ul class="space-y-5 text-slate-400 text-sm">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-envelope mt-1 shrink-0 text-white"></i>
                                <div>
                                    <a href="mailto:support@sirenata.go.id"
                                        class="hover:text-white transition-colors">support@sirenata.go.id</a>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-map-marker-alt mt-1 shrink-0 text-white"></i>
                                <div>
                                    <span>Kementerian Ketenagakerjaan RI<br>Jakarta, Indonesia</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Garis Bawah & Copyright -->
            <div
                class="pt-8 border-t border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4 text-slate-400 text-sm font-medium">
                <p>&copy; 2026 Kementerian Ketenagakerjaan Republik Indonesia.</p>
                <p class="text-xs opacity-60 tracking-wider">ALL RIGHTS RESERVED.</p>
            </div>
        </div>
    </footer>

    <!-- ========================================== -->
    <!-- SCROLL TO TOP BUTTON                       -->
    <!-- ========================================== -->
    <div x-data="{ showScrollTop: false }" @scroll.window="showScrollTop = (window.pageYOffset > 400)"
        class="fixed bottom-6 right-6 z-50">
        <button x-show="showScrollTop" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10"
            class="w-12 h-12 flex items-center justify-center bg-[#13416B] text-white rounded-full shadow-lg hover:bg-blue-800 hover:-translate-y-1 transition-all focus:outline-none border-2 border-white/20">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- SCRIPT OBSERVER UNTUK ANIMASI MUNCUL (REVEAL) -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const revealOptions = {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.15
                };

                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('active');
                        }
                    });
                }, revealOptions);

                const revealElements = document.querySelectorAll('.reveal-left, .reveal-right, .reveal-up');
                revealElements.forEach(el => revealObserver.observe(el));

                document.querySelectorAll('.course-scroll-container').forEach(container => {
                    let animationFrame;
                    let isInteracting = false;
                    let lastPointerY = 0;
                    let hasDragged = false;
                    const autoScrollSpeed = 7 / 60;

                    const autoScroll = () => {
                        if (!isInteracting) {
                            container.scrollTop += autoScrollSpeed;

                            if (container.scrollTop >= container.scrollHeight / 3) {
                                container.scrollTop -= container.scrollHeight / 3;
                            }
                        }

                        animationFrame = requestAnimationFrame(autoScroll);
                    };

                    container.addEventListener('pointerdown', event => {
                        isInteracting = true;
                        hasDragged = false;
                        lastPointerY = event.clientY;
                        container.classList.add('is-dragging');
                        container.setPointerCapture(event.pointerId);
                    });

                    container.addEventListener('pointermove', event => {
                        if (!isInteracting) return;

                        const distance = event.clientY - lastPointerY;
                        if (Math.abs(distance) > 1) hasDragged = true;
                        container.scrollTop -= distance;
                        lastPointerY = event.clientY;
                    });

                    const stopDragging = event => {
                        if (!isInteracting) return;

                        isInteracting = false;
                        container.classList.remove('is-dragging');
                        if (event.pointerId !== undefined && container.hasPointerCapture(event.pointerId)) {
                            container.releasePointerCapture(event.pointerId);
                        }
                    };

                    container.addEventListener('pointerup', stopDragging);
                    container.addEventListener('pointercancel', stopDragging);
                    container.addEventListener('click', event => {
                        if (hasDragged) {
                            event.preventDefault();
                            event.stopPropagation();
                            hasDragged = false;
                        }
                    }, true);

                    animationFrame = requestAnimationFrame(autoScroll);
                    container.addEventListener('DOMNodeRemoved', () => cancelAnimationFrame(animationFrame));
                });

                const statOptions = {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.5
                };

                const statObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const targetEl = entry.target;
                            const targetNum = parseFloat(targetEl.getAttribute('data-target'));
                            const isFloat = targetNum % 1 !== 0;
                            const suffix = targetEl.getAttribute('data-suffix') || '';
                            const duration = 2000;
                            let startTime = null;

                            const animateCount = (timestamp) => {
                                if (!startTime) startTime = timestamp;
                                const progress = Math.min((timestamp - startTime) / duration, 1);

                                const easeProgress = 1 - Math.pow(1 - progress, 3);
                                let currentNum = easeProgress * targetNum;

                                if (isFloat) {
                                    targetEl.innerText = currentNum.toFixed(1) + suffix;
                                } else {
                                    targetEl.innerText = Math.floor(currentNum) + suffix;
                                }

                                if (progress < 1) {
                                    requestAnimationFrame(animateCount);
                                } else {
                                    targetEl.innerText = targetNum + suffix;
                                }
                            };
                            requestAnimationFrame(animateCount);
                            observer.unobserve(targetEl);
                        }
                    });
                }, statOptions);

                const statCounters = document.querySelectorAll('.stat-counter');
                statCounters.forEach(counter => statObserver.observe(counter));
            });
        </script>
    @endpush

</x-landingpage::layouts.master>
