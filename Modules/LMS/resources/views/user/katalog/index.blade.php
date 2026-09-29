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
                            class="flex items-center justify-center gap-1.5 sm:gap-2.5 px-3 py-1.5 sm:px-4 sm:py-2.5 bg-white border border-slate-300 rounded-lg text-xs sm:text-sm font-semibold text-slate-700 hover:border-[#13416B]/50 hover:text-[#13416B] transition-all"
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
                             class="absolute right-0 mt-2 w-64 sm:w-72 bg-white rounded-lg shadow-xl border border-slate-200 p-4 sm:p-5"
                             style="display: none;">
                            
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-slate-800">Kategori Modul</h3>
                                @if(!empty($selectedCategories))
                                    <a href="{{ route('user.catalog.index') }}" class="text-[11px] font-bold text-red-500 hover:text-red-700 hover:underline">Reset</a>
                                @endif
                            </div>

                            <form action="{{ route('user.catalog.index') }}" method="GET" id="catalogCategoryForm">
                                <div class="space-y-1 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                                    @forelse($categories as $category)
                                        <label class="flex items-start gap-3 cursor-pointer group px-2 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                                class="w-4 h-4 mt-0.5 rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] cursor-pointer"
                                                onchange="document.getElementById('catalogCategoryForm').submit();"
                                                {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                            <div class="flex-1 flex items-start justify-between min-w-0 gap-2">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 group-hover:text-[#13416B] transition-colors line-clamp-2">
                                                    {{ $category->name }}
                                                </span>
                                                <span class="shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                    {{ $category->courses_count }}
                                                </span>
                                            </div>
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

            {{-- Grid Cards Responsif --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 px-4 md:px-0">
                @forelse ($courses as $course)
                    <div class="group flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-[#13416B]/30 transition-all duration-300 overflow-hidden ">
                        
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

                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-white/95 backdrop-blur-sm rounded-md shadow-sm">
                                    {{ $course->category->name ?? 'Umum' }}
                                </span>
                            </div>

                            <div class="absolute bottom-3 left-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-white bg-black/40 backdrop-blur-sm rounded-md">
                                    <i class="fas fa-layer-group"></i> {{ $course->sections->count() }} Modul
                                </span>
                            </div>
                        </div>

                        {{-- Konten Text --}}
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="text-base font-bold text-slate-800 leading-snug mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2" title="{{ $course->name }}">
                                {{ $course->name }}
                            </h3>

                            <p class="text-xs text-slate-500 mb-5 line-clamp-2 leading-relaxed flex-1">
                                {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
                            </p>

                            {{-- Footer Aksi --}}
                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <form action="{{ route('user.catalog.enroll', $course->slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 text-sm font-bold text-center rounded-xl transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-sm flex items-center justify-center gap-2">
                                        Daftar Sekarang
                                        
                                    </button>
                                </form>
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

            {{-- Pagination --}}
            @if ($courses->hasPages())
                <div class="mt-8 flex justify-center [&_nav]:text-sm">
                    {{ $courses->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard::layouts.dashboard>