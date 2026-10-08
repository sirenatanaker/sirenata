<x-dashboard::layouts.dashboard title="Dashboard Pembelajaran">
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        </style>
    @endpush

    <div class="p-0 sm:p-6 lg:p-8 max-w-full mx-auto space-y-6">

        <!-- ===================================== -->
        <!-- 1. STATS GRID (Solid Colored Cards)   -->
        <!-- ===================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <!-- Total Kursus (Dark Navy) -->
            <div
                class="relative overflow-hidden bg-[#13416B] rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Total Kursus
                        </p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stats['total'] }}</h3>
                        <p class="text-[10px] sm:text-xs text-white/80 mt-1">Seluruh kursus pada sistem</p>
                    </div>
                    <!-- Badge Putih Solid, Ikon Dark Navy -->
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#13416B] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <!-- Dekorasi Watermark Background -->
                <div
                    class="absolute -right-6 -bottom-6 text-white opacity-[0.05] group-hover:opacity-[0.1] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <svg class="w-36 h-36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>

            <!-- Rata-rata Progress (Slate Blue) -->
            <div
                class="relative overflow-hidden bg-[#547996] rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Rata-rata
                            Progress</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stats['avg_progress'] }}%</h3>
                        <p class="text-[10px] sm:text-xs text-white/80 mt-1">Tingkat penyelesaian topik</p>
                    </div>
                    <!-- Badge Putih Solid, Ikon Slate Blue -->
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#547996] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute -right-6 -bottom-6 text-white opacity-[0.05] group-hover:opacity-[0.1] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <svg class="w-36 h-36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>

            <!-- Kursus Selesai (Light Blue) -->
            <div
                class="relative overflow-hidden bg-[#8BB1CC] rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-white/85 text-xs sm:text-sm font-bold uppercase mb-1 tracking-wider">Kursus
                            Selesai</p>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stats['selesai'] }}</h3>
                        <p class="text-[10px] sm:text-xs text-white/80 mt-1">Kursus yang telah dituntaskan</p>
                    </div>
                    <!-- Badge Putih Solid, Ikon Light Blue -->
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#8BB1CC] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute -right-6 -bottom-6 text-white opacity-[0.1] group-hover:opacity-[0.15] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <svg class="w-36 h-36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- ===================================== -->
        <!-- 2. ANALITIK EVALUASI FULL WIDTH       -->
        <!-- ===================================== -->
        <div class="bg-white rounded-md p-5 sm:p-6 shadow-sm border border-slate-200">
            <div
                class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">

                    <div>
                        <h2 class="text-base lg:text-lg font-extrabold text-slate-800">Evaluasi Anda per Kursus</h2>
                        <p class="text-[11px] sm:text-xs text-slate-500">Nilai akhir dari post-test yang telah Anda
                            kerjakan</p>
                    </div>
                </div>

                @if (isset($chartDataByCourse) && count($chartDataByCourse) > 0)
                    <div class="w-full sm:w-auto sm:max-w-xs shrink-0">
                        <select id="courseChartFilter"
                            class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#13416B] focus:border-[#13416B] text-ellipsis overflow-hidden pr-8 cursor-pointer shadow-sm">
                            @foreach ($chartDataByCourse as $cId => $cData)
                                <option value="{{ $cId }}">{{ $cData['course_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            @if (isset($chartDataByCourse) && count($chartDataByCourse) > 0)
                <div class="relative w-full overflow-hidden" style="min-height: 280px;">
                    <canvas id="postTestChart"></canvas>
                </div>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Lulus (≥ KKM)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span> Di bawah KKM</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block h-3 border-l-2 border-slate-500"></span> Penanda KKM tiap evaluasi</span>
                </div>
            @else
                <div class="bg-slate-50 rounded-xl p-10 text-center border border-dashed border-slate-200 my-4">
                    <div
                        class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm text-slate-400">
                        <i class="fas fa-chart-area text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Belum ada data evaluasi</p>
                    <p class="text-xs text-slate-500 mt-1">Daftar dan selesaikan modul kursus untuk memunculkan laporan
                        analitik evaluasi.</p>
                </div>
            @endif
        </div>

        <!-- ===================================== -->
        <!-- 3. GRID BAWAH (LANJUTKAN & RECENT)    -->
        <!-- ===================================== -->
        <!-- Hapus "items-start" agar tinggi Grid kembali menyelaraskan (stretch) kiri dan kanan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- KOLOM KIRI: Sebagai Penentu Utama Tinggi Baris -->
            <div class="flex flex-col gap-6">

                <!-- CARD 1: Lanjutkan Belajar -->
                <div
                    class="bg-white rounded-md p-4 sm:p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100 shrink-0">
                            <div
                                class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center bg-[#13416B] text-white rounded-md shrink-0 shadow-md">
                                <i class="fas fa-play-circle text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-800">Lanjutkan Belajar</h2>
                                <p class="text-[11px] sm:text-xs text-slate-500">Aktivitas pembelajaran terakhir Anda
                                </p>
                            </div>
                        </div>

                        @if ($lastCourse)
                            @php
                                $lThumb = $lastCourse->thumbnail
                                    ? (str_starts_with($lastCourse->thumbnail, 'http')
                                        ? $lastCourse->thumbnail
                                        : asset('storage/' . $lastCourse->thumbnail))
                                    : 'https://ui-avatars.com/api/?name=' .
                                        urlencode(substr($lastCourse->name, 0, 2)) .
                                        '&background=13416B&color=fff';
                                if (str_contains($lThumb, 'ui-avatars.com') && !str_contains($lThumb, 'font-size')) {
                                    $lThumb .= '&font-size=0.33';
                                }

                                // Warna progress: <50% kuning, 50-99% biru, 100% hijau
                                $lPct = (int) ($lastCourse->progress ?? 0);
                                if ($lPct >= 100) { $lBar = 'bg-emerald-500'; $lText = 'text-emerald-600'; }
                                elseif ($lPct >= 50) { $lBar = 'bg-[#13416B]'; $lText = 'text-[#13416B]'; }
                                else { $lBar = 'bg-amber-500'; $lText = 'text-amber-600'; }
                            @endphp

                            <div
                                class="bg-white rounded-md border border-slate-200 shadow-sm flex flex-row overflow-hidden transition-all hover:border-[#13416B]/40 hover:shadow-md mb-2">
                                <!-- Thumbnail Kiri -->
                                <div class="w-28 sm:w-36 shrink-0 bg-slate-100 relative border-r border-slate-100">
                                    <img src="{{ $lThumb }}" alt="{{ $lastCourse->name }}"
                                        class="absolute inset-0 w-full h-full object-cover">
                                </div>

                                <!-- Konten Teks & Progress Kanan -->
                                <div class="min-w-0 flex-1 flex flex-col justify-between p-3 sm:p-4">
                                    <div class="mb-2">
                                        <span
                                            class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Aktivitas
                                            Terakhir</span>
<h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-2 mb-1">
                                            {{ $lastCourse->name }}
                                        </h3>
                                        <p
                                            class="text-[10px] sm:text-[11px] text-slate-500 line-clamp-1 sm:line-clamp-2 hidden md:block">
                                            {{ $lastCourse->description ?? 'Lanjutkan pembelajaran Anda pada kursus ini.' }}
                                        </p>
                                    </div>

                                    <div class="mt-auto">
                                        <div
                                            class="flex items-center justify-between text-[9px] sm:text-[10px] text-slate-500 mb-1 font-medium">
                                            <span>Progress</span>
                                            <span class="font-bold {{ $lText }}">{{ $lastCourse->progress }}%</span>
                                        </div>

                                        <div
                                            class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden shadow-inner mb-3">
                                            <div class="{{ $lBar }} h-full rounded-full transition-all duration-300"
                                                style="width: {{ $lastCourse->progress }}%"></div>
                                        </div>

                                        @if ($lPct >= 100)
                                            <a href="{{ route('user.course.my-course.detail', $lastCourse->slug) }}"
                                                class="flex items-center justify-center gap-1.5 w-full bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 px-4 py-2 rounded-lg font-bold transition-all shadow-sm text-xs group">
                                                <i class="fas fa-award text-[11px]"></i>
                                                <span>Lihat Sertifikat</span>
                                            </a>
                                        @elseif ($lPct <= 0)
                                            <a href="{{ route('user.course.my-course.detail', $lastCourse->slug) }}?target=auto"
                                                class="flex items-center justify-center gap-1.5 w-full bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 rounded-lg font-bold transition-all shadow-sm text-xs group">
                                                <i class="fas fa-play text-[10px] group-hover:scale-110 transition-transform"></i>
                                                <span>Mulai Belajar</span>
                                            </a>
                                        @else
                                            <a href="{{ route('user.course.my-course.detail', $lastCourse->slug) }}?target=auto"
                                                class="flex items-center justify-center gap-1.5 w-full bg-[#13416B] text-white hover:bg-[#0f3354] px-4 py-2 rounded-lg font-bold transition-all shadow-sm text-xs group">
                                                <i class="fas fa-play text-[10px] group-hover:scale-110 transition-transform"></i>
                                                <span>Lanjutkan Belajar</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="bg-slate-50 rounded-xl p-6 text-center border border-dashed border-slate-200 my-2 flex flex-col justify-center items-center">
                                <div
                                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-slate-400 border border-slate-100">
                                    <i class="fas fa-book-open text-lg"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700 mb-3">Belum ada aktivitas belajar</p>
                                <a href="{{ route('user.catalog.index') }}"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-[#13416B] text-xs font-bold hover:bg-slate-50 shadow-sm transition-all">
                                    <span>Lihat Katalog Kursus</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CARD 2: Terakhir Dilihat (Perpustakaan) -->
                <div
                    class="bg-white rounded-md p-4 sm:p-5 shadow-sm border border-slate-200 flex flex-col justify-between flex-1">
                    <div>
                        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-100 shrink-0">
                            <div
                                class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center bg-[#547996] text-white rounded-md shrink-0 shadow-md">
                                <i class="fas fa-history text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-800">Terakhir Dilihat</h2>
                                <p class="text-[11px] sm:text-xs text-slate-500">Koleksi perpustakaan terakhir diakses
                                </p>
                            </div>
                        </div>

                        @if (isset($lastLibrary) && $lastLibrary)
                            <!-- Tautan mengarah ke halaman library dengan membawa parameter '?open=ID' -->
                            <a href="{{ route('user.library.index') }}?open={{ $lastLibrary->id }}"
                                class="flex items-center sm:items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-all group">

                                <div
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-md overflow-hidden shrink-0 shadow-sm relative bg-white border border-slate-200 flex items-center justify-center">
                                    @if ($lastLibrary->cover_image)
                                        <img src="{{ $lastLibrary->cover_image_url }}"
                                            alt="Cover" class="w-full h-full object-cover">
                                        <div
                                            class="absolute bottom-1 right-1 w-5 h-5 bg-[#13416B] text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">
                                            <i class="{{ $lastLibrary->icon }}"></i>
                                        </div>
                                    @else
                                        <i
                                            class="{{ $lastLibrary->icon }} text-[#547996] text-2xl group-hover:scale-110 transition-transform"></i>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0 py-0.5">
                                    <div class="flex justify-between items-center sm:items-start mb-1.5">
                                        <span
                                            class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-md bg-[#547996]/10 text-[#547996] border border-[#547996]/20">
                                            {{ $lastLibrary->libraryCategory->name ?? 'Kategori Umum' }}
                                        </span>
                                        <span class="text-[10px] sm:text-[11px] text-slate-400 font-medium">
                                            {{ \Carbon\Carbon::parse($lastLibrary->last_accessed_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <h3
                                        class="text-sm sm:text-base font-bold text-slate-800 line-clamp-1 group-hover:text-[#13416B] transition-colors mb-1">
                                        {{ $lastLibrary->title }}
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-slate-500 line-clamp-1 sm:line-clamp-2">
                                        {{ $lastLibrary->description ?? 'Tidak ada deskripsi' }}
                                    </p>
                                </div>
                            </a>
                        @else
                            <div class="text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-xs font-semibold text-slate-600 mb-2">Belum ada riwayat baca.</p>
                                <a href="{{ route('user.library.index') }}"
                                    class="text-xs font-bold text-[#13416B] hover:underline">
                                    Jelajahi Perpustakaan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Kursus Saya (3 kursus penuh + 1 mengintip terpotong/blur) -->
            <div>
                <div
                    class="bg-white rounded-md p-5 sm:p-6 shadow-sm border border-slate-200 flex flex-col h-full w-full">

                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center bg-[#13416B] text-white rounded-md shrink-0 shadow-md">
                                <i class="fas fa-graduation-cap text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-800">Kursus Saya</h2>
                                <p class="text-[11px] sm:text-xs text-slate-500">Daftar kursus yang Anda ikuti</p>
                            </div>
                        </div>
                    </div>

                    <!-- Area daftar kursus yang di set relatif dan menyembunyikan elemen berlebih -->
                    <div class="relative flex-1">
                        <div class="space-y-3 flex flex-col">
                            @forelse ($recentCourses->take(4) as $course)
                                @php
                                    $rThumb = $course->thumbnail
                                        ? (str_starts_with($course->thumbnail, 'http')
                                            ? $course->thumbnail
                                            : asset('storage/' . $course->thumbnail))
                                        : 'https://ui-avatars.com/api/?name=' .
                                            urlencode(substr($course->name, 0, 2)) .
                                            '&background=13416B&color=fff';
                                    if (
                                        str_contains($rThumb, 'ui-avatars.com') &&
                                        !str_contains($rThumb, 'font-size')
                                    ) {
                                        $rThumb .= '&font-size=0.33';
                                    }

                                    // Warna progress: <50% kuning, 50-99% biru, 100% hijau
                                    $rPct = (int) ($course->pivot->progress ?? 0);
                                    if ($rPct >= 100) { $rBar = 'bg-emerald-500'; $rText = 'text-emerald-600'; }
                                    elseif ($rPct >= 50) { $rBar = 'bg-[#13416B]'; $rText = 'text-[#13416B]'; }
                                    else { $rBar = 'bg-amber-500'; $rText = 'text-amber-600'; }
                                @endphp
                                <!-- Tambahan "shrink-0" memastikan card kursus tidak memipih/gepeng meskipun meluber -->
                                <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                                    @if ($loop->index === 3) aria-hidden="true" tabindex="-1" @endif
                                    class="shrink-0 flex flex-row gap-3 sm:gap-4 bg-white border border-slate-200 rounded-xl p-3 sm:p-4 transition-all duration-200 hover:border-[#13416B]/40 hover:shadow-sm group items-center sm:items-start {{ $loop->index === 3 ? 'max-h-[64px] overflow-hidden pointer-events-none select-none' : '' }}">

                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-md overflow-hidden bg-slate-100 border border-slate-200 relative shadow-sm">
                                        <img src="{{ $rThumb }}" alt="{{ $course->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>

                                    <div class="min-w-0 flex-1 flex flex-col justify-between h-full w-full py-0.5">
                                        <div>
                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                <!-- Title untuk Layar Desktop / Tablet (sm ke atas) -->
                                                <h3
                                                    class="hidden sm:block font-bold text-slate-800 text-sm truncate group-hover:text-[#13416B] transition-colors">
                                                    {{ $course->name }}
                                                </h3>

                                                @if ($course->pivot->status === 'completed' || $rPct >= 100)
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase text-white bg-emerald-600 shrink-0">Selesai</span>
                                                @elseif ($rPct > 0)
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase text-white bg-amber-500 shrink-0">Berjalan</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase text-white bg-slate-400 shrink-0">Belum Dimulai</span>
                                                @endif
                                            </div>

                                            <!-- Deskripsi Kursus: Di-hidden untuk Resolusi Mobile -->
                                            <p
                                                class="hidden sm:block text-[11px] sm:text-xs text-slate-500 line-clamp-1 mb-2">
                                                {{ $course->description ?? 'Deskripsi kursus tidak tersedia.' }}
                                            </p>

                                            <!-- Title untuk Resolusi Mobile: Ditaruh di posisi tempat Deskripsi berada -->
                                            <h3
                                                class="block sm:hidden font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 mb-2 group-hover:text-[#13416B] transition-colors leading-snug">
                                                {{ $course->name }}
                                            </h3>
                                        </div>

                                        <div>
                                            <div
                                                class="flex items-center justify-between text-[9px] sm:text-[10px] text-slate-500 mb-1 font-medium">
                                                <span>Progress</span>
                                                <span
                                                    class="font-bold {{ $rText }}">{{ $course->pivot->progress }}%</span>
                                            </div>
                                            <div
                                                class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden shadow-inner">
                                                <div class="{{ $rBar }} h-full rounded-full transition-all duration-300"
                                                    style="width: {{ $course->pivot->progress }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div
                                    class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    <p class="text-xs font-semibold text-slate-600">Belum ada kursus yang diikuti.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Kursus ke-4 dst. hanya mengintip (terpotong + blur); selengkapnya lewat "Lihat semua" --}}
                        @if ($recentCourses->count() > 3)
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24">
                                <div class="absolute inset-0 backdrop-blur-[3px] [mask-image:linear-gradient(to_top,black,transparent)] [-webkit-mask-image:linear-gradient(to_top,black,transparent)]"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-white via-white/85 to-transparent"></div>
                            </div>
                            <div class="absolute inset-x-0 bottom-2 flex justify-center">
                                <a href="{{ route('user.course.my-course') }}"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline underline-offset-4 transition-colors">
                                    Lihat semua kursus 
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!$profile || empty($profile->instansi))
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 backdrop-blur-sm"
            style="background-color: rgba(15, 23, 42, 0.6)">
            <div
                class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[calc(100dvh-1rem)] sm:max-h-[90vh] overflow-y-auto border border-slate-200 overscroll-contain">
                <div class="p-4 sm:p-8">
                    <div class="text-center mb-8">
                        {{-- <div
                            class="bg-[#13416B]/10 text-[#13416B] w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border border-[#13416B]/20 shadow-sm">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo Kemnaker"
                                class="h-9 w-9 object-contain">
                        </div> --}}
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

                        <div id="kementerianSection" class="hidden">
                            <label for="kementerian"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Kementerian/Lembaga
                                <span class="text-red-500">*</span></label>
                            <select id="kementerian" class="w-full">
                                <option value="">Pilih Kementerian/Lembaga</option>
                            </select>
                            <!-- Input tersembunyi untuk menampung nilai akhir pusat -->
                            <input type="hidden" name="instansi" id="finalInstansiPusat" />
                        </div>

                        <div id="provinsiSection" class="hidden">
                            <label for="provinsi"
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Provinsi
                                <span class="text-red-500">*</span></label>
                            <select id="provinsi" class="w-full " name="province_code">
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
                                <!-- Tambahkan name="instansi" di sini -->
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

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('postTestChart');

                // Warna batang berdasarkan hasil: lulus = hijau, di bawah KKM = kuning.
                // KKM berbeda untuk setiap evaluasi, jadi dibaca per batang.
                const COLOR_PASS = '#10b981';
                const COLOR_FAIL = '#f59e0b';
                const DEFAULT_KKM = 70; // cadangan jika data evaluasi tidak membawa nilai KKM

                // Urutan KKM harus sejajar dengan labels/user_scores.
                // Sumber (berurutan): passing_scores[] -> kkms[] -> kkm (tunggal) -> DEFAULT_KKM
                const getKkms = (courseData) => {
                    const count = (courseData.user_scores || []).length;
                    const list = courseData.passing_scores || courseData.kkms;
                    const single = Number(courseData.kkm) || DEFAULT_KKM;
                    return Array.from({ length: count }, (_, i) => {
                        const v = Array.isArray(list) ? Number(list[i]) : NaN;
                        return v > 0 ? v : single;
                    });
                };
                const scoreColors = (scores, kkms) => scores.map((v, i) => (Number(v) >= kkms[i] ? COLOR_PASS : COLOR_FAIL));

                // Plugin: penanda KKM pada setiap batang + angka nilai di ujung batang
                const kkmAndValuePlugin = {
                    id: 'kkmAndValue',
                    afterDatasetsDraw(chart, args, opts) {
                        const {ctx, scales} = chart;
                        const x = scales.x;
                        const kkms = (opts && opts.kkms) || [];
                        const meta = chart.getDatasetMeta(0);
                        ctx.save();

                        meta.data.forEach((bar, i) => {
                            const value = chart.data.datasets[0].data[i];
                            const kkm = kkms[i];
                            const half = bar.height / 2;
                            let textX = bar.x;

                            if (kkm) {
                                const kx = x.getPixelForValue(kkm);
                                ctx.setLineDash([]);
                                ctx.lineWidth = 2;
                                ctx.strokeStyle = '#475569';
                                ctx.beginPath();
                                ctx.moveTo(kx, bar.y - half - 5);
                                ctx.lineTo(kx, bar.y + half + 5);
                                ctx.stroke();

                                ctx.fillStyle = '#64748b';
                                ctx.font = '600 9px sans-serif';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'bottom';
                                ctx.fillText('KKM ' + kkm, kx, bar.y - half - 6);
                                textX = Math.max(bar.x, kx);
                            }

                            ctx.fillStyle = '#334155';
                            ctx.font = '700 11px sans-serif';
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(value, textX + 6, bar.y);
                        });

                        ctx.restore();
                    }
                };

                function formatMultilineLabel(text) {
                    if (!text) return text;
                    const maxChars = window.innerWidth >= 640 ? 35 : 20;
                    const words = text.split(' ');
                    let lines = [];
                    let currentLine = '';

                    words.forEach(word => {
                        if ((currentLine + word).length > maxChars) {
                            if (currentLine.trim() !== '') lines.push(currentLine.trim());
                            currentLine = word + ' ';
                        } else {
                            currentLine += word + ' ';
                        }
                    });
                    if (currentLine.trim() !== '') lines.push(currentLine.trim());
                    return lines;
                }

                if (ctx && @json(isset($chartDataByCourse) ? count($chartDataByCourse) : 0) > 0) {
                    const allChartData = @json($chartDataByCourse ?? []);

                    const courseKeys = Object.keys(allChartData);
                    let currentCourseId = courseKeys[0];
                    let currentData = allChartData[currentCourseId];

                    let currentKkms = getKkms(currentData);
                    const dynamicColors = scoreColors(currentData.user_scores, currentKkms);

                    const postTestChart = new Chart(ctx.getContext('2d'), {
                        type: 'bar',
                        plugins: [kkmAndValuePlugin],
                        data: {
                            labels: currentData.labels.map(label => formatMultilineLabel(label)),
                            datasets: [{
                                label: 'Skor Anda',
                                data: currentData.user_scores,
                                backgroundColor: dynamicColors,
                                borderRadius: 4,
                                barPercentage: 0.5,
                                categoryPercentage: 0.8
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 14,
                                    right: 30
                                }
                            },
                            plugins: {
                                kkmAndValue: {
                                    kkms: currentKkms
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    titleFont: {
                                        size: 13
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        title: function(context) {
                                            return context[0].label.replaceAll(',', ' ');
                                        },
                                        label: function(context) {
                                            const kkm = postTestChart.options.plugins.kkmAndValue.kkms[context.dataIndex];
                                            const status = Number(context.parsed.x) >= kkm ? 'Lulus' : 'Di bawah KKM';
                                            return 'Skor Anda: ' + context.parsed.x + ' • KKM: ' + kkm + ' (' + status + ')';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 100,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        stepSize: 20
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', sans-serif",
                                            lineHeight: 1.3
                                        },
                                        autoSkip: false,
                                        padding: 8
                                    },
                                    afterFit: function(scaleInstance) {
                                        if (window.innerWidth >= 640) {
                                            if (scaleInstance.width < 180) {
                                                scaleInstance.width = 180;
                                            }
                                        } else {
                                            if (scaleInstance.width < 120) {
                                                scaleInstance.width = 120;
                                            }
                                        }
                                    }
                                }
                            },
                            interaction: {
                                mode: 'index',
                                axis: 'y',
                                intersect: false
                            }
                        }
                    });

                    const filterSelect = document.getElementById('courseChartFilter');
                    if (filterSelect) {
                        filterSelect.addEventListener('change', function() {
                            const selectedId = this.value;
                            const newData = allChartData[selectedId];

                            if (newData) {
                                const newHeight = Math.max(180, newData.labels.length * 80);
                                ctx.parentElement.style.height = newHeight + 'px';

                                postTestChart.data.labels = newData.labels.map(label => formatMultilineLabel(
                                    label));
                                postTestChart.data.datasets[0].data = newData.user_scores;
                                currentKkms = getKkms(newData);
                                postTestChart.options.plugins.kkmAndValue.kkms = currentKkms;
                                postTestChart.data.datasets[0].backgroundColor = scoreColors(newData.user_scores, currentKkms);
                                postTestChart.update();
                            }
                        });

                        const initialHeight = Math.max(180, currentData.labels.length * 80);
                        ctx.parentElement.style.height = initialHeight + 'px';
                    }
                }
            });
            @if (!$profile || empty($profile->instansi))
                $(document).ready(function() {
                    // Pesan error inline (pengganti alert bawaan browser)
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

                    // 1. Muat data pusat saat pertama kali modal muncul
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

                    // 2. Inisialisasi Select2 untuk semua elemen dropdown terkait
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

                    // 3. Handler saat radio Asal Instansi berubah (Pusat / Provinsi / Kab Kota)
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

                    // 4. Fungsi helper untuk mengambil data instansi daerah berdasarkan wilayah terpilih
                    function populateInstansi() {
                        $('#instansi').empty().append(new Option('Pilih Instansi', ''));

                        const provinceCode = $('#provinsi').val();
                        const regencyCode = $('#kabkota').val();
                        const asalInstansi = $('input[name="asalInstansi"]:checked')
                            .val(); // Ambil tipe level (provinsi/kabkota)

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

                    // 5. Handler saat Provinsi dipilih
                    $('#provinsi').on('change', function() {
                        const provinsiCode = $(this).val();
                        const asalInstansi = $('input[name="asalInstansi"]:checked').val();

                        if (provinsiCode) {
                            if (asalInstansi === 'kabkota') {
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
                                                $('#kabkota').append(new Option(regency
                                                    .name, regency.code));
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

                    // 6. Handler saat Kabupaten/Kota dipilih
                    $('#kabkota').on('change', function() {
                        if ($(this).val()) {
                            populateInstansi();
                            $('#instansiSection').removeClass('hidden');
                            $('#customInstansiSection, #unitKerjaSection').addClass('hidden');
                        } else {
                            $('#instansiSection, #customInstansiSection, #unitKerjaSection').addClass('hidden');
                        }
                    });

                    // 7. Handler saat pilihan instansi berubah (menangani opsi "Lainnya")
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

                    // 8. Handler input teks kustom instansi
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

                    // 9. Validasi sebelum form disubmit
                    $('#instansiForm').on('submit', function(e) {
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