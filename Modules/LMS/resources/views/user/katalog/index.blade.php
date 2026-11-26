<x-dashboard::layouts.dashboard title="Katalog Kursus | SIRENATA">
    
    {{-- Impor Font Kalam dan Oswald dari Google Fonts --}}
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    @endpush

    <div class="p-0 sm:p-6 lg:p-8 bg-slate-50 min-h-screen">
        
        {{-- ========================================== --}}
        {{-- HEADER KATALOG DENGAN ILUSTRASI GRUP       --}}
        {{-- ========================================== --}}
        <div class="relative bg-[#13416B] rounded-md px-5 py-7 sm:p-8 lg:p-10 mb-6 sm:mb-8 flex items-center justify-between border border-blue-900/20 shadow-lg overflow-hidden min-h-[200px] sm:min-h-[280px] lg:min-h-[320px]">
            
            <!-- Efek Dekoratif Bubbles Geometris Profesional -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="absolute -top-24 -right-16 w-80 h-80 border-[30px] border-white/5 rounded-full"></div>
                <div class="absolute -bottom-20 right-[10%] w-64 h-64 bg-white/5 rounded-full"></div>
                <div class="absolute top-[15%] right-[38%] w-24 h-24 border-[8px] border-amber-400/20 rounded-full"></div>
                <div class="absolute bottom-[30%] right-[45%] w-8 h-8 bg-blue-400/20 rounded-full"></div>
                <div class="absolute top-[20%] left-[45%] w-12 h-12 bg-white/5 rounded-full"></div>
                <div class="absolute left-0 top-0 w-2/3 h-full bg-gradient-to-r from-[#13416B] via-[#13416B]/80 to-transparent z-10"></div>
            </div>

            <!-- Sisi Kiri: Teks Utama -->
            <div class="relative z-20 w-full sm:w-[60%] lg:w-[60%] text-left">
                
                {{-- BADGE: MENGGUNAKAN FONT KALAM --}}
                <span class="inline-flex items-center gap-2 px-2.5 py-0.5 sm:px-3 sm:py-1 bg-white/10 backdrop-blur-md rounded-md text-xs sm:text-base tracking-wider text-white border border-white/10 mb-2 sm:mb-4 shadow-sm" style="font-family: 'Kalam', cursive;">
                     Eksplorasi Kompetensi
                </span>
                
                {{-- JUDUL: MENGGUNAKAN FONT OSWALD --}}
                <h1 class="text-xl sm:text-3xl md:text-4xl lg:text-5xl text-white mb-2 sm:mb-4" style="font-family: 'Oswald', sans-serif;">
                    Katalog Pembelajaran
                </h1>
                
                <p class="text-xs sm:text-sm md:text-base text-blue-100/90 leading-relaxed max-w-xl lg:max-w-2xl font-medium">
                    Temukan dan pelajari berbagai kursus pengembangan kompetensi yang dirancang khusus untuk meningkatkan keterampilan dan profesionalitas Anda.
                </p>

            </div>

            <!-- Sisi Kanan: Ilustrasi Pegawai Kemnaker -->
            <div class="hidden sm:flex absolute bottom-0 right-0 lg:right-5 z-10 w-[45%] lg:w-[35%] h-[90%] lg:h-[95%] pointer-events-none justify-end items-end">
                <img src="{{ asset('images/catalog_illustration.webp') }}" 
                     alt="Pegawai Kemnaker" 
                     class="w-full h-full object-contain object-bottom drop-shadow-[0_15px_25px_rgba(0,0,0,0.3)] relative z-20"
                     onerror="this.style.display='none'">
            </div>
        </div>

       
        <div>
            {{-- TAB FILTER STATUS GELOMBANG (SELARAS DENGAN KURSUS SAYA) --}}
            @php
                $activeWaveStatus = $waveStatus ?? request('status', 'all');
                if (!in_array($activeWaveStatus, ['all', 'open_registration', 'ongoing'], true)) {
                    $activeWaveStatus = 'all';
                }
                $statusOptions = [
                    'all' => [
                        'label' => 'Semua',
                        'url'   => route('user.catalog.index', request()->except(['page', 'status', 'type'])),
                    ],
                    'open_registration' => [
                        'label' => 'Pendaftaran Buka',
                        'url'   => route('user.catalog.index', array_merge(request()->except(['page', 'type']), ['status' => 'open_registration'])),
                    ],
                    'ongoing' => [
                        'label' => 'Sedang Berlangsung',
                        'url'   => route('user.catalog.index', array_merge(request()->except(['page', 'type']), ['status' => 'ongoing'])),
                    ],
                ];
            @endphp

            <div class="mb-6 sm:mb-8 border-b border-slate-200 dark:border-slate-700/80">
                <nav class="flex space-x-6 overflow-x-auto -mb-px">
                    @foreach ($statusOptions as $key => $opt)
                        <a href="{{ $opt['url'] }}"
                           class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors {{ $activeWaveStatus === $key ? 'border-[#13416B] text-[#13416B] dark:border-white dark:text-white font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 font-medium' }}">
                            <span>{{ $opt['label'] }}{{ $activeWaveStatus === $key ? ' (' . $courses->total() . ')' : '' }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- ========================================== --}}
            {{-- GRID KURSUS + MODAL KONFIRMASI PENDAFTARAN --}}
            {{-- ========================================== --}}
            @php
                // Payload ringan untuk modal (tanpa query tambahan per kartu)
                $coursePayload = $courses->mapWithKeys(function ($c) use ($enrolledCourseIds, $userBimtekEventIds) {
                    $sections = $c->sections->values()->map(function ($s, $i) {
                        $rawName = (string) ($s->name ?? $s->title ?? '');
                        $cleanName = trim((string) preg_replace('/^(modul|hari)\s*(ke[\-\s]*)?\d+\s*[:\-–]\s*/i', '', $rawName));

                        return [
                            'name'     => $cleanName !== '' ? $cleanName : ($rawName !== '' ? $rawName : 'Bagian ' . ($i + 1)),
                            'contents' => $s->contents->values()->map(fn($ct, $idx) => [
                                'letter' => chr(97 + ($idx % 26)),
                                'name'   => $ct->name,
                                'type'   => $ct->video ? 'video' : ($ct->document ? 'document' : 'text'),
                            ])->values(),
                        ];
                    });

                    $activeB = $c->bimtekEvents->first();
                    $isEnrolledInCourse = in_array($c->id, $enrolledCourseIds);
                    $isEnrolledInThisBimtek = $activeB && in_array($activeB->id, $userBimtekEventIds);
                    $curPhase = $activeB?->current_timeline_phase ?? [];
                    $phaseKey = $curPhase['phase'] ?? ($activeB?->status === 'open_registration' ? 'registration' : 'learning');
                    $isRegistrationOpen = ($phaseKey === 'registration' && $activeB?->status === 'open_registration');

                    return [$c->id => [
                        'name'                   => $c->name,
                        'slug'                   => $c->slug,
                        'myCourseUrl'            => route('user.course.my-course.detail', $c->slug),
                        'description'            => $c->description,
                        'students'               => $c->students_count,
                        'modules'                => $c->sections_count,
                        'topics'                 => $sections->sum(fn($s) => count($s['contents'])),
                        'thumbnail'              => ! empty($c->thumbnail)
                            ? (str_starts_with($c->thumbnail, 'http') ? $c->thumbnail : asset('storage/' . $c->thumbnail))
                            : null,
                        'audienceLabel'          => ($c->audience ?? 'instansi') === 'swasta' ? 'Swasta' : 'Instansi',
                        'sections'               => $sections,
                        'enrollUrl'              => route('user.catalog.enroll', $c->slug),
                        'isEvent'                => (bool) $activeB,
                        'isRegistrationOpen'     => $isRegistrationOpen,
                        'bimtekId'               => $activeB?->id,
                        'bimtekBatchName'        => $activeB?->formatted_wave_subtitle,
                        'bimtekBatchCode'        => $activeB?->batch_code,
                        'bimtekStatus'           => $activeB?->status,
                        'bimtekStatusBadge'      => $activeB?->status_badge,
                        'bimtekExecutionType'    => $activeB?->execution_type,
                        'bimtekDates'            => $activeB ? ($activeB->start_date ? $activeB->start_date->format('d M') : '-') . ' s/d ' . ($activeB->end_date ? $activeB->end_date->format('d M Y') : '-') : null,
                        'bimtekTime'             => $activeB?->start_date ? $activeB->start_date->format('H:i') . ' WIB' : '08:00 WIB',
                        'bimtekQuota'            => (int) ($activeB?->quota ?? 0),
                        'bimtekParticipants'     => (int) ($activeB?->participants_count ?? 0),
                        'bimtekQuotaPct'         => ($activeB?->quota ?? 0) > 0 ? min(100, (int) round((($activeB?->participants_count ?? 0) / $activeB->quota) * 100)) : 0,
                        'timelineSteps'          => $activeB ? $activeB->timeline_steps : [],
                        'currentTimelinePhase'   => $activeB ? $activeB->current_timeline_phase : null,
                        'isEnrolledInCourse'     => $isEnrolledInCourse,
                        'isEnrolledInThisBimtek'  => $isEnrolledInThisBimtek,
                    ]];
                });
            @endphp

            <div
                x-data="{
                    courses: @js($coursePayload),
                    selected: null,
                    open: false,          // modal lihat kurikulum
                    confirmOpen: false,   // alert konfirmasi daftar
                    submitting: false,
                    activeTab: 'timeline',
                    expandedSections: [0],
                    show(id) {
                        this.selected = this.courses[id];
                        this.activeTab = (this.selected && this.selected.isEvent && this.selected.timelineSteps && this.selected.timelineSteps.length > 0) ? 'timeline' : 'curriculum';
                        this.expandedSections = (this.selected && this.selected.sections) ? this.selected.sections.map((_, i) => i) : [];
                        this.submitting = false;
                        this.open = true;
                        this.$nextTick(() => {
                            if (this.$refs.modalBody) this.$refs.modalBody.scrollTop = 0;
                        });
                    },
                    setTab(tab) {
                        this.activeTab = tab;
                        this.$nextTick(() => {
                            if (this.$refs.modalBody) this.$refs.modalBody.scrollTop = 0;
                        });
                    },
                    askEnroll(id) {
                        const c = this.courses[id];
                        if (c && c.isEvent && (!c.isRegistrationOpen || c.bimtekStatus !== 'open_registration') && !c.isEnrolledInThisBimtek) {
                            return;
                        }
                        this.selected = c;
                        this.activeTab = (c && c.isEvent && c.timelineSteps && c.timelineSteps.length > 0) ? 'timeline' : 'curriculum';
                        this.expandedSections = (c && c.sections) ? c.sections.map((_, i) => i) : [];
                        this.submitting = false;
                        this.confirmOpen = true;
                    },
                    close() {
                        if (this.submitting) return;
                        this.open = false;
                    },
                    cancelConfirm() {
                        if (this.submitting) return;
                        this.confirmOpen = false;
                    },
                    onEscape() {
                        this.confirmOpen ? this.cancelConfirm() : this.close();
                    },
                    isExpanded(i) {
                        return this.expandedSections.includes(i);
                    },
                    toggleSection(i) {
                        if (this.isExpanded(i)) {
                            this.expandedSections = this.expandedSections.filter(idx => idx !== i);
                        } else {
                            this.expandedSections.push(i);
                        }
                    },
                    topicLetter(index) {
                        const letters = 'abcdefghijklmnopqrstuvwxyz';
                        return letters[Number(index) % 26] ?? String(Number(index) + 1);
                    }
                }"
                x-effect="document.body.classList.toggle('overflow-hidden', open || confirmOpen)"
                @keydown.escape.window="onEscape()"
            >
                @if ($courses->isNotEmpty())
                    <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-y-5 gap-x-3 sm:gap-6 items-stretch">
                        @foreach ($courses as $course)
                            @include('lms::user.katalog.partials.course-card', ['course' => $course])
                        @endforeach
                    </div>
                @else
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white rounded-md border border-dashed border-slate-200">
                        <div class="w-16 h-16 bg-[#13416B]/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-[#13416B]/10 text-[#13416B]/40">
                            <i class="fas fa-box-open text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Belum ada gelombang pelatihan yang sesuai</h3>
                        <p class="text-sm text-slate-500 max-w-sm mb-4">Kamu sudah terdaftar di seluruh gelombang yang tersedia, atau coba pilih status gelombang lain.</p>
                        @if(($waveStatus ?? request('status', 'all')) !== 'all' || request('search'))
                            <a href="{{ route('user.catalog.index') }}" class="text-xs font-bold text-[#13416B] hover:underline">Tampilkan semua gelombang</a>
                        @endif
                    </div>
                @endif

                {{-- ========== MODAL: RINGKASAN KURIKULUM + KONFIRMASI ========== --}}
                <div x-show="open" x-cloak style="display: none;"
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                     role="dialog" aria-modal="true" aria-labelledby="enroll-title">

                    {{-- Backdrop --}}
                    <div x-show="open"
                         x-transition.opacity.duration.200ms
                         @click="close()"
                         class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    {{-- Panel Modal (bottom-sheet di mobile, dialog di desktop) --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95"
                         class="relative w-full sm:max-w-xl md:max-w-2xl max-h-[90dvh] sm:max-h-[85vh] flex flex-col bg-white rounded-t-md sm:rounded-md shadow-2xl ring-1 ring-slate-900/10 overflow-hidden">

                        <template x-if="selected">
                            <div class="flex flex-col min-h-0 flex-1">

                                {{-- Header Modal Biru: Drag Handle, Nama Event, Nama Kursus, Deskripsi & Close --}}
                                <div class="bg-[#13416B] text-white shrink-0">
                                    {{-- Drag handle bar untuk mobile --}}
                                    <div class="sm:hidden pt-2.5 pb-1 flex justify-center">
                                        <div class="w-10 h-1 bg-white/30 rounded-full"></div>
                                    </div>

                                    <div class="px-5 sm:px-6 pt-4 sm:pt-5 pb-7 sm:pb-8">
                                        <div class="flex items-start justify-between gap-3.5 sm:gap-4">
                                            <div class="flex-1 min-w-0">
                                                <template x-if="selected.isEvent && selected.bimtekBatchName">
                                                    <div class="flex items-center gap-2 text-xs sm:text-sm mb-1.5">
                                                        <span class="font-semibold text-sky-200 tracking-wide truncate" :title="selected.bimtekBatchName" x-text="selected.bimtekBatchName"></span>
                                                    </div>
                                                </template>

                                                <h3 id="enroll-title" class="text-lg sm:text-xl font-bold text-white leading-snug line-clamp-2"
                                                    :title="selected.name" x-text="selected.name"></h3>

                                                <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed mt-1.5 line-clamp-2"
                                                   x-show="selected.description" x-text="selected.description"></p>
                                            </div>
                                            <button type="button" @click="close()" aria-label="Tutup"
                                                    class="shrink-0 w-8 h-8 -mr-1 rounded-md flex items-center justify-center text-white/75 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
                                                <i class="fas fa-times text-base"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tab Navigasi Konteks: Timeline & Jadwal vs Struktur Kurikulum --}}
                                <template x-if="selected.isEvent && selected.timelineSteps && selected.timelineSteps.length > 0">
                                    <div class="bg-white border-b border-slate-200 px-5 sm:px-6 pt-1.5 sm:pt-2 shrink-0" role="tablist">
                                        <div class="flex items-center gap-5 sm:gap-6 -mb-px">
                                            <button type="button"
                                                    role="tab"
                                                    :aria-selected="activeTab === 'timeline'"
                                                    @click="setTab('timeline')"
                                                    class="relative py-3 sm:py-3.5 text-xs sm:text-sm border-b-2 transition-colors inline-flex items-center gap-2 cursor-pointer"
                                                    :class="activeTab === 'timeline'
                                                        ? 'border-[#13416B] text-[#13416B] font-bold'
                                                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'">
                                                <i class="far fa-calendar-alt text-xs"></i>
                                                <span>Timeline &amp; Jadwal</span>
                                            </button>

                                            <button type="button"
                                                    role="tab"
                                                    :aria-selected="activeTab === 'curriculum'"
                                                    @click="setTab('curriculum')"
                                                    class="relative py-3 sm:py-3.5 text-xs sm:text-sm border-b-2 transition-colors inline-flex items-center gap-2 cursor-pointer"
                                                    :class="activeTab === 'curriculum'
                                                        ? 'border-[#13416B] text-[#13416B] font-bold'
                                                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'">
                                                <i class="fas fa-list-ul text-xs"></i>
                                                <span>Struktur Kurikulum</span>
                                                <span class="px-1.5 py-0.5 rounded-md text-[11px] font-semibold transition-colors"
                                                      :class="activeTab === 'curriculum'
                                                          ? 'bg-[#13416B]/10 text-[#13416B]'
                                                          : 'bg-slate-100 text-slate-500'"
                                                      x-text="(selected.topics || 0) + ' materi'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Body: Detail Kursus & Kurikulum (Scrollable, 1 Konteks per Tab) --}}
                                <div x-ref="modalBody" class="flex-1 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5 custom-scrollbar bg-slate-50/40">

                                    {{-- TAB 1: TIMELINE & JADWAL --}}
                                    <template x-if="selected && selected.isEvent && selected.timelineSteps && activeTab === 'timeline'">
                                        <div class="space-y-4">
                                            {{-- Banner Notifikasi Jika Pendaftaran Gelombang Ini Ditutup (Disuruh Ikut Batch Berikutnya) --}}
                                            <template x-if="!selected.isEnrolledInThisBimtek && (!selected.isRegistrationOpen || selected.bimtekStatus !== 'open_registration')">
                                                <div class="p-3.5 sm:p-4 rounded-md bg-amber-50 border border-amber-200/90 flex items-start gap-3 text-amber-900 shadow-2xs">
                                                    <div class="w-8 h-8 rounded-md bg-amber-100/80 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                                                        <i class="fas fa-calendar-times text-sm"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h5 class="text-sm sm:text-base font-bold text-amber-900 mb-0.5">Pendaftaran Gelombang Ini Telah Ditutup</h5>
                                                        <p class="text-xs sm:text-sm text-amber-800/90 leading-relaxed">
                                                            Kursus untuk gelombang ini sedang berlangsung atau masa pendaftaran telah berakhir. Anda dapat mengikuti kursus pada <strong>gelombang berikutnya</strong>.
                                                        </p>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Stepper 3 Tahap Beralur --}}
                                            <div class="relative pl-9 sm:pl-10 space-y-3.5 sm:space-y-4 before:absolute before:left-3 sm:before:left-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                                <template x-for="(step, idx) in selected.timelineSteps" :key="idx">
                                                    <div class="relative">
                                                        {{-- Dot / Icon Step --}}
                                                        <div class="absolute -left-9 sm:-left-10 top-3.5 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-all shadow-2xs"
                                                             :class="{
                                                                 'bg-emerald-600 text-white ring-3 ring-emerald-50': step.status === 'completed',
                                                                 'bg-[#0B5CFF] text-white ring-3 ring-blue-100': step.status === 'active',
                                                                 'bg-white text-slate-400 border-2 border-slate-300': step.status === 'upcoming'
                                                             }">
                                                            <template x-if="step.status === 'completed'">
                                                                <i class="fas fa-check text-2xs sm:text-xs"></i>
                                                            </template>
                                                            <template x-if="step.status !== 'completed'">
                                                                <span x-text="step.step"></span>
                                                            </template>
                                                        </div>

                                                        {{-- Card Timeline Beralur --}}
                                                        <div class="p-3.5 sm:p-4 rounded-md border transition-all"
                                                             :class="{
                                                                 'bg-white border-[#0B5CFF]/35 shadow-xs': step.status === 'active',
                                                                 'bg-slate-50/80 border-slate-200/80': step.status === 'completed',
                                                                 'bg-white border-slate-200/70': step.status === 'upcoming'
                                                             }">
                                                            <div class="font-bold text-base sm:text-[17px] text-slate-800 leading-snug" x-text="step.title"></div>
                                                            <ul class="mt-2.5 space-y-1.5 list-disc pl-4 text-xs sm:text-sm text-slate-600 leading-relaxed marker:text-slate-400">
                                                                <template x-if="step.step === 1">
                                                                    <li class="contents">
                                                                        <ul class="space-y-1.5 list-disc">
                                                                            <li>Pendaftaran peserta dibuka pada <span class="font-semibold text-slate-800" x-text="step.date_range"></span>.</li>
                                                                            <li>
                                                                                <template x-if="selected.isRegistrationOpen">
                                                                                    <span>
                                                                                        <template x-if="selected.bimtekQuota > 0">
                                                                                            <span>Saat ini sudah <strong class="font-bold text-slate-800" x-text="selected.bimtekParticipants"></strong> dari kuota <strong class="font-bold text-slate-800" x-text="selected.bimtekQuota"></strong> peserta yang bergabung.</span>
                                                                                        </template>
                                                                                        <template x-if="selected.bimtekQuota <= 0">
                                                                                            <span>Terbuka tanpa batasan kuota (<strong class="font-bold text-slate-800" x-text="selected.bimtekParticipants"></strong> peserta telah bergabung).</span>
                                                                                        </template>
                                                                                    </span>
                                                                                </template>
                                                                                <template x-if="!selected.isRegistrationOpen">
                                                                                    <span x-text="selected.bimtekQuota > 0 ? 'Kuota pelatihan dibatasi untuk ' + selected.bimtekQuota + ' peserta.' : 'Terbuka bagi seluruh peserta tanpa batasan kuota.'"></span>
                                                                                </template>
                                                                            </li>
                                                                        </ul>
                                                                    </li>
                                                                </template>
                                                                <template x-if="step.step === 2">
                                                                    <li class="contents">
                                                                        <ul class="space-y-1.5 list-disc">
                                                                            <li>Pembelajaran mandiri berlangsung pada <span class="font-semibold text-slate-800" x-text="step.date_range"></span>.</li>
                                                                            <template x-if="step.zoom_time">
                                                                                <li>Sesi tatap muka daring dijadwalkan pada <span class="font-semibold text-[#13416B]" x-text="step.zoom_time"></span>.</li>
                                                                            </template>
                                                                        </ul>
                                                                    </li>
                                                                </template>
                                                                <template x-if="step.step === 3">
                                                                    <li class="contents">
                                                                        <ul class="space-y-1.5 list-disc">
                                                                            <li>Pengerjaan evaluasi akhir berlangsung pada <span class="font-semibold text-slate-800" x-text="step.date_range"></span>.</li>
                                                                            <li>Sertifikat diterbitkan setelah seluruh kegiatan harian dan evaluasi dituntaskan.</li>
                                                                        </ul>
                                                                    </li>
                                                                </template>
                                                            </ul>
                                                            <template x-if="step.step === 1 && selected.isRegistrationOpen && selected.bimtekQuota > 0">
                                                                <div class="mt-3 pt-2.5 border-t border-slate-100">
                                                                    <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                                                        <span class="font-semibold text-slate-600">
                                                                            Keterisian Kuota (<span x-text="selected.bimtekParticipants + '/' + selected.bimtekQuota + ' Peserta'"></span>)
                                                                        </span>
                                                                        <span class="font-bold"
                                                                              :class="selected.bimtekQuotaPct >= 85 ? 'text-amber-600' : 'text-[#0B5CFF]'"
                                                                              x-text="selected.bimtekQuotaPct + '%'"></span>
                                                                    </div>
                                                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                                        <div class="h-full rounded-full transition-all duration-300"
                                                                             :class="selected.bimtekQuotaPct >= 85 ? 'bg-amber-500' : 'bg-[#0B5CFF]'"
                                                                             :style="'width: ' + selected.bimtekQuotaPct + '%'"></div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- TAB 2: STRUKTUR KURIKULUM --}}
                                    <template x-if="activeTab === 'curriculum' || !(selected && selected.isEvent && selected.timelineSteps && selected.timelineSteps.length > 0)">
                                        <div>
                                            <div class="space-y-2.5">
                                                <template x-for="(section, i) in selected.sections" :key="i">
                                                    <div class="bg-white rounded-md border transition-all duration-200 overflow-hidden"
                                                         :class="isExpanded(i) ? 'border-slate-300 shadow-xs' : 'border-slate-200/80 hover:border-slate-300'">

                                                        <button type="button"
                                                                @click="toggleSection(i)"
                                                                class="w-full flex items-center gap-3 px-3.5 sm:px-4 py-3 text-left transition-colors cursor-pointer select-none"
                                                                :class="isExpanded(i) ? 'bg-slate-50/70' : 'hover:bg-slate-50/40'"
                                                                :aria-expanded="isExpanded(i)">

                                                            <span class="shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-full text-xs sm:text-sm font-semibold flex items-center justify-center transition-colors"
                                                                  :class="isExpanded(i) ? 'bg-[#0B5CFF] text-white shadow-xs' : 'bg-slate-100 text-slate-600'"
                                                                  x-text="i + 1"></span>

                                                            <span class="flex-1 min-w-0 text-sm sm:text-base font-semibold text-slate-800 leading-snug line-clamp-2 wrap-break-word"
                                                                  :title="section.name"
                                                                  x-text="section.name"></span>

                                                            <div class="shrink-0 flex items-center gap-2">
                                                                <span class="text-xs sm:text-[13px] font-medium text-slate-500"
                                                                      x-text="section.contents.length + ' materi'"></span>
                                                                <i class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                                                                   :class="{ 'rotate-180 text-slate-600': isExpanded(i) }"></i>
                                                            </div>
                                                        </button>

                                                        <div x-show="isExpanded(i)"
                                                             x-collapse
                                                             x-cloak
                                                             class="border-t border-slate-100 divide-y divide-slate-100 bg-slate-50/50">

                                                            <!-- Daftar Materi Konten -->
                                                            <template x-for="(content, j) in section.contents" :key="j">
                                                                <div class="flex items-start gap-2 pl-9 sm:pl-12 pr-3.5 sm:pr-4 py-2.5 text-xs sm:text-sm">
                                                                    <span class="font-semibold text-slate-500 shrink-0 leading-snug"
                                                                          x-text="(content.letter || topicLetter(j)) + '.'">
                                                                    </span>

                                                                    <span class="flex-1 min-w-0 font-medium text-slate-700 leading-snug line-clamp-2 wrap-break-word"
                                                                          :title="content.name"
                                                                          x-text="content.name"></span>
                                                                </div>
                                                            </template>

                                                            <div x-show="section.contents.length === 0" class="py-3 text-center text-xs sm:text-sm italic text-slate-400">
                                                                Belum ada topik materi pada bagian ini.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="selected.sections.length === 0" class="py-10 text-center bg-white rounded-md border border-dashed border-slate-200">
                                                    <div class="w-11 h-11 mx-auto mb-2.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                                        <i class="fas fa-book-open text-base"></i>
                                                    </div>
                                                    <p class="text-sm sm:text-base font-semibold text-slate-700">Kurikulum Belum Tersedia</p>
                                                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Materi untuk kursus ini sedang dalam tahap penyusunan.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Footer Modal: Aksi --}}
                                <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-white shrink-0">
                                    {{-- KONDISI 1: SEDANG BERLANGSUNG / MENUNGGU BATCH BERIKUTNYA --}}
                                    <template x-if="selected.isEvent && !selected.isEnrolledInThisBimtek && (!selected.isRegistrationOpen || selected.bimtekStatus !== 'open_registration')">
                                        <div class="flex items-center justify-end w-full">
                                            <button type="button" disabled
                                                    class="px-4 py-2 sm:py-2.5 text-xs sm:text-[15px] font-semibold rounded-md text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed inline-flex items-center justify-center gap-1.5 shadow-none">
                                                <i class="fas fa-lock text-[10px] sm:text-xs"></i>
                                                <span class="truncate">Pendaftaran Ditutup</span>
                                            </button>
                                        </div>
                                    </template>

                                    {{-- KONDISI 2: SUDAH TERDAFTAR --}}
                                    <template x-if="selected.isEvent && selected.isEnrolledInThisBimtek">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                            <div class="text-xs sm:text-[15px] text-emerald-700 font-semibold text-left flex items-center justify-start gap-1.5">
                                                <i class="fas fa-check-circle text-emerald-600"></i>
                                                <span>Anda telah terdaftar pada gelombang ini.</span>
                                            </div>
                                            <div class="flex items-center justify-end w-full sm:w-auto shrink-0">
                                                <a :href="selected.myCourseUrl"
                                                   class="px-4 sm:px-5 py-2 sm:py-2.5 text-xs sm:text-[15px] font-semibold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-center">
                                                    <span>Masuk Pelatihan</span>
                                                    <i class="fas fa-arrow-up-right-from-square text-[10px] sm:text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- KONDISI 3: BISA DAFTAR (OPEN REGISTRATION / MANDIRI) --}}
                                    <template x-if="(!selected.isEvent) || (selected.isEvent && !selected.isEnrolledInThisBimtek && selected.isRegistrationOpen && selected.bimtekStatus === 'open_registration')">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                            <p class="text-xs sm:text-[15px] text-slate-600 font-medium text-left">
                                                <span x-text="selected.isEvent ? 'Tertarik mengikuti pelatihan ini? Pendaftaran masih dibuka.' : 'Apakah Anda ingin mendaftar kursus ini?'"></span>
                                            </p>
                                            <div class="flex items-center justify-end w-full sm:w-auto shrink-0">
                                                <button type="button" @click="confirmOpen = true"
                                                        class="px-5 py-2 sm:py-2.5 text-xs sm:text-[15px] font-semibold rounded-md text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-center cursor-pointer">
                                                    <span>Daftar</span>
                                                    <i class="fas fa-arrow-up-right-from-square text-[10px] sm:text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ========== ALERT KONFIRMASI DAFTAR ========== --}}
                <div x-show="confirmOpen" x-cloak style="display: none;"
                     class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-4"
                     role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">

                    <div x-show="confirmOpen"
                         x-transition.opacity.duration.200ms
                         @click="cancelConfirm()"
                         class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <div x-show="confirmOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-sm sm:max-w-md max-h-[90dvh] overflow-y-auto bg-white rounded-md shadow-2xl p-4 sm:p-6 text-center">

                        {{-- KONDISI A: MODAL KONFIRMASI EVENT BIMTEK --}}
                        <template x-if="selected && selected.isEvent">
                            <div>
                                <div class="w-10 h-10 sm:w-11 sm:h-11 mx-auto mb-2.5 sm:mb-3 rounded-full bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center text-base sm:text-lg">
                                    <i class="fas fa-calendar-check"></i>
                                </div>

                                <h3 id="confirm-title" class="text-base sm:text-lg font-extrabold text-slate-800 mb-1">
                                    Daftar ke Pelatihan Ini?
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-3 sm:mb-4">
                                    Anda akan mendaftar ke gelombang pelatihan daring berikut ini.
                                </p>

                                {{-- Kartu Ringkasan Tiket Event --}}
                                <div class="p-3 sm:p-3.5 rounded-md bg-slate-50 border border-slate-200 text-left text-xs sm:text-sm space-y-2 mb-3">
                                    <div>
                                        <span class="text-[11px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Materi Program</span>
                                        <p class="font-bold text-slate-900 mt-0.5 line-clamp-2 leading-snug" :title="selected.name" x-text="selected.name"></p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80">
                                        <div>
                                            <span class="text-[11px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Gelombang</span>
                                            <p class="font-bold text-[#13416B] mt-0.5 line-clamp-2 leading-snug" :title="selected.bimtekBatchName" x-text="selected.bimtekBatchName"></p>
                                        </div>
                                        <div>
                                            <span class="text-[11px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Kuota</span>
                                            <p class="font-bold text-slate-800 mt-0.5" x-text="selected.bimtekQuota > 0 ? selected.bimtekQuota + ' Peserta' : 'Tanpa Batasan'"></p>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/80">
                                        <span class="text-[11px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Jadwal Pelaksanaan</span>
                                        <p class="font-semibold text-slate-800 mt-0.5 leading-snug">
                                            <span x-text="selected.bimtekDates"></span> (<span x-text="selected.bimtekTime"></span>)
                                        </p>
                                    </div>
                                </div>

                                <div class="p-2.5 sm:p-3 rounded-md bg-emerald-50 border border-emerald-200/70 text-xs sm:text-sm text-emerald-800 text-left mb-3.5 sm:mb-4 flex items-start gap-2 leading-relaxed">
                                    <i class="fas fa-info-circle text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                                    <span>Pastikan Anda dapat mengikuti jadwal pelaksanaan dan menuntaskan seluruh materi.</span>
                                </div>
                            </div>
                        </template>

                        {{-- KONDISI B: MODAL KONFIRMASI KURSUS MANDIRI BIASA --}}
                        <template x-if="selected && !selected.isEvent">
                            <div>
                                <div class="w-10 h-10 sm:w-11 sm:h-11 mx-auto mb-2.5 sm:mb-3 rounded-full bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-base sm:text-lg">
                                    <i class="fas fa-question"></i>
                                </div>

                                <h3 id="confirm-title" class="text-base sm:text-lg font-bold text-slate-800 mb-1">Daftar ke kursus ini?</h3>
                                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                    Kamu akan mendaftar ke
                                    <span class="font-semibold text-slate-700" x-text="selected ? selected.name : ''"></span>
                                    dan kursus ini akan langsung masuk ke daftar Kursus Saya.
                                </p>
                            </div>
                        </template>

                        <template x-if="selected">
                            <form :action="selected.enrollUrl" method="POST" @submit="submitting = true">
                                @csrf
                                <input type="hidden" name="bimtek_event_id" :value="selected.bimtekId">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="cancelConfirm()" :disabled="submitting"
                                            class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-md text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors disabled:opacity-50 cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="submitting"
                                            class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-md text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-colors inline-flex items-center justify-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer">
                                        <svg x-show="submitting" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Mendaftarkan...' : 'Ya, Daftar'"></span>
                                    </button>
                                </div>
                            </form>
                        </template>

                        <div x-show="!open && !submitting" class="mt-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="confirmOpen = false; open = true"
                                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#13416B] hover:underline cursor-pointer">
                                <i class="fas fa-list-ul text-[11px] sm:text-xs"></i> Lihat detail dulu
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            @if ($courses->hasPages())
                <div class="mt-8 flex justify-center [&_nav]:text-sm">
                    {{ $courses->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard::layouts.dashboard>