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
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_1.5px,transparent_1.5px)] [background-size:24px_24px]"></div>
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
            {{-- HEADER DAFTAR KURSUS & FILTER --}}
            <div class="mb-5 sm:mb-6 border-b border-slate-200 pb-3.5 sm:pb-4">
                
                {{-- Baris Atas: Judul & Tombol Filter (Sejajar di semua resolusi) --}}
                <div class="flex flex-row items-center justify-between gap-3 sm:gap-4 {{ !empty($selectedCategories) ? 'mb-2.5 sm:mb-4' : 'mb-0' }}">
                    
                    {{-- Kiri: Teks (Diperkecil di mobile) --}}
                    <div class="flex flex-col gap-0.5 sm:gap-1">
                        <h2 class="text-base sm:text-xl font-extrabold text-slate-800 leading-tight">
                            Jelajahi Program Pelatihan
                        </h2>
                        <p class="text-[11px] sm:text-sm font-medium text-slate-500">
                            Ada <span class="text-[#13416B] font-bold">{{ $courses->total() }}</span> program yang siap dipelajari.
                        </p>
                    </div>

                    {{-- Kanan: Tombol Filter & Dropdown (Alpine.js) --}}
                    <div x-data="{ openFilter: false }" class="relative z-30 shrink-0">
                        <button @click="openFilter = !openFilter" 
                            class="flex items-center justify-center gap-1.5 sm:gap-2.5 px-3 py-1.5 sm:px-4 sm:py-2.5 bg-white border border-slate-300 rounded-md text-xs sm:text-sm font-semibold text-slate-700 hover:border-[#13416B]/50 hover:text-[#13416B] transition-all"
                            :class="{ 'border-[#13416B] text-[#13416B] ring-2 ring-[#13416B]/10': openFilter }">
                            
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span>Filter Kategori</span>
                            
                            @if(!empty($selectedCategories))
                                <span class="flex items-center justify-center w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-amber-400 text-[9px] sm:text-[10px] font-bold text-slate-900 ml-1">
                                    {{ count($selectedCategories) }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="openFilter" 
                             @click.away="openFilter = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute right-0 mt-2 w-[min(92vw,24rem)] bg-white rounded-md shadow-xl border border-slate-200 p-4 sm:p-5"
                             style="display: none;">
                            
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-slate-800">Kategori Modul</h3>
                                @if(!empty($selectedCategories))
                                    <a href="{{ route('user.catalog.index', ['type' => request('type')]) }}" class="text-[11px] font-bold text-red-500 hover:text-red-700 hover:underline">Reset</a>
                                @endif
                            </div>

                            <form action="{{ route('user.catalog.index') }}" method="GET" id="catalogCategoryForm">
                                @if(request('type'))
                                    <input type="hidden" name="type" value="{{ request('type') }}">
                                @endif
                                <div class="flex flex-wrap gap-2 max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                                    @forelse($categories as $category)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                                class="peer sr-only"
                                                onchange="document.getElementById('catalogCategoryForm').submit();"
                                                {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                            <span class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-full border border-slate-200 bg-white text-[11px] sm:text-xs font-semibold text-slate-600 transition-all
                                                         hover:border-[#13416B]/40 hover:bg-slate-50
                                                         peer-checked:border-[#13416B] peer-checked:bg-[#13416B] peer-checked:text-white
                                                         peer-checked:[&>span]:bg-white/20 peer-checked:[&>span]:text-white peer-checked:[&>span]:border-transparent
                                                         peer-focus-visible:ring-2 peer-focus-visible:ring-[#13416B]/30">
                                                {{ $category->name }}
                                                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                    {{ $category->courses_count }}
                                                </span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-slate-400 italic px-2">Tidak ada kategori tersedia.</p>
                                    @endforelse
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



                {{-- Chip Kategori Aktif --}}
                @if(!empty($selectedCategories))
                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-400 font-medium">Filter Kategori:</span>
                        @foreach($categories->whereIn('id', $selectedCategories) as $active)
                            <a href="{{ route('user.catalog.index', array_merge(request()->except(['page']), ['categories' => array_values(array_diff($selectedCategories, [$active->id]))])) }}"
                               class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-lg bg-[#13416B]/5 border border-[#13416B]/20 text-[11px] sm:text-xs font-semibold text-[#13416B] hover:bg-[#13416B]/10 transition-colors">
                                {{ $active->name }}
                                <svg class="w-3 h-3 text-slate-400 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endforeach
                        <a href="{{ route('user.catalog.index', request()->except(['page', 'categories'])) }}" class="text-[11px] sm:text-xs font-semibold text-slate-400 hover:text-red-500 underline underline-offset-2 transition-colors ml-1">
                            Hapus filter
                        </a>
                    </div>
                @endif
                
            </div>

            {{-- ========================================== --}}
            {{-- GRID KURSUS + MODAL KONFIRMASI PENDAFTARAN --}}
            {{-- ========================================== --}}
            @php
                // Payload ringan untuk modal (tanpa query tambahan per kartu)
                $coursePayload = $courses->mapWithKeys(function ($c) use ($enrolledCourseIds, $userBimtekEventIds) {
                    $sections = $c->sections->values()->map(function ($s, $i) {
                        return [
                            'name'     => $s->name ?? $s->title ?? 'Modul ' . ($i + 1),
                            'contents' => $s->contents->map(fn($ct) => [
                                'name' => $ct->name,
                                'type' => $ct->video ? 'video' : ($ct->document ? 'document' : 'text'),
                            ])->values(),
                        ];
                    });

                    $activeB = $c->bimtekEvents->first();
                    $isEnrolledInCourse = in_array($c->id, $enrolledCourseIds);
                    $isEnrolledInThisBimtek = $activeB && in_array($activeB->id, $userBimtekEventIds);

                    return [$c->id => [
                        'name'                   => $c->name,
                        'slug'                   => $c->slug,
                        'myCourseUrl'            => route('user.course.my-course.detail', $c->slug),
                        'category'               => $c->category->name ?? 'Umum',
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
                        'bimtekId'               => $activeB?->id,
                        'bimtekBatchName'        => $activeB?->batch_name,
                        'bimtekBatchCode'        => $activeB?->batch_code,
                        'bimtekStatus'           => $activeB?->status,
                        'bimtekStatusBadge'      => $activeB?->status_badge,
                        'bimtekExecutionType'    => $activeB?->execution_type,
                        'bimtekDates'            => $activeB ? ($activeB->start_date ? $activeB->start_date->format('d M') : '-') . ' s/d ' . ($activeB->end_date ? $activeB->end_date->format('d M Y') : '-') : null,
                        'bimtekTime'             => $activeB?->start_date ? $activeB->start_date->format('H:i') . ' WIB' : '08:00 WIB',
                        'bimtekQuota'            => $activeB?->quota ?? 0,
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
                    expandedSections: [0],
                    show(id) {
                        this.selected = this.courses[id];
                        this.expandedSections = [0];
                        this.submitting = false;
                        this.open = true;
                    },
                    askEnroll(id) {
                        const c = this.courses[id];
                        if (c && c.isEvent && c.bimtekStatus !== 'open_registration' && !c.isEnrolledInThisBimtek) {
                            return;
                        }
                        this.selected = c;
                        this.expandedSections = [0];
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
                    allExpanded() {
                        return this.selected && this.selected.sections && this.selected.sections.length > 0 && this.expandedSections.length === this.selected.sections.length;
                    },
                    toggleAll() {
                        if (!this.selected || !this.selected.sections) return;
                        if (this.allExpanded()) {
                            this.expandedSections = [];
                        } else {
                            this.expandedSections = this.selected.sections.map((_, i) => i);
                        }
                    },
                    icon(type) {
                        return { video: 'fa-play', document: 'fa-file-alt', text: 'fa-book-open' }[type] ?? 'fa-book-open';
                    }
                }"
                x-effect="document.body.classList.toggle('overflow-hidden', open || confirmOpen)"
                @keydown.escape.window="onEscape()"
            >
                @if ($courses->isNotEmpty())
                    {{-- 1. KHUSUS MOBILE: 2 Kolom Waterfall/Masonry (Card bawah langsung dempet rapat di bawah card atasnya) --}}
                    <div class="sm:hidden grid grid-cols-2 gap-3 items-start">
                        {{-- Kolom Kiri (Ganjil urutan visual: 0, 2, 4, ...) --}}
                        <div class="flex flex-col gap-5">
                            @foreach ($courses as $index => $course)
                                @if ($index % 2 === 0)
                                    @include('lms::user.katalog.partials.course-card', ['course' => $course])
                                @endif
                            @endforeach
                        </div>

                        {{-- Kolom Kanan (Genap urutan visual: 1, 3, 5, ...) --}}
                        <div class="flex flex-col gap-5">
                            @foreach ($courses as $index => $course)
                                @if ($index % 2 === 1)
                                    @include('lms::user.katalog.partials.course-card', ['course' => $course])
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. DESKTOP / TABLET: Grid Simetris Sejajar (2, 3, atau 4 kolom) --}}
                    <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 px-4 md:px-0 items-stretch">
                        @foreach ($courses as $course)
                            @include('lms::user.katalog.partials.course-card', ['course' => $course])
                        @endforeach
                    </div>
                @else
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                        <div class="w-16 h-16 bg-[#13416B]/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-[#13416B]/10 text-[#13416B]/40">
                            <i class="fas fa-box-open text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Belum ada program di kategori ini</h3>
                        <p class="text-sm text-slate-500 max-w-sm mb-4">Kamu sudah terdaftar di semua program yang tersedia, atau coba ubah filter kategori/tipe untuk melihat pilihan lain.</p>
                        @if(!empty($selectedCategories) || request('type'))
                            <a href="{{ route('user.catalog.index') }}" class="text-xs font-bold text-[#13416B] hover:underline">Tampilkan semua program</a>
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
                         class="relative w-full sm:max-w-xl md:max-w-2xl max-h-[92vh] sm:max-h-[85vh] flex flex-col bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">

                        <template x-if="selected">
                            <div class="flex flex-col min-h-0 flex-1">

                                {{-- Drag handle bar untuk mobile --}}
                                <div class="sm:hidden pt-2.5 pb-1 flex justify-center bg-white shrink-0">
                                    <div class="w-10 h-1 bg-slate-200 rounded-full"></div>
                                </div>

                                {{-- Header Modal: Title, Meta & Close Button --}}
                                <div class="px-5 sm:px-6 pt-4 sm:pt-5 pb-4 border-b border-slate-100 bg-white shrink-0">
                                    <div class="flex items-start justify-between gap-3.5 sm:gap-4">
                                        <div class="flex-1 min-w-0">
                                            <template x-if="selected.isEvent && selected.bimtekBatchName">
                                                <div class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md mb-1.5 max-w-full">
                                                    <i class="fas fa-satellite-dish text-[10px] sm:text-[11px] text-amber-600 shrink-0"></i>
                                                    <span class="truncate" :title="selected.bimtekBatchName" x-text="selected.bimtekBatchName"></span>
                                                </div>
                                            </template>

                                            <h3 id="enroll-title" class="text-base sm:text-lg font-bold text-slate-900 leading-snug line-clamp-2"
                                                :title="selected.name" x-text="selected.name"></h3>
                                            
                                            <p class="text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2"
                                               x-show="selected.description" x-text="selected.description"></p>
                                        </div>
                                        <button type="button" @click="close()" aria-label="Tutup"
                                                class="shrink-0 w-8 h-8 -mr-1 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                    </div>

                                </div>

                                {{-- Body: Detail Kursus & Kurikulum (Scrollable) --}}
                                <div class="flex-1 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5 custom-scrollbar bg-slate-50/40">

                                    {{-- Timeline Pelaksanaan Pelatihan (Khusus Event Pelatihan) --}}
                                    <template x-if="selected && selected.isEvent && selected.timelineSteps">
                                        <div class="mb-6">
                                            {{-- Header di luar card seperti pada Kurikulum (Badge di bawah title & deskripsi) --}}
                                            <div class="mb-3.5">
                                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Timeline Pelaksanaan Pelatihan</h4>
                                                <p class="text-[11px] text-slate-400 mt-0.5">Rangkaian tahapan dari registrasi hingga sertifikasi</p>
                                                <template x-if="selected.currentTimelinePhase">
                                                    <div class="mt-2">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white shadow-2xs"
                                                              :class="selected.currentTimelinePhase.badge_bg">
                                                            <i class="fas text-[9px]" :class="selected.currentTimelinePhase.icon || 'fa-clock'"></i>
                                                            <span x-text="selected.currentTimelinePhase.badge"></span>
                                                        </span>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Stepper 3 Tahap Tanpa Outer Card Container (Gap Luwes) --}}
                                            <div class="relative pl-9 sm:pl-12 space-y-4 before:absolute before:left-3 sm:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                                <template x-for="(step, idx) in selected.timelineSteps" :key="idx">
                                                    <div class="relative">
                                                        {{-- Dot / Icon Step --}}
                                                        <div class="absolute -left-9 sm:-left-12 top-1 w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-2xs"
                                                             :class="{
                                                                 'bg-emerald-600 text-white ring-4 ring-emerald-50': step.status === 'completed',
                                                                 'bg-[#13416B] text-white ring-4 ring-blue-100 animate-pulse': step.status === 'active',
                                                                 'bg-white text-slate-400 border-2 border-slate-300': step.status === 'upcoming'
                                                             }">
                                                            <template x-if="step.status === 'completed'">
                                                                <i class="fas fa-check text-[10px] sm:text-xs"></i>
                                                            </template>
                                                            <template x-if="step.status !== 'completed'">
                                                                <span x-text="step.step"></span>
                                                            </template>
                                                        </div>

                                                        {{-- Card Timeline --}}
                                                        <div class="p-3 sm:p-3.5 rounded-xl border transition-all"
                                                             :class="{
                                                                 'bg-white border-[#13416B]/30 shadow-xs ring-1 ring-[#13416B]/10': step.status === 'active',
                                                                 'bg-slate-50/70 border-slate-200/80': step.status === 'completed',
                                                                 'bg-white border-slate-200/70': step.status === 'upcoming'
                                                             }">
                                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                                <div class="flex items-center gap-1.5 font-bold text-xs sm:text-sm text-slate-800">
                                                                    <i class="fas" :class="step.icon + (step.status === 'active' ? ' text-[#13416B]' : ' text-slate-400')"></i>
                                                                    <span x-text="step.title"></span>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                                      :class="{
                                                                          'bg-emerald-100 text-emerald-700': step.status === 'completed',
                                                                          'bg-[#13416B] text-white': step.status === 'active',
                                                                          'bg-slate-100 text-slate-500': step.status === 'upcoming'
                                                                      }"
                                                                      x-text="step.status === 'completed' ? 'Selesai' : (step.status === 'active' ? 'Sedang Berlangsung' : 'Akan Datang')">
                                                                </span>
                                                            </div>
                                                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed" x-text="step.subtitle"></p>
                                                            
                                                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-3 text-[11px] text-slate-700 font-medium">
                                                                <span class="inline-flex items-center gap-1.5">
                                                                    <i class="far fa-calendar-alt text-[#13416B]"></i>
                                                                    <span x-text="step.date_range"></span>
                                                                </span>
                                                                <template x-if="step.zoom_time">
                                                                    <span class="inline-flex items-center gap-1.5 text-blue-700 font-semibold">
                                                                        <i class="fas fa-video text-blue-500"></i>
                                                                        <span>Live Zoom: <strong x-text="step.zoom_time"></strong></span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="step.step === 1">
                                                                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                                                                        <i class="fas fa-users text-slate-400"></i>
                                                                        <span>Kuota: <strong x-text="selected.bimtekQuota + ' Peserta'"></strong></span>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="mb-3.5">
                                        <div>
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Struktur Kurikulum</h4>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Daftar modul dan topik materi pembelajaran</p>
                                        </div>

                                        {{-- Meta Stats Ribbon (Modul • Peserta • Live Zoom) --}}
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2.5 pt-2.5 border-t border-slate-200/70 text-[11px] sm:text-xs text-slate-600">
                                            <div class="inline-flex items-center gap-1.5 font-medium">
                                                <i class="far fa-folder-open text-[#13416B]/80 text-xs"></i>
                                                <span><strong class="font-bold text-slate-800" x-text="selected.modules"></strong> Modul</span>
                                            </div>
                                            <span class="text-slate-300">•</span>
                                            <div class="inline-flex items-center gap-1.5 font-medium">
                                                <i class="far fa-user text-[#13416B]/80 text-xs"></i>
                                                <span><strong class="font-bold text-slate-800" x-text="selected.students.toLocaleString('id-ID')"></strong> Peserta</span>
                                            </div>
                                            <template x-if="selected.isEvent">
                                                <div class="inline-flex items-center gap-2 font-medium">
                                                    <span class="text-slate-300">•</span>
                                                    <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60 font-semibold">
                                                        <i class="fas fa-video text-blue-500 text-[11px]"></i>
                                                        <span>Live Zoom</span>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="space-y-2.5">
                                        <template x-for="(section, i) in selected.sections" :key="i">
                                            <div class="bg-white rounded-xl border transition-all duration-200 overflow-hidden"
                                                 :class="isExpanded(i) ? 'border-slate-300 shadow-xs' : 'border-slate-200/80 hover:border-slate-300'">
                                                
                                                <button type="button"
                                                        @click="toggleSection(i)"
                                                        class="w-full flex items-center gap-3 px-3.5 sm:px-4 py-3 text-left transition-colors cursor-pointer select-none"
                                                        :class="isExpanded(i) ? 'bg-slate-50/50' : 'hover:bg-slate-50/40'"
                                                        :aria-expanded="isExpanded(i)">
                                                    
                                                    <span class="shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors"
                                                          :class="isExpanded(i) ? 'bg-[#13416B] text-white shadow-xs' : 'bg-slate-100 text-slate-600'"
                                                          x-text="i + 1"></span>

                                                    <span class="flex-1 min-w-0 text-xs sm:text-sm font-semibold text-slate-800 leading-snug line-clamp-2 break-words"
                                                          :title="section.name"
                                                          x-text="section.name"></span>

                                                    <div class="shrink-0 flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-medium bg-slate-100 text-slate-500"
                                                              x-text="section.contents.length + ' materi'"></span>
                                                        <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                                                           :class="{ 'rotate-180 text-slate-600': isExpanded(i) }"></i>
                                                    </div>
                                                </button>

                                                <div x-show="isExpanded(i)"
                                                     x-collapse
                                                     x-cloak
                                                     class="border-t border-slate-100 bg-slate-50/30 p-2.5 sm:p-3 space-y-1.5">
                                                    
                                                    <!-- Daftar Materi Konten -->
                                                    <template x-for="(content, j) in section.contents" :key="j">
                                                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-white border border-slate-100 hover:border-slate-200 transition-colors text-xs">
                                                            <span class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 text-[10px]"
                                                                  :class="{
                                                                      'bg-sky-50 text-sky-600': content.type === 'video',
                                                                      'bg-emerald-50 text-emerald-600': content.type === 'document',
                                                                      'bg-slate-100 text-slate-600': content.type !== 'video' && content.type !== 'document'
                                                                  }">
                                                                <i class="fas" :class="icon(content.type)"></i>
                                                            </span>

                                                            <span class="flex-1 min-w-0 font-medium text-slate-700 leading-snug truncate"
                                                                  :title="content.name"
                                                                  x-text="content.name"></span>
                                                        </div>
                                                    </template>

                                                    <div x-show="section.contents.length === 0" class="py-2.5 text-center text-xs italic text-slate-400">
                                                        Belum ada topik materi pada modul ini.
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="selected.sections.length === 0" class="py-12 text-center bg-white rounded-xl border border-dashed border-slate-200">
                                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                                <i class="fas fa-book-open text-base"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700">Kurikulum Belum Tersedia</p>
                                            <p class="text-xs text-slate-400 mt-1">Materi untuk kursus ini sedang dalam tahap penyusunan.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Modal: Aksi --}}
                                <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-slate-100 bg-white shrink-0">
                                    {{-- KONDISI 1: SEDANG BERLANGSUNG / MENUNGGU BATCH BERIKUTNYA --}}
                                    <template x-if="selected.isEvent && !selected.isEnrolledInThisBimtek && selected.bimtekStatus !== 'open_registration'">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                            <p class="text-xs sm:text-sm text-slate-500 font-medium text-center sm:text-left">
                                                Pendaftaran ditutup (menunggu batch berikutnya).
                                            </p>

                                            {{-- Tombol Aksi --}}
                                            <div class="flex items-center gap-2 justify-end w-full sm:w-auto shrink-0">
                                                <button type="button" @click="close()"
                                                        class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                                    Tutup
                                                </button>
                                                <button type="button" disabled
                                                        class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed inline-flex items-center justify-center shadow-none">
                                                    <span class="truncate">Menunggu Batch Berikutnya</span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- KONDISI 2: SUDAH TERDAFTAR --}}
                                    <template x-if="selected.isEvent && selected.isEnrolledInThisBimtek">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                            <div class="text-xs sm:text-sm text-emerald-700 font-semibold text-center sm:text-left flex items-center justify-center sm:justify-start gap-1.5">
                                                <i class="fas fa-check-circle text-emerald-600"></i>
                                                <span>Anda telah terdaftar pada gelombang ini.</span>
                                            </div>
                                            <div class="flex items-center gap-2 justify-end w-full sm:w-auto shrink-0">
                                                <button type="button" @click="close()"
                                                        class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                                    Tutup
                                                </button>
                                                <a :href="selected.myCourseUrl"
                                                   class="flex-1 sm:flex-none px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-center">
                                                    <span>Masuk Pelatihan</span>
                                                    <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- KONDISI 3: BISA DAFTAR (OPEN REGISTRATION / MANDIRI) --}}
                                    <template x-if="(!selected.isEvent) || (selected.isEvent && !selected.isEnrolledInThisBimtek && selected.bimtekStatus === 'open_registration')">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                            <p class="text-xs sm:text-sm text-slate-600 font-medium text-center sm:text-left">
                                                <span x-text="selected.isEvent ? 'Tertarik mengikuti pelatihan ini? Pendaftaran masih dibuka.' : 'Apakah Anda ingin mendaftar kursus ini?'"></span>
                                            </p>
                                            <div class="flex items-center gap-2 justify-end w-full sm:w-auto shrink-0">
                                                <button type="button" @click="close()"
                                                        class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                                    Tutup
                                                </button>
                                                <button type="button" @click="confirmOpen = true"
                                                        class="flex-1 sm:flex-none px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-center">
                                                    <span>Daftar</span>
                                                    <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
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
                     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
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
                         class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-7 text-center">

                        {{-- KONDISI A: MODAL KONFIRMASI EVENT BIMTEK --}}
                        <template x-if="selected && selected.isEvent">
                            <div>
                                <div class="w-12 h-12 mx-auto mb-3.5 rounded-full bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center text-xl">
                                    <i class="fas fa-calendar-check"></i>
                                </div>

                                <h3 id="confirm-title" class="text-base sm:text-lg font-extrabold text-slate-800 mb-1">
                                    Daftar ke Pelatihan Ini?
                                </h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                                    Anda akan mendaftar ke gelombang pelatihan daring resmi kementerian:
                                </p>

                                {{-- Kartu Ringkasan Tiket Event --}}
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2.5 mb-4">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Materi Program</span>
                                        <p class="font-extrabold text-slate-900 mt-0.5" x-text="selected.name"></p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80">
                                        <div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Gelombang / Angkatan</span>
                                            <p class="font-bold text-[#13416B] mt-0.5 truncate" x-text="selected.bimtekBatchName"></p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kuota</span>
                                            <p class="font-bold text-slate-800 mt-0.5" x-text="selected.bimtekQuota + ' Peserta'"></p>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/80">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jadwal Pelaksanaan</span>
                                        <p class="font-semibold text-slate-800 mt-0.5">
                                            <span x-text="selected.bimtekDates"></span> (<span x-text="selected.bimtekTime"></span>)
                                        </p>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200/70 text-[11px] text-emerald-800 text-left mb-5 flex items-start gap-2">
                                    <i class="fas fa-info-circle text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                                    <span>Pastikan Anda dapat mengikuti rangkaian jadwal pelaksanaan dan menuntaskan seluruh materi pembelajaran.</span>
                                </div>
                            </div>
                        </template>

                        {{-- KONDISI B: MODAL KONFIRMASI KURSUS MANDIRI BIASA --}}
                        <template x-if="selected && !selected.isEvent">
                            <div>
                                <div class="w-12 h-12 mx-auto mb-3.5 rounded-full bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-xl">
                                    <i class="fas fa-question"></i>
                                </div>

                                <h3 id="confirm-title" class="text-base sm:text-lg font-bold text-slate-800 mb-1.5">Daftar ke kursus ini?</h3>
                                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-6">
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
                                <div class="flex flex-col-reverse sm:flex-row gap-2">
                                    <button type="button" @click="cancelConfirm()" :disabled="submitting"
                                            class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors disabled:opacity-50">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="submitting"
                                            class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Mendaftarkan...' : (selected.isEvent ? 'Ya, Daftar Pelatihan' : 'Ya, Daftar')"></span>
                                    </button>
                                </div>
                            </form>
                        </template>

                        <div x-show="!open && !submitting" class="mt-5 pt-4 border-t border-slate-100">
                            <button type="button" @click="confirmOpen = false; open = true"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#13416B] hover:underline">
                                <i class="fas fa-list-ul text-[11px]"></i> Lihat detail dulu
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