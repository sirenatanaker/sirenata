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
        <div class="relative bg-[#13416B] rounded-md px-5 py-4 sm:p-8 lg:p-10 mb-4 sm:mb-8 flex items-center justify-between border border-blue-900/20 shadow-lg overflow-hidden min-h-[180px] sm:min-h-[280px] lg:min-h-[320px]">
            
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
            <div class="mb-6 border-b border-slate-200 pb-4">
                
                {{-- Baris Atas: Judul & Tombol Filter (Sejajar di semua resolusi) --}}
                <div class="flex flex-row items-center justify-between gap-3 sm:gap-4">
                    
                    {{-- Kiri: Teks (Diperkecil di mobile) --}}
                    <div class="flex flex-col gap-0.5 sm:gap-1">
                        <h2 class="text-base sm:text-xl font-extrabold text-slate-800 leading-tight">
                            Jelajahi Kursus Baru
                        </h2>
                        <p class="text-[11px] sm:text-sm font-medium text-slate-500">
                            Ada <span class="text-[#13416B] font-bold">{{ $courses->total() }}</span> kursus yang siap dipelajari.
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
                            <span>Filter</span>
                            
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
                                    <a href="{{ route('user.catalog.index') }}" class="text-[11px] font-bold text-red-500 hover:text-red-700 hover:underline">Reset</a>
                                @endif
                            </div>

                            <form action="{{ route('user.catalog.index') }}" method="GET" id="catalogCategoryForm">
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

                {{-- Baris Bawah: Chip kategori aktif (Dilepas dari baris atas agar tidak merusak layout sejajar) --}}
                @if(!empty($selectedCategories))
                    <div class="flex flex-wrap items-center gap-2 mt-3 sm:mt-4">
                        @foreach($categories->whereIn('id', $selectedCategories) as $active)
                            <a href="{{ route('user.catalog.index', ['categories' => array_values(array_diff($selectedCategories, [$active->id]))]) }}"
                               class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-lg bg-[#13416B]/5 border border-[#13416B]/20 text-[11px] sm:text-xs font-semibold text-[#13416B] hover:bg-[#13416B]/10 transition-colors">
                                {{ $active->name }}
                                <svg class="w-3 h-3 text-slate-400 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endforeach
                        <a href="{{ route('user.catalog.index') }}" class="text-[11px] sm:text-xs font-semibold text-slate-400 hover:text-red-500 underline underline-offset-2 transition-colors ml-1">
                            Hapus semua
                        </a>
                    </div>
                @endif
                
            </div>

            {{-- ========================================== --}}
            {{-- GRID KURSUS + MODAL KONFIRMASI PENDAFTARAN --}}
            {{-- ========================================== --}}
            @php
                // Payload ringan untuk modal (tanpa query tambahan per kartu)
                $coursePayload = $courses->mapWithKeys(function ($c) {
                    $sections = $c->sections->values()->map(function ($s, $i) {
                        return [
                            'name'     => $s->name ?? $s->title ?? 'Modul ' . ($i + 1),
                            'contents' => $s->contents->map(fn($ct) => [
                                'name' => $ct->name,
                                'type' => $ct->video ? 'video' : ($ct->document ? 'document' : 'text'),
                            ])->values(),
                        ];
                    });

                    return [$c->id => [
                        'name'          => $c->name,
                        'category'      => $c->category->name ?? 'Umum',
                        'description'   => $c->description,
                        'students'      => $c->students_count,
                        'modules'       => $c->sections_count,
                        'topics'        => $sections->sum(fn($s) => count($s['contents'])),
                        'thumbnail'     => ! empty($c->thumbnail)
                            ? (str_starts_with($c->thumbnail, 'http') ? $c->thumbnail : asset('storage/' . $c->thumbnail))
                            : null,
                        'audienceLabel' => ($c->audience ?? 'instansi') === 'swasta' ? 'Swasta' : 'Instansi',
                        'sections'      => $sections,
                        'enrollUrl'     => route('user.catalog.enroll', $c->slug),
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
                        this.selected = this.courses[id];
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
                {{-- Grid Cards Responsif --}}
                <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-y-6 gap-x-3 sm:gap-6 px-0 sm:px-4 md:px-0">
                    @forelse ($courses as $course)
                        <div class="group flex flex-col bg-transparent sm:bg-white rounded-none sm:rounded-md border-0 sm:border border-slate-200 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                            {{-- Thumbnail & Badge --}}
                            <div class="relative h-24 sm:h-48 overflow-hidden bg-slate-100 rounded-t-md">
                                @if (!empty($course->thumbnail))
                                    <img src="{{ str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail) }}"
                                        alt="{{ $course->name }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                                        <i class="fas fa-graduation-cap text-2xl sm:text-4xl text-slate-300"></i>
                                    </div>
                                @endif

                                {{-- Badge Kategori (Mojok Kanan Atas seperti status label pada card kursus) --}}
                                <span class="absolute top-0 right-0 z-10 max-w-[80%] px-2.5 sm:px-3 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-[#13416B] shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                                    <i class="fas fa-tag text-[8px] sm:text-[9px] flex-shrink-0"></i>
                                    <span class="truncate">{{ $course->category->name ?? 'Umum' }}</span>
                                </span>
                            </div>

                            {{-- Konten Text --}}
                            <div class="p-0 pt-2.5 sm:p-5 flex flex-col flex-1">
                                <h3 class="text-xs sm:text-base font-bold text-slate-800 leading-snug mb-1.5 sm:mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2" title="{{ $course->name }}">
                                    {{ $course->name }}
                                </h3>

                                {{-- Statistik: modul & peserta (Kalem & Profesional) --}}
                                <div class="flex items-center gap-2 sm:gap-2.5 mb-2 sm:mb-2.5 text-[10px] sm:text-xs font-medium text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="far fa-folder-open text-slate-400 text-[10px] sm:text-xs"></i>
                                        <span>{{ $course->sections_count }} Modul</span>
                                    </span>
                                    <span class="text-slate-300 select-none">•</span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="far fa-user text-slate-400 text-[10px] sm:text-xs"></i>
                                        <span>{{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta</span>
                                    </span>
                                </div>

                                <p class="hidden sm:line-clamp-2 text-xs text-slate-500 mb-5 leading-relaxed flex-1">
                                    {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
                                </p>

                                {{-- Footer Aksi: ditumpuk di mobile, berdampingan di layar sm+ --}}
                                <div class="mt-auto pt-3 sm:pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-1.5 sm:gap-2">
                                    <button type="button" @click="show('{{ $course->id }}')"
                                        class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2.5 text-[10px] sm:text-sm font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                                        <span class="sm:hidden">Kurikulum</span>
                                        <span class="hidden sm:inline">Lihat Kurikulum</span>
                                    </button>
                                    <button type="button" @click="askEnroll('{{ $course->id }}')"
                                        class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2.5 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-sm">
                                        Daftar
                                        <i class="fas fa-arrow-up-right-from-square text-[11px] sm:text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                            <div class="w-16 h-16 bg-[#13416B]/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-[#13416B]/10 text-[#13416B]/40">
                                <i class="fas fa-box-open text-2xl"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 mb-1">Belum ada modul di kategori ini</h3>
                            <p class="text-sm text-slate-500 max-w-sm mb-4">Kamu sudah terdaftar di semua kursus yang tersedia, atau coba ubah filter kategori untuk melihat pilihan lain.</p>
                            @if(!empty($selectedCategories))
                                <a href="{{ route('user.catalog.index') }}" class="text-xs font-bold text-[#13416B] hover:underline">Tampilkan semua kategori</a>
                            @endif
                        </div>
                    @endforelse
                </div>

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

                                    {{-- Meta Stats Ribbon --}}
                                    <div class="flex flex-wrap items-center gap-3 sm:gap-5 mt-3.5 pt-3 border-t border-slate-100 text-xs text-slate-500">
                                        <div class="inline-flex items-center gap-1.5 font-medium">
                                            <i class="far fa-folder-open text-[#13416B]/70 text-xs"></i>
                                            <span><strong class="font-semibold text-slate-800" x-text="selected.modules"></strong> Modul</span>
                                        </div>
                                        <span class="text-slate-200">•</span>
                                        <div class="inline-flex items-center gap-1.5 font-medium">
                                            <i class="far fa-file-alt text-[#13416B]/70 text-xs"></i>
                                            <span><strong class="font-semibold text-slate-800" x-text="selected.topics"></strong> Materi Topik</span>
                                        </div>
                                        <span class="text-slate-200">•</span>
                                        <div class="inline-flex items-center gap-1.5 font-medium">
                                            <i class="far fa-user text-[#13416B]/70 text-xs"></i>
                                            <span><strong class="font-semibold text-slate-800" x-text="selected.students.toLocaleString('id-ID')"></strong> Peserta</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Body: Daftar Kurikulum (Scrollable) --}}
                                <div class="flex-1 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5 custom-scrollbar bg-slate-50/40">
                                    <div class="mb-3.5">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Struktur Kurikulum</h4>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar modul dan topik materi pembelajaran</p>
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
                                <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-white shrink-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                        <p class="text-xs sm:text-sm text-slate-600 font-medium text-center sm:text-left">
                                            Apakah Anda ingin mendaftar kursus ini?
                                        </p>
                                        <div class="flex items-center gap-2 justify-end w-full sm:w-auto">
                                            <button type="button" @click="close()"
                                                    class="flex-1 sm:flex-none px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                                Tutup
                                            </button>
                                            <button type="button" @click="confirmOpen = true"
                                                    class="flex-1 sm:flex-none px-5 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-center">
                                                <span>Daftar Kursus Ini</span>
                                                <i class="fas fa-arrow-up-right-from-square text-[11px] sm:text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
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

                        <div class="w-12 h-12 mx-auto mb-3.5 rounded-full bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-xl">
                            <i class="fas fa-question"></i>
                        </div>

                        <h3 id="confirm-title" class="text-base sm:text-lg font-bold text-slate-800 mb-1.5">Daftar ke kursus ini?</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-6">
                            Kamu akan mendaftar ke
                            <span class="font-semibold text-slate-700" x-text="selected ? selected.name : ''"></span>
                            dan kursus ini akan langsung masuk ke daftar Kursus Saya.
                        </p>

                        <template x-if="selected">
                            <form :action="selected.enrollUrl" method="POST" @submit="submitting = true">
                                @csrf
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
                                        <span x-text="submitting ? 'Mendaftarkan...' : 'Ya, Daftar'"></span>
                                    </button>
                                </div>
                            </form>
                        </template>

                        <div x-show="!open && !submitting" class="mt-5 pt-4 border-t border-slate-100">
                            <button type="button" @click="confirmOpen = false; open = true"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#13416B] hover:underline">
                                <i class="fas fa-list-ul text-[11px]"></i> Lihat kurikulum dulu
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