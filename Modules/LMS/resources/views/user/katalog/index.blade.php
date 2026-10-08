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
        <div class="relative bg-[#13416B] rounded-md p-6 sm:p-8 lg:p-10 mb-6 sm:mb-8 flex items-center justify-between border border-blue-900/20 shadow-lg overflow-hidden min-h-[280px] sm:min-h-[280px] lg:min-h-[320px]">
            
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
            <div class="relative z-20 w-full sm:w-[60%] lg:w-[60%]  text-left ">
                
                {{-- BADGE: MENGGUNAKAN FONT KALAM --}}
                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-md text-sm sm:text-base tracking-wider text-white border border-white/10 mb-4 shadow-sm" style="font-family: 'Kalam', cursive;">
                     Eksplorasi Kompetensi
                </span>
                
                {{-- JUDUL: MENGGUNAKAN FONT OSWALD --}}
                <h1 class="text-2xl md:text-4xl lg:text-5xl text-white mb-4" style="font-family: 'Oswald', sans-serif;">
                    Katalog Pembelajaran
                </h1>
                
                <p class="text-sm md:text-base text-blue-100/90 leading-relaxed max-w-xl lg:max-w-2xl font-medium">
                    Temukan dan pelajari berbagai kursus pengembangan kompetensi yang dirancang khusus untuk meningkatkan keterampilan dan profesionalitas Anda.
                </p>

            </div>

            <!-- Sisi Kanan: Ilustrasi Pegawai Kemnaker -->
            <div class="hidden sm:flex absolute bottom-0 right-0 lg:right-5 z-10 w-[45%] lg:w-[35%] h-[90%] lg:h-[95%] pointer-events-none justify-end items-end">
                <img src="{{ asset('images/catalog_illustration.png') }}" 
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
                                <h3 class="text-sm font-bold text-slate-800">Kategori Kursus</h3>
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
                        'name'         => $c->name,
                        'category'     => $c->category->name ?? 'Umum',
                        'description'  => $c->description,
                        'students'     => $c->students_count,
                        'modules'      => $c->sections_count,
                        'topics'       => $sections->sum(fn($s) => count($s['contents'])),
                        'sections'     => $sections,
                        'enrollUrl'    => route('user.catalog.enroll', $c->slug),
                    ]];
                });
            @endphp

            <div
                x-data="{
                    courses: @js($coursePayload),
                    selected: null,
                    open: false,
                    submitting: false,
                    expanded: 0,
                    show(id) {
                        this.selected = this.courses[id];
                        this.expanded = 0;
                        this.submitting = false;
                        this.open = true;
                        document.body.classList.add('overflow-hidden');
                    },
                    close() {
                        if (this.submitting) return;
                        this.open = false;
                        document.body.classList.remove('overflow-hidden');
                    },
                    icon(type) {
                        return { video: 'fa-play-circle', document: 'fa-file-alt', text: 'fa-align-left' }[type] ?? 'fa-align-left';
                    }
                }"
                @keydown.escape.window="close()"
            >
                {{-- Grid Cards Responsif --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 px-4 md:px-0">
                    @forelse ($courses as $course)
                        @php $totalTopik = $course->sections->sum(fn($s) => $s->contents->count()); @endphp
                        <div class="group flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-[#13416B]/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                            {{-- Thumbnail & Badge --}}
                            <div class="relative h-44 sm:h-48 overflow-hidden bg-slate-100">
                                @if (!empty($course->thumbnail))
                                    <img src="{{ str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail) }}"
                                        alt="{{ $course->name }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                                        <i class="fas fa-graduation-cap text-4xl text-slate-300"></i>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-white/95 backdrop-blur-sm rounded-md shadow-sm">
                                        {{ $course->category->name ?? 'Umum' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Konten Text --}}
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-slate-800 leading-snug mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2" title="{{ $course->name }}">
                                    {{ $course->name }}
                                </h3>

                                {{-- Statistik: modul, topik, peserta --}}
                                <div class="flex flex-wrap items-center gap-2 mb-3 text-xs font-medium text-slate-500">
                                    <span class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                        <i class="fas fa-layer-group text-slate-400"></i> {{ $course->sections_count }} Modul
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                        <i class="fas fa-list-ul text-slate-400"></i> {{ $totalTopik }} Topik
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-[#13416B]/5 text-[#13416B] px-2 py-1 rounded border border-[#13416B]/10">
                                        <i class="fas fa-users"></i>
                                        @if ($course->students_count > 0)
                                            {{ number_format($course->students_count, 0, ',', '.') }} Peserta
                                        @else
                                            Jadilah peserta pertama
                                        @endif
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500 mb-5 line-clamp-2 leading-relaxed flex-1">
                                    {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
                                </p>

                                {{-- Footer Aksi --}}
                                <div class="mt-auto pt-4 border-t border-slate-100 flex gap-2">
                                    <button type="button" @click="show('{{ $course->id }}')"
                                        class="flex-1 py-2.5 text-sm font-bold text-center rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                                        Lihat Kurikulum
                                    </button>
                                    <button type="button" @click="show('{{ $course->id }}')"
                                        class="flex-1 py-2.5 text-sm font-bold text-center rounded-xl transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-sm">
                                        Daftar
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
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
                     role="dialog" aria-modal="true" aria-labelledby="enroll-title">

                    {{-- Backdrop --}}
                    <div x-show="open"
                         x-transition.opacity.duration.200ms
                         @click="close()"
                         class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                    {{-- Panel (bottom-sheet di mobile, dialog di desktop) --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95"
                         class="relative w-full sm:max-w-lg max-h-[92vh] sm:max-h-[88vh] flex flex-col bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden">

                        <template x-if="selected">
                            <div class="flex flex-col min-h-0 flex-1">

                                {{-- Header --}}
                                <div class="px-5 pt-5 pb-4 border-b border-slate-100">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="inline-block px-2 py-0.5 mb-2 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/5 rounded"
                                                  x-text="selected.category"></span>
                                            <h3 id="enroll-title" class="text-lg font-bold text-slate-800 leading-snug" x-text="selected.name"></h3>
                                        </div>
                                        <button type="button" @click="close()" aria-label="Tutup"
                                                class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                                        <div class="bg-slate-50 rounded-lg py-2">
                                            <div class="text-base font-extrabold text-[#13416B]" x-text="selected.modules"></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Modul</div>
                                        </div>
                                        <div class="bg-slate-50 rounded-lg py-2">
                                            <div class="text-base font-extrabold text-[#13416B]" x-text="selected.topics"></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Topik</div>
                                        </div>
                                        <div class="bg-slate-50 rounded-lg py-2">
                                            <div class="text-base font-extrabold text-[#13416B]" x-text="selected.students.toLocaleString('id-ID')"></div>
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Peserta</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Isi: deskripsi + kurikulum (scrollable) --}}
                                <div class="flex-1 overflow-y-auto px-5 py-4 custom-scrollbar">
                                    <p class="text-sm text-slate-600 leading-relaxed mb-5"
                                       x-show="selected.description" x-text="selected.description"></p>

                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Yang akan kamu pelajari</h4>

                                    <div class="space-y-2">
                                        <template x-for="(section, i) in selected.sections" :key="i">
                                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                                <button type="button"
                                                        @click="expanded = (expanded === i ? null : i)"
                                                        class="w-full flex items-center gap-3 px-3.5 py-3 text-left hover:bg-slate-50 transition-colors"
                                                        :aria-expanded="expanded === i">
                                                    <span class="shrink-0 w-6 h-6 rounded-full bg-[#13416B] text-white text-[11px] font-bold flex items-center justify-center" x-text="i + 1"></span>
                                                    <span class="flex-1 min-w-0 text-sm font-semibold text-slate-800 truncate" x-text="section.name"></span>
                                                    <span class="shrink-0 text-[11px] font-medium text-slate-400" x-text="section.contents.length + ' topik'"></span>
                                                    <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                                                       :class="{ 'rotate-180': expanded === i }"></i>
                                                </button>

                                                <ul x-show="expanded === i" x-transition.opacity.duration.150ms
                                                    class="border-t border-slate-100 bg-slate-50/60 px-3.5 py-2 space-y-1.5">
                                                    <template x-for="(content, j) in section.contents" :key="j">
                                                        <li class="flex items-start gap-2.5 text-xs text-slate-600">
                                                            <i class="fas mt-0.5 w-3.5 text-center text-slate-400" :class="icon(content.type)"></i>
                                                            <span x-text="content.name"></span>
                                                        </li>
                                                    </template>
                                                    <li x-show="section.contents.length === 0" class="text-xs italic text-slate-400">Belum ada topik pada modul ini.</li>
                                                </ul>
                                            </div>
                                        </template>

                                        <p x-show="selected.sections.length === 0" class="text-sm italic text-slate-400 text-center py-6">
                                            Kurikulum untuk kursus ini belum tersedia.
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer: konfirmasi --}}
                                <form :action="selected.enrollUrl" method="POST" @submit="submitting = true"
                                      class="px-5 py-4 border-t border-slate-100 bg-white">
                                    @csrf
                                    <p class="text-xs text-slate-500 mb-3 text-center sm:text-left">
                                        Apakah kamu yakin ingin mendaftar? Kursus akan masuk ke daftar <span class="font-semibold text-slate-700">Kursus Saya</span>.
                                    </p>
                                    <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                                        <button type="button" @click="close()" :disabled="submitting"
                                                class="px-5 py-2.5 text-sm font-bold rounded-xl text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 transition-colors disabled:opacity-50">
                                            Batal
                                        </button>
                                        <button type="submit" :disabled="submitting"
                                                class="px-5 py-2.5 text-sm font-bold rounded-xl text-white bg-amber-500 border border-amber-500 hover:bg-amber-600 shadow-sm transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                            <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span x-text="submitting ? 'Mendaftarkan...' : 'Ya, Daftar Sekarang'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </template>
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