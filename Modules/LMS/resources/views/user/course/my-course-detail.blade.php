<x-dashboard::layouts.dashboard
    title="Kursus Saya | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    <!-- PERBAIKAN: Menghapus padding horizontal di mobile (px-0) -->
    <div class="px-0 py-2 sm:p-6 max-w-full mx-auto">

        {{-- Breadcrumb Custom (Responsif & Tanpa Icon Home) --}}
        <nav class="flex mb-4 sm:mb-6 mt-0 sm:mt-4 " aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-y-1 gap-x-1.5 sm:gap-x-2">
                <li>
                    <a href="{{ route('user.course.my-course') }}"
                        class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors whitespace-nowrap">
                        Kursus Saya
                    </a>
                </li>
                <li>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-[10px]"></i>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug truncate max-w-[200px] xs:max-w-[260px] sm:max-w-none">
                            {{ data_get($course, 'course_name') ?? data_get($course, 'name') }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- PROPERTI DASAR TAMPILAN (Disiapkan dari CourseService) --}}
        @php
            $thumbnailUrl = data_get($course, 'thumbnail_url');
            $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
            $courseSlug = data_get($course, 'slug') ?? request()->route('slug');
            $courseId = data_get($course, 'id') ?? data_get($course, 'course_id');
            $sectionsList = data_get($course, 'sections', []);
            $totalSections = count($sectionsList);

            if (empty($thumbnailUrl)) {
                $encodedName = urlencode($courseName);
                $thumbnailUrl = "https://ui-avatars.com/api/?name={$encodedName}&background=eff6ff&color=1e3a8a&size=512&font-size=0.33&bold=true";
            }
        @endphp

        {{-- Header Card --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-5 lg:p-6 mb-6 transition-all">
            
            <div class="flex flex-col md:flex-row gap-4 sm:gap-5 lg:gap-6 items-start">
                {{-- Thumbnail Bersih & Proporsional (rounded-md) --}}
                <div class="w-full md:w-52 lg:w-64 shrink-0 rounded-md overflow-hidden bg-slate-100 aspect-video md:aspect-[16/10] border border-slate-200 shadow-2xs relative">
                    <img src="{{ $thumbnailUrl }}" alt="{{ $courseName }}" class="w-full h-full object-cover" />
                </div>

                {{-- Detail Informasi & Progress --}}
                <div class="flex-1 min-w-0 w-full flex flex-col justify-between gap-3.5 sm:gap-4.5">
                    <div>
                        {{-- Baris Judul & Tombol Panduan --}}
                        <div class="flex items-start justify-between gap-3">
                            <h1 class="text-base sm:text-xl lg:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                                {{ $courseName }}
                            </h1>

                            <button type="button" onclick="window.startCoursePlayerTour && window.startCoursePlayerTour()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-[#13416B] border border-slate-200 rounded-md transition-all shadow-2xs cursor-pointer shrink-0">
                                <i class="fas fa-route text-[#13416B]"></i>
                                <span>Panduan</span>
                            </button>
                        </div>

                        {{-- Info Pelatihan / Event: Terstruktur & Fleksibel (Bimtek vs Kursus Mandiri) --}}
                        @if ($isBimtekEvent)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5 mt-3.5 sm:mt-4 max-w-2xl">
                                @if ($activeBimtek->batch_name)
                                    <div class="flex items-center gap-3 px-3.5 py-2.5 sm:py-3 rounded-md bg-slate-50/80 border border-slate-200/80 text-xs text-slate-700 min-w-0">
                                        <div class="w-8 h-8 rounded-md bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-satellite-dish text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1 flex flex-col gap-1.5 sm:gap-2">
                                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Gelombang / Batch</span>
                                            <span class="font-bold text-slate-800 truncate text-xs sm:text-[13px] leading-tight" title="{{ $activeBimtek->batch_name }}">{{ $activeBimtek->batch_name }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($formattedPeriod)
                                    <div class="flex items-center gap-3 px-3.5 py-2.5 sm:py-3 rounded-md bg-slate-50/80 border border-slate-200/80 text-xs text-slate-700 min-w-0">
                                        <div class="w-8 h-8 rounded-md bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center shrink-0">
                                            <i class="far fa-calendar-alt text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1 flex flex-col gap-1.5 sm:gap-2">
                                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Periode Pelaksanaan</span>
                                            <span class="font-bold text-slate-800 truncate text-xs sm:text-[13px] leading-tight">{{ $formattedPeriod }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5 mt-3.5 sm:mt-4 max-w-2xl">
                                <div class="flex items-center gap-3 px-3.5 py-2.5 sm:py-3 rounded-md bg-slate-50/80 border border-slate-200/80 text-xs text-slate-700 min-w-0">
                                    <div class="w-8 h-8 rounded-md bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center shrink-0">
                                        <i class="fas fa-laptop-code text-xs"></i>
                                    </div>
                                    <div class="min-w-0 flex-1 flex flex-col gap-1.5 sm:gap-2">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Tipe Pelatihan</span>
                                        <span class="font-bold text-slate-800 truncate text-xs sm:text-[13px] leading-tight">Kursus Mandiri (Self-Paced)</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 px-3.5 py-2.5 sm:py-3 rounded-md bg-slate-50/80 border border-slate-200/80 text-xs text-slate-700 min-w-0">
                                    <div class="w-8 h-8 rounded-md bg-blue-50 text-[#13416B] border border-blue-100 flex items-center justify-center shrink-0">
                                        <i class="far fa-clock text-xs"></i>
                                    </div>
                                    <div class="min-w-0 flex-1 flex flex-col gap-1.5 sm:gap-2">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Akses Belajar</span>
                                        <span class="font-bold text-slate-800 truncate text-xs sm:text-[13px] leading-tight">Fleksibel (Kapan Saja)</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Progress Bar (Tanpa Icon Progres) --}}
                    <div data-tour="progres" class="w-full max-w-2xl pt-2.5 sm:pt-3.5 border-t border-slate-100">
                        <div class="flex justify-between items-center mb-1.5 text-xs">
                            <span class="font-semibold text-slate-600">
                                {{ $isWaitingForStart ? 'Status Pelatihan' : 'Progres Pembelajaran' }}
                            </span>
                            <span class="font-bold text-xs sm:text-sm {{ $isFullyCompleted ? 'text-emerald-600' : 'text-[#13416B]' }}">
                                {{ $isWaitingForStart ? 'Menunggu Pelaksanaan' : $currentProgress . '%' }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/50">
                            <div class="{{ $progressBarClass }} h-full rounded-full transition-all duration-700 shadow-2xs"
                                style="width: {{ $isWaitingForStart ? 0 : $currentProgress }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid Layout (Materi & Sertifikat) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Left column: List Modul & Evaluasi Akhir --}}
            <div class="lg:col-span-2 space-y-6">

                <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                <div data-tour="modul"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6">

                    <div
                        class="flex items-center justify-between mb-5 sm:mb-6 pb-4 border-b border-slate-100 gap-3 sm:gap-4">
                        <div class="flex items-start gap-3 sm:gap-3.5">
                            <i class="fas {{ $isBimtekEvent ? 'fa-calendar-alt' : 'fa-layer-group' }} text-[#13416B] text-xl sm:text-2xl mt-0.5 shrink-0"></i>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-700 leading-tight">
                                    {{ $isBimtekEvent ? 'Timeline & Agenda Pelaksanaan' : 'Daftar Modul Pembelajaran' }}
                                </h2>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-1">
                                    {{ $isBimtekEvent 
                                        ? 'Alur tahapan harian: sesi tatap muka (Zoom), pembelajaran materi, dan evaluasi harian' 
                                        : 'Pelajari seluruh materi modul secara mandiri dan bertahap sesuai kecepatan belajar Anda' }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md shrink-0">
                            {{ $totalSections }} {{ $isBimtekEvent ? 'Hari' : 'Modul' }}
                        </span>
                    </div>

                    <div class="relative space-y-4 sm:space-y-5">
                        @php
                            $sectionProofs = $sectionProofs ?? [];
                            $isPreviousSectionDone = true;
                        @endphp

                        {{-- Garis vertikal timeline yang menyambung secara kontinu dari badge pertama hingga badge terakhir --}}
                        @if ($totalSections > 1)
                            <div class="absolute left-6 sm:left-7 top-6 sm:top-7 bottom-6 sm:bottom-7 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
                        @endif

                        @forelse ($sectionsList as $index => $section)
                            @php
                                $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
                                $rawSectionName = data_get($section, 'name', '');
                                // Hapus prefix seperti "Modul 1:", "Modul 1 -", "Hari 1:", dll agar langsung ke judul materi
                                $cleanSectionName = preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $rawSectionName);
                                $displayTitle = !empty(trim($cleanSectionName)) ? trim($cleanSectionName) : ($rawSectionName ?: ($isBimtekEvent ? 'Hari ' : 'Modul ') . ($index + 1));

                                $dayDate = null;
                                try {
                                    if ($activeBimtek && $activeBimtek->start_date) {
                                        $dayDate = \Carbon\Carbon::parse($activeBimtek->start_date)->addDays($index);
                                    }
                                } catch (\Exception $e) {}

                                $sectionContentsRaw = data_get($section, 'section_contents', []) ?: data_get($section, 'contents', []);
                                $totalCount = count($sectionContentsRaw);
                                $completedCount = data_get($section, 'completed_count', 0);
                                $isContentCompleted = data_get($section, 'is_content_completed', $totalCount === 0 || $completedCount === $totalCount);
                                $postTestBab = data_get($section, 'post_test_bab');
                                $isPostTestBabCompleted = data_get($section, 'is_post_test_bab_completed', true);
                                $hasZoomProof = !empty($sectionProofs[$sectionId]);
                                $isSectionCompleted = data_get($section, 'is_section_completed', $isContentCompleted && $isPostTestBabCompleted);
                                $isLocked = $canReviewAll ? false : data_get($section, 'is_locked', !$isPreviousSectionDone);
                                $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
                            @endphp

                            <div class="relative flex items-start gap-3 sm:gap-4 group">
                                {{-- Node Timeline: Badge Hari / Modul seperti Kalender --}}
                                <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-md {{ $isLocked ? 'bg-slate-100 text-slate-400 border border-slate-300 border-r-2 border-b-2 border-r-slate-400 border-b-slate-400' : 'bg-[#13416B] text-white border border-[#13416B] border-r-2 border-b-2 border-r-[#092035] border-b-[#092035] shadow-2xs' }} shrink-0 z-[1] relative transition-transform group-hover:scale-105">
                                    <span class="text-[9px] font-bold uppercase tracking-wider {{ $isLocked ? 'text-slate-400' : 'text-slate-300' }} leading-none">
                                        {{ $isBimtekEvent ? 'HARI' : 'MODUL' }}
                                    </span>
                                    <span class="text-base sm:text-lg font-black leading-tight mt-0.5">{{ $index + 1 }}</span>
                                </div>

                                {{-- Card Modul Harian (rounded-md) --}}
                                <div
                                    class="flex-1 min-w-0 border {{ $isLocked ? 'border-slate-200/80 bg-slate-50/60' : 'border-slate-200 bg-white hover:border-[#13416B]/40 hover:shadow-xs' }} rounded-md p-4 sm:p-5 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    
                                    {{-- Kolom Kiri: Judul Langsung & Ringkasan Agenda Terpadu dengan Radio Ceklis --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <h3 class="font-bold {{ $isLocked ? 'text-slate-500' : 'text-slate-800' }} text-sm sm:text-base leading-snug break-words">
                                                {{ $displayTitle }}
                                            </h3>
                                            @if ($isBimtekEvent && $dayDate)
                                                <span class="text-slate-300 hidden sm:inline">•</span>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 shrink-0">
                                                    <i class="far fa-calendar-alt text-slate-400"></i> {{ $dayDate->translatedFormat('d F Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Baris Aktivitas: Micro-checklist Bersih, Proporsional & Adaptif --}}
                                        <div class="flex flex-wrap items-center gap-x-4 sm:gap-x-5 gap-y-2 mt-2.5 sm:mt-3">
                                            {{-- 1. Sesi Tatap Muka Online (Khusus Bimtek dengan Zoom) --}}
                                            @if ($isBimtekEvent && $hasZoom)
                                                <div class="inline-flex items-center gap-1.5 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isZoomAttended ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $isZoomAttended ? 'Tatap Muka: Hadir' : 'Tatap Muka: Belum Hadir' }}">
                                                    @if ($isZoomAttended)
                                                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[9px] shadow-2xs">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    @else
                                                        <span class="w-4 h-4 rounded-full border-2 {{ $isLocked ? 'border-slate-300 bg-slate-100' : 'border-slate-300 bg-white' }} shrink-0"></span>
                                                    @endif
                                                    <span>Tatap Muka</span>
                                                </div>
                                            @endif

                                            {{-- 2. Topik Materi Mandiri --}}
                                            <div class="inline-flex items-center gap-1.5 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isContentCompleted ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="Materi Pembelajaran">
                                                @if ($isContentCompleted)
                                                    <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[9px] shadow-2xs">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                @else
                                                    <span class="w-4 h-4 rounded-full border-2 {{ $isLocked ? 'border-slate-300 bg-slate-100' : 'border-slate-300 bg-white' }} shrink-0"></span>
                                                @endif
                                                <span>{{ $isBimtekEvent ? 'Materi' : 'Materi (' . $totalCount . ' Topik)' }}</span>
                                            </div>

                                            {{-- 3. Upload Bukti Pelengkap / Tugas Drive (Khusus Bimtek dengan Zoom) --}}
                                            @if ($isBimtekEvent && $hasZoom)
                                                <div class="inline-flex items-center gap-1.5 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($hasZoomProof ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $hasZoomProof ? 'Bukti pelengkap sudah diunggah' : 'Belum upload bukti pelengkap' }}">
                                                    @if ($hasZoomProof)
                                                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[9px] shadow-2xs">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    @else
                                                        <span class="w-4 h-4 rounded-full border-2 {{ $isLocked ? 'border-slate-300 bg-slate-100' : 'border-slate-300 bg-white' }} shrink-0"></span>
                                                    @endif
                                                    <span>Bukti Pelengkap</span>
                                                </div>
                                            @endif

                                            {{-- 4. Evaluasi Harian / Modul (Post-Test Bab) --}}
                                            @if ($postTestBab)
                                                <div class="inline-flex items-center gap-1.5 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isPostTestBabCompleted ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $isPostTestBabCompleted ? 'Evaluasi tuntas' : 'Evaluasi belum dikerjakan' }}">
                                                    @if ($isPostTestBabCompleted)
                                                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[9px] shadow-2xs">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    @else
                                                        <span class="w-4 h-4 rounded-full border-2 {{ $isLocked ? 'border-slate-300 bg-slate-100' : 'border-slate-300 bg-white' }} shrink-0"></span>
                                                    @endif
                                                    <span>{{ $isBimtekEvent ? 'Evaluasi Harian' : 'Evaluasi Modul' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Kolom Kanan: Tombol Masuk ke Isi Modul (Brand Primary Navy #13416B) --}}
                                    <div class="shrink-0 self-end md:self-center w-full md:w-auto">
                                        @if ($isWaitingForStart)
                                            <button type="button" disabled
                                                title="Modul dibuka saat periode pelaksanaan pelatihan resmi dimulai"
                                                class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none whitespace-nowrap">
                                                <span>Terkunci</span>
                                            </button>
                                        @elseif ($isLocked)
                                            <button type="button" disabled
                                                title="Selesaikan modul sebelumnya terlebih dahulu"
                                                class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none whitespace-nowrap">
                                                <span>Terkunci</span>
                                            </button>
                                        @else
                                            <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                                class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all whitespace-nowrap">
                                                <span>Masuk</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php
                                $isPreviousSectionDone = $isSectionCompleted;
                            @endphp
                        @empty
                            <div
                                class="p-8 text-center text-slate-500 bg-slate-50 rounded-md border border-dashed border-slate-200">
                                <i class="fas fa-calendar-times mb-3 text-slate-300 text-3xl block"></i>
                                <p class="font-semibold text-sm">Belum ada agenda pelaksanaan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- CARD EVALUASI AKHIR (FLEKSIBEL: PILIHAN GANDA CBT / PENUGASAN GOOGLE DRIVE) --}}
                @php
                    $evaluasiAkhirTitle = 'Evaluasi Akhir : ' . $courseName;
                    $evaluasiQuestionsCount = $evaluasiAkhir ? data_get($evaluasiAkhir, 'questions', collect())->count() : 0;
                    $isCbtPostTest = $evaluasiAkhir && $evaluasiQuestionsCount > 0;
                    $isDriveAssignment = $evaluasiAkhir && $evaluasiQuestionsCount === 0;
                    $adminDriveLink = $activeBimtek?->submission_drive_link ?? null;
                @endphp

                @if ($evaluasiAkhir)
                    <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                    <div data-tour="evaluasi"
                        class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 transition-all">
                        <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                            {{-- Kolom Kiri: Informasi Evaluasi & Tombol Aksi --}}
                            <div class="flex-1 min-w-0 sm:py-1">
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 leading-snug max-w-full lg:max-w-[85%]">
                                    {{ $evaluasiAkhirTitle }}
                                </h3>

                                {{-- Deskripsi Dinamis Mengenai Evaluasi Akhir --}}
                                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                    @if ($isCbtPostTest)
                                        Ujian evaluasi komprehensif seluruh materi sebagai syarat kelulusan dan penerbitan sertifikat.
                                    @else
                                        Pengumpulan berkas tugas akhir atau portofolio pelatihan ke Google Drive panitia sebagai syarat kelulusan dan penerbitan sertifikat.
                                    @endif
                                </p>

                                <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-2.5 sm:gap-3">
                                    @if ($isCbtPostTest)
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-list-ol text-[10px] sm:text-xs text-white"></i>
                                            {{ $evaluasiQuestionsCount }} Soal
                                        </span>
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-stopwatch text-[10px] sm:text-xs text-white"></i>
                                            {{ data_get($evaluasiAkhir, 'duration', 0) }} Menit
                                        </span>
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-bullseye text-[10px] sm:text-xs text-white"></i> KKM:
                                            {{ data_get($evaluasiAkhir, 'passing_score', 0) }}
                                        </span>
                                    @else
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fab fa-google-drive text-[10px] sm:text-xs text-white"></i>
                                            Tugas Google Drive
                                        </span>
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-file-invoice text-[10px] sm:text-xs text-white"></i>
                                            Unggah Dokumen
                                        </span>
                                        <span
                                            class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-user-check text-[10px] sm:text-xs text-white"></i>
                                            Review Instruktur
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4 sm:mt-5 flex flex-wrap items-center gap-2.5 justify-start">
                                    @if ($isCbtPostTest)
                                        @if ($isEvaluasiAkhirCompleted)
                                            <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all shadow-xs">
                                                <i class="fas fa-eye text-xs"></i> Lihat Hasil
                                            </a>
                                        @elseif ($isWaitingForStart)
                                            <button type="button" disabled
                                                title="Evaluasi dibuka saat periode pelaksanaan pelatihan resmi dimulai"
                                                class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                                <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                            </button>
                                        @elseif (!$isEvaluasiAkhirLocked && !$isEventEnded)
                                            <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                                <i class="fas fa-play text-xs"></i> Mulai Evaluasi
                                            </a>
                                        @elseif ($isEventEnded && !$isEvaluasiAkhirCompleted)
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                <i class="fas fa-clock text-slate-400"></i>
                                                <span>Periode Pelatihan Telah Berakhir</span>
                                            </div>
                                        @else
                                            <button type="button" disabled
                                                title="Selesaikan materi terlebih dahulu"
                                                class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                                <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                            </button>
                                        @endif
                                    @else
                                        {{-- Penugasan Akhir via Google Drive --}}
                                        @if ($adminDriveLink)
                                            <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                                <i class="fab fa-google-drive text-xs"></i>
                                                <span>Buka Folder Drive Tugas</span>
                                                <i class="fas fa-external-link-alt text-[10px] ml-0.5 opacity-80"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all shadow-xs">
                                            <i class="fas fa-tasks text-xs"></i>
                                            <span>Lembar Penyerahan Tugas</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Kolom Kanan: Ilustrasi Gambar khusus Layar Tablet/Desktop (sm ke atas) --}}
                            <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-3.5 lg:-mb-4">
                                <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                    alt="Ilustrasi Evaluasi Akhir"
                                    class="sm:h-44 md:h-48 lg:h-52 w-auto object-contain object-bottom select-none pointer-events-none" />
                            </div>
                        </div>
                    </div>
                @endif

                @if ($isBimtekEvent)
                {{-- CARD EVALUASI NARASUMBER / PEMATERI (KHUSUS BIMTEK) --}}
                <div data-tour="evaluasi-narasumber"
                    id="card-evaluasi-narasumber"
                    @open-evaluasi-narasumber.window="showModal = true; $el.scrollIntoView({ behavior: 'smooth' })"
                    x-data="{
                        showModal: false,
                        loading: false,
                        submitted: {{ $isEvaluasiNarasumberCompleted ? 'true' : 'false' }},
                        ratingMateri: {{ $existingEvaluasiNarasumber?->rating_materi ?? 5 }},
                        ratingPenyampaian: {{ $existingEvaluasiNarasumber?->rating_penyampaian ?? 5 }},
                        ratingFasilitasi: {{ $existingEvaluasiNarasumber?->rating_fasilitasi ?? 5 }},
                        feedback: @js($existingEvaluasiNarasumber?->feedback ?? ''),
                        showSuccessToast: false,
                        errorMessage: '',
                        async saveEvaluation() {
                            if (this.loading) return;
                            this.loading = true;
                            this.errorMessage = '';
                            try {
                                const response = await fetch('{{ route('user.course.my-course.submit-evaluasi-narasumber', ['slug' => $courseSlug]) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        rating_materi: this.ratingMateri,
                                        rating_penyampaian: this.ratingPenyampaian,
                                        rating_fasilitasi: this.ratingFasilitasi,
                                        feedback: this.feedback
                                    })
                                });
                                const data = await response.json();
                                if (response.ok && data.success) {
                                    this.submitted = true;
                                    this.showModal = false;
                                    this.showSuccessToast = true;
                                    window.dispatchEvent(new CustomEvent('evaluasi-narasumber-submitted', { detail: data.data }));
                                    setTimeout(() => {
                                        this.showSuccessToast = false;
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    this.errorMessage = data.message || 'Gagal menyimpan evaluasi. Silakan periksa isian Anda.';
                                }
                            } catch (error) {
                                console.error('Error submitting evaluation:', error);
                                this.errorMessage = 'Terjadi gangguan jaringan saat mengirim data. Silakan coba beberapa saat lagi.';
                            } finally {
                                this.loading = false;
                            }
                        }
                    }"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 transition-all">

                    {{-- Alert Notifikasi Sukses --}}
                    <div x-show="showSuccessToast" x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="mb-4 p-3.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-600 text-base shrink-0"></i>
                        <span>Terima kasih! Evaluasi narasumber & fasilitas bimtek berhasil dikirimkan.</span>
                    </div>

                    <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                        {{-- Kolom Kiri: Informasi Evaluasi Narasumber & Tombol Aksi --}}
                        <div class="flex-1 min-w-0 sm:py-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 leading-snug">
                                    Evaluasi Narasumber & Penyelenggaraan
                                </h3>
                                <template x-if="submitted">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-check-circle text-emerald-600 text-xs"></i> Sudah Diisi
                                    </span>
                                </template>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                Penilaian objektif terhadap penyampaian materi oleh narasumber dan pelaksanaan bimbingan teknis.
                            </p>

                            {{-- Meta Badges (Sesuai Gaya Desain Evaluasi Akhir) --}}
                            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-2.5 sm:gap-3">
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-user-secret text-[10px] sm:text-xs text-white"></i> Anonim & Rahasia
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="far fa-clock text-[10px] sm:text-xs text-white"></i> ± 3 Menit
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-star text-[10px] sm:text-xs text-amber-300"></i> Skala 1 - 5
                                </span>
                            </div>

                            {{-- Tombol Aksi di Bawah Badges (Konsisten dengan Evaluasi Akhir) --}}
                            <div class="mt-4 sm:mt-5 flex justify-start">
                                @if ($isEvaluasiNarasumberLocked)
                                    <button type="button" disabled
                                        title="{{ $evaluasiNarasumberLockReason }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                        <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                    </button>
                                @else
                                    <button type="button" @click="showModal = true"
                                        :class="submitted ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm'"
                                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold transition-all shadow-xs">
                                        <i :class="submitted ? 'fas fa-eye' : 'fas fa-pen-alt'" class="text-xs"></i>
                                        <span x-text="submitted ? 'Lihat / Edit Ulasan' : 'Mulai Evaluasi'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Kolom Kanan: Ilustrasi Narasumber khusus Layar Tablet/Desktop (sm ke atas) --}}
                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-5 sm:-mb-5 lg:-mb-6">
                            <img src="{{ asset('images/narasumber_ilustrasi.webp') }}"
                                alt="Ilustrasi Narasumber"
                                class="sm:h-44 md:h-48 lg:h-52 w-auto object-contain object-bottom select-none pointer-events-none" />
                        </div>
                    </div>

                    {{-- Modal Kuesioner Evaluasi Narasumber (Popup Elegan) --}}
                    <template x-teleport="body">
                        <div x-show="showModal" x-cloak
                            @keydown.escape.window="showModal = false"
                            class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                            
                            <div @click.away="showModal = false"
                                class="bg-white rounded-lg shadow-xl max-w-lg w-full p-5 sm:p-6 space-y-4 text-left border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
                            
                            {{-- Header Modal --}}
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-md bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-sm font-bold">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-800">Evaluasi & Feedback Narasumber</h4>
                                </div>
                                <button type="button" @click="showModal = false"
                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition-colors">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>

                            <p class="text-xs text-slate-500 leading-relaxed">
                                Mohon berikan penilaian secara objektif mengenai pelaksanaan bimbingan teknis dan narasumber. Data evaluasi ini bersifat anonim dan digunakan untuk peningkatan mutu.
                            </p>

                            <div class="space-y-4 py-1">
                                {{-- 1. Penguasaan Materi --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        1. Penguasaan Materi & Kejelasan Paparan Narasumber
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" @click="ratingMateri = star"
                                                class="text-xl sm:text-2xl transition-transform hover:scale-110 focus:outline-none"
                                                :class="star <= ratingMateri ? 'text-amber-400' : 'text-slate-200'">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </template>
                                        <span class="text-xs font-semibold text-slate-600 ml-2" x-text="ratingMateri + ' / 5'"></span>
                                    </div>
                                </div>

                                {{-- 2. Interaktivitas & Komunikasi --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        2. Metode Penyampaian & Interaktivitas Sesi
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" @click="ratingPenyampaian = star"
                                                class="text-xl sm:text-2xl transition-transform hover:scale-110 focus:outline-none"
                                                :class="star <= ratingPenyampaian ? 'text-amber-400' : 'text-slate-200'">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </template>
                                        <span class="text-xs font-semibold text-slate-600 ml-2" x-text="ratingPenyampaian + ' / 5'"></span>
                                    </div>
                                </div>

                                {{-- 3. Ketepatan Waktu & Fasilitasi --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        3. Ketepatan Waktu & Pengelolaan Diskusi
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" @click="ratingFasilitasi = star"
                                                class="text-xl sm:text-2xl transition-transform hover:scale-110 focus:outline-none"
                                                :class="star <= ratingFasilitasi ? 'text-amber-400' : 'text-slate-200'">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </template>
                                        <span class="text-xs font-semibold text-slate-600 ml-2" x-text="ratingFasilitasi + ' / 5'"></span>
                                    </div>
                                </div>

                                {{-- 4. Masukan & Saran --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Catatan / Masukan Tambahan untuk Narasumber & Panitia
                                    </label>
                                    <textarea x-model="feedback" rows="3"
                                        placeholder="Tuliskan masukan atau hal yang perlu ditingkatkan untuk narasumber..."
                                        class="w-full text-xs sm:text-sm rounded-md border border-slate-300 p-2.5 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none transition-all placeholder:text-slate-400"></textarea>
                                </div>

                                {{-- Pesan Error Validasi / Jaringan --}}
                                <div x-show="errorMessage" x-cloak
                                    class="p-2.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle text-rose-500 shrink-0"></i>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </div>

                            {{-- Footer Tombol Modal --}}
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                <button type="button" @click="showModal = false" :disabled="loading"
                                    class="px-4 py-2 rounded-md text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors disabled:opacity-50">
                                    Batal
                                </button>
                                <button type="button" @click="saveEvaluation()" :disabled="loading"
                                    class="px-5 py-2 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-xs disabled:opacity-70 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                                    <template x-if="loading">
                                        <i class="fas fa-spinner fa-spin text-xs"></i>
                                    </template>
                                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Evaluasi'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @endif
        </div>

            {{-- Right Column: Side Widget (Sertifikat & Info) --}}
            <div class="space-y-0 lg:sticky lg:top-24 lg:self-start">
                <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                <div data-tour="sertifikat"
                    @evaluasi-narasumber-submitted.window="evalNarasumberDone = true"
                    x-data="{
                        showRequirementsModal: false,
                        evalNarasumberDone: {{ $isEvaluasiNarasumberCompleted ? 'true' : 'false' }}
                    }"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-0 md:mb-4 relative">

                    {{-- Header Widget --}}
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div
                            class="w-10 h-10 flex items-center justify-center bg-[#13416B] text-white border border-[#13416B] rounded-md shrink-0 shadow-xs">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <h3 class="text-base md:text-lg font-bold text-slate-700">Sertifikat Kelulusan</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Dokumen resmi bukti penyelesaian kursus</p>
                        </div>
                    </div>

                    @if ($isFullyCompleted)
                        @if (!empty(data_get($course, 'certificate_file')))
                            {{-- STATE 1: SERTIFIKAT SUDAH DITERBITKAN --}}
                            <div class="space-y-5">
                                <div class="bg-emerald-600 border border-emerald-700 rounded-md p-5 text-center text-white shadow-xs">
                                    <div
                                        class="w-14 h-14 shrink-0 bg-white text-emerald-600 rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-2xl border border-white">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <p class="font-bold text-white text-sm">Sertifikat Siap Diunduh</p>
                                    <p class="text-xs text-emerald-50 mt-1.5 leading-relaxed">
                                        Selamat! Anda berhak mendapatkan sertifikat ini sebagai bukti sah telah
                                         menguasai seluruh materi.
                                    </p>
                                </div>

                                <div class="bg-slate-50 p-4 rounded-md border border-slate-200 space-y-3 text-xs">
                                    @if (!empty(data_get($course, 'official_cert_number')))
                                        <div class="flex justify-between border-b border-slate-200 pb-2">
                                            <span class="text-slate-500 font-semibold">Nomor Resmi PPSDM</span>
                                            <span class="font-mono font-bold text-[#13416B]">{{ data_get($course, 'official_cert_number') }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between border-b border-slate-200 pb-2">
                                        <span class="text-slate-500 font-semibold">Kode Sertifikat</span>
                                        <span
                                            class="font-mono font-bold text-slate-800">{{ data_get($course, 'certificate_code') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 font-semibold">Tgl Terbit</span>
                                        <span
                                            class="font-bold text-slate-800">{{ \Carbon\Carbon::parse(data_get($course, 'certificate_issued_at'))->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2.5">
                                    <a href="{{ data_get($course, 'certificate_file') }}" target="_blank" download
                                        class="w-full bg-[#13416B] hover:bg-[#0f3354] text-white py-3 rounded-md font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm">
                                        <i class="fas fa-download"></i> Unduh e-Sertifikat Resmi
                                    </a>
                                    <a href="{{ route('user.profile') }}#certificates"
                                        class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-md font-semibold transition-all flex items-center justify-center gap-1.5 text-xs border border-slate-200 shadow-2xs">
                                        <i class="fas fa-award text-slate-500"></i>
                                        <span>Lihat Sertifikat Lain</span>
                                    </a>
                                </div>
                            </div>
                        @else
                            {{-- STATE 2: SELESAI, TAPI CEK APAKAH SEDANG DIAJUKAN KE PPSDM --}}
                            <div class="space-y-4">
                                @if ($isSubmittedToPPSDM)
                                    <div class="bg-amber-500 border border-amber-600 rounded-md p-5 text-center text-white shadow-xs">
                                        <div class="w-12 h-12 bg-white text-amber-600 rounded-full shadow-xs flex items-center justify-center mx-auto mb-2.5 text-xl">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <p class="font-bold text-white text-sm">Sedang Diproses di PPSDM</p>
                                        <p class="text-xs text-amber-50 mt-1.5 leading-relaxed">
                                            Selamat, Anda telah lulus! Berkas kelulusan Anda saat ini sedang dalam proses verifikasi dan penetapan nomor registrasi resmi oleh PPSDM kementerian.
                                        </p>
                                    </div>
                                @else
                                    <div class="bg-[#13416B] border border-[#0f3354] rounded-md p-5 text-center text-white shadow-xs">
                                        <div
                                            class="w-14 h-14 shrink-0 bg-white text-[#13416B] rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-2xl">
                                            <i class="fas fa-medal"></i>
                                        </div>
                                        <p class="font-bold text-white text-sm">Anda Memenuhi Syarat!</p>
                                        <p class="text-xs text-blue-100 mt-1.5 leading-relaxed">
                                            Seluruh materi dan evaluasi telah diselesaikan dengan baik. Sistem siap
                                            menerbitkan sertifikat kelulusan atas nama Anda.
                                        </p>
                                    </div>
                                @endif

                                <div class="bg-slate-50 border border-slate-200 rounded-md p-4 flex items-start gap-3">
                                    <i class="fas fa-id-card text-[#13416B] mt-0.5 text-lg shrink-0"></i>
                                    <div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">
                                            Sertifikat akan diterbitkan atas nama terverifikasi berikut:
                                        </p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">
                                            {{ auth()->user()->siapkerja_id ? auth()->user()->name : (auth()->user()->profile?->full_name ?? auth()->user()->name) }}
                                        </p>
                                    </div>
                                </div>

                                @if ($isSubmittedToPPSDM)
                                    <div class="p-3.5 bg-[#547996] border border-[#446680] rounded-lg text-center space-y-1.5 text-white shadow-xs">
                                        <p class="text-xs font-bold text-white flex items-center justify-center gap-1.5">
                                            <i class="fas fa-clock text-amber-300"></i> Menunggu Nomor Registrasi PPSDM
                                        </p>
                                        <p class="text-[11px] text-blue-50 leading-relaxed">
                                            e-Sertifikat resmi ber-QR Code akan otomatis diterbitkan dan siap diunduh begitu penetapan nomor registrasi dari PPSDM selesai.
                                        </p>
                                    </div>
                                @else
                                    <form
                                        action="{{ route('user.course.my-course.generate-certificate', ['slug' => request()->route('slug')]) }}"
                                        method="POST" x-data="{ loading: false }" @submit="loading = true">
                                        @csrf
                                        <button type="submit" :disabled="loading"
                                            class="w-full bg-[#13416B] hover:bg-[#0f3354] text-white py-3 rounded-md font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm disabled:opacity-70 disabled:cursor-not-allowed">
                                            <template x-if="!loading">
                                                <span class="flex items-center gap-2"><i class="fas fa-cogs"></i>
                                                    Terbitkan Sertifikat</span>
                                            </template>
                                            <template x-if="loading">
                                                <span class="flex items-center gap-2"><i
                                                        class="fas fa-spinner fa-spin"></i> Memproses...</span>
                                            </template>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @else
                        {{-- STATE 3: BELUM SELESAI (TERKUNCI) --}}
                        <div class="py-6 flex flex-col items-center justify-center text-center px-2">
                            <div
                                class="w-16 h-16 shrink-0 bg-[#547996] border border-[#446680] text-white rounded-full flex items-center justify-center mb-4 text-2xl shadow-xs">
                                <i class="fas fa-lock"></i>
                            </div>
                            <p class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-600 text-white text-xs font-bold mb-2.5 shadow-xs">
                                <i class="fas fa-lock text-[10px]" aria-hidden="true"></i>
                                Sertifikat Terkunci
                            </p>

                            <p class="text-xs text-slate-500 leading-relaxed max-w-[240px]">
                                @if ($isBimtekEvent)
                                    Akses sertifikat terbuka otomatis setelah menyelesaikan seluruh agenda hari, evaluasi akhir, dan evaluasi narasumber.
                                @else
                                    Akses sertifikat terbuka otomatis setelah menyelesaikan seluruh materi modul dan evaluasi akhir.
                                @endif
                            </p>

                            {{-- Tombol Lihat Detail Tahapan Syarat Kelulusan --}}
                            <div class="mt-4 w-full">
                                <button type="button" @click="showRequirementsModal = true"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs font-bold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-all border border-slate-200 shadow-2xs">
                                    <i class="fas fa-tasks text-xs"></i>
                                    <span>Lihat Detail Syarat</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- MODAL / BOTTOM SHEET TAHAPAN SYARAT SERTIFIKAT --}}
                    @php
                        $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
                        $hasProofUploaded = !empty($sectionProofs);
                    @endphp
                    <template x-teleport="body">
                        <div x-show="showRequirementsModal" x-cloak
                            @keydown.escape.window="showRequirementsModal = false"
                            class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
                            
                            {{-- Overlay Click to Dismiss --}}
                            <div class="fixed inset-0" @click="showRequirementsModal = false"></div>

                        {{-- Panel Konten: Bottom Sheet di Mobile (items-end, rounded-t-2xl), Modal di Tablet/Desktop (rounded-lg) --}}
                        <div
                            x-show="showRequirementsModal"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                            class="relative bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-lg shadow-2xl border-t sm:border border-slate-200 p-5 sm:p-6 space-y-4 text-left max-h-[88vh] sm:max-h-[85vh] overflow-y-auto z-10">
                            
                            {{-- Drag Indicator Bar khusus Tampilan Mobile (Bottom Sheet UX) --}}
                            <div class="sm:hidden w-12 h-1.5 bg-slate-300 rounded-full mx-auto -mt-2 mb-2"></div>

                            {{-- Header Modal / Bottom Sheet --}}
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-md bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-sm font-bold">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-slate-800 leading-tight">Syarat Penerbitan Sertifikat</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Pantau tahapan yang perlu diraih untuk kelulusan</p>
                                    </div>
                                </div>
                                <button type="button" @click="showRequirementsModal = false"
                                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-md transition-colors">
                                    <i class="fas fa-times text-base"></i>
                                </button>
                            </div>

                            {{-- Mini Overview Progress Bar --}}
                            <div class="p-3.5 bg-slate-50 rounded-md border border-slate-200/80 space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-slate-600">Progres Keseluruhan</span>
                                    <span class="font-bold text-[#13416B]">{{ $currentProgress }}% Selesai</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $progressBarClass }} h-full rounded-full transition-all duration-500"
                                        style="width: {{ $currentProgress }}%"></div>
                                </div>
                            </div>

                            {{-- Daftar Tahapan Persyaratan: Desain Bersih & Elegan (Bukan Box Bertumpuk) --}}
                            <div class="divide-y divide-slate-100 py-1">
                                {{-- 1. Sesi Tatap Muka Online --}}
                                @if ($hasZoom)
                                    <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            @if ($isZoomAttended)
                                                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @elseif ($isWaitingForStart)
                                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[10px] mt-0.5 border border-slate-200">
                                                    <i class="fas fa-lock text-[9px]"></i>
                                                </span>
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                                                    <i class="fas fa-video text-[9px]"></i>
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">Sesi Tatap Muka (Zoom)</p>
                                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">Hadir & isi presensi tatap muka daring</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if ($isZoomAttended)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-check text-[9px]"></i> Selesai
                                                </span>
                                            @elseif ($isWaitingForStart)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-lock text-[9px]"></i> Terkunci
                                                </span>
                                            @else
                                                <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $firstIncompleteSectionId]) }}#sesi-zoom"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs whitespace-nowrap">
                                                    <span>Masuk</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- 2. Materi Pembelajaran Mandiri --}}
                                <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        @if ($areAllTopicsCompleted)
                                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @elseif ($isWaitingForStart)
                                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[10px] mt-0.5 border border-slate-200">
                                                <i class="fas fa-lock text-[9px]"></i>
                                            </span>
                                        @else
                                            <span class="w-6 h-6 rounded-full bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                                                <i class="fas fa-book-reader text-[9px]"></i>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">Materi Pembelajaran Mandiri</p>
                                            <p class="text-[11px] text-slate-500 leading-tight mt-0.5">Tuntaskan seluruh topik ({{ $completedContents }}/{{ $totalContents }} selesai)</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        @if ($areAllTopicsCompleted)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md">
                                                <i class="fas fa-check text-[9px]"></i> Selesai
                                            </span>
                                        @elseif ($isWaitingForStart)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
                                                <i class="fas fa-lock text-[9px]"></i> Terkunci
                                            </span>
                                        @else
                                            <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $firstIncompleteSectionId]) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs whitespace-nowrap">
                                                <span>Pelajari</span>
                                                <i class="fas fa-arrow-right text-[9px]"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3. Unggah Bukti Pelengkap Pelaksanaan --}}
                                @if ($hasZoom)
                                    <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            @if ($hasProofUploaded)
                                                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @elseif ($isWaitingForStart)
                                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[10px] mt-0.5 border border-slate-200">
                                                    <i class="fas fa-lock text-[9px]"></i>
                                                </span>
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                                                    <i class="fas fa-file-upload text-[9px]"></i>
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">Bukti Pelengkap Pelaksanaan</p>
                                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">Unggah bukti keikutsertaan atau surat tugas</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if ($hasProofUploaded)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-check text-[9px]"></i> Selesai
                                                </span>
                                            @elseif ($isWaitingForStart)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-lock text-[9px]"></i> Terkunci
                                                </span>
                                            @else
                                                <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $firstIncompleteSectionId]) }}#bukti-zoom"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs whitespace-nowrap">
                                                    <span>Unggah</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- 4. Evaluasi Akhir Materi (Post-Test) --}}
                                @if ($evaluasiAkhir)
                                    <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            @if ($isEvaluasiAkhirCompleted)
                                                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @elseif ($isEvaluasiAkhirLocked || $isWaitingForStart || $isEventEnded)
                                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[10px] mt-0.5 border border-slate-200">
                                                    <i class="fas fa-lock text-[9px]"></i>
                                                </span>
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                                                    <i class="fas fa-file-signature text-[9px]"></i>
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">{{ $isCbtPostTest ? 'Evaluasi Akhir Komprehensif' : 'Tugas Akhir / Portofolio' }}</p>
                                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">{{ $isCbtPostTest ? 'Lulus nilai minimal KKM: ' . data_get($evaluasiAkhir, 'passing_score', 0) : 'Pengumpulan tugas akhir via Google Drive' }}</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if ($isEvaluasiAkhirCompleted)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-check text-[9px]"></i> Selesai
                                                </span>
                                            @elseif ($isEvaluasiAkhirLocked || $isWaitingForStart || $isEventEnded)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md" title="Selesaikan materi terlebih dahulu">
                                                    <i class="fas fa-lock text-[9px]"></i> Terkunci
                                                </span>
                                            @else
                                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs whitespace-nowrap">
                                                    <span>{{ $isCbtPostTest ? 'Kerjakan' : 'Kumpulkan' }}</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- 5. Evaluasi Narasumber & Penyelenggara (Khusus Bimtek) --}}
                                @if ($isBimtekEvent)
                                    <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <span x-show="evalNarasumberDone" class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                                <i class="fas fa-check"></i>
                                            </span>
                                            <span x-show="!evalNarasumberDone && {{ $isEvaluasiNarasumberLocked ? 'true' : 'false' }}"
                                                title="{{ $evaluasiNarasumberLockReason }}"
                                                class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[10px] mt-0.5 border border-slate-200">
                                                <i class="fas fa-lock text-[9px]"></i>
                                            </span>
                                            <span x-show="!evalNarasumberDone && !{{ $isEvaluasiNarasumberLocked ? 'true' : 'false' }}" class="w-6 h-6 rounded-full bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                                                <i class="fas fa-chalkboard-teacher text-[9px]"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">Evaluasi Narasumber & Bimtek</p>
                                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">Penilaian kualitas pengajar & fasilitas bimtek</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <template x-if="evalNarasumberDone">
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-check text-[9px]"></i> Selesai
                                                </span>
                                            </template>
                                            <template x-if="!evalNarasumberDone && {{ $isEvaluasiNarasumberLocked ? 'true' : 'false' }}">
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md"
                                                    title="{{ $evaluasiNarasumberLockReason }}">
                                                    <i class="fas fa-lock text-[9px]"></i> Terkunci
                                                </span>
                                            </template>
                                            <template x-if="!evalNarasumberDone && !{{ $isEvaluasiNarasumberLocked ? 'true' : 'false' }}">
                                                <button type="button" @click="showRequirementsModal = false; $dispatch('open-evaluasi-narasumber')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs whitespace-nowrap">
                                                    <span>Isi</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('lms::user.course.partials.guide-assets')
        <script>
            (function () {
                const PAGE = 'course_player';
                const STEPS = [
                    {
                        element: '[data-tour="modul"]',
                        popover: {
                            title: '{{ $isBimtekEvent ? "Tahap 1: Agenda Pelaksanaan Harian" : "Tahap 1: Modul Pembelajaran Mandiri" }}',
                            description: '{!! addslashes($isBimtekEvent ? "Ikuti agenda pelatihan bertahap dari <strong>Hari 1 hingga hari terakhir</strong>. Pelajari <strong>materi mandiri</strong>, hadiri <strong>Zoom</strong>, dan selesaikan <strong>evaluasi harian</strong>." : "Pelajari materi topik secara berurutan & tekan <strong>Tandai Selesai</strong>. Tuntaskan <strong>kuis bab</strong> untuk membuka modul berikutnya.") !!}',
                            side: 'bottom',
                            align: 'start',
                        },
                    },
                    {
                        element: '[data-tour="evaluasi"]',
                        popover: {
                            title: 'Tahap 2: Evaluasi Akhir Pelatihan',
                            description: '{!! addslashes($isBimtekEvent ? "Ujian penentu kelulusan. Terbuka setelah seluruh <strong>materi mandiri tuntas</strong> dan <strong>presensi Zoom hari terakhir</strong> terverifikasi." : "Ujian penentu kelulusan. Terbuka otomatis setelah seluruh <strong>modul diselesaikan</strong>. Capai <strong>nilai KKM</strong> untuk lulus.") !!}',
                            side: 'top',
                            align: 'start',
                        },
                    },
                    @if ($isBimtekEvent)
                    {
                        element: '[data-tour="evaluasi-narasumber"]',
                        popover: {
                            title: 'Tahap 3: Evaluasi Narasumber & Penyelenggaraan',
                            description: 'Berikan penilaian dan umpan balik untuk <strong>narasumber pelatihan</strong>. Terbuka pada <strong>hari terakhir</strong> setelah sesi tatap muka (<strong>Zoom</strong>) dihadiri.',
                            side: 'top',
                            align: 'start',
                        },
                    },
                    @endif
                    {
                        element: '[data-tour="sertifikat"]',
                        popover: {
                            title: '{{ $isBimtekEvent ? "Tahap 4: Penerbitan Sertifikat Kelulusan" : "Tahap 3: Penerbitan Sertifikat Kelulusan" }}',
                            description: 'Dokumen resmi bukti kelulusan. Setelah <strong>seluruh tahapan tuntas</strong> dan <strong>lulus evaluasi</strong>, terbitkan serta unduh <strong>e-sertifikat resmi</strong> di sini.',
                            side: 'bottom',
                            align: 'start',
                        },
                    },
                ];

                window.startCoursePlayerTour = function () {
                    window.dispatchEvent(new CustomEvent('guide-start', { detail: { page: PAGE } }));
                    window.SirenataTour.start(PAGE, STEPS);
                };

                document.addEventListener('DOMContentLoaded', function () {
                    window.SirenataTour.autoStart(PAGE, STEPS);
                });
            })();
        </script>
    @endpush
</x-dashboard::layouts.dashboard>