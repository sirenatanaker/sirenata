<x-dashboard::layouts.dashboard title="Dashboard Pembelajaran">
    @push('styles')
        <style>
            #chartScrollContainer::-webkit-scrollbar,
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            #chartScrollContainer::-webkit-scrollbar-track,
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #f8fafc;
                border-radius: 4px;
            }
            #chartScrollContainer::-webkit-scrollbar-thumb,
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            #chartScrollContainer::-webkit-scrollbar-thumb:hover,
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    @endpush

    @php
        $authUser = auth()->user();
        $needsSectorOnboarding = !$profile || (empty($profile->institution_type) && empty($profile->instansi));
        $displayName = trim((string) ($authUser?->profile?->full_name ?: $authUser?->name ?: 'Peserta'));
        $firstName = explode(' ', $displayName)[0];
    @endphp

    <div class="px-0 py-3 sm:p-5 lg:p-6 max-w-full mx-auto space-y-5 sm:space-y-6">

        <!-- ========================================================= -->
        <!-- 1. LAYOUT 2 KOLOM SEIMBANG: KIRI (8) & KANAN (4)          -->
        <!-- ========================================================= -->
        @php
            $ganttYear = $userEventGantt['year'] ?? now()->year;
            $ganttHasEvents = !empty($userEventGantt['events']);
            $ganttTodayLabel = $userEventGantt['today_label'] ?? now()->translatedFormat('d M Y');
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-stretch">

            <!-- ===================================================== -->
            <!-- KOLOM KIRI (8 COLS): JADWAL PELATIHAN TERSEDIA        -->
            <!-- ===================================================== -->
            <div class="lg:col-span-8 flex flex-col min-w-0">

                <!-- CARD KIRI: Jadwal Pelatihan Tersedia (Gantt Chart) -->
                <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col flex-1 min-w-0">
                    <header class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shrink-0">
                        <div class="min-w-0">
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800">
                                Jadwal Pelatihan Tersedia
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Linimasa gelombang pelatihan di katalog untuk rencana belajar Anda berikutnya
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                            <label for="userGanttStatusFilter" class="sr-only">Filter status pelatihan</label>
                            <select id="userGanttStatusFilter"
                                class="text-xs sm:text-sm border-slate-200 rounded-md focus:ring-[#13416B] focus:border-[#13416B] cursor-pointer bg-white">
                                <option value="all">Semua Status</option>
                                <option value="open_registration">Periode Pendaftaran</option>
                                <option value="ongoing">Periode Pelaksanaan</option>
                                <option value="evaluation">Periode Evaluasi</option>
                            </select>

                            <a href="{{ route('user.catalog.index') }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shadow-2xs">
                                <span>Jelajahi Katalog</span>
                            </a>
                        </div>
                    </header>

                    {{-- Legenda Fase --}}
                    <div class="px-4 sm:px-6 py-3 border-b border-slate-100 bg-slate-50/50 shrink-0">
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs sm:text-sm text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-xs bg-sky-300"></span>Pendaftaran
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-xs bg-[#2B6496]"></span>Pelaksanaan &amp; Live Zoom
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-xs bg-emerald-500"></span>Evaluasi &amp; Sertifikasi
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="inline-block w-4 border-t-2 border-dashed border-red-500"></span>Hari Ini ({{ $ganttTodayLabel }})
                            </span>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 flex flex-col flex-1">
                        {{-- Kontainer Gantt Chart --}}
                        <div id="userGanttWrapper"
                             class="border border-slate-200 rounded-md overflow-hidden bg-white flex-1 flex flex-col {{ $ganttHasEvents ? '' : 'hidden' }}">
                            <div id="userGanttScrollArea" class="overflow-x-auto overflow-y-auto flex-1 max-h-[460px] custom-scrollbar relative">
                                <div id="userGanttInner" class="min-w-[820px] relative">
                                    <div id="userGanttHeader" class="sticky top-0 z-30 bg-slate-50 border-b border-slate-200"></div>
                                    <div id="userGanttRows" class="divide-y divide-slate-100 relative"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Empty State --}}
                        <div id="userGanttEmptyState"
                             class="flex-1 flex flex-col items-center justify-center py-10 px-4 text-center {{ $ganttHasEvents ? 'hidden' : '' }}">
                            <i class="fas fa-calendar-xmark text-3xl text-slate-300 mb-3 block"></i>
                            <p class="text-sm sm:text-base font-semibold text-slate-700">Belum Ada Jadwal Pelatihan Tersedia</p>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">Belum ada gelombang pelatihan yang tersedia untuk status tersebut saat ini.</p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ===================================================== -->
            <!-- KOLOM KANAN (4 COLS): SESI TERDEKAT & TERAKHIR DILIHAT -->
            <!-- ===================================================== -->
            <div class="lg:col-span-4 flex flex-col gap-5 sm:gap-6 min-w-0">

                <!-- CARD KANAN 1: Sesi Terdekat (Single Card, Adaptif Zoom / Pembukaan / Evaluasi) -->
                <section class="bg-white rounded-md p-4 sm:p-5 shadow-sm border border-slate-200 flex flex-col shrink-0">
                    <div class="mb-4 pb-3 border-b border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800">Sesi Terdekat</h2>
                            <p class="text-xs text-slate-500">Jadwal sesi atau tenggat terdekat Anda</p>
                        </div>
                        @if (!empty($nearestSession))
                            @php
                                $nsKind = $nearestSession['kind'] ?? 'zoom';
                                $nsPillCls = match (true) {
                                    !empty($nearestSession['is_today']) => 'bg-red-50 text-red-600 border-red-200/80',
                                    $nsKind === 'evaluation'            => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    $nsKind === 'start'                 => 'bg-sky-50 text-[#2B6496] border-sky-200/80',
                                    default                             => 'bg-sky-50 text-[#13416B] border-sky-200/80',
                                };
                                $nsDotCls = match (true) {
                                    !empty($nearestSession['is_today']) => 'bg-red-500 animate-pulse',
                                    $nsKind === 'evaluation'            => 'bg-amber-500',
                                    $nsKind === 'start'                 => 'bg-[#2B6496]',
                                    default                             => 'bg-[#13416B]',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 border {{ $nsPillCls }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $nsDotCls }}"></span>
                                {{ $nearestSession['status_label'] }}
                            </span>
                        @endif
                    </div>

                    @if (!empty($nearestSession))
                        @php
                            $nsKind = $nearestSession['kind'] ?? 'zoom';
                            $nsCalHeaderBg = match (true) {
                                !empty($nearestSession['is_today']) => 'bg-red-500',
                                $nsKind === 'evaluation'            => 'bg-amber-500',
                                $nsKind === 'start'                 => 'bg-[#2B6496]',
                                default                             => 'bg-[#13416B]',
                            };
                            $nsBtnCls = match ($nsKind) {
                                'evaluation' => 'bg-amber-500 hover:bg-amber-600 text-white',
                                'start'      => 'bg-[#2B6496] hover:bg-[#13416B] text-white',
                                default      => 'bg-[#13416B] hover:bg-[#0f3354] text-white',
                            };
                        @endphp
                        <div class="space-y-4">
                            <div class="flex items-start gap-3.5">
                                {{-- Kotak Tanggal Kalender Adaptif Warna per Jenis Sesi --}}
                                <div class="w-14 shrink-0 rounded-md overflow-hidden border border-slate-200 bg-white shadow-2xs text-center">
                                    <div class="{{ $nsCalHeaderBg }} text-white text-[10px] font-extrabold uppercase tracking-wider py-1.5">
                                        {{ $nearestSession['month_short'] ?? strtoupper(now()->locale('id')->translatedFormat('M')) }}
                                    </div>
                                    <div class="py-2.5 text-xl font-extrabold text-slate-800 leading-none tabular-nums">
                                        {{ $nearestSession['day_number'] ?? now()->format('d') }}
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    @php
                                        $nsSubtitle = $nearestSession['subtitle'] ?? $nearestSession['course_name'];
                                    @endphp
                                    <p class="text-xs font-semibold text-[#547996] truncate" title="{{ $nsSubtitle }}">
                                        {{ $nsSubtitle }}
                                    </p>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-800 leading-snug line-clamp-3 mt-0.5" title="{{ $nearestSession['title'] }}">
                                        {{ $nearestSession['title'] }}
                                    </h3>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="far fa-calendar-alt text-slate-400 text-[11px]"></i>
                                            {{ $nearestSession['date_label'] }}
                                        </span>
                                        @if (!empty($nearestSession['time_label']))
                                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                                <i class="far fa-clock text-slate-400 text-[11px]"></i>
                                                {{ $nearestSession['time_label'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <a href="{{ $nearestSession['url'] }}"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md font-bold transition-colors shadow-2xs text-xs sm:text-sm {{ $nsBtnCls }}">
                                    @if ($nsKind === 'zoom')
                                        <i class="fas fa-video text-xs"></i>
                                    @endif
                                    <span>{{ $nearestSession['action_label'] }}</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center">
                            <i class="far fa-calendar-check text-2xl text-slate-300 mb-2 block"></i>
                            <p class="text-xs sm:text-sm font-semibold text-slate-700">Belum ada sesi terjadwal</p>
                            <p class="text-xs text-slate-500 mt-0.5">Jadwal sesi atau tenggat pelatihan aktif Anda akan tampil di sini.</p>
                        </div>
                    @endif
                </section>

                <!-- CARD KANAN 2: Terakhir Dilihat (Single Card Tanpa Nested Card & Tanpa Badge Kategori) -->
                <section class="bg-white rounded-md p-4 sm:p-5 shadow-sm border border-slate-200 flex flex-col shrink-0">
                    <div class="mb-4 pb-3 border-b border-slate-100">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-800">Terakhir Dilihat</h2>
                        <p class="text-xs text-slate-500">Koleksi perpustakaan terakhir diakses</p>
                    </div>

                    @if (isset($lastLibrary) && $lastLibrary)
                        <a href="{{ route('user.library.index') }}?open={{ $lastLibrary->id }}"
                            class="flex items-start gap-3.5 group">

                            <div class="w-20 h-24 rounded-md overflow-hidden shrink-0 shadow-2xs relative bg-slate-50 border border-slate-200 flex items-center justify-center">
                                @if ($lastLibrary->cover_image)
                                    <img src="{{ $lastLibrary->cover_image_url }}"
                                        alt="{{ $lastLibrary->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute bottom-1 right-1 w-5 h-5 bg-[#13416B] text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">
                                        <i class="{{ $lastLibrary->icon }}"></i>
                                    </div>
                                @else
                                    <i class="{{ $lastLibrary->icon }} text-[#547996] text-2xl group-hover:scale-110 transition-transform"></i>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-800 leading-snug line-clamp-2 group-hover:text-[#13416B] transition-colors">
                                        {{ $lastLibrary->title }}
                                    </h3>
                                    <p class="line-clamp-2 text-xs text-slate-500 leading-relaxed mt-1">
                                        {{ $lastLibrary->description ?? 'Klik untuk membuka kembali koleksi perpustakaan ini.' }}
                                    </p>
                                </div>
                                @if (!empty($lastLibrary->last_accessed_at))
                                    <p class="text-[11px] text-slate-400 mt-2">
                                        Diakses {{ \Carbon\Carbon::parse($lastLibrary->last_accessed_at)->locale('id')->diffForHumans() }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @else
                        <div class="py-6 text-center">
                            <i class="fas fa-book-reader text-2xl text-slate-300 mb-2 block"></i>
                            <p class="text-xs sm:text-sm font-semibold text-slate-700 mb-1">Belum ada riwayat baca</p>
                            <a href="{{ route('user.library.index') }}"
                                class="text-xs font-bold text-[#13416B] hover:underline">
                                Jelajahi Perpustakaan
                            </a>
                        </div>
                    @endif
                </section>

            </div>
        </div>

        {{-- Floating Tooltip Gantt Chart --}}
        <div id="userGanttTooltip"
             class="fixed z-[100] hidden pointer-events-none w-72 bg-[#13416B] text-white rounded-md shadow-xl border border-white/15 p-3.5 text-xs space-y-2">
        </div>

    </div>

    <!-- ===================================== -->
    <!-- 3. MODAL ONBOARDING MINIMALIS         -->
    <!-- ===================================== -->
    @if ($needsSectorOnboarding)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 backdrop-blur-xs overflow-y-auto"
            style="background-color: rgba(15, 23, 42, 0.55)"
            x-data="{ selectedSector: '{{ old('sector', 'instansi') }}', submitting: false }">
            <div
                class="relative bg-white rounded-xl sm:rounded-2xl shadow-xl max-w-md sm:max-w-2xl w-full max-h-[calc(100dvh-1.5rem)] overflow-y-auto border border-slate-200 overscroll-contain my-auto overflow-x-hidden">
                <div class="relative p-4 sm:p-7">
                    {{-- Header Rata Kiri dengan Jarak Lega --}}
                    <div class="text-left mb-6 sm:mb-8 relative z-10">
                        <h2 class="text-base sm:text-xl font-extrabold text-[#0F2544] leading-snug">
                            Selamat datang, {{ $firstName }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                            Pilih latar belakang Anda untuk menyesuaikan katalog pelatihan.
                        </p>
                    </div>

                    <form id="instansiForm" method="POST" action="{{ route('user.update-instansi') }}"
                        @submit="if (!selectedSector) { $event.preventDefault(); return; } submitting = true;"
                        class="relative space-y-5 sm:space-y-6">
                        @csrf

                        @if ($errors->any())
                            <div class="relative z-10 rounded-lg border border-red-200 bg-red-50 p-2.5 sm:p-3 text-xs sm:text-sm text-red-700 text-left" role="alert">
                                Silakan pilih salah satu latar belakang Anda terlebih dahulu.
                            </div>
                        @endif

                        {{-- Wrapper Kartu + Ornamen Gelembung Organik Pastel (Ramping di Atas, Lembut di Sisi Kartu) --}}
                        <div class="relative py-1 sm:py-2">
                            {{-- Ornamen Gelembung Kiri: ramping di atas, memeluk lembut sudut kiri-bawah --}}
                            <svg aria-hidden="true" viewBox="0 0 360 320" fill="none"
                                class="pointer-events-none select-none absolute -left-4 sm:-left-6 -bottom-3 sm:-bottom-5 w-44 sm:w-64 h-auto text-[#E8F1FD] opacity-90 z-0">
                                <path fill="currentColor" d="M244 16C274 10 292 34 284 78C274 134 246 254 156 292C76 326 8 282 10 202C12 132 76 92 152 54C194 33 220 21 244 16Z" />
                            </svg>

                            {{-- Ornamen Gelembung Kanan: ramping/thin di atas, melengkung lembut di sisi kanan --}}
                            <svg aria-hidden="true" viewBox="0 0 340 340" fill="none"
                                class="pointer-events-none select-none absolute -right-4 sm:-right-6 -top-2 sm:-top-3 w-44 sm:w-64 h-auto text-[#E8F1FD] opacity-90 z-0">
                                <path fill="currentColor" d="M254 14C294 6 328 42 330 108C332 184 298 286 238 320C188 348 128 314 108 254C86 190 104 112 154 64C190 29 222 20 254 14Z" />
                            </svg>

                            {{-- 2 Kartu Pilihan Proporsional (Ringkas di Mobile, Normal di Desktop) --}}
                            <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                {{-- Kartu 1: Instansi / Lembaga --}}
                                <label @click="selectedSector = 'instansi'"
                                    class="group flex flex-col rounded-xl border-2 bg-white overflow-hidden cursor-pointer transition-all duration-200 select-none text-left"
                                    :class="selectedSector === 'instansi'
                                        ? 'border-[#2563EB] shadow-md ring-2 ring-[#2563EB]/15'
                                        : 'border-slate-200 hover:border-blue-300 shadow-2xs'">
                                    <input type="radio" name="sector" value="instansi" x-model="selectedSector" class="sr-only">

                                    {{-- Ilustrasi Gedung Instansi --}}
                                    <div class="w-full h-28 sm:h-32 bg-[#EEF4FD] overflow-hidden">
                                        <img src="{{ asset('images/onboarding_instansi_bg.jpg') }}"
                                            alt="Ilustrasi Gedung Instansi dan Lembaga"
                                            class="w-full h-full object-fill sm:object-cover sm:object-center" />
                                    </div>

                                    {{-- Konten Radio Normal + Teks Rata Kiri --}}
                                    <div class="flex items-start gap-2.5 p-3 sm:p-3.5 bg-white border-t border-slate-100 flex-1 text-left">
                                        <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                            :class="selectedSector === 'instansi' ? 'border-[#2563EB]' : 'border-slate-300'">
                                            <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#2563EB] transition-transform duration-150"
                                                :class="selectedSector === 'instansi' ? 'scale-100' : 'scale-0'"></span>
                                        </span>
                                        <div class="min-w-0 text-left">
                                            <span class="block text-sm sm:text-[15px] font-bold text-[#0F2544] leading-snug">
                                                Instansi / Lembaga
                                            </span>
                                            <span class="block text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">
                                                Pegawai kementerian/lembaga di tingkat pusat maupun instansi pemerintah provinsi dan kabupaten/kota.
                                            </span>
                                        </div>
                                    </div>
                                </label>

                                {{-- Kartu 2: Swasta --}}
                                <label @click="selectedSector = 'swasta'"
                                    class="group flex flex-col rounded-xl border-2 bg-white overflow-hidden cursor-pointer transition-all duration-200 select-none text-left"
                                    :class="selectedSector === 'swasta'
                                        ? 'border-[#2563EB] shadow-md ring-2 ring-[#2563EB]/15'
                                        : 'border-slate-200 hover:border-blue-300 shadow-2xs'">
                                    <input type="radio" name="sector" value="swasta" x-model="selectedSector" class="sr-only">

                                    {{-- Ilustrasi Gedung Swasta --}}
                                    <div class="w-full h-28 sm:h-32 bg-[#EEF4FD] overflow-hidden">
                                        <img src="{{ asset('images/onboarding_swasta_bg.jpg') }}"
                                            alt="Ilustrasi Gedung Perkantoran Swasta"
                                            class="w-full h-full object-fill sm:object-cover sm:object-center" />
                                    </div>

                                    {{-- Konten Radio Normal + Teks Rata Kiri --}}
                                    <div class="flex items-start gap-2.5 p-3 sm:p-3.5 bg-white border-t border-slate-100 flex-1 text-left">
                                        <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                            :class="selectedSector === 'swasta' ? 'border-[#2563EB]' : 'border-slate-300'">
                                            <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#2563EB] transition-transform duration-150"
                                                :class="selectedSector === 'swasta' ? 'scale-100' : 'scale-0'"></span>
                                        </span>
                                        <div class="min-w-0 text-left">
                                            <span class="block text-sm sm:text-[15px] font-bold text-[#0F2544] leading-snug">
                                                Swasta
                                            </span>
                                            <span class="block text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">
                                                Karyawan perusahaan, pelaku usaha, atau praktisi profesional di luar instansi pemerintah.
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Tombol Lanjutkan --}}
                        <div class="relative z-10 pt-1 flex justify-end">
                            <button type="submit" :disabled="!selectedSector || submitting"
                                class="w-auto min-w-[130px] sm:min-w-[150px] bg-[#13416B] text-white py-2 sm:py-2.5 px-5 rounded-lg text-xs sm:text-sm font-bold hover:bg-[#0f3354] transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">Lanjutkan</span>
                                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin text-xs"></i> Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif (!empty($showProfileReminderModal))
        <!-- ===================================== -->
        <!-- 3B. POP-UP PENGINGAT PROFIL (1X/SESI) -->
        <!-- ===================================== -->
        <div x-data="{ open: true }"
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            style="background-color: rgba(15, 23, 42, 0.55)"
            @click.self="open = false">
            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="bg-white rounded-xl shadow-xl max-w-md w-full border border-slate-200 overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-id-card text-base"></i>
                        </div>
                        <button type="button" @click="open = false"
                            class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                            aria-label="Tutup">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>

                    <div class="text-left">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                            Informasi pribadi &amp; instansi belum dilengkapi
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                            Anda tetap bisa mengikuti pelatihan sekarang. Data ini wajib dilengkapi di menu <strong class="text-slate-700">Profil</strong> saat hendak mengklaim sertifikat.
                        </p>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5">
                        <button type="button" @click="open = false"
                            class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                            Nanti Saja
                        </button>
                        <a href="{{ route('user.profile') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-xs">
                            <span>Lengkapi Profil</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ===================================== -->
    <!-- 4. SCRIPTS SECTION                    -->
    <!-- ===================================== -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // =========================================================
                // GANTT CHART JADWAL PELATIHAN USER (LMS BY EVENT)
                // =========================================================
                const UserEventGantt = (() => {
                    const wrapperEl = document.getElementById('userGanttWrapper');
                    const headerEl = document.getElementById('userGanttHeader');
                    const rowsEl = document.getElementById('userGanttRows');
                    const emptyEl = document.getElementById('userGanttEmptyState');
                    const tooltipEl = document.getElementById('userGanttTooltip');
                    const statusSelect = document.getElementById('userGanttStatusFilter');

                    if (!wrapperEl || !headerEl || !rowsEl) return;

                    const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const DAY_MS = 86400000;
                    const data = @json($userEventGantt ?? []);

                    let currentStatus = statusSelect ? statusSelect.value : 'all';

                    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                    }[c]));

                    const parseIsoDate = (iso) => {
                        if (!iso) return null;
                        const [y, m, d] = iso.split('-').map(Number);
                        return Date.UTC(y, m - 1, d);
                    };

                    const formatTick = (ms) => {
                        const dt = new Date(ms);
                        return `${dt.getUTCDate()} ${MONTH_SHORT[dt.getUTCMonth()]}`;
                    };

                    function computeDomain(events, todayMs) {
                        let minMs = Infinity;
                        let maxMs = -Infinity;

                        events.forEach((ev) => {
                            [ev.reg_start, ev.reg_end, ev.exec_start, ev.exec_end, ev.eval_start, ev.eval_end].forEach((d) => {
                                const ms = parseIsoDate(d);
                                if (ms !== null) {
                                    if (ms < minMs) minMs = ms;
                                    if (ms > maxMs) maxMs = ms;
                                }
                            });
                        });

                        if (!isFinite(minMs) || !isFinite(maxMs)) {
                            const y = data.year || new Date().getFullYear();
                            return { minMs: Date.UTC(y, 0, 1), maxMs: Date.UTC(y, 11, 31) };
                        }

                        if (todayMs && todayMs >= minMs - 14 * DAY_MS && todayMs <= maxMs + 14 * DAY_MS) {
                            minMs = Math.min(minMs, todayMs);
                            maxMs = Math.max(maxMs, todayMs);
                        }

                        minMs -= 2 * DAY_MS;
                        maxMs += 3 * DAY_MS;
                        if (maxMs <= minMs) maxMs = minMs + 14 * DAY_MS;

                        return { minMs, maxMs };
                    }

                    function buildTicks(minMs, maxMs) {
                        const totalDays = Math.max(1, Math.round((maxMs - minMs) / DAY_MS));
                        const ticks = [];

                        if (totalDays > 180) {
                            const startDt = new Date(minMs);
                            let y = startDt.getUTCFullYear();
                            let m = startDt.getUTCMonth();
                            while (Date.UTC(y, m, 1) <= maxMs) {
                                const ms = Date.UTC(y, m, 1);
                                if (ms >= minMs) ticks.push({ ms, label: `${MONTH_SHORT[m]} ${y}` });
                                m++;
                                if (m > 11) { m = 0; y++; }
                            }
                        } else {
                            const stepDays = totalDays <= 45 ? 5 : (totalDays <= 110 ? 7 : 14);
                            for (let ms = minMs; ms <= maxMs; ms += stepDays * DAY_MS) {
                                ticks.push({ ms, label: formatTick(ms) });
                            }
                        }

                        const months = [];
                        const sDt = new Date(minMs);
                        let y = sDt.getUTCFullYear();
                        let m = sDt.getUTCMonth();
                        while (Date.UTC(y, m, 1) <= maxMs) {
                            const mStart = Math.max(minMs, Date.UTC(y, m, 1));
                            const nextM = m === 11 ? Date.UTC(y + 1, 0, 1) : Date.UTC(y, m + 1, 1);
                            const mEnd = Math.min(maxMs, nextM);
                            if (mEnd > mStart) {
                                months.push({
                                    label: `${MONTH_SHORT[m]} ${y}`,
                                    leftPct: ((mStart - minMs) / (maxMs - minMs)) * 100,
                                    widthPct: ((mEnd - mStart) / (maxMs - minMs)) * 100,
                                });
                            }
                            m++;
                            if (m > 11) { m = 0; y++; }
                        }

                        return { ticks, months };
                    }

                    function calcSegment(startIso, endIso, minMs, spanMs) {
                        const s = parseIsoDate(startIso);
                        const e = parseIsoDate(endIso);
                        if (s === null || e === null) return null;
                        const endInclusive = Math.max(s + DAY_MS, e + DAY_MS);
                        const clampedS = Math.max(minMs, s);
                        const clampedE = Math.min(minMs + spanMs, endInclusive);
                        if (clampedE <= clampedS) return null;

                        const left = ((clampedS - minMs) / spanMs) * 100;
                        const width = Math.max(1.2, ((clampedE - clampedS) / spanMs) * 100);
                        return { left, width: Math.min(width, 100 - left) };
                    }

                    function positionTooltip(clientX, clientY) {
                        if (!tooltipEl || tooltipEl.classList.contains('hidden')) return;
                        const pad = 14;
                        const rect = tooltipEl.getBoundingClientRect();
                        let x = clientX + pad;
                        let y = clientY + pad;
                        if (x + rect.width > window.innerWidth - 10) x = clientX - rect.width - pad;
                        if (y + rect.height > window.innerHeight - 10) y = clientY - rect.height - pad;
                        tooltipEl.style.left = `${Math.max(8, x)}px`;
                        tooltipEl.style.top = `${Math.max(8, y)}px`;
                    }

                    function showTooltip(ev, clientX, clientY) {
                        if (!tooltipEl) return;
                        const userFooter = ev.is_enrolled
                            ? `<span>Status Anda: <strong class="text-emerald-300">Terdaftar</strong></span>
                               <span>Progres: <strong class="text-amber-300">${Number(ev.user_progress || 0)}%</strong></span>`
                            : `<span>Status Anda: <strong class="text-sky-200">Belum Terdaftar</strong></span>
                               <span class="text-amber-300 font-semibold">Klik untuk daftar</span>`;

                        const zoomRow = ev.next_zoom_label
                            ? `<div class="pt-1.5 border-t border-white/15 text-[11px] text-sky-200 flex items-center gap-1.5">
                                   <i class="fas fa-video text-[10px]"></i>
                                   <span class="truncate">${escapeHtml(ev.next_zoom_label)}</span>
                               </div>`
                            : '';

                        tooltipEl.innerHTML = `
                            <div class="border-b border-white/15 pb-2">
                                <p class="font-bold text-white leading-snug">${escapeHtml(ev.batch_name)}</p>
                                <p class="text-[11px] text-blue-200 mt-0.5">${escapeHtml(ev.course_name)}</p>
                            </div>
                            <div class="space-y-1 text-[11px]">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-sky-300"></span>Pendaftaran</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.reg_label)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-white"></span>Pelaksanaan</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.exec_label)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-emerald-400"></span>Evaluasi &amp; Sertifikasi</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.eval_label)}</span>
                                </div>
                            </div>
                            ${zoomRow}
                            <div class="pt-2 border-t border-white/15 flex items-center justify-between text-[11px] text-blue-100 tabular-nums">
                                ${userFooter}
                            </div>`;
                        tooltipEl.classList.remove('hidden');
                        positionTooltip(clientX, clientY);
                    }

                    function hideTooltip() {
                        if (tooltipEl) tooltipEl.classList.add('hidden');
                    }

                    function resolveDisplayStatus(ev, todayMs) {
                        const progress = Number(ev.user_progress || 0);
                        const execEndMs = parseIsoDate(ev.exec_end);
                        const isPastExec = todayMs !== null && execEndMs !== null && todayMs > execEndMs;

                        if (ev.status_key === 'completed' || isPastExec) {
                            if (progress >= 100) {
                                return { key: 'completed', label: 'Selesai', cls: 'bg-emerald-500 text-white' };
                            }
                            return { key: 'evaluation', label: 'Periode Evaluasi', cls: 'bg-emerald-500 text-white' };
                        }
                        if (ev.status_key === 'ongoing') {
                            return { key: 'ongoing', label: 'Periode Pelaksanaan', cls: 'bg-[#2B6496] text-white' };
                        }
                        if (ev.status_key === 'open_registration') {
                            return { key: 'open_registration', label: 'Periode Pendaftaran', cls: 'bg-sky-300 text-white' };
                        }
                        return { key: 'other', label: ev.status_label || 'Mendatang', cls: 'bg-slate-400 text-white' };
                    }

                    function render() {
                        const todayMs = parseIsoDate(data.today);
                        const allEvents = Array.isArray(data.events) ? data.events : Object.values(data.events || {});
                        const baseEvents = allEvents.filter((ev) => {
                            if (ev.user_completed) return false;
                            return resolveDisplayStatus(ev, todayMs).key !== 'completed';
                        });
                        const filtered = baseEvents.filter((ev) => {
                            const st = resolveDisplayStatus(ev, todayMs);
                            if (currentStatus !== 'all' && st.key !== currentStatus) return false;
                            return true;
                        });

                        const hasData = filtered.length > 0;
                        wrapperEl.classList.toggle('hidden', !hasData);
                        if (emptyEl) emptyEl.classList.toggle('hidden', hasData);
                        hideTooltip();
                        if (!hasData) return;

                        const { minMs, maxMs } = computeDomain(filtered, todayMs);
                        const spanMs = maxMs - minMs;
                        const { ticks, months } = buildTicks(minMs, maxMs);

                        const todayInRange = todayMs !== null && todayMs >= minMs && todayMs <= maxMs;
                        const todayPct = todayInRange ? ((todayMs - minMs) / spanMs) * 100 : null;

                        const monthBandsHtml = months.map((m) => `
                            <div class="absolute top-0 bottom-0 flex items-center px-2 border-r border-slate-200/80 text-xs font-bold uppercase tracking-wider text-[#13416B] truncate"
                                 style="left:${m.leftPct}%;width:${m.widthPct}%">
                                ${escapeHtml(m.label)}
                            </div>`).join('');

                        const tickLabelsHtml = ticks.map((t) => {
                            const pct = ((t.ms - minMs) / spanMs) * 100;
                            return `
                                <span class="absolute top-1 text-xs font-medium text-slate-600 -translate-x-1/2 whitespace-nowrap"
                                      style="left:${pct}%">
                                    ${escapeHtml(t.label)}
                                </span>`;
                        }).join('');

                        const todayHeadBadge = todayInRange
                            ? `<div class="absolute top-0 bottom-0 border-l-2 border-dashed border-red-500 z-20 pointer-events-none" style="left:${todayPct}%">
                                   <span class="absolute -top-0.5 left-1 px-1.5 py-0.5 rounded bg-red-500 text-white text-[11px] font-bold whitespace-nowrap shadow-2xs">Hari Ini</span>
                               </div>`
                            : '';

                        headerEl.innerHTML = `
                            <div class="grid grid-cols-[260px_1fr] lg:grid-cols-[330px_1fr]">
                                <div class="px-4 py-3 border-r border-slate-200 flex items-center bg-slate-50">
                                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700">Gelombang Pelatihan</span>
                                </div>
                                <div class="relative bg-slate-50">
                                    <div class="relative h-8 border-b border-slate-200/80 overflow-hidden">${monthBandsHtml}</div>
                                    <div class="relative h-7 overflow-hidden">${tickLabelsHtml}</div>
                                    ${todayHeadBadge}
                                </div>
                            </div>`;

                        const gridLinesHtml = ticks.map((t) => {
                            const pct = ((t.ms - minMs) / spanMs) * 100;
                            return `<div class="absolute top-0 bottom-0 border-l border-slate-100 pointer-events-none" style="left:${pct}%"></div>`;
                        }).join('');

                        const todayLineHtml = todayInRange
                            ? `<div class="absolute top-0 bottom-0 border-l-2 border-dashed border-red-500/80 z-20 pointer-events-none" style="left:${todayPct}%"></div>`
                            : '';

                        rowsEl.innerHTML = filtered.map((ev, idx) => {
                            const regSeg = calcSegment(ev.reg_start, ev.reg_end, minMs, spanMs);
                            const execSeg = calcSegment(ev.exec_start, ev.exec_end, minMs, spanMs);
                            const evalSeg = calcSegment(ev.eval_start, ev.eval_end, minMs, spanMs);
                            const displayStatus = resolveDisplayStatus(ev, todayMs);

                            const regBar = regSeg
                                ? `<a href="${escapeHtml(ev.url)}" class="absolute top-1/2 -translate-y-1/2 h-7 rounded-md bg-sky-300 hover:bg-sky-400 ring-1 ring-white transition-colors z-10" style="left:${regSeg.left}%;width:${regSeg.width}%"></a>`
                                : '';
                            const evalBar = evalSeg
                                ? `<a href="${escapeHtml(ev.url)}" class="absolute top-1/2 -translate-y-1/2 h-7 rounded-md bg-emerald-500 hover:bg-emerald-600 ring-1 ring-white transition-colors z-10" style="left:${evalSeg.left}%;width:${evalSeg.width}%"></a>`
                                : '';
                            const execBar = execSeg
                                ? `<a href="${escapeHtml(ev.url)}" class="absolute top-1/2 -translate-y-1/2 h-7 rounded-md bg-[#2B6496] hover:bg-[#1B568A] ring-1 ring-white shadow-2xs transition-colors z-15 flex items-center px-2 overflow-hidden" style="left:${execSeg.left}%;width:${execSeg.width}%">
                                       <span class="text-xs font-semibold text-white truncate">${execSeg.width >= 12 ? escapeHtml(ev.exec_label) : ''}</span>
                                   </a>`
                                : '';

                            return `
                                <div class="user-gantt-row grid grid-cols-[260px_1fr] lg:grid-cols-[330px_1fr] hover:bg-blue-50/35 transition-colors" data-idx="${idx}">
                                    <div class="px-4 py-3 border-r border-slate-200 min-w-0 flex flex-col justify-center gap-1">
                                        <p class="text-xs font-semibold text-[#547996] truncate" title="${escapeHtml(ev.batch_name)}">
                                            ${escapeHtml(ev.batch_name)}
                                        </p>
                                        <a href="${escapeHtml(ev.url)}"
                                           class="text-sm sm:text-[15px] font-bold text-slate-900 hover:text-[#13416B] line-clamp-2 leading-snug"
                                           title="${escapeHtml(ev.course_name)}">
                                            ${escapeHtml(ev.course_name)}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs pt-0.5">
                                            <span class="inline-flex px-2 py-0.5 rounded font-bold ${displayStatus.cls}">${escapeHtml(displayStatus.label)}</span>
                                            ${ev.is_enrolled ? '<span class="inline-flex px-2 py-0.5 rounded font-bold bg-[#13416B]/10 text-[#13416B]">Terdaftar</span>' : ''}
                                        </div>
                                    </div>
                                    <div class="relative min-h-[84px] overflow-hidden">
                                        ${gridLinesHtml}
                                        ${todayLineHtml}
                                        ${regBar}
                                        ${evalBar}
                                        ${execBar}
                                    </div>
                                </div>`;
                        }).join('');

                        rowsEl.querySelectorAll('.user-gantt-row').forEach((rowEl) => {
                            const ev = filtered[Number(rowEl.dataset.idx)];
                            if (!ev) return;
                            rowEl.addEventListener('mouseenter', (e) => showTooltip(ev, e.clientX, e.clientY));
                            rowEl.addEventListener('mousemove', (e) => positionTooltip(e.clientX, e.clientY));
                            rowEl.addEventListener('mouseleave', hideTooltip);
                        });
                    }

                    if (statusSelect) {
                        statusSelect.addEventListener('change', (e) => {
                            currentStatus = e.target.value;
                            render();
                        });
                    }

                    render();
                })();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>