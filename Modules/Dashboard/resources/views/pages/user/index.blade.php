<x-dashboard::layouts.dashboard title="Dashboard Pembelajaran">
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            #instansiForm .select2-container .select2-selection--single {
                height: 44px;
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
            }

            #instansiForm .select2-container .select2-selection__rendered {
                line-height: 42px;
                padding-left: 12px;
                padding-right: 32px;
            }

            #instansiForm .select2-container .select2-selection__arrow {
                height: 42px;
                right: 8px;
            }

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

    <div class="px-0 py-3 sm:p-5 lg:p-6 max-w-full mx-auto space-y-5 sm:space-y-6">

        <!-- ===================================== -->
        <!-- 1. STATS GRID (Solid Colored Cards)   -->
        <!-- ===================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <!-- Total Kursus (Dark Navy) -->
            <div
                class="relative overflow-hidden bg-[#13416B] rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <!-- Efek Dekoratif Bubbles Geometris Profesional -->
                <div class="absolute inset-0 pointer-events-none z-0">
                    <div class="absolute -top-12 -right-10 w-44 h-44 border-[16px] border-white/5 rounded-full group-hover:scale-105 transition-transform duration-500"></div>
                    <div class="absolute -bottom-10 right-10 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute top-3 right-24 w-12 h-12 border-[4px] border-amber-400/20 rounded-full"></div>
                    <div class="absolute bottom-4 right-32 w-3 h-3 bg-blue-300/20 rounded-full"></div>
                    <div class="absolute top-5 right-40 w-2 h-2 bg-white/10 rounded-full"></div>
                </div>

                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Pelatihan Diikuti</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $eventStats['total_enrolled'] ?? $stats['total'] }}</h3>
                    </div>

                    <!-- Badge Putih Solid, Ikon Topi Toga / Edukasi -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#13416B] flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q.105.105.215.205" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Rata-rata Progress (Slate Blue) -->
            <div
                class="relative overflow-hidden bg-[#547996] rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <!-- Efek Dekoratif Bubbles Geometris Profesional -->
                <div class="absolute inset-0 pointer-events-none z-0">
                    <div class="absolute -top-12 -right-10 w-44 h-44 border-[16px] border-white/5 rounded-full group-hover:scale-105 transition-transform duration-500"></div>
                    <div class="absolute -bottom-10 right-10 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute top-3 right-24 w-12 h-12 border-[4px] border-amber-400/25 rounded-full"></div>
                    <div class="absolute bottom-4 right-32 w-3 h-3 bg-white/20 rounded-full"></div>
                    <div class="absolute top-5 right-40 w-2 h-2 bg-white/10 rounded-full"></div>
                </div>

                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Rata-rata Progres</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stats['avg_progress'] }}%</h3>
                    </div>

                    <!-- Badge Putih Solid, Ikon Tren Progress -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#547996] flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pelatihan Selesai (Emerald Green) -->
            <div
                class="relative overflow-hidden bg-emerald-600 rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <!-- Efek Dekoratif Bubbles Geometris Profesional -->
                <div class="absolute inset-0 pointer-events-none z-0">
                    <div class="absolute -top-12 -right-10 w-44 h-44 border-[16px] border-white/10 rounded-full group-hover:scale-105 transition-transform duration-500"></div>
                    <div class="absolute -bottom-10 right-10 w-28 h-28 bg-white/10 rounded-full"></div>
                    <div class="absolute top-3 right-24 w-12 h-12 border-[4px] border-amber-300/30 rounded-full"></div>
                    <div class="absolute bottom-4 right-32 w-3 h-3 bg-white/25 rounded-full"></div>
                    <div class="absolute top-5 right-40 w-2 h-2 bg-white/15 rounded-full"></div>
                </div>

                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Pelatihan Selesai</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stats['selesai'] }}</h3>
                    </div>

                    <!-- Badge Putih Solid, Ikon Piala Prestasi -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-emerald-600 flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A6.002 6.002 0 0 0 18 9.75V6A2.25 2.25 0 0 0 15.75 3.75H8.25A2.25 2.25 0 0 0 6 6v3.75a6.002 6.002 0 0 0 3.504 5.625m4.992 0a6.01 6.01 0 0 1-4.992 0M6 6.75H4.875A2.625 2.625 0 0 0 2.25 9.375V9.75a5.25 5.25 0 0 0 4.125 5.143M18 6.75h1.125a2.625 2.625 0 0 1 2.625 2.625V9.75a5.25 5.25 0 0 1-4.125 5.143" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- 2. GANTT CHART JADWAL PELATIHAN (LMS BY EVENT)            -->
        <!-- ========================================================= -->
        @php
            $ganttYear = $userEventGantt['year'] ?? now()->year;
            $ganttHasEvents = !empty($userEventGantt['events']);
            $ganttTodayLabel = $userEventGantt['today_label'] ?? now()->translatedFormat('d M Y');
        @endphp
        <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <header class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-800">
                        Jadwal Pelatihan Terdaftar ({{ $ganttYear }})
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Linimasa pendaftaran, pelaksanaan Live Zoom, dan evaluasi gelombang pelatihan Anda
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
                        <option value="completed">Selesai</option>
                    </select>

                    <a href="{{ route('user.catalog.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shadow-2xs">
                        <span>Jelajahi Katalog</span>
                    </a>
                </div>
            </header>

            {{-- Legenda Fase --}}
            <div class="px-4 sm:px-6 py-3 border-b border-slate-100 bg-slate-50/50">
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

            <div class="p-4 sm:p-6">
                {{-- Kontainer Gantt Chart --}}
                <div id="userGanttWrapper"
                     class="border border-slate-200 rounded-md overflow-hidden bg-white {{ $ganttHasEvents ? '' : 'hidden' }}">
                    <div id="userGanttScrollArea" class="overflow-x-auto overflow-y-auto max-h-[480px] custom-scrollbar relative">
                        <div id="userGanttInner" class="min-w-[880px] relative">
                            <div id="userGanttHeader" class="sticky top-0 z-30 bg-slate-50 border-b border-slate-200"></div>
                            <div id="userGanttRows" class="divide-y divide-slate-100 relative"></div>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="userGanttEmptyState"
                     class="bg-slate-50 rounded-md p-10 text-center border border-dashed border-slate-200 {{ $ganttHasEvents ? 'hidden' : '' }}">
                    <div class="w-12 h-12 bg-white border border-slate-200 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i class="fas fa-calendar-xmark text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Belum Ada Pelatihan Terdaftar</p>
                    <p class="text-xs text-slate-500 mt-1">Anda belum mendaftar pada gelombang pelatihan atau tidak ada pelatihan dengan status tersebut. Jelajahi katalog untuk mulai mendaftar.</p>
                </div>
            </div>
        </section>

        {{-- Floating Tooltip Gantt Chart --}}
        <div id="userGanttTooltip"
             class="fixed z-[100] hidden pointer-events-none w-72 bg-[#13416B] text-white rounded-md shadow-xl border border-white/15 p-3.5 text-xs space-y-2">
        </div>

        <!-- ======================================================= -->
        <!-- 3. AGENDA & GELOMBANG DIBUKA (2-COLUMN BALANCED GRID)   -->
        <!-- ======================================================= -->
        <div id="dashboardBottomGrid" class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-stretch">

            <!-- KOLOM KIRI (6 COLS): Agenda & Tindak Lanjut Terdekat -->
            <div class="lg:col-span-6 flex flex-col min-h-0">
                <div
                    class="bg-white rounded-md p-5 sm:p-6 shadow-sm border border-slate-200 flex flex-col flex-1 min-h-0">
                    <div class="mb-4 pb-4 border-b border-slate-100 shrink-0">
                        <h2 class="text-base sm:text-lg font-bold text-slate-800">Agenda &amp; Tindak Lanjut</h2>
                        <p class="text-xs sm:text-sm text-slate-500 truncate mt-0.5">Tindakan penting yang perlu segera diselesaikan</p>
                    </div>

                    @if (isset($actionItems) && $actionItems->count() > 0)
                        <div class="overflow-y-auto custom-scrollbar flex-1 min-h-[280px] max-h-[396px] pr-1 space-y-3">
                                @foreach ($actionItems as $item)
                                    @php
                                        $iconBg = match ($item['type']) {
                                            'zoom'        => 'bg-[#13416B] text-white',
                                            'evaluation'  => 'bg-amber-500 text-white',
                                            'narasumber'  => 'bg-[#547996] text-white',
                                            'certificate' => 'bg-emerald-600 text-white',
                                            default       => 'bg-slate-600 text-white',
                                        };
                                    @endphp
                                    <a href="{{ $item['url'] }}"
                                        class="flex items-center gap-3.5 p-4 rounded-md border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-[#13416B]/40 hover:shadow-xs transition-all group">
                                        <div class="w-10 h-10 rounded-md {{ $iconBg }} flex items-center justify-center shrink-0 text-sm shadow-2xs">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            @if (!empty($item['batch_name']))
                                                <p class="text-xs font-semibold text-[#547996] truncate" title="{{ $item['batch_name'] }}">
                                                    {{ $item['batch_name'] }}
                                                </p>
                                            @endif
                                            <h3 class="text-sm sm:text-base font-semibold text-slate-800 group-hover:text-[#13416B] transition-colors line-clamp-2 leading-snug {{ !empty($item['batch_name']) ? 'mt-0.5' : '' }}" title="{{ $item['title'] }}">
                                                {{ $item['title'] }}
                                            </h3>
                                            <p class="text-xs sm:text-sm text-slate-500 truncate mt-1" title="{{ $item['subtitle'] }}">
                                                {{ $item['subtitle'] }}
                                            </p>
                                        </div>

                                        <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-[#13416B] group-hover:translate-x-0.5 transition-transform shrink-0 pl-1">
                                            <span>{{ $item['action_text'] }}</span>
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center bg-slate-50 rounded-md p-8 text-center border border-dashed border-slate-200 min-h-[280px]">
                            <div class="w-12 h-12 bg-white rounded-md border border-slate-200 flex items-center justify-center mx-auto mb-3 text-emerald-500">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                            <p class="text-base font-semibold text-slate-700">Semua agenda sudah tuntas</p>
                            <p class="text-sm text-slate-500 mt-1 max-w-xs">Tidak ada sesi Zoom atau evaluasi tertunda saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- KOLOM KANAN (6 COLS): Gelombang Pendaftaran Dibuka (Katalog) -->
            <div class="lg:col-span-6 flex flex-col min-h-0">
                <div
                    class="bg-white rounded-md p-5 sm:p-6 shadow-sm border border-slate-200 flex flex-col flex-1 min-h-0">
                    <div class="mb-4 pb-4 border-b border-slate-100 shrink-0">
                        <h2 class="text-base sm:text-lg font-bold text-slate-800">Gelombang Pendaftaran Dibuka</h2>
                        <p class="text-xs sm:text-sm text-slate-500 truncate mt-0.5">Pelatihan di Katalog yang masih membuka kuota pendaftaran</p>
                    </div>

                    @if (isset($openRegistrationEvents) && $openRegistrationEvents->count() > 0)
                        <div class="flex-1 flex flex-col min-h-0 justify-between">
                            <div class="overflow-y-auto custom-scrollbar flex-1 min-h-[280px] max-h-[354px] pr-1 space-y-3">
                                @foreach ($openRegistrationEvents as $ev)
                                    @php
                                        $quotaPct = $ev['quota_pct'];
                                        $quotaBarCls = match (true) {
                                            $quotaPct !== null && $quotaPct >= 80 => 'bg-amber-500',
                                            $quotaPct !== null && $quotaPct >= 50 => 'bg-emerald-500',
                                            default                               => 'bg-sky-500',
                                        };
                                    @endphp
                                    <a href="{{ $ev['url'] }}"
                                        class="block p-4 rounded-md border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-[#13416B]/40 hover:shadow-xs transition-all group">
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-[#547996] truncate" title="{{ $ev['batch_name'] }}">
                                                {{ $ev['batch_name'] }}
                                            </p>
                                            <h3 class="text-sm sm:text-base font-semibold text-slate-800 group-hover:text-[#13416B] transition-colors line-clamp-2 leading-snug mt-0.5" title="{{ $ev['course_name'] }}">
                                                {{ $ev['course_name'] }}
                                            </h3>
                                        </div>

                                        @if ($quotaPct !== null)
                                            <div class="mt-3 flex items-center gap-3">
                                                <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all {{ $quotaBarCls }}" style="width: {{ $quotaPct }}%"></div>
                                                </div>
                                                <span class="text-xs sm:text-sm font-semibold text-slate-600 tabular-nums shrink-0">
                                                    Sisa <strong class="text-slate-800">{{ $ev['remaining_quota'] }}</strong>/{{ $ev['quota'] }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="mt-2.5 flex items-center justify-between gap-2 text-xs sm:text-sm">
                                            <span class="inline-flex items-center gap-1.5 text-slate-500 truncate">
                                                <i class="far fa-calendar text-xs shrink-0"></i>
                                                <span class="truncate">Pelaksanaan: {{ $ev['exec_label'] }}</span>
                                            </span>
                                            <span class="font-bold text-[#13416B] group-hover:translate-x-0.5 transition-transform shrink-0">
                                                Daftar <i class="fas fa-chevron-right text-[10px] ml-0.5"></i>
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1.5 mt-2.5 pt-3 border-t border-slate-100 text-xs sm:text-sm text-slate-500 shrink-0">
                                <span>Tersedia <strong class="text-slate-700">{{ $openRegistrationEvents->count() }}</strong> gelombang pendaftaran terbuka</span>
                                <a href="{{ route('user.catalog.index') }}" class="font-bold text-[#13416B] hover:underline">
                                    Jelajahi Semua Katalog
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center bg-slate-50 rounded-md p-8 text-center border border-dashed border-slate-200 min-h-[280px]">
                            <div class="w-12 h-12 bg-white rounded-md border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <i class="fas fa-calendar-check text-lg"></i>
                            </div>
                            <p class="text-base font-semibold text-slate-700">Belum ada gelombang baru dibuka</p>
                            <p class="text-sm text-slate-500 mt-1 max-w-xs">Seluruh gelombang terbuka saat ini sudah Anda ikuti atau masa pendaftarannya telah berakhir.</p>
                            <a href="{{ route('user.catalog.index') }}"
                                class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors">
                                Lihat Katalog
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- ===================================== -->
    <!-- 3. MODAL INSTANSI                     -->
    <!-- ===================================== -->
    @if (!$profile || empty($profile->instansi))
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 backdrop-blur-sm"
            style="background-color: rgba(15, 23, 42, 0.6)">
            <div
                class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[calc(100dvh-1rem)] sm:max-h-[90vh] overflow-y-auto border border-slate-200 overscroll-contain">
                <div class="p-4 sm:p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Pilih Instansi Anda</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi informasi institusi untuk melanjutkan akses
                            pembelajaran</p>
                    </div>

                    <form id="instansiForm" method="POST" action="{{ route('user.update-instansi') }}"
                        class="space-y-5">
                        @csrf
                        @if ($errors->any())
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
                                <p class="font-semibold">Periksa kembali data yang diisi:</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Anda
                                berasal dari <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-1.5 sm:gap-3">
                                <label
                                    class="flex items-center justify-center min-w-0 px-1.5 py-2 sm:px-3 sm:py-2.5 border border-slate-200 rounded-lg cursor-pointer hover:border-[#13416B] hover:bg-[#13416B]/5 transition-all font-semibold text-xs sm:text-sm text-slate-700">
                                    <input type="radio" name="sector" value="instansi" checked
                                        class="mr-1.5 shrink-0 text-[#13416B] focus:ring-[#13416B]"> <span class="whitespace-nowrap">Instansi / Lembaga</span>
                                </label>
                                <label
                                    class="flex items-center justify-center min-w-0 px-1.5 py-2 sm:px-3 sm:py-2.5 border border-slate-200 rounded-lg cursor-pointer hover:border-[#13416B] hover:bg-[#13416B]/5 transition-all font-semibold text-xs sm:text-sm text-slate-700">
                                    <input type="radio" name="sector" value="swasta"
                                        class="mr-1.5 shrink-0 text-[#13416B] focus:ring-[#13416B]"> <span class="whitespace-nowrap">Swasta</span>
                                </label>
                            </div>
                        </div>

                        <div id="asalInstansiSection">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Asal
                                    Instansi <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-3 gap-1.5 sm:gap-3">
                                    <label
                                        class="flex items-center justify-center min-w-0 px-1.5 py-2 sm:px-3 sm:py-2.5 border border-slate-200 rounded-lg cursor-pointer hover:border-[#13416B] hover:bg-[#13416B]/5 transition-all font-semibold text-xs sm:text-sm text-slate-700">
                                        <input type="radio" name="asalInstansi" value="pusat"
                                            class="mr-1.5 shrink-0 text-[#13416B] focus:ring-[#13416B]"> <span class="whitespace-nowrap">Pusat</span>
                                    </label>
                                    <label
                                        class="flex items-center justify-center min-w-0 px-1.5 py-2 sm:px-3 sm:py-2.5 border border-slate-200 rounded-lg cursor-pointer hover:border-[#13416B] hover:bg-[#13416B]/5 transition-all font-semibold text-xs sm:text-sm text-slate-700">
                                        <input type="radio" name="asalInstansi" value="provinsi"
                                            class="mr-1.5 shrink-0 text-[#13416B] focus:ring-[#13416B]"> <span class="whitespace-nowrap">Provinsi</span>
                                    </label>
                                    <label
                                        class="flex items-center justify-center min-w-0 px-1.5 py-2 sm:px-3 sm:py-2.5 border border-slate-200 rounded-lg cursor-pointer hover:border-[#13416B] hover:bg-[#13416B]/5 transition-all font-semibold text-xs sm:text-sm text-slate-700">
                                        <input type="radio" name="asalInstansi" value="kabkota"
                                            class="mr-1.5 shrink-0 text-[#13416B] focus:ring-[#13416B]"> <span class="whitespace-nowrap">Kab/Kota</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="swastaSection" class="hidden">
                            <label for="companyName"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Nama
                                Perusahaan <span class="text-red-500">*</span></label>
                            <input type="text" name="instansi" id="companyName" maxlength="255"
                                placeholder="Contoh: PT Maju Bersama"
                                class="w-full min-h-11 px-3 py-2 border border-slate-200 rounded-lg text-sm placeholder:text-slate-400 placeholder:font-normal focus:ring-2 focus:ring-[#13416B] focus:border-[#13416B]" />
                            <p class="mt-1.5 text-xs text-slate-500">Nama perusahaan tempat Anda bekerja.</p>
                        </div>

                        <div id="kementerianSection" class="hidden">
                            <label for="kementerian"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Kementerian/Lembaga
                                <span class="text-red-500">*</span></label>
                            <select id="kementerian" class="w-full">
                                <option value="">Pilih Kementerian/Lembaga</option>
                            </select>
                            <input type="hidden" name="instansi" id="finalInstansiPusat" />
                        </div>

                        <div id="provinsiSection" class="hidden">
                            <label for="provinsi"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Provinsi
                                <span class="text-red-500">*</span></label>
                            <select id="provinsi" class="w-full" name="province_code">
                                <option value="">Pilih Provinsi</option>
                                @foreach ($provinces as $prov)
                                    <option value="{{ $prov->code }}" data-name="{{ $prov->name }}">
                                        {{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="kabkotaSection" class="hidden">
                            <label for="kabkota"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Kabupaten/Kota
                                <span class="text-red-500">*</span></label>
                            <select id="kabkota" class="w-full" name="regency_code">
                                <option value="">Pilih Kabupaten/Kota</option>
                            </select>
                        </div>

                        <div id="instansiSection" class="hidden">
                            <label for="instansi"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Instansi
                                <span class="text-red-500">*</span></label>
                            <select id="instansi" name="instansi" class="w-full">
                                <option value="">Pilih Instansi</option>
                            </select>
                            <p class="mt-1.5 text-xs text-slate-500">Pilih opsi "Lainnya" apabila instansi Anda tidak
                                ditemukan.</p>
                        </div>

                        <div id="customInstansiSection" class="hidden">
                            <label for="customInstansi"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Nama
                                Instansi <span class="text-red-500">*</span></label>
                            <input type="text" name="instansi_lainnya" id="customInstansi" maxlength="255"
                                placeholder="Masukkan nama instansi"
                                class="w-full min-h-11 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#13416B] focus:border-[#13416B]" />
                        </div>

                        <div id="unitKerjaSection" class="hidden">
                            <label for="unitKerja"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Unit Kerja
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="unit_kerja" id="unitKerja" maxlength="255" placeholder="Contoh: Bagian SDM"
                                class="w-full min-h-11 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#13416B] focus:border-[#13416B]" />
                        </div>

                        <div id="instansiClientError" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 flex items-start gap-2" role="alert" aria-live="assertive">
                            <i class="fas fa-exclamation-circle mt-0.5"></i>
                            <span id="instansiClientErrorText"></span>
                        </div>

                        <div class="pt-3">
                            <button type="submit"
                                class="w-full bg-[#13416B] text-white py-3 px-5 rounded-lg text-sm font-bold hover:bg-[#0f3354] transition-colors shadow-sm flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> <span>Simpan & Lanjutkan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ===================================== -->
    <!-- 4. SCRIPTS SECTION                    -->
    <!-- ===================================== -->
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                        const baseEvents = allEvents.filter((ev) => ev.is_enrolled);
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
                                    <div class="px-4 py-3 border-r border-slate-200 min-w-0 flex flex-col justify-center gap-1.5">
                                        <a href="${escapeHtml(ev.url)}"
                                           class="text-sm sm:text-[15px] font-semibold text-slate-900 hover:text-[#13416B] line-clamp-2 leading-snug"
                                           title="${escapeHtml(ev.batch_name)}">
                                            ${escapeHtml(ev.batch_name)}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            <span class="inline-flex px-2 py-0.5 rounded font-bold ${displayStatus.cls}">${escapeHtml(displayStatus.label)}</span>
                                        </div>
                                    </div>
                                    <div class="relative min-h-[68px] overflow-hidden">
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

            @if (!$profile || empty($profile->instansi))
                $(document).ready(function() {
                    function showFormError(message) {
                        $('#instansiClientErrorText').text(message);
                        $('#instansiClientError').removeClass('hidden');
                        const box = document.getElementById('instansiClientError');
                        if (box && box.scrollIntoView) box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    function clearFormError() {
                        $('#instansiClientError').addClass('hidden');
                    }
                    $('#instansiForm').on('change input', clearFormError);

                    $('input[name="sector"]').on('change', function() {
                        const sector = $(this).val();
                        if (sector === 'swasta') {
                            $('#asalInstansiSection').addClass('hidden');
                            $('#kementerianSection, #provinsiSection, #kabkotaSection, #instansiSection, #customInstansiSection, #unitKerjaSection')
                                .addClass('hidden');
                            $('input[name="asalInstansi"]').prop('checked', false);
                            $('#provinsi').val('').trigger('change');
                            $('#kabkota').empty().append(new Option('Pilih Kabupaten/Kota', ''));
                            $('#swastaSection').removeClass('hidden');
                            $('#provinsiSection').removeClass('hidden');
                            $('#companyName').prop('required', true);
                            $('#unitKerja').val('').prop('required', false);
                            $('#customInstansi').prop('required', false);
                        } else {
                            $('#swastaSection').addClass('hidden');
                            $('#companyName').val('').prop('required', false);
                            $('#asalInstansiSection').removeClass('hidden');
                            $('#provinsi').val('').trigger('change');
                            $('#kabkota').empty().append(new Option('Pilih Kabupaten/Kota', ''));
                            $('#kabkotaSection, #instansiSection, #customInstansiSection, #unitKerjaSection')
                                .addClass('hidden');
                            $('#unitKerja').prop('required', false);
                        }
                        clearFormError();
                    });

                    $.ajax({
                        url: '{{ route('api.masterdata.institutions.index') }}?type=pusat',
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                response.data
                                    .filter(institution => institution.name.trim().toLowerCase() !== 'lainnya')
                                    .forEach(institution => {
                                        $('#kementerian').append(new Option(institution.name, institution.name));
                                });
                                $('#kementerian').append(new Option(
                                    'Lainnya (instansi belum terdaftar)', 'lainnya'));
                                $('#kementerian').trigger('change');
                            }
                        },
                        error: function() {
                            $('#kementerian').append(new Option(
                                'Daftar instansi gagal dimuat; pilih Lainnya untuk melanjutkan',
                                '', true, true));
                            $('#kementerian').append(new Option(
                                'Lainnya (instansi belum terdaftar)', 'lainnya'));
                            $('#kementerian').trigger('change');
                        }
                    });

                    $('#kementerian, #provinsi, #kabkota, #instansi').select2({
                        placeholder: function() {
                            return $(this).find('option:first').text();
                        },
                        allowClear: false,
                        width: '100%'
                    });

                    $('#kementerian').on('change', function() {
                        const isOther = $(this).val() === 'lainnya';
                        $('#customInstansiSection').toggleClass('hidden', !isOther);
                        $('#unitKerjaSection').toggleClass('hidden', !isOther && !$(this).val());
                        $('#customInstansi').val('').prop('required', isOther);
                        $('#unitKerja').prop('required', Boolean($(this).val()));
                        if (isOther) {
                            $('#unitKerjaSection').removeClass('hidden');
                        }
                    });

                    $('input[name="asalInstansi"]').on('change', function() {
                        const value = $(this).val();
                        $('#kementerianSection, #provinsiSection, #kabkotaSection, #instansiSection, #customInstansiSection, #unitKerjaSection')
                            .addClass('hidden');
                        $('#kementerian, #provinsi, #kabkota, #instansi, #customInstansi, #unitKerja').val('')
                            .trigger('change');
                        $('#finalInstansiPusat').val('');
                        $('#customInstansi, #unitKerja').prop('required', false);

                        if (value === 'pusat') {
                            $('#kementerianSection').removeClass('hidden');
                        } else if (value === 'provinsi' || value === 'kabkota') {
                            $('#provinsiSection').removeClass('hidden');
                        }
                    });

                    function populateInstansi() {
                        $('#instansi').empty().append(new Option('Pilih Instansi', ''));

                        const provinceCode = $('#provinsi').val();
                        const regencyCode = $('#kabkota').val();
                        const asalInstansi = $('input[name="asalInstansi"]:checked').val();

                        $.ajax({
                            url: '{{ route('api.masterdata.institutions.index') }}',
                            type: 'GET',
                            data: {
                                type: 'daerah',
                                province_code: provinceCode,
                                regency_code: regencyCode,
                                regional_level: asalInstansi
                            },
                            success: function(response) {
                                if (response.success) {
                                    const institutions = response.data.filter(institution =>
                                        institution.name.trim().toLowerCase() !== 'lainnya');

                                    if (institutions.length > 0) {
                                        institutions.forEach(institution => {
                                            $('#instansi').append(new Option(institution.name, institution.name));
                                        });
                                    }

                                    $('#instansi').append(new Option(
                                        'Lainnya (instansi belum terdaftar)', 'lainnya'));
                                    $('#instansi').trigger('change');
                                }
                            },
                            error: function() {
                                $('#instansi').append(new Option(
                                    'Lainnya (instansi belum terdaftar)', 'lainnya'));
                                $('#instansi').trigger('change');
                            }
                        });
                    }

                    $('#provinsi').on('change', function() {
                        const provinsiCode = $(this).val();
                        const sector = $('input[name="sector"]:checked').val();
                        const asalInstansi = $('input[name="asalInstansi"]:checked').val();

                        if (provinsiCode) {
                            if (sector === 'swasta') {
                                $.ajax({
                                    url: '{{ route('user.get-regencies') }}',
                                    type: 'GET',
                                    data: {
                                        province_code: provinsiCode
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $('#kabkota').empty().append(new Option(
                                                'Pilih Kabupaten/Kota', ''));
                                            response.data.forEach(regency => {
                                                $('#kabkota').append(new Option(regency.name, regency.code));
                                            });
                                            $('#kabkota').trigger('change');
                                            $('#kabkotaSection').removeClass('hidden');
                                        }
                                    }
                                });
                            } else if (asalInstansi === 'kabkota') {
                                $.ajax({
                                    url: '{{ route('user.get-regencies') }}',
                                    type: 'GET',
                                    data: {
                                        province_code: provinsiCode
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $('#kabkota').empty().append(new Option(
                                                'Pilih Kabupaten/Kota', ''));
                                            response.data.forEach(regency => {
                                                $('#kabkota').append(new Option(regency.name, regency.code));
                                            });
                                            $('#kabkota').trigger('change');
                                            $('#kabkotaSection').removeClass('hidden');
                                            $('#instansiSection, #customInstansiSection, #unitKerjaSection')
                                                .addClass('hidden');
                                        }
                                    }
                                });
                            } else if (asalInstansi === 'provinsi') {
                                populateInstansi();
                                $('#instansiSection').removeClass('hidden');
                                $('#kabkotaSection, #customInstansiSection').addClass('hidden');
                            }
                        } else {
                            $('#kabkotaSection, #instansiSection, #customInstansiSection, #unitKerjaSection')
                                .addClass('hidden');
                        }
                    });

                    $('#kabkota').on('change', function() {
                        const sector = $('input[name="sector"]:checked').val();
                        if (sector === 'swasta') {
                            return;
                        }
                        if ($(this).val()) {
                            populateInstansi();
                            $('#instansiSection').removeClass('hidden');
                            $('#customInstansiSection, #unitKerjaSection').addClass('hidden');
                        } else {
                            $('#instansiSection, #customInstansiSection, #unitKerjaSection').addClass('hidden');
                        }
                    });

                    $('#instansi').on('change', function() {
                        const value = $(this).val();
                        if (value === 'lainnya') {
                            $('#customInstansiSection').removeClass('hidden');
                            $('#unitKerjaSection').toggleClass('hidden', !$('#customInstansi').val().trim());
                            $('#customInstansi').prop('required', true);
                            $('#unitKerja').prop('required', Boolean($('#customInstansi').val().trim()));
                        } else if (value) {
                            $('#customInstansiSection').addClass('hidden');
                            $('#unitKerjaSection').removeClass('hidden');
                            $('#customInstansi').val('').prop('required', false);
                            $('#unitKerja').prop('required', true);
                        } else {
                            $('#customInstansiSection, #unitKerjaSection').addClass('hidden');
                            $('#customInstansi, #unitKerja').prop('required', false);
                        }
                    });

                    $('#customInstansi').on('input', function() {
                        const isValid = Boolean($(this).val().trim());
                        const isOtherSelected = $('#instansi').val() === 'lainnya' ||
                            $('#kementerian').val() === 'lainnya';
                        if (isValid && isOtherSelected) {
                            $('#unitKerjaSection').removeClass('hidden');
                            $('#unitKerja').prop('required', true);
                        } else {
                            $('#unitKerjaSection').addClass('hidden');
                            $('#unitKerja').prop('required', false);
                        }
                    });

                    $('#instansiForm').on('submit', function(e) {
                        const sector = $('input[name="sector"]:checked').val();
                        if (!sector) {
                            e.preventDefault();
                            showFormError('Pilih asal pengguna terlebih dahulu!');
                            return;
                        }

                        if (sector === 'swasta') {
                            if (!$('#companyName').val().trim()) {
                                e.preventDefault();
                                showFormError('Masukkan nama perusahaan!');
                                return;
                            }
                            if (!$('#provinsi').val()) {
                                e.preventDefault();
                                showFormError('Pilih provinsi domisili!');
                                return;
                            }
                            if (!$('#kabkota').val()) {
                                e.preventDefault();
                                showFormError('Pilih kabupaten/kota domisili!');
                                return;
                            }
                            $('#unitKerja').val('');
                            $('#asalInstansiSection input, #kementerian, #instansi, #customInstansi, #finalInstansiPusat')
                                .prop('disabled', true);
                            return;
                        }

                        const asalInstansi = $('input[name="asalInstansi"]:checked').val();
                        if (!asalInstansi) {
                            e.preventDefault();
                            showFormError('Pilih asal instansi terlebih dahulu!');
                            return;
                        }

                        if (asalInstansi === 'pusat') {
                            const kemVal = $('#kementerian').val();
                            if (!kemVal) {
                                e.preventDefault();
                                showFormError('Pilih Kementerian/Lembaga!');
                                return;
                            }

                            const isOther = kemVal === 'lainnya';
                            if (isOther && !$('#customInstansi').val().trim()) {
                                e.preventDefault();
                                showFormError('Masukkan nama instansi yang belum terdaftar.');
                                return;
                            }

                            $('#customInstansi').prop('required', isOther);
                            $('#unitKerja').prop('required', true);
                            $('#instansi').prop('disabled', true);
                            $('#finalInstansiPusat').val(isOther ? 'lainnya' : kemVal);
                        } else {
                            $('#instansi').prop('disabled', false);
                        }

                        if (asalInstansi === 'provinsi' || asalInstansi === 'kabkota') {
                            const instansiVal = $('#instansi').val();
                            if (instansiVal === 'lainnya') {
                                const customVal = $('#customInstansi').val().trim();
                                if (!customVal) {
                                    e.preventDefault();
                                    showFormError('Masukkan nama instansi secara manual!');
                                    return;
                                }
                            } else {
                                if (!instansiVal) {
                                    e.preventDefault();
                                    showFormError('Pilih instansi terlebih dahulu!');
                                    return;
                                }
                            }

                            $('#unitKerja').prop('required', true);
                        }
                    });
                });
            @endif
        </script>
    @endpush
</x-dashboard::layouts.dashboard>