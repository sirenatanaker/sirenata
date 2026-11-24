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

            $formatIndoDateRange = function ($start, $end) {
                $s = \Carbon\Carbon::parse($start)->locale('id');
                $e = \Carbon\Carbon::parse($end)->locale('id');
                if ($s->format('Y-m') === $e->format('Y-m')) {
                    return $s->isoFormat('D') . ' - ' . $e->isoFormat('D MMMM Y');
                }
                if ($s->format('Y') === $e->format('Y')) {
                    return $s->isoFormat('D MMM') . ' - ' . $e->isoFormat('D MMMM Y');
                }
                return $s->isoFormat('D MMMM Y') . ' - ' . $e->isoFormat('D MMMM Y');
            };

            if ($activeBimtek && $activeBimtek->start_date && $activeBimtek->end_date) {
                $formattedPeriod = $formatIndoDateRange($activeBimtek->start_date, $activeBimtek->end_date);
            }

            $formattedEvaluationPeriod = $formattedEvaluationPeriod ?? null;
            if ($activeBimtek) {
                $evalStart = $activeBimtek->assessment_start_at ?? $activeBimtek->end_date;
                $evalEnd   = $activeBimtek->assessment_end_at ?? ($activeBimtek->end_date ? \Carbon\Carbon::parse($activeBimtek->end_date)->addDays(3) : null);
                if ($evalStart && $evalEnd) {
                    $formattedEvaluationPeriod = $formatIndoDateRange($evalStart, $evalEnd);
                }
            }

            $now = now();
            $execStartObj = ($activeBimtek && $activeBimtek->start_date) ? \Carbon\Carbon::parse($activeBimtek->start_date)->startOfDay() : null;
            $execEndObj   = ($activeBimtek && $activeBimtek->end_date) ? \Carbon\Carbon::parse($activeBimtek->end_date)->endOfDay() : null;

            $rawEvalStart = $activeBimtek ? ($activeBimtek->assessment_start_at ?? $activeBimtek->end_date) : null;
            $rawEvalEnd   = $activeBimtek ? ($activeBimtek->assessment_end_at ?? ($activeBimtek->end_date ? \Carbon\Carbon::parse($activeBimtek->end_date)->addDays(3) : null)) : null;
            $evalStartObj = $rawEvalStart ? \Carbon\Carbon::parse($rawEvalStart)->startOfDay() : null;
            $evalEndObj   = $rawEvalEnd ? \Carbon\Carbon::parse($rawEvalEnd)->endOfDay() : null;

            $isExecPeriodActive = $execStartObj && $execEndObj && $now->between($execStartObj, $execEndObj);
            $isEvalPeriodActive = $evalStartObj && $evalEndObj && $now->between($evalStartObj, $evalEndObj);

            $evalDividerDate = null;
            if ($evalStartObj && $evalEndObj) {
                $evalDividerDate = $evalStartObj->isSameDay($evalEndObj)
                    ? $evalStartObj->locale('id')->isoFormat('D MMMM Y')
                    : $formattedEvaluationPeriod;
            }

            $totalBimtekDays = $totalSections;
            if ($isBimtekEvent && $execStartObj && $execEndObj) {
                if ($evalStartObj && $evalEndObj) {
                    if ($evalStartObj->lessThanOrEqualTo($execEndObj)) {
                        $totalBimtekDays = (int) $execStartObj->diffInDays($evalEndObj->copy()->startOfDay()) + 1;
                    } else {
                        $totalBimtekDays = ((int) $execStartObj->diffInDays($execEndObj->copy()->startOfDay()) + 1)
                            + ((int) $evalStartObj->diffInDays($evalEndObj->copy()->startOfDay()) + 1);
                    }
                } else {
                    $totalBimtekDays = (int) $execStartObj->diffInDays($execEndObj->copy()->startOfDay()) + 1;
                }
            }

            $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
            $sectionProofs = $sectionProofs ?? [];
            $microTotal = 0;
            $microCompleted = 0;

            foreach ($sectionsList as $sec) {
                $secId = data_get($sec, 'id') ?? data_get($sec, 'section_id');
                $secContents = data_get($sec, 'section_contents', []) ?: data_get($sec, 'contents', []);
                $secTotalCount = count($secContents);
                $secDoneCount = data_get($sec, 'completed_count', 0);
                $secContentDone = data_get($sec, 'is_content_completed', $secTotalCount === 0 || $secDoneCount === $secTotalCount);
                $secPostTest = data_get($sec, 'post_test_bab');
                $secPostTestDone = data_get($sec, 'is_post_test_bab_completed', true);

                // 1. Tatap Muka per hari
                if ($isBimtekEvent && $hasZoom) {
                    $microTotal++;
                    if ($isZoomAttended) {
                        $microCompleted++;
                    }
                }

                // 2. Materi per hari
                $microTotal++;
                if ($secContentDone) {
                    $microCompleted++;
                }

                // 3. Bukti Hadir per hari
                if ($isBimtekEvent && $hasZoom) {
                    $microTotal++;
                    if (!empty($sectionProofs[$secId])) {
                        $microCompleted++;
                    }
                }

                // 4. Evaluasi Harian / Modul
                if ($secPostTest) {
                    $microTotal++;
                    if ($secPostTestDone) {
                        $microCompleted++;
                    }
                }
            }

            // 5. Evaluasi Akhir
            if ($evaluasiAkhir) {
                $microTotal++;
                if ($isEvaluasiAkhirCompleted) {
                    $microCompleted++;
                }
            }

            // 6. Evaluasi Narasumber
            if ($isBimtekEvent) {
                $microTotal++;
                if ($isEvaluasiNarasumberCompleted) {
                    $microCompleted++;
                }
            }

            $currentProgress = $microTotal > 0 ? (int) round(($microCompleted / $microTotal) * 100) : 0;
            if ($isCourseAlreadyFinished) {
                $currentProgress = 100;
                $microCompleted = $microTotal;
            }
            $progressBarClass = $currentProgress >= 100 ? 'bg-emerald-500' : 'bg-[#13416B]';
        @endphp

        {{-- Header Card (Gaya YouTube Playlist) --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-5 lg:p-6 mb-6 transition-all">
            
            <div class="flex flex-col md:flex-row gap-5 lg:gap-6 items-start">
                {{-- Thumbnail Playlist YouTube Style (16:9 dengan Badge Overlay) --}}
                <div class="w-full md:w-60 lg:w-72 shrink-0 rounded-md overflow-hidden bg-slate-900 aspect-video md:aspect-[16/10] border border-slate-200 shadow-sm relative group">
                    <img src="{{ $thumbnailUrl }}" alt="{{ $courseName }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    
                    {{-- Overlay Badge Playlist khas YouTube --}}
                    <div class="absolute bottom-2.5 right-2.5 bg-black/80 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm">
                        <i class="far fa-clock text-[10px] text-white/90"></i>
                        <span>{{ $isBimtekEvent ? $totalBimtekDays . ' Hari' : $totalSections . ' Modul' }}</span>
                    </div>

                    {{-- Hover Play Button Overlay --}}
                    @php
                        $targetSectionId = $firstIncompleteSectionId ?? data_get($sectionsList->first(), 'id');
                    @endphp
                    @if ($targetSectionId && !$isWaitingForStart)
                        <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $targetSectionId]) }}"
                            class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="w-12 h-12 rounded-full bg-[#13416B] text-white flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                <i class="fas fa-play text-sm ml-0.5"></i>
                            </span>
                        </a>
                    @endif
                </div>

                {{-- Detail Informasi Playlist --}}
                <div class="flex-1 min-w-0 w-full flex flex-col justify-between self-stretch gap-4">
                    <div class="space-y-2 w-[95%] max-w-[95%] sm:w-full sm:max-w-none">
                        @if ($isBimtekEvent && $activeBimtek?->batch_name)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-semibold text-slate-600 text-xs tracking-wide">{{ preg_replace('/\bbatch\b/i', 'Gelombang', $activeBimtek->batch_name) }}</span>
                            </div>
                        @endif

                        {{-- Judul Kursus --}}
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-medium text-slate-700 tracking-wide leading-snug" style="font-family: 'Oswald', sans-serif;">
                            {{ $courseName }}
                        </h1>

                        {{-- Info Periode Pelaksanaan & Periode Evaluasi --}}
                        @if ($formattedPeriod || $formattedEvaluationPeriod)
                            <ul class="pt-1.5 space-y-1.5 list-disc pl-4 text-xs sm:text-[13px] text-slate-600 leading-relaxed marker:text-slate-400">
                                @if ($formattedPeriod)
                                    <li>
                                        Periode pelaksanaan berlangsung pada <span class="font-bold text-slate-800">{{ $formattedPeriod }}</span>@if ($isExecPeriodActive) <span class="font-semibold text-emerald-600">(sedang berlangsung)</span>@elseif ($execEndObj && $now->greaterThan($execEndObj)) <span class="text-slate-400">(telah selesai)</span>@elseif ($execStartObj && $now->lessThan($execStartObj)) <span class="text-slate-400">(menunggu jadwal)</span>@endif.
                                    </li>
                                @endif
                                @if ($formattedEvaluationPeriod)
                                    <li>
                                        Periode evaluasi berlangsung pada <span class="font-bold text-slate-800">{{ $formattedEvaluationPeriod }}</span>@if ($isEvalPeriodActive) <span class="font-semibold text-emerald-600">(sedang berlangsung)</span>@elseif ($evalEndObj && $now->greaterThan($evalEndObj)) <span class="text-slate-400">(telah selesai)</span>@elseif ($evalStartObj && $now->lessThan($evalStartObj)) <span class="text-slate-400">(menunggu jadwal)</span>@endif.
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>

                    {{-- Progress Bar Pembelajaran (Mikro: Seluruh Tahapan & Agenda Harian) --}}
                    <div data-tour="progres" class="w-full pt-2.5 sm:pt-3 border-t border-slate-100">
                        <div class="flex justify-between items-center mb-1.5 text-xs">
                            <span class="font-semibold text-slate-600">
                                {{ $isWaitingForStart ? 'Status Pelatihan' : 'Progres Pembelajaran' }}
                            </span>
                            <span class="font-bold text-xs sm:text-sm {{ $currentProgress >= 100 ? 'text-emerald-600' : 'text-[#13416B]' }}">
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
                <div id="agenda-pelaksanaan"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 scroll-mt-6">

                    <div
                        class="flex items-center justify-between mb-5 sm:mb-6 pb-4 border-b border-slate-100 gap-3 sm:gap-4">
                        <div class="flex items-start gap-3 sm:gap-3.5">
                            
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">
                                    {{ $isBimtekEvent ? 'Linimasa Kegiatan' : 'Daftar Modul Pembelajaran' }}
                                </h2>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-1">
                                    {{ $isBimtekEvent 
                                        ? 'Alur tahapan harian: sesi tatap muka (Zoom), pembelajaran materi, dan evaluasi harian' 
                                        : 'Pelajari seluruh materi modul secara mandiri dan bertahap sesuai kecepatan belajar Anda' }}
                                </p>
                            </div>
                        </div>
                        {{-- Tombol Panduan Tour --}}
                        <button type="button" onclick="window.startCoursePlayerTour && window.startCoursePlayerTour()"
                            class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-[#13416B] border border-slate-200 rounded-md transition-all shadow-2xs cursor-pointer shrink-0">
                            <i class="fas fa-route text-xs sm:text-sm text-[#13416B]"></i>
                            <span>Panduan</span>
                        </button>
                    </div>

                    <div data-tour="modul" class="relative space-y-4 sm:space-y-5">
                        @php
                            $sectionProofs = $sectionProofs ?? [];
                            $isPreviousSectionDone = true;
                        @endphp

                        {{-- Garis vertikal timeline yang menyambung secara kontinu dari badge/divider pertama hingga terakhir --}}
                        @if ($totalSections > 1)
                            <div class="absolute left-6 sm:left-7 top-4 sm:top-5 bottom-6 sm:bottom-7 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
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

                            <div class="space-y-2.5">
                                {{-- Date Divider di Luar Card Timeline (Tanpa Icon Bullet) --}}
                                @if ($isBimtekEvent && $dayDate)
                                    <div class="flex items-center gap-3 pl-[60px] sm:pl-[72px] {{ $index > 0 ? 'pt-4 sm:pt-5' : 'pt-0.5' }}">
                                        <span class="text-xs sm:text-[13px] font-bold {{ $isLocked ? 'text-slate-400' : ($dayDate->isToday() ? 'text-[#13416B]' : 'text-slate-700') }} tracking-wide">
                                            {{ $dayDate->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                        </span>
                                        @if ($dayDate->isToday())
                                            <span class="text-[10px] sm:text-[11px] font-bold text-white bg-[#13416B] px-2.5 py-0.5 rounded-md shadow-2xs shrink-0">Hari Ini</span>
                                        @endif
                                        <div class="h-px bg-slate-200 flex-1"></div>
                                    </div>
                                @endif

                                <div class="relative flex items-start gap-3 sm:gap-4 group">
                                    {{-- Node Timeline: Badge Hari / Modul seperti Kalender --}}
                                    <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-md {{ $isLocked ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-[#13416B] text-white border border-[#13416B] shadow-2xs' }} shrink-0 z-[1] relative transition-transform group-hover:scale-105">
                                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $isLocked ? 'text-slate-400' : 'text-slate-200' }} leading-none">
                                            {{ $isBimtekEvent ? 'HARI' : 'MODUL' }}
                                        </span>
                                        <span class="text-base sm:text-lg font-black leading-tight mt-0.5">{{ $index + 1 }}</span>
                                        @if ($isSectionCompleted)
                                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] ring-2 ring-white shadow-2xs" title="Selesai">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Card Modul Harian (Rapi & Profesional tanpa icon bullet) --}}
                                    <div
                                        class="flex-1 min-w-0 border {{ $isLocked ? 'border-slate-200/80 bg-slate-50/60' : 'border-slate-200 bg-white hover:border-slate-300 shadow-2xs hover:shadow-xs' }} rounded-md p-4 sm:p-5 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        
                                        {{-- Kolom Kiri: Judul Langsung & Ringkasan Agenda Terpadu --}}
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold {{ $isLocked ? 'text-slate-500' : 'text-slate-800' }} text-sm sm:text-base leading-snug break-words mb-1">
                                                {{ $displayTitle }}
                                            </h3>

                                            {{-- Baris Aktivitas: Checklist Bulat Bersih & Rapi --}}
                                            <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:flex sm:flex-wrap sm:items-center sm:gap-x-6 sm:gap-y-2.5 mt-3">
                                                {{-- 1. Sesi Tatap Muka Online (Khusus Bimtek dengan Zoom) --}}
                                                @if ($isBimtekEvent && $hasZoom)
                                                    <div class="inline-flex items-center gap-2 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isZoomAttended ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $isZoomAttended ? 'Tatap Muka: Hadir' : 'Tatap Muka: Belum Hadir' }}">
                                                        @if ($isZoomAttended)
                                                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[8px] shadow-2xs">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                        @else
                                                            <span class="w-4 h-4 rounded-full border border-slate-300 bg-white shrink-0"></span>
                                                        @endif
                                                        <span>Tatap Muka</span>
                                                    </div>
                                                @endif

                                                {{-- 2. Topik Materi Mandiri --}}
                                                <div class="inline-flex items-center gap-2 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isContentCompleted ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="Materi Pembelajaran">
                                                    @if ($isContentCompleted)
                                                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[8px] shadow-2xs">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    @else
                                                        <span class="w-4 h-4 rounded-full border border-slate-300 bg-white shrink-0"></span>
                                                    @endif
                                                    <span>{{ $isBimtekEvent ? 'Materi' : 'Materi (' . $totalCount . ' Topik)' }}</span>
                                                </div>

                                                {{-- 3. Upload Bukti Pelengkap / Tugas Drive (Khusus Bimtek dengan Zoom) --}}
                                                @if ($isBimtekEvent && $hasZoom)
                                                    <div class="inline-flex items-center gap-2 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($hasZoomProof ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $hasZoomProof ? 'Bukti pelengkap sudah diunggah' : 'Belum upload bukti pelengkap' }}">
                                                        @if ($hasZoomProof)
                                                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[8px] shadow-2xs">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                        @else
                                                            <span class="w-4 h-4 rounded-full border border-slate-300 bg-white shrink-0"></span>
                                                        @endif
                                                        <span>Bukti Hadir</span>
                                                    </div>
                                                @endif

                                                {{-- 4. Evaluasi Harian / Modul (Post-Test Bab) --}}
                                                @if ($postTestBab)
                                                    <div class="inline-flex items-center gap-2 text-xs sm:text-[13px] {{ $isLocked ? 'text-slate-400' : ($isPostTestBabCompleted ? 'text-slate-800 font-medium' : 'text-slate-500') }}" title="{{ $isPostTestBabCompleted ? 'Evaluasi tuntas' : 'Evaluasi belum dikerjakan' }}">
                                                        @if ($isPostTestBabCompleted)
                                                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-[8px] shadow-2xs">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                        @else
                                                            <span class="w-4 h-4 rounded-full border border-slate-300 bg-white shrink-0"></span>
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
                                                    class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-semibold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none whitespace-nowrap">
                                                    <span>Terkunci</span>
                                                </button>
                                            @elseif ($isLocked)
                                                <button type="button" disabled
                                                    title="Selesaikan modul sebelumnya terlebih dahulu"
                                                    class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-semibold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none whitespace-nowrap">
                                                    <span>Terkunci</span>
                                                </button>
                                            @else
                                                <a href="{{ route('user.course.section.show', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                                    class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#1E568A] hover:bg-[#13416B] text-white shadow-xs transition-all whitespace-nowrap">
                                                    <span>Masuk</span>
                                                </a>
                                            @endif
                                        </div>
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

                    {{-- EVALUASI AKHIR & EVALUASI NARASUMBER DI DALAM CARD TIMELINE (TANPA TIMELINE BADGE + GARIS TANGGAL) --}}
                    @php
                        $evaluasiAkhirTitle = 'Evaluasi Akhir : ' . $courseName;
                        $evaluasiQuestionsCount = $evaluasiAkhir ? data_get($evaluasiAkhir, 'questions', collect())->count() : 0;
                        $isCbtPostTest = $evaluasiAkhir && $evaluasiQuestionsCount > 0;
                        $isDriveAssignment = $evaluasiAkhir && $evaluasiQuestionsCount === 0;
                        $adminDriveLink = $activeBimtek?->submission_drive_link ?? null;
                    @endphp

                    @if ($evaluasiAkhir || $isBimtekEvent)
                        <div class="mt-6 sm:mt-7 space-y-4 sm:space-y-5">
                            {{-- Garis Tanggal Periode Evaluasi (Tanpa Timeline Badge) --}}
                            @if ($isBimtekEvent && $evalDividerDate)
                                <div class="flex items-center gap-3 pt-1">
                                    <span class="text-xs sm:text-[13px] font-bold {{ ($isEvaluasiAkhirLocked && $isEvaluasiNarasumberLocked) ? 'text-slate-400' : ($isEvalPeriodActive ? 'text-[#13416B]' : 'text-slate-700') }} tracking-wide">
                                        {{ $evalDividerDate }}
                                    </span>
                                    @if ($isEvalPeriodActive)
                                        <span class="text-[10px] sm:text-[11px] font-bold text-white bg-emerald-600 px-2.5 py-0.5 rounded-md shadow-2xs shrink-0">Periode Evaluasi</span>
                                    @endif
                                    <div class="h-px bg-slate-200 flex-1"></div>
                                </div>
                            @endif

                            {{-- CARD EVALUASI AKHIR (FLEKSIBEL: PILIHAN GANDA CBT / PENUGASAN GOOGLE DRIVE) --}}
                            @if ($evaluasiAkhir)
                                <div data-tour="evaluasi"
                                    x-data="{ showStartEvaluasiModal: false }"
                                    class="overflow-hidden border border-[#0f3354] bg-[#13416B] text-white shadow-sm hover:shadow-md rounded-md p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 transition-all">
                                    <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                                        {{-- Kolom Kiri: Informasi Evaluasi & Tombol Aksi --}}
                                        <div class="flex-1 min-w-0 sm:py-1">
                                            <h3 class="text-base sm:text-lg lg:text-xl font-medium text-white leading-snug tracking-wide max-w-full lg:max-w-[85%] line-clamp-2" style="font-family: 'Oswald', sans-serif;" title="{{ $evaluasiAkhirTitle }}">
                                                {{ $evaluasiAkhirTitle }}
                                            </h3>

                                            {{-- Deskripsi Dinamis Mengenai Evaluasi Akhir --}}
                                            <p class="text-xs sm:text-sm text-white/85 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                                @if ($isCbtPostTest)
                                                    Ujian evaluasi komprehensif seluruh materi sebagai syarat kelulusan dan penerbitan sertifikat.
                                                @else
                                                    Pengumpulan berkas tugas akhir atau portofolio pelatihan ke Google Drive panitia sebagai syarat kelulusan dan penerbitan sertifikat.
                                                @endif
                                            </p>

                                            <div class="mt-4 sm:mt-5 flex flex-wrap items-center gap-2.5 justify-start">
                                                @if ($isCbtPostTest)
                                                    @if ($isEvaluasiAkhirCompleted)
                                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] transition-all shadow-sm">
                                                            <i class="fas fa-eye text-xs"></i> Lihat Hasil
                                                        </a>
                                                    @elseif ($isWaitingForStart)
                                                        <button type="button" disabled
                                                            title="Evaluasi dibuka saat periode pelaksanaan pelatihan resmi dimulai"
                                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white/15 text-white/75 border border-white/25 cursor-not-allowed select-none">
                                                            <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                                        </button>
                                                    @elseif (!$isEvaluasiAkhirLocked && !$isEventEnded)
                                                        <button type="button" @click="showStartEvaluasiModal = true"
                                                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] shadow-sm transition-all cursor-pointer">
                                                            <i class="fas fa-play text-xs"></i> Mulai Evaluasi
                                                        </button>
                                                    @elseif ($isEventEnded && !$isEvaluasiAkhirCompleted)
                                                        <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold bg-white/15 text-white/75 border border-white/25">
                                                            <i class="fas fa-clock text-white/75"></i>
                                                            <span>Periode Pelatihan Telah Berakhir</span>
                                                        </div>
                                                    @else
                                                        <button type="button" disabled
                                                            title="Selesaikan materi terlebih dahulu"
                                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white/15 text-white/75 border border-white/25 cursor-not-allowed select-none">
                                                            <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                                        </button>
                                                    @endif
                                                @else
                                                    {{-- Penugasan Akhir via Google Drive --}}
                                                    @if ($adminDriveLink)
                                                        <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] shadow-sm transition-all">
                                                            <i class="fab fa-google-drive text-xs"></i>
                                                            <span>Buka Folder Drive Tugas</span>
                                                            <i class="fas fa-external-link-alt text-[10px] ml-0.5 opacity-80"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] transition-all shadow-sm">
                                                        <i class="fas fa-tasks text-xs"></i>
                                                        <span>Lembar Penyerahan Tugas</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Kolom Kanan: Ilustrasi Gambar khusus Layar Tablet/Desktop (sm ke atas) --}}
                                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-6 sm:-mb-6 lg:-mb-7">
                                            <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                                alt="Ilustrasi Evaluasi Akhir"
                                                class="sm:h-44 md:h-48 lg:h-52 w-auto object-contain object-bottom translate-y-1 select-none pointer-events-none" />
                                        </div>
                                    </div>

                                    {{-- MODAL / BOTTOM SHEET KONFIRMASI MULAI EVALUASI AKHIR --}}
                                    <template x-teleport="body">
                                        <div x-show="showStartEvaluasiModal" x-cloak
                                            @keydown.escape.window="showStartEvaluasiModal = false"
                                            class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">

                                            {{-- Overlay Click to Dismiss --}}
                                            <div class="fixed inset-0" @click="showStartEvaluasiModal = false"></div>

                                            {{-- Panel Konten: Bottom Sheet di Mobile, Modal di Tablet/Desktop --}}
                                            <div x-show="showStartEvaluasiModal"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                                                class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-lg shadow-2xl border-t sm:border border-slate-200 p-5 sm:p-6 space-y-4 text-left max-h-[88vh] sm:max-h-[85vh] overflow-y-auto z-10">

                                                {{-- Drag Indicator Bar khusus Mobile --}}
                                                <div class="sm:hidden w-12 h-1.5 bg-slate-300 rounded-full mx-auto -mt-2 mb-2"></div>

                                                {{-- Header Modal / Bottom Sheet --}}
                                                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <div class="w-9 h-9 rounded-md bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-sm font-bold shrink-0">
                                                            <i class="fas fa-clipboard-check"></i>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h4 class="text-base font-bold text-slate-800 leading-tight">Konfirmasi Mulai Evaluasi</h4>
                                                            <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $evaluasiAkhirTitle }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="showStartEvaluasiModal = false"
                                                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-md transition-colors cursor-pointer shrink-0">
                                                        <i class="fas fa-times text-base"></i>
                                                    </button>
                                                </div>

                                                {{-- Ringkasan Ketentuan Evaluasi --}}
                                                <div class="bg-slate-50 border border-slate-200 rounded-md divide-y divide-slate-200/80 text-xs sm:text-[13px]">
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-list-ol text-slate-400 w-4 text-center"></i>
                                                            Bentuk &amp; Jumlah Soal
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            {{ $isCbtPostTest ? 'Pilihan Ganda • ' . $evaluasiQuestionsCount . ' Soal' : 'Penugasan Berkas / Portofolio' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-stopwatch text-slate-400 w-4 text-center"></i>
                                                            Waktu Pengerjaan
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            {{ data_get($evaluasiAkhir, 'duration', 0) > 0 ? data_get($evaluasiAkhir, 'duration') . ' Menit' : 'Tanpa Batas Waktu' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-bullseye text-slate-400 w-4 text-center"></i>
                                                            Ambang Batas (KKM)
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            {{ data_get($evaluasiAkhir, 'passing_score', 0) > 0 ? data_get($evaluasiAkhir, 'passing_score') . ' Poin' : '-' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Pesan Kesiapan --}}
                                                <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed">
                                                    Apakah Anda yakin ingin memulai pengerjaan evaluasi sekarang? Pastikan koneksi internet stabil sebelum melanjutkan.
                                                </p>

                                                {{-- Footer Tombol Aksi --}}
                                                <div class="pt-1 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                                                    <button type="button" @click="showStartEvaluasiModal = false"
                                                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-md text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors cursor-pointer">
                                                        Batal
                                                    </button>
                                                    <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#1E568A] hover:bg-[#13416B] text-white shadow-sm transition-all">
                                                        <i class="fas fa-play text-xs"></i>
                                                        <span>Ya, Kerjakan Sekarang</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif

                            @if ($isBimtekEvent)
                                @php
                                    $narasumberQuestions = \Modules\LMS\Models\BimtekEvaluationQuestion::where('is_active', true)->get();
                                    $narasumberQuestionsCount = $narasumberQuestions->count() ?: 4;
                                    $narasumberScaleCount = $narasumberQuestions->where('type', 'scale')->count();
                                    $narasumberTextCount = $narasumberQuestions->where('type', '!=', 'scale')->count();
                                    $narasumberFormTypeLabel = ($narasumberScaleCount > 0 && $narasumberTextCount > 0)
                                        ? 'Skala Rating (1–5) & Masukan Tertulis'
                                        : ($narasumberScaleCount > 0 ? 'Kuesioner Skala Rating (1–5)' : 'Kuesioner Evaluasi');
                                @endphp
                                {{-- CARD EVALUASI NARASUMBER / PEMATERI (KHUSUS BIMTEK - GAYA GOOGLE FORM PINDAH HALAMAN) --}}
                                <div data-tour="evaluasi-narasumber"
                                    id="card-evaluasi-narasumber"
                                    x-data="{ showStartNarasumberModal: false }"
                                    class="border border-[#446680] bg-[#547996] text-white shadow-sm hover:shadow-md rounded-md p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 transition-all">

                                    <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                                        {{-- Kolom Kiri: Informasi Evaluasi Narasumber & Tombol Aksi Pindah Halaman --}}
                                        <div class="flex-1 min-w-0 sm:py-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-base sm:text-lg lg:text-xl font-medium text-white leading-snug tracking-wide" style="font-family: 'Oswald', sans-serif;">
                                                    Evaluasi Narasumber & Penyelenggaraan
                                                </h3>
                                            </div>

                                            <p class="text-xs sm:text-sm text-white/90 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                                Penilaian bergaya kuesioner terhadap penguasaan materi, metode penyampaian narasumber, dan fasilitas penyelenggaraan bimbingan teknis.
                                            </p>

                                            {{-- Tombol Aksi: Pindah Halaman Langsung (Google Form Style) --}}
                                            <div class="mt-4 sm:mt-5 flex justify-start">
                                                @if ($isEvaluasiNarasumberLocked)
                                                    <button type="button" disabled
                                                        title="{{ $evaluasiNarasumberLockReason }}"
                                                        class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white/15 text-white/75 border border-white/25 cursor-not-allowed select-none">
                                                        <i class="fas fa-lock text-xs"></i> Evaluasi Terkunci
                                                    </button>
                                                @elseif ($isEvaluasiNarasumberCompleted)
                                                    <a href="{{ route('user.course.my-course.evaluasi-narasumber', ['slug' => $courseSlug]) }}"
                                                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold transition-all shadow-sm bg-white hover:bg-slate-100 text-[#13416B]">
                                                        <i class="fas fa-eye text-xs"></i>
                                                        <span>Lihat / Edit Tanggapan Kuesioner</span>
                                                    </a>
                                                @else
                                                    <button type="button" @click="showStartNarasumberModal = true"
                                                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold transition-all bg-white hover:bg-slate-100 text-[#13416B] shadow-sm cursor-pointer">
                                                        <i class="fas fa-pen-alt text-xs"></i>
                                                        <span>Buka Kuesioner Evaluasi</span>
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

                                    {{-- MODAL / BOTTOM SHEET KONFIRMASI MULAI EVALUASI NARASUMBER --}}
                                    <template x-teleport="body">
                                        <div x-show="showStartNarasumberModal" x-cloak
                                            @keydown.escape.window="showStartNarasumberModal = false"
                                            class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">

                                            {{-- Overlay Click to Dismiss --}}
                                            <div class="fixed inset-0" @click="showStartNarasumberModal = false"></div>

                                            {{-- Panel Konten: Bottom Sheet di Mobile, Modal di Tablet/Desktop --}}
                                            <div x-show="showStartNarasumberModal"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                                                class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-lg shadow-2xl border-t sm:border border-slate-200 p-5 sm:p-6 space-y-4 text-left max-h-[88vh] sm:max-h-[85vh] overflow-y-auto z-10">

                                                {{-- Drag Indicator Bar khusus Mobile --}}
                                                <div class="sm:hidden w-12 h-1.5 bg-slate-300 rounded-full mx-auto -mt-2 mb-2"></div>

                                                {{-- Header Modal / Bottom Sheet --}}
                                                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <div class="w-9 h-9 rounded-md bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-sm font-bold shrink-0">
                                                            <i class="fas fa-poll-h"></i>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h4 class="text-base font-bold text-slate-800 leading-tight">Konfirmasi Kuesioner Evaluasi</h4>
                                                            <p class="text-[11px] text-slate-500 mt-0.5 truncate">Evaluasi Narasumber &amp; Penyelenggaraan</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="showStartNarasumberModal = false"
                                                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-md transition-colors cursor-pointer shrink-0">
                                                        <i class="fas fa-times text-base"></i>
                                                    </button>
                                                </div>

                                                {{-- Ringkasan Ketentuan Kuesioner --}}
                                                <div class="bg-slate-50 border border-slate-200 rounded-md divide-y divide-slate-200/80 text-xs sm:text-[13px]">
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-list-ol text-slate-400 w-4 text-center"></i>
                                                            Jumlah Pertanyaan
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            {{ $narasumberQuestionsCount }} Pertanyaan
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-sliders-h text-slate-400 w-4 text-center"></i>
                                                            Bentuk Evaluasi
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            {{ $narasumberFormTypeLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                                            <i class="fas fa-stopwatch text-slate-400 w-4 text-center"></i>
                                                            Waktu Pengerjaan
                                                        </span>
                                                        <span class="font-bold text-slate-800 text-right">
                                                            Tanpa Batas Waktu
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Pesan Kesiapan --}}
                                                <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed">
                                                    Mohon berikan penilaian objektif terhadap pemateri dan penyelenggaraan bimtek. Lanjutkan untuk membuka lembar kuesioner?
                                                </p>

                                                {{-- Footer Tombol Aksi --}}
                                                <div class="pt-1 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                                                    <button type="button" @click="showStartNarasumberModal = false"
                                                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-md text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors cursor-pointer">
                                                        Batal
                                                    </button>
                                                    <a href="{{ route('user.course.my-course.evaluasi-narasumber', ['slug' => $courseSlug]) }}"
                                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#1E568A] hover:bg-[#13416B] text-white shadow-sm transition-all">
                                                        <i class="fas fa-pen-alt text-xs"></i>
                                                        <span>Ya, Isi Kuesioner Sekarang</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Side Widget (Sertifikat & Info) --}}
            <div class="space-y-0 lg:sticky lg:top-24 lg:self-start">
                <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                <div data-tour="sertifikat"
                    @evaluasi-narasumber-submitted.window="evalNarasumberDone = true"
                    x-data="{
                        showRequirementsModal: false,
                        evalNarasumberDone: {{ ($isEvaluasiNarasumberCompleted && !$isEvaluasiNarasumberLocked) ? 'true' : 'false' }}
                    }"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-0 md:mb-4 relative">

                    {{-- Header Widget --}}
                    <div class="mb-5 pb-4 border-b border-slate-100">
                        <h3 class="text-base md:text-lg font-bold text-slate-800">Sertifikat Kelulusan</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Dokumen resmi bukti penyelesaian kursus</p>
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
                                        class="w-full bg-[#1E568A] hover:bg-[#13416B] text-white py-3 rounded-md font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm">
                                        <i class="fas fa-download"></i> Unduh e-Sertifikat
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
                                            class="w-full bg-[#1E568A] hover:bg-[#13416B] text-white py-3 rounded-md font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm disabled:opacity-70 disabled:cursor-not-allowed">
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
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#1E568A] hover:bg-[#13416B] transition-colors shadow-2xs whitespace-nowrap">
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
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#1E568A] hover:bg-[#13416B] transition-colors shadow-2xs whitespace-nowrap">
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
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#1E568A] hover:bg-[#13416B] transition-colors shadow-2xs whitespace-nowrap">
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
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#1E568A] hover:bg-[#13416B] transition-colors shadow-2xs whitespace-nowrap">
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
                                                <a href="{{ route('user.course.my-course.evaluasi-narasumber', ['slug' => $courseSlug]) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#1E568A] hover:bg-[#13416B] transition-colors shadow-2xs whitespace-nowrap">
                                                    <span>Isi</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
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