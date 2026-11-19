<x-dashboard::layouts.dashboard title="Pusat Kendali Gelombang Bimtek - Admin Pusat">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
        <style>
            @media (min-width: 1024px) {
                .timeline-arrow-start {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%);
                    padding-left: 1rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-mid {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-end {
                    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1rem;
                }
            }
        </style>
    @endpush

    <div class="w-full p-4 sm:p-6 lg:p-8 max-w-400 mx-auto space-y-6">

        {{-- Breadcrumb Navigation --}}
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="text-sm sm:text-[15px] font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        Periode Event
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-xs text-slate-300 mx-1.5"></i>
                    <span class="text-sm sm:text-[15px] font-bold text-slate-900">
                        Pusat Kendali Event
                    </span>
                </li>
            </ol>
        </nav>

        @php
            $now = now();
            $regStart = $event->registration_start_at ?? $event->start_date?->copy()->subDays(7);
            $regEnd = $event->registration_end_at ?? $event->start_date?->copy()->subDay();
            $execStart = $event->start_date;
            $execEnd = $event->end_date;
            $evalStart = $event->assessment_start_at ?? $event->end_date;
            $evalEnd = $event->assessment_end_at ?? $event->end_date?->copy()->addDays(3);

            $overallStart = $event->overall_start_at;
            $overallEnd = $event->overall_end_at;
            $totalDays = ($overallStart && $overallEnd)
                ? max(1, (int) $overallStart->copy()->startOfDay()->diffInDays($overallEnd->copy()->startOfDay()) + 1)
                : 0;
            $isUnlimitedQuota = is_null($event->quota) || $event->quota <= 0;
            $quotaPercent = (!$isUnlimitedQuota && $event->quota > 0)
                ? min(100, round(($totalRegistered / $event->quota) * 100))
                : 0;
            $eventBadge = $event->status_badge;
        @endphp

        {{-- Header Banner Utama Event + Navigasi Panah 4 Tahap --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 border-b border-white/15 pb-4">
                <div class="space-y-2 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-white tracking-wide" style="font-family: 'Oswald', sans-serif;">{{ $event->batch_name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5 text-sm sm:text-base text-slate-200">
                        <span class="px-3 py-1 rounded text-sm font-bold {{ $eventBadge['class'] }}">
                            {{ $eventBadge['label'] }}
                        </span>
                        <span class="text-white/40 select-none">|</span>
                        <span>Master Kursus: <strong class="text-white">{{ $event->course?->name ?? '-' }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap shrink-0">
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.edit', $event->id) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-white/15 hover:bg-white/25 border border-white/20 rounded-md transition-colors shadow-2xs">
                        <i class="fas fa-pen text-xs"></i>
                        <span>Edit Event &amp; Jadwal</span>
                    </a>
                </div>
            </div>

            {{-- Info Linimasa & Kapasitas --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Linimasa Keseluruhan Program</span>
                        @if ($totalDays > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-white/15 border border-white/20 text-white">
                                {{ $totalDays }} Hari
                            </span>
                        @endif
                    </div>
                    <p class="text-base sm:text-xl font-extrabold text-white">
                        {{ $event->overall_timeline_formatted }}
                    </p>
                </div>

                <div class="w-full md:w-72 space-y-1.5">
                    @if ($isUnlimitedQuota)
                        <div class="flex items-center justify-between text-xs sm:text-sm text-slate-200">
                            <span>Kapasitas Pendaftaran</span>
                            <span class="font-bold text-amber-300">Tanpa Batas</span>
                        </div>
                        <div class="px-3 py-1.5 rounded-md bg-white/10 border border-white/15 text-xs sm:text-sm text-white flex items-center justify-between">
                            <span class="text-slate-200">Peserta Terdaftar:</span>
                            <strong class="font-bold text-white">{{ $totalRegistered }} Peserta</strong>
                        </div>
                    @else
                        <div class="flex justify-between text-xs sm:text-sm text-slate-200">
                            <span>Kuota ({{ $totalRegistered }} / {{ $event->quota }} Peserta)</span>
                            <span class="font-bold text-amber-300">{{ $quotaPercent }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-400 h-2 rounded-full transition-all" style="width: {{ $quotaPercent }}%"></div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Navigasi 4 Tab Berurutan Desain Card Panah --}}
            <div class="border-t border-white/15 pt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-1.5">
                @php
                    $tabs = [
                        [
                            'key'        => 'overview',
                            'num'        => '1',
                            'label'      => 'Ringkasan & Jadwal',
                            'sub'        => 'Linimasa 3 Tahap Event',
                            'badge'      => null,
                            'arrowClass' => 'timeline-arrow-start rounded-md lg:rounded-none lg:rounded-l-md',
                        ],
                        [
                            'key'        => 'attendance',
                            'num'        => '2',
                            'label'      => 'Absensi Harian',
                            'sub'        => "{$totalAgendaDays} Hari Agenda Zoom",
                            'badge'      => null,
                            'arrowClass' => 'timeline-arrow-mid rounded-md lg:rounded-none',
                        ],
                        [
                            'key'        => 'evaluation',
                            'num'        => '3',
                            'label'      => 'Penilaian Evaluasi',
                            'sub'        => $pendingEvalTotal > 0 ? "{$pendingEvalTotal} Menunggu Dinilai" : 'Harian & Evaluasi Akhir',
                            'badge'      => $pendingEvalTotal > 0 ? ['text' => $pendingEvalTotal, 'cls' => 'bg-amber-500 text-white'] : null,
                            'arrowClass' => 'timeline-arrow-mid rounded-md lg:rounded-none',
                        ],
                        [
                            'key'        => 'graduation',
                            'num'        => '4',
                            'label'      => 'Kelulusan & PPSDM',
                            'sub'        => "{$totalCompleted} Tuntas • {$totalCertified} Terbit",
                            'badge'      => $readyForPpsdmCount > 0 ? ['text' => $readyForPpsdmCount, 'cls' => 'bg-emerald-500 text-white'] : null,
                            'arrowClass' => 'timeline-arrow-end rounded-md lg:rounded-none lg:rounded-r-md',
                        ],
                    ];
                @endphp

                @foreach ($tabs as $t)
                    @php $isCurrent = $activeTab === $t['key']; @endphp
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => $t['key']]) }}"
                       class="{{ $t['arrowClass'] }} flex items-center gap-3 py-3 px-4 transition-colors {{ $isCurrent ? 'bg-white text-slate-900 shadow-xs' : 'bg-white/10 hover:bg-white/20 text-white' }}">
                        <span class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center shrink-0 {{ $isCurrent ? 'bg-[#13416B] text-white' : 'bg-white/15 text-white' }}">
                            {{ $t['num'] }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1.5">
                                <span class="text-sm font-bold truncate {{ $isCurrent ? 'text-slate-900' : 'text-white' }}">
                                    {{ $t['label'] }}
                                </span>
                                @if ($t['badge'])
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold leading-none shrink-0 {{ $t['badge']['cls'] }}">
                                        {{ $t['badge']['text'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs truncate mt-0.5 {{ $isCurrent ? 'text-slate-500' : 'text-blue-100/85' }}">
                                {{ $t['sub'] }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Konten Tab Aktif --}}
        @if ($activeTab === 'overview')
            @include('lms::admin-pusat.bimtek-events.partials.tab-overview')
        @elseif ($activeTab === 'attendance')
            @include('lms::admin-pusat.bimtek-events.partials.tab-attendance')
        @elseif ($activeTab === 'evaluation')
            @include('lms::admin-pusat.bimtek-events.partials.tab-evaluation')
        @elseif ($activeTab === 'graduation')
            @include('lms::admin-pusat.bimtek-events.partials.tab-graduation')
        @endif

        {{-- Pop-up Notifikasi Penilaian (Otomatis / Manual / Publikasi) --}}
        @if (session('grading_popup'))
            @php $popup = session('grading_popup'); @endphp
            <div x-data="{ open: true }"
                 x-show="open"
                 x-cloak
                 @keydown.escape.window="open = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div class="fixed inset-0" @click="open = false"></div>

                <div class="relative z-10 w-full max-w-md bg-white rounded-md border border-slate-200 shadow-2xl overflow-hidden">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                    {{ $popup['method'] ?? 'Penilaian Selesai' }}
                                </p>
                                <h3 class="text-lg font-extrabold text-slate-900 mt-0.5 leading-snug">
                                    {{ $popup['title'] ?? 'Berhasil Menilai' }}
                                </h3>
                            </div>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div class="p-3.5 rounded-md bg-slate-50 border border-slate-200 space-y-1.5 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Peserta</span>
                                <span class="font-bold text-slate-900">{{ $popup['student_name'] ?? '-' }}</span>
                            </div>
                            @if (!is_null($popup['score'] ?? null))
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-slate-500">Nilai Akhir</span>
                                    <span class="font-extrabold {{ !empty($popup['is_passed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $popup['score'] }} ({{ !empty($popup['is_passed']) ? 'Lulus' : 'Perlu Revisi' }})
                                    </span>
                                </div>
                            @elseif (!empty($popup['summary']))
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-slate-500">Status</span>
                                    <span class="font-bold text-slate-800">{{ $popup['summary'] }}</span>
                                </div>
                            @endif
                        </div>

                        @if (!empty($popup['message']))
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-emerald-50/60 border border-emerald-200/80 rounded-md p-3">
                                {{ $popup['message'] }}
                            </p>
                        @endif

                        <div class="pt-1 flex justify-end">
                            <button type="button"
                                    @click="open = false"
                                    class="px-5 py-2.5 rounded-md text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-2xs cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-dashboard::layouts.dashboard>
