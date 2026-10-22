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

            #chartScrollContainer::-webkit-scrollbar {
                width: 6px;
            }
            #chartScrollContainer::-webkit-scrollbar-track {
                background: #f8fafc;
                border-radius: 4px;
            }
            #chartScrollContainer::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            #chartScrollContainer::-webkit-scrollbar-thumb:hover {
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
                        <i class="fas fa-book-open text-xl sm:text-2xl" aria-hidden="true"></i>
                    </div>
                </div>
                <!-- Dekorasi Watermark Background -->
                <div
                    class="absolute -right-5 -bottom-5 sm:-right-6 sm:-bottom-6 text-white opacity-[0.10] group-hover:opacity-[0.16] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-book-open text-8xl sm:text-9xl" aria-hidden="true"></i>
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
                        <i class="fas fa-chart-pie text-xl sm:text-2xl" aria-hidden="true"></i>
                    </div>
                </div>
                <div
                    class="absolute -right-5 -bottom-5 sm:-right-6 sm:-bottom-6 text-white opacity-[0.10] group-hover:opacity-[0.16] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-chart-pie text-8xl sm:text-9xl" aria-hidden="true"></i>
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
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#2b658d] flex items-center justify-center shrink-0 shadow-sm">
                        <i class="fas fa-award text-xl sm:text-2xl" aria-hidden="true"></i>
                    </div>
                </div>
                <div
                    class="absolute -right-5 -bottom-5 sm:-right-6 sm:-bottom-6 text-white opacity-[0.12] group-hover:opacity-[0.18] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-award text-8xl sm:text-9xl" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- 2. BALANCED 2-COLUMN GRID (PREVIOUS CARDS, BALANCED)    -->
        <!-- ======================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

            <!-- ===================================== -->
            <!-- KOLOM KIRI (7 COLS): RESUME & CHART   -->
            <!-- ===================================== -->
            <div class="lg:col-span-7 flex flex-col gap-5 sm:gap-6 lg:h-full min-h-0">

                <!-- CARD 1: Lanjutkan Belajar -->
                <div class="bg-white rounded-md p-4 sm:p-5 shadow-sm shrink-0">
                    <!-- Header Card -->
                    <div class="mb-3.5 pb-2.5 sm:pb-3 border-b border-slate-100 shrink-0">
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-800">Lanjutkan Belajar</h2>
                        <p class="text-xs sm:text-sm text-slate-500">Aktivitas pembelajaran terakhir Anda</p>
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

                            // Warna progress: 100% hijau, sedang berprogress amber
                            $lPct = (int) ($lastCourse->progress ?? 0);
                            if ($lPct >= 100) {
                                $lBar = 'bg-emerald-500';
                                $lText = 'text-emerald-600';
                            } else {
                                $lBar = 'bg-amber-500';
                                $lText = 'text-amber-600';
                            }
                        @endphp

                        <div
                            class="bg-slate-50/70 rounded-md shadow-xs flex flex-row overflow-hidden transition-all hover:bg-slate-100/80 hover:shadow-sm mb-1 group">
                            <!-- Thumbnail Kiri (Persegi Panjang Landscape Ringkas, Disembunyikan di Mobile) -->
                            <div class="hidden sm:block sm:w-40 md:w-48 lg:w-52 xl:w-56 shrink-0 bg-slate-100 relative overflow-hidden">
                                <img src="{{ $lThumb }}" alt="{{ $lastCourse->name }}"
                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>

                            <!-- Konten Teks & Progress Kanan -->
                            <div class="min-w-0 flex-1 flex flex-col justify-between p-3 sm:p-4">
                                <div class="mb-2">
                                    <span
                                        class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Aktivitas
                                        Terakhir</span>
                                    <h3 class="font-bold text-slate-800 text-base sm:text-lg line-clamp-2 mb-1.5 leading-snug">
                                        {{ $lastCourse->name }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                        {{ $lastCourse->description ?? 'Lanjutkan pembelajaran Anda pada kursus ini.' }}
                                    </p>
                                </div>

                                <div class="mt-auto">
                                    <div
                                        class="flex items-center justify-between text-[10px] sm:text-xs text-slate-500 mb-1 font-medium">
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
                                            class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 sm:py-2.5 rounded-md font-bold transition-all shadow-xs text-xs sm:text-sm group">
                                            <i class="fas fa-award text-xs sm:text-sm"></i>
                                            <span>Lihat Sertifikat</span>
                                        </a>
                                    @elseif ($lPct <= 0)
                                        <a href="{{ route('user.course.my-course.detail', $lastCourse->slug) }}?target=auto"
                                            class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 sm:py-2.5 rounded-md font-bold transition-all shadow-xs text-xs sm:text-sm group">
                                            <i class="fas fa-play text-[10px] sm:text-xs group-hover:scale-110 transition-transform"></i>
                                            <span>Mulai Belajar</span>
                                        </a>
                                    @else
                                        <a href="{{ route('user.course.my-course.detail', $lastCourse->slug) }}?target=auto"
                                            class="flex items-center justify-center w-full bg-amber-500 text-white hover:bg-amber-600 px-4 py-2 sm:py-2.5 rounded-md font-bold transition-all shadow-xs text-xs sm:text-sm group">
                                            <span>Lanjutkan Belajar</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="bg-slate-50 rounded-xl p-6 text-center my-2 flex flex-col justify-center items-center">
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

                <!-- CARD 2: Evaluasi Anda per Kursus (Analitik) -->
                <div class="bg-white rounded-md p-5 sm:p-6 shadow-sm border border-slate-200 flex flex-col lg:flex-1 min-h-0">
                    <div
                        class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4 pb-4 border-b border-slate-100 shrink-0">
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
                        <div class="flex-1 flex flex-col min-h-0 justify-between">
                            <!-- Area Scroll Vertikal (Hanya Label Sumbu Y dan Data Batang yang ter-scroll) -->
                            <div id="chartScrollContainer" class="relative w-full overflow-y-auto overflow-x-hidden flex-1 min-h-[160px] max-h-[300px] lg:max-h-none pr-1">
                                <div id="chartCanvasWrapper" class="relative w-full">
                                    <canvas id="postTestChart"></canvas>
                                </div>
                            </div>

                            <!-- Sumbu X Tetap / Fixed di Bawah (Angka 0, 20, 40, 60, 80, 100 tidak ikut ter-scroll) -->
                            <div id="chartXAxisWrapper" class="relative w-full h-6 shrink-0 border-t border-slate-200">
                                <canvas id="postTestXAxis" class="w-full h-full block"></canvas>
                            </div>

                            <!-- Penanda KKM & Status Kelulusan -->
                            <div
                                class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-2 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 shrink-0">
                                <span class="flex items-center gap-1.5"><span
                                        class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Lulus (≥ KKM)</span>
                                <span class="flex items-center gap-1.5"><span
                                        class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span> Di bawah KKM</span>
                                <span class="flex items-center gap-1.5"><span
                                        class="inline-block h-3 border-l-2 border-slate-500"></span> Penanda KKM tiap evaluasi</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-slate-50 rounded-xl p-10 text-center border border-dashed border-slate-200 my-auto">
                            <div
                                class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm text-slate-400">
                                <i class="fas fa-chart-area text-xl"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">Belum ada data evaluasi</p>
                            <p class="text-xs text-slate-500 mt-1">Daftar dan selesaikan modul kursus untuk memunculkan
                                laporan analitik evaluasi.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- ===================================== -->
            <!-- KOLOM KANAN (5 COLS): KURSUS & LIB    -->
            <!-- ===================================== -->
            <div class="lg:col-span-5 flex flex-col gap-5 sm:gap-6 lg:h-full min-h-0">

                <!-- CARD 3: Kursus Saya (Menyesuaikan Tinggi & Card ke-4 Mengintip Blur) -->
                <div class="bg-white rounded-none sm:rounded-md p-4 sm:p-6 shadow-none sm:shadow-sm flex flex-col lg:flex-1 w-full min-h-0">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 shrink-0">
                        <div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-800">Kursus Saya</h2>
                            <p class="text-xs sm:text-sm text-slate-500">Daftar kursus yang Anda ikuti</p>
                        </div>
                        <a href="{{ route('user.course.my-course') }}"
                            class="text-xs sm:text-sm font-semibold text-[#13416B] hover:text-[#0f3354] hover:underline transition-colors shrink-0">
                            Lihat semua kursus
                        </a>
                    </div>

                    <!-- Area daftar kursus yang di set relatif dan menyembunyikan elemen berlebih -->
                    <div class="relative flex-1">
                        <div class="space-y-3.5 sm:space-y-3 flex flex-col">
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

                                    // Warna progress: 100% hijau, sedang berprogress amber
                                    $rPct = (int) ($course->pivot->progress ?? 0);
                                    if ($rPct >= 100) {
                                        $rBar = 'bg-emerald-500';
                                        $rText = 'text-emerald-600';
                                    } else {
                                        $rBar = 'bg-amber-500';
                                        $rText = 'text-amber-600';
                                    }
                                @endphp

                                <!-- Tambahan "shrink-0" memastikan card kursus tidak memipih/gepeng meskipun meluber -->
                                <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                                    @if ($loop->index === 3) aria-hidden="true" tabindex="-1" @endif
                                    class="shrink-0 flex flex-row gap-3 sm:gap-4 bg-transparent sm:bg-slate-50/70 rounded-none sm:rounded-xl p-0 sm:p-4 border-b border-slate-100 sm:border-b-0 pb-3.5 sm:pb-0 transition-all duration-200 hover:bg-slate-100/90 hover:shadow-xs group items-start {{ $loop->index === 3 ? 'max-h-[76px] overflow-hidden pointer-events-none select-none' : '' }}">

                                    <div
                                        class="w-28 sm:w-36 aspect-video shrink-0 rounded-md overflow-hidden bg-slate-100 relative shadow-xs">
                                        <img src="{{ $rThumb }}" alt="{{ $course->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                                        {{-- Badge Status di Pojok Kanan Atas Thumbnail (Normal case & diperkecil) --}}
                                        @if ($course->pivot->status === 'completed' || $rPct >= 100)
                                            <span
                                                class="absolute top-0 right-0 z-10 px-1.5 sm:px-2 py-0.5 text-[7px] sm:text-[8px] font-semibold text-white bg-emerald-600 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md">
                                                <i class="fas fa-check-circle text-[6.5px] sm:text-[7.5px]"></i>
                                                <span>Selesai</span>
                                            </span>
                                        @elseif ($rPct > 0)
                                            <span
                                                class="absolute top-0 right-0 z-10 px-1.5 sm:px-2 py-0.5 text-[7px] sm:text-[8px] font-semibold text-white bg-amber-500 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md">
                                                <i class="fas fa-clock text-[6.5px] sm:text-[7.5px]"></i>
                                                <span>Berjalan</span>
                                            </span>
                                        @else
                                            <span
                                                class="absolute top-0 right-0 z-10 px-1.5 sm:px-2 py-0.5 text-[7px] sm:text-[8px] font-semibold text-white bg-slate-500/90 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md">
                                                <i class="fas fa-play text-[6.5px] sm:text-[7.5px]"></i>
                                                <span>Belum Mulai</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1 flex flex-col justify-between h-full w-full py-0.5">
                                        <div>
                                            <h3
                                                class="font-bold text-slate-800 text-sm sm:text-base line-clamp-2 leading-snug group-hover:text-[#13416B] transition-colors mb-1">
                                                {{ $course->name }}
                                            </h3>

                                            <p
                                                class="line-clamp-2 text-xs text-slate-500 mb-2 leading-relaxed">
                                                {{ $course->description ?? 'Deskripsi kursus tidak tersedia.' }}
                                            </p>
                                        </div>

                                        <div>
                                            <div
                                                class="flex items-center justify-between text-[10px] sm:text-xs text-slate-500 mb-1 font-medium">
                                                <span>Progress</span>
                                                <span class="font-bold {{ $rText }}">{{ $course->pivot->progress }}%</span>
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
                                    class="text-center py-10 bg-slate-50 rounded-xl">
                                    <p class="text-xs font-semibold text-slate-600">Belum ada kursus yang diikuti.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Kursus ke-4 dst. hanya mengintip (terpotong + blur) --}}
                        @if ($recentCourses->count() > 3)
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-14">
                                <div
                                    class="absolute inset-0 backdrop-blur-[1px] [mask-image:linear-gradient(to_top,black,transparent)] [-webkit-mask-image:linear-gradient(to_top,black,transparent)]">
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-white/90 via-white/30 to-transparent">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CARD 4: Terakhir Dilihat (Perpustakaan) -->
                <div
                    class="bg-white rounded-none sm:rounded-md p-4 sm:p-5 shadow-none sm:shadow-sm border-0 sm:border border-slate-200 flex flex-col justify-between shrink-0">
                    <div>
                        <div class="mb-3 pb-3 border-b border-slate-100 shrink-0">
                            <h2 class="text-base md:text-lg font-extrabold text-slate-800">Terakhir Dilihat</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500">Koleksi perpustakaan terakhir diakses</p>
                        </div>

                        @if (isset($lastLibrary) && $lastLibrary)
                            <a href="{{ route('user.library.index') }}?open={{ $lastLibrary->id }}"
                                class="flex items-stretch gap-3 sm:gap-4 p-0 sm:p-4 rounded-none sm:rounded-xl border-0 sm:border border-slate-100 bg-transparent sm:bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-all group">

                                <div
                                    class="w-20 min-h-[6.5rem] sm:min-h-[5.5rem] self-stretch rounded-md overflow-hidden shrink-0 shadow-sm relative bg-white border border-slate-200 flex items-center justify-center">
                                    @if ($lastLibrary->cover_image)
                                        <img src="{{ $lastLibrary->cover_image_url }}"
                                            alt="Cover" class="absolute inset-0 w-full h-full object-cover">
                                        <div
                                            class="absolute bottom-1 right-1 w-5 h-5 bg-[#13416B] text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">
                                            <i class="{{ $lastLibrary->icon }}"></i>
                                        </div>
                                    @else
                                        <i
                                            class="{{ $lastLibrary->icon }} text-[#547996] text-2xl group-hover:scale-110 transition-transform"></i>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0 py-0.5 flex flex-col justify-start">
                                    <div class="flex justify-between items-center sm:items-start mb-1.5">
                                        <span
                                            class="inline-block truncate max-w-[55%] sm:max-w-none px-2 sm:px-2.5 py-0.5 text-[9px] sm:text-[10px] font-extrabold rounded-md bg-[#547996]/10 text-[#547996] border border-[#547996]/20">
                                            {{ $lastLibrary->libraryCategory->name ?? 'Kategori Umum' }}
                                        </span>
                                        <span class="text-[9px] sm:text-[11px] text-slate-400 font-medium shrink-0">
                                            {{ \Carbon\Carbon::parse($lastLibrary->last_accessed_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <h3
                                        class="text-xs sm:text-base font-bold text-slate-800 leading-snug line-clamp-2 group-hover:text-[#13416B] transition-colors mb-1">
                                        {{ $lastLibrary->title }}
                                    </h3>
                                    <p class="line-clamp-2 text-[10px] sm:text-xs text-slate-500 leading-relaxed">
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
                    const maxChars = window.innerWidth >= 640 ? 30 : 18;
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
                        plugins: [
                            kkmAndValuePlugin,
                            {
                                id: 'fixedXAxisTrigger',
                                afterRender() {
                                    setTimeout(drawFixedXAxis, 15);
                                }
                            }
                        ],
                        data: {
                            labels: (currentData.labels || []).map(label => formatMultilineLabel(label)),
                            datasets: [{
                                label: 'Skor Anda',
                                data: currentData.user_scores,
                                backgroundColor: dynamicColors,
                                borderRadius: 6,
                                maxBarThickness: 36,
                                barPercentage: 0.72,
                                categoryPercentage: 0.88
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 12,
                                    bottom: 6,
                                    right: 32
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
                                        display: false
                                    },
                                    border: {
                                        display: false
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
                                            if (scaleInstance.width < 160) {
                                                scaleInstance.width = 160;
                                            }
                                        } else {
                                            if (scaleInstance.width < 110) {
                                                scaleInstance.width = 110;
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

                    // Fungsi render sumbu X tetap (fixed scale 0, 20, 40, 60, 80, 100)
                    function drawFixedXAxis() {
                        const xCanvas = document.getElementById('postTestXAxis');
                        if (!xCanvas || !postTestChart) return;

                        const rect = xCanvas.getBoundingClientRect();
                        if (rect.width === 0) return;

                        const dpr = window.devicePixelRatio || 1;
                        xCanvas.width = rect.width * dpr;
                        xCanvas.height = rect.height * dpr;

                        const ctxX = xCanvas.getContext('2d');
                        ctxX.scale(dpr, dpr);
                        ctxX.clearRect(0, 0, rect.width, rect.height);

                        const xScale = postTestChart.scales.x;
                        if (!xScale) return;

                        const mainCanvasRect = postTestChart.canvas.getBoundingClientRect();
                        const xOffset = mainCanvasRect.left - rect.left;

                        ctxX.fillStyle = '#64748b';
                        ctxX.font = '600 10px sans-serif';
                        ctxX.textAlign = 'center';
                        ctxX.textBaseline = 'top';

                        const tickValues = [0, 20, 40, 60, 80, 100];
                        tickValues.forEach(val => {
                            const xPos = xScale.getPixelForValue(val) + xOffset;
                            if (xPos >= 0 && xPos <= rect.width) {
                                ctxX.strokeStyle = '#cbd5e1';
                                ctxX.lineWidth = 1;
                                ctxX.beginPath();
                                ctxX.moveTo(xPos, 0);
                                ctxX.lineTo(xPos, 4);
                                ctxX.stroke();

                                ctxX.fillText(val.toString(), xPos, 6);
                            }
                        });
                    }

                    // Fungsi dinamis untuk menyesuaikan tinggi canvas:
                    // 1. Mengatur jarak antar data evaluasi pada sumbu Y agar proporsional dan tidak ada ruang kosong terhadap sumbu X di bawah.
                    // 2. Menyesuaikan tinggi canvas secara penuh dengan kontainer scroll yang tersedia agar data mendekati sumbu X.
                    // 3. Jika data banyak, dialokasikan minimal 70px per bar dan kontainer scroll aktif secara vertikal.
                    function updateChartHeight(labelsCount) {
                        const scrollContainer = document.getElementById('chartScrollContainer');
                        const canvasWrapper = document.getElementById('chartCanvasWrapper') || (ctx ? ctx.parentElement : null);
                        if (!scrollContainer || !canvasWrapper) return;

                        const count = typeof labelsCount === 'number'
                            ? labelsCount
                            : (postTestChart && postTestChart.data.labels ? postTestChart.data.labels.length : 1);

                        const containerHeight = scrollContainer.clientHeight || 0;
                        const minHeightByCount = count * 70;
                        const finalHeight = Math.max(containerHeight, minHeightByCount, 180);

                        canvasWrapper.style.height = finalHeight + 'px';

                        if (postTestChart) {
                            postTestChart.resize();
                            drawFixedXAxis();
                        }
                    }

                    const filterSelect = document.getElementById('courseChartFilter');
                    if (filterSelect) {
                        filterSelect.addEventListener('change', function() {
                            const selectedId = this.value;
                            const newData = allChartData[selectedId];

                            if (newData) {
                                currentCourseId = selectedId;
                                currentData = newData;

                                postTestChart.data.labels = (newData.labels || []).map(label => formatMultilineLabel(label));
                                postTestChart.data.datasets[0].data = newData.user_scores;
                                currentKkms = getKkms(newData);
                                postTestChart.options.plugins.kkmAndValue.kkms = currentKkms;
                                postTestChart.data.datasets[0].backgroundColor = scoreColors(newData.user_scores, currentKkms);

                                updateChartHeight((newData.labels || []).length);
                                postTestChart.update();
                                setTimeout(drawFixedXAxis, 30);
                            }
                        });
                    }

                    // Inisialisasi tinggi chart awal dan sinkronisasi sumbu X
                    updateChartHeight((currentData.labels || []).length);
                    setTimeout(() => {
                        updateChartHeight((currentData.labels || []).length);
                        if (postTestChart) {
                            postTestChart.resize();
                            drawFixedXAxis();
                        }
                    }, 80);

                    // ResizeObserver untuk memantau perubahan ukuran kontainer (misal: saat card lain selesai memuat gambar/font)
                    if (window.ResizeObserver) {
                        let roTimer;
                        const ro = new ResizeObserver(() => {
                            clearTimeout(roTimer);
                            roTimer = setTimeout(() => {
                                const count = currentData && currentData.labels ? currentData.labels.length : 1;
                                updateChartHeight(count);
                            }, 40);
                        });
                        const scrollContainer = document.getElementById('chartScrollContainer');
                        if (scrollContainer) ro.observe(scrollContainer);
                    }

                    // Saat semua resource (termasuk gambar thumbnail kursus) selesai dimuat
                    window.addEventListener('load', function() {
                        const count = currentData && currentData.labels ? currentData.labels.length : 1;
                        updateChartHeight(count);
                    });

                    // Resize listener saat window diubah ukurannya
                    window.addEventListener('resize', function() {
                        const count = currentData && currentData.labels ? currentData.labels.length : 1;
                        updateChartHeight(count);
                        if (postTestChart && currentData) {
                            postTestChart.data.labels = (currentData.labels || []).map(label => formatMultilineLabel(label));
                            postTestChart.resize();
                        }
                        setTimeout(drawFixedXAxis, 50);
                    });
                }
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