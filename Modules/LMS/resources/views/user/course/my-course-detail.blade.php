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

        {{-- KALKULASI PROGRESS REAL (Disiapkan dari CourseService) --}}
        @php
            $thumbnailUrl = data_get($course, 'thumbnail_url');
            $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
            $courseSlug = data_get($course, 'slug') ?? request()->route('slug');
            $courseId = data_get($course, 'id') ?? data_get($course, 'course_id');

            $totalContents = data_get($course, 'total_contents', 0);
            $completedContents = data_get($course, 'completed_count', 0);
            $totalTests = data_get($course, 'total_tests', 0);
            $passedTests = data_get($course, 'passed_tests', 0);
            $evaluasiAkhir = data_get($course, 'evaluasi_akhir');
            $isEvaluasiAkhirCompleted = data_get($course, 'is_evaluasi_akhir_completed', false);
            $totalItems = data_get($course, 'total_items', 0);
            $completedItems = data_get($course, 'completed_items', 0);
            $currentProgress = data_get($course, 'current_progress', 0);
            $isFullyCompleted = data_get($course, 'is_fully_completed', false);

            $activeBimtek = \Modules\LMS\Models\BimtekEvent::where('course_id', data_get($course, 'id'))->latest()->first();
            $enrollmentData = \DB::table('course_student')
                ->where('course_id', data_get($course, 'id'))
                ->where('user_id', auth()->id())
                ->first();
            $areAllTopicsCompleted = ($totalContents > 0 && $completedContents >= $totalContents);
            $isZoomUnlocked = !$activeBimtek || $areAllTopicsCompleted;
            $isZoomAttended = (bool) ($enrollmentData?->zoom_attended);
            $curBimtekPhase = $activeBimtek?->current_timeline_phase;

            // Warna progress: 100% hijau, sedang berprogress amber; teks persen slate/gray
            if ($currentProgress >= 100) {
                $progressBarClass = 'bg-emerald-500';
                $progressTextClass = 'text-slate-600';
            } else {
                $progressBarClass = 'bg-amber-500';
                $progressTextClass = 'text-slate-600';
            }

            if (empty($thumbnailUrl)) {
                $encodedName = urlencode($courseName);
                $thumbnailUrl = "https://ui-avatars.com/api/?name={$encodedName}&background=eff6ff&color=1e3a8a&size=512&font-size=0.33&bold=true";
            }
        @endphp

        {{-- Header Card --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 lg:p-7 mb-6 flex flex-col md:flex-row gap-5 lg:gap-7 items-start md:items-stretch transition-all">
            {{-- Bagian Kiri: Thumbnail --}}
            <div
                class="w-full md:w-1/3 lg:w-[28%] shrink-0 rounded-md overflow-hidden bg-slate-100 aspect-video md:aspect-[4/3] relative border border-slate-200 shadow-xs">
                <img src="{{ $thumbnailUrl }}" alt="{{ $courseName }}" class="w-full h-full object-cover" />
                
                {{-- Tombol Panduan di Resolusi Mobile (Diletakkan di atas thumbnail agar tidak mengganggu badge) --}}
                <button type="button" onclick="window.startCoursePlayerTour && window.startCoursePlayerTour()"
                    class="md:hidden absolute top-2.5 right-2.5 z-10 inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-white bg-[#13416B] border border-[#13416B] rounded-md shadow-sm hover:bg-[#0f3354] transition-colors">
                    <i class="fas fa-route"></i> Panduan
                </button>
            </div>

            {{-- Bagian Kanan: Informasi Kursus --}}
            <div class="flex-1 flex flex-col w-full justify-between">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        {{-- Gelombang / Nama Batch Bimtek --}}
                        @if ($activeBimtek && $activeBimtek->batch_name)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-bold bg-amber-500 text-white shadow-xs" title="{{ $activeBimtek->batch_name }}">
                                <i class="fas fa-satellite-dish text-[10px] shrink-0"></i>
                                <span class="truncate max-w-[220px] sm:max-w-xs">{{ $activeBimtek->batch_name }}</span>
                            </span>
                        @else
                            <div></div>
                        @endif

                        {{-- Tombol Panduan Desktop --}}
                        <button type="button" onclick="window.startCoursePlayerTour && window.startCoursePlayerTour()"
                            class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#13416B] border border-[#13416B] rounded-md hover:bg-[#0f3354] transition-colors ml-auto shadow-xs cursor-pointer">
                            <i class="fas fa-route"></i> Panduan
                        </button>
                    </div>

                    {{-- Judul --}}
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 mb-2.5 tracking-tight leading-snug">
                        {{ $courseName }}
                    </h1>

                    {{-- Deskripsi --}}
                    <div class="prose prose-sm max-w-none text-slate-600 mb-5">
                        <p class="text-xs sm:text-sm leading-relaxed">
                            {{ data_get($course, 'description', 'Tidak ada deskripsi tersedia untuk course ini.') }}
                        </p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-auto pt-5 border-t border-slate-100 max-w-md w-full" data-tour="progres">
                    <div class="flex justify-between items-center mb-2.5">
                        <span class="text-xs font-semibold text-slate-500">Progress Pembelajaran</span>
                        <span
                            class="text-sm font-bold {{ $progressTextClass }}">
                            {{ $currentProgress }}%
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="{{ $progressBarClass }} h-full rounded-full transition-all duration-700"
                            style="width: {{ $currentProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- WIDGET INFORMASI BELAJAR --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-6">
            <div class="mb-4 sm:mb-5 pb-3 sm:pb-4 border-b border-slate-100">
                <h3 class="text-base sm:text-lg md:text-xl font-extrabold text-slate-800">Informasi Belajar</h3>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Pantau ringkasan progres dan informasi pelaksanaan kursus Anda</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- Status --}}
                <div class="p-3 sm:p-4 bg-slate-50 rounded-md border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-info-circle text-slate-400"></i> Status
                    </span>
                    <span
                        class="inline-flex items-center justify-center px-2 py-1.5 rounded-md text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white {{ $isFullyCompleted ? 'bg-emerald-600 border border-emerald-600' : 'bg-amber-500 border border-amber-500' }}">
                        {{ $isFullyCompleted ? 'Selesai' : 'Berjalan' }}
                    </span>
                </div>

                {{-- Timeline Pelaksanaan --}}
                <div class="p-3 sm:p-4 bg-slate-50 rounded-md border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="far fa-calendar-alt text-slate-400"></i> Timeline Pelaksanaan
                    </span>
                    @if ($activeBimtek && $activeBimtek->start_date)
                        <span class="text-xs sm:text-sm font-extrabold text-slate-800 leading-snug">
                            {{ $activeBimtek->start_date->translatedFormat('d M') }} - {{ $activeBimtek->end_date ? $activeBimtek->end_date->translatedFormat('d M Y') : '' }}
                        </span>
                    @else
                        <span class="text-xs sm:text-sm font-extrabold text-slate-800 leading-snug">
                            Mandiri / Fleksibel
                        </span>
                    @endif
                </div>

                {{-- Modul --}}
                <div class="p-3 sm:p-4 bg-slate-50 rounded-md border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="far fa-folder-open text-slate-400"></i> Modul
                    </span>
                    <span
                        class="text-base sm:text-lg font-extrabold text-slate-800">{{ count(data_get($course, 'sections', [])) }}</span>
                </div>

                {{-- Selesai --}}
                <div class="p-3 sm:p-4 bg-slate-50 rounded-md border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-check text-slate-400"></i> Selesai
                    </span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-800">{{ $completedItems }}
                        <span class="text-xs sm:text-sm text-slate-400 font-medium">/ {{ $totalItems }}</span>
                    </span>
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
                        <div>
                            <h2 class="text-md sm:text-lg font-extrabold text-slate-800 leading-tight">Daftar Modul Pembelajaran</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Selesaikan seluruh tahapan modul secara berurutan</p>
                        </div>
                    </div>
                    <div x-data="{
                        activeAccordion: localStorage.getItem('active_section_{{ $courseSlug }}') || 'section-0',
                    
                        init() {
                            const urlParams = new URLSearchParams(window.location.search);
                            const targetId = urlParams.get('target');
                    
                            /*
                             * Jika dashboard mengirim target=auto,
                             * cari modul pertama yang belum selesai.
                             */
                            if (targetId === 'auto') {
                    
                                setTimeout(() => {
                    
                                    const sections = Array.from(
                                        document.querySelectorAll('[data-section-id]')
                                    );
                    
                                    let targetSection = null;
                    
                                    for (const section of sections) {
                    
                                        /*
                                         * Modul terkunci tidak dipilih.
                                         */
                                        if (section.dataset.locked === '1') {
                                            continue;
                                        }
                    
                                        /*
                                         * Cari bagian yang belum selesai.
                                         */
                                        if (section.dataset.completed !== '1') {
                                            targetSection = section;
                                            break;
                                        }
                                    }
                    
                                    /*
                                     * Jika semua modul selesai,
                                     * buka modul pertama.
                                     */
                                    if (!targetSection && sections.length > 0) {
                                        targetSection = sections[0];
                                    }
                    
                                    if (targetSection) {
                    
                                        this.activeAccordion =
                                            targetSection.getAttribute('id');
                    
                                        setTimeout(() => {
                    
                                            targetSection.scrollIntoView({
                                                behavior: 'smooth',
                                                block: 'center'
                                            });
                    
                                            targetSection.classList.add(
                                                'ring-2',
                                                'ring-[#2563EB]'
                                            );
                    
                                            setTimeout(() => {
                    
                                                targetSection.classList.remove(
                                                    'ring-2',
                                                    'ring-[#2563EB]'
                                                );
                    
                                            }, 2000);
                    
                                        }, 150);
                    
                                    }
                    
                                }, 300);
                    
                                return;
                            }
                    
                    
                            /*
                             * Target normal berdasarkan section ID.
                             */
                            if (targetId) {
                    
                                setTimeout(() => {
                    
                                    const el = document.querySelector(
                                        `[data-section-id='${targetId}']`
                                    );
                    
                                    if (el) {
                    
                                        this.activeAccordion =
                                            el.getAttribute('id');
                    
                                        setTimeout(() => {
                    
                                            el.scrollIntoView({
                                                behavior: 'smooth',
                                                block: 'center'
                                            });
                    
                                            el.classList.add(
                                                'ring-2',
                                                'ring-[#2563EB]'
                                            );
                    
                                            setTimeout(() => {
                    
                                                el.classList.remove(
                                                    'ring-2',
                                                    'ring-[#2563EB]'
                                                );
                    
                                            }, 2000);
                    
                                        }, 150);
                    
                                    }
                    
                                }, 300);
                            }
                        }
                    }" x-init="init();
                    
                    $watch(
                        'activeAccordion',
                        value => localStorage.setItem(
                            'active_section_{{ $courseSlug }}',
                            value
                        )
                    )" class="space-y-3 sm:space-y-4">

                        @php
                            $isPreviousSectionDone = true;
                        @endphp

                        @forelse (data_get($course, 'sections', []) as $index => $section)
                            @php
                                $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
                                $sectionName = data_get($section, 'name', 'Bagian ' . ($index + 1));
                                $sectionContentsRaw = data_get($section, 'section_contents', []);
                                $totalCount = count($sectionContentsRaw);
                                $completedCount = data_get($section, 'completed_count', 0);
                                $isContentCompleted = data_get($section, 'is_content_completed', $totalCount === 0 || $completedCount === $totalCount);
                                $postTestBab = data_get($section, 'post_test_bab');
                                $isPostTestBabCompleted = data_get($section, 'is_post_test_bab_completed', true);
                                $isSectionCompleted = data_get($section, 'is_section_completed', $isContentCompleted && $isPostTestBabCompleted);
                                $isLocked = data_get($section, 'is_locked', !$isPreviousSectionDone);
                            @endphp

                            <div x-data="{ id: 'section-{{ $index }}', locked: {{ $isLocked ? 'true' : 'false' }} }" id="section-{{ $index }}"
                                data-section-id="{{ $sectionId }}"
                                data-locked="{{ $isLocked ? 1 : 0 }}" data-completed="{{ $isSectionCompleted ? 1 : 0 }}"
                                class="border {{ $isLocked ? 'border-slate-100 bg-slate-50' : 'border-slate-200 bg-white' }} rounded-md overflow-hidden shadow-sm transition-all duration-500"
                                :class="{
                                    'border-[#13416B] shadow-md ring-1 ring-gray-100': activeAccordion == id && !
                                        locked
                                }">

                                <button @click="if(!locked) activeAccordion = (activeAccordion == id ? '' : id)"
                                    class="flex items-start sm:items-center justify-between w-full p-3.5 sm:p-5 text-left transition-colors border-l-4 border-l-transparent {{ $isLocked ? 'cursor-not-allowed opacity-80' : 'hover:bg-slate-50/50' }}"
                                    :class="{ 'bg-slate-50/80 border-l-[#13416B]': activeAccordion == id && !locked }">

                                    <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0 pr-2">
                                        @if ($isLocked)
                                            <span
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-400 text-white text-xs mt-0.5 shrink-0 border border-slate-400">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        @elseif($isSectionCompleted)
                                            <span
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 text-white text-xs mt-0.5 shrink-0 border border-emerald-500">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @else
                                            <span
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#13416B] text-white text-xs font-bold mt-0.5 shrink-0 border border-[#13416B]">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <h3
                                                class="font-bold {{ $isLocked ? 'text-slate-500' : 'text-slate-800' }} text-sm sm:text-base leading-snug break-words">
                                                {{ $sectionName }}
                                            </h3>

                                            <div
                                                class="flex flex-wrap items-center gap-x-2 sm:gap-x-3 gap-y-1.5 mt-1.5">
                                                <span
                                                    class="text-[11px] sm:text-xs text-slate-500 font-medium flex items-center gap-1 whitespace-nowrap">
                                                    <i class="far fa-file-alt text-slate-400"></i> {{ $totalCount }}
                                                    Topik
                                                </span>

                                                @if ($postTestBab)
                                                    <span
                                                        class="text-[10px] text-slate-300 hidden sm:inline-block">•</span>
                                                    <span
                                                        class="text-[11px] sm:text-xs text-slate-500 font-medium flex items-center gap-1 whitespace-nowrap">
                                                        <i class="fas fa-file-signature text-slate-400"></i> 1 Evaluasi
                                                    </span>
                                                @endif

                                                <span class="text-[10px] text-slate-300 hidden sm:inline-block">•</span>

                                                @if ($isLocked)
                                                    <span
                                                        class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-semibold text-slate-400 whitespace-nowrap">
                                                        <i class="fas fa-lock text-[10px]" aria-hidden="true"></i>
                                                        Terkunci
                                                    </span>
                                                @else
                                                    <span
                                                        class="text-[11px] sm:text-xs font-semibold {{ $isSectionCompleted ? 'text-emerald-600 font-bold' : 'text-gray-500' }} whitespace-nowrap">
                                                        {{ $completedCount }}/{{ $totalCount }} Selesai
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 {{ $isLocked ? 'text-slate-300' : 'text-slate-400' }} transition-transform duration-200 shrink-0 mt-1 sm:mt-0"
                                        :class="{ 'rotate-180 text-[#13416B]': activeAccordion == id && !locked }"
                                        viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>

                                <div x-show="activeAccordion == id && !locked" x-collapse x-cloak>
                                    <div
                                        class="p-3 sm:p-5 border-t border-slate-100 bg-slate-50/50 space-y-3 sm:space-y-4">

                                        {{-- Looping Topik (berurutan: topik berikutnya terkunci sampai topik sebelumnya selesai) --}}
                                        @php $isPreviousContentDone = true; @endphp

                                        @forelse ($sectionContentsRaw as $content)
                                            @php
                                                $contentId = data_get($content, 'id', Str::random(5));
                                                $contentName = data_get($content, 'name', 'Topik Tanpa Judul');
                                                $videoUrlRaw = data_get($content, 'video_url');
                                                $documentUrlRaw = data_get($content, 'document_url');
                                                $isContentItemCompleted = data_get($content, 'is_completed', false);
                                                // Terkunci jika topik sebelumnya belum selesai (kecuali topik ini sudah pernah diselesaikan)
                                                $isContentLocked = !$isPreviousContentDone && !$isContentItemCompleted;
                                            @endphp

                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-emerald-600/40 hover:shadow-sm transition-all duration-200 gap-3 sm:gap-4">
                                                <div
                                                    class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                                    @php
                                                        $contentIconBox = $isContentItemCompleted
                                                            ? 'bg-emerald-600 text-white border-emerald-600'
                                                            : ($isContentLocked ? 'bg-slate-400 text-white border-slate-400' : 'bg-[#547996] text-white border-[#547996]');
                                                    @endphp
                                                    <span
                                                        class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full {{ $contentIconBox }} shrink-0 border mt-0.5 sm:mt-0">
                                                        @if (!empty($videoUrlRaw))
                                                            <i class="fas fa-play text-sm"></i>
                                                        @elseif (!empty($documentUrlRaw))
                                                            <i class="fas fa-file-pdf text-lg"></i>
                                                        @else
                                                            <i class="fas fa-file-alt text-lg"></i>
                                                        @endif
                                                    </span>

                                                    <div class="flex-1 min-w-0">
                                                        <p
                                                            class="font-bold text-slate-800 text-sm leading-tight break-words">
                                                            {{ $contentName }}
                                                        </p>
                                                        @if ($isContentItemCompleted)
                                                            <div class="mt-2 flex items-center gap-1.5">
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-emerald-600 px-2.5 py-0.5 rounded-full border border-emerald-600">
                                                                    <i class="fas fa-check"></i> Selesai
                                                                </span>
                                                            </div>
                                                        @elseif ($isContentLocked)
                                                            <div class="mt-2 flex items-center gap-1.5">
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-slate-400 px-2 py-0.5 rounded-full border border-slate-400">
                                                                    <i class="fas fa-lock text-[9px]"></i> Terkunci
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="mt-2 flex items-center gap-1.5">
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-amber-500 px-2.5 py-0.5 rounded-full border border-amber-500">
                                                                    <i class="fas fa-clock text-[9px]"></i> Belum Selesai
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if (!$isContentLocked)
                                                    <a href="{{ route('user.course.content.show', ['slug' => $courseSlug, 'content' => $contentId]) }}"
                                                        class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2.5 sm:py-2 {{ $isContentItemCompleted ? 'bg-white border border-emerald-600/30 text-emerald-700 hover:bg-emerald-50' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' }} rounded-md text-xs font-bold transition-all text-center shrink-0 flex items-center justify-center gap-2">
                                                        <i
                                                            class="fas {{ $isContentItemCompleted ? 'fa-eye' : 'fa-play' }}"></i>
                                                        {{ $isContentItemCompleted ? 'Lihat Kembali' : 'Pelajari Topik' }}
                                                    </a>
                                                @else
                                                    <button type="button" disabled
                                                        class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 sm:py-2 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-bold text-center shrink-0 flex items-center justify-center gap-1.5 cursor-not-allowed select-none">
                                                        <i class="fas fa-lock text-[10px]"></i> Terkunci
                                                    </button>
                                                @endif
                                            </div>

                                            @php $isPreviousContentDone = $isContentItemCompleted; @endphp
                                        @empty
                                            <div
                                                class="p-4 text-center text-slate-500 text-sm bg-white rounded-md border border-dashed border-slate-200">
                                                <i class="fas fa-folder-open mb-2 text-slate-300 text-xl block"></i>
                                                Topik sedang disiapkan.
                                            </div>
                                        @endforelse

                                        {{-- Evaluasi per Modul (terkunci sampai semua topik selesai) --}}
                                        @if ($postTestBab)
                                            @php
                                                $isPostTestBabLocked = !$isContentCompleted && !$isPostTestBabCompleted;
                                                $postTestIconBox = $isPostTestBabCompleted
                                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                                    : ($isPostTestBabLocked ? 'bg-slate-400 text-white border-slate-400' : 'bg-[#13416B] text-white border-[#13416B]');
                                            @endphp
                                            <div class="mt-4 pt-4 border-t border-slate-200/80">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm transition-all duration-200 gap-3 sm:gap-4">
                                                    <div
                                                        class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                                        <span
                                                            class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-md {{ $postTestIconBox }} shrink-0 border mt-0.5 sm:mt-0">
                                                            <i
                                                                class="fas {{ $isPostTestBabLocked ? 'fa-lock' : 'fa-clipboard-check' }} text-base"></i>
                                                        </span>
                                                        <div class="flex-1 min-w-0">
                                                            <p
                                                                class="font-bold text-slate-800 text-sm leading-tight break-words">
                                                                {{ data_get($postTestBab, 'title', 'Post Test: ' . $sectionName) }}
                                                            </p>

                                                            <!-- Info Evaluasi Topik: Cukup Text Saja (Jumlah Soal & Durasi) -->
                                                            <div class="mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-500">
                                                                <span class="inline-flex items-center gap-1">
                                                                    <i class="fas fa-list-ol text-slate-400 text-xs"></i>
                                                                    {{ data_get($postTestBab, 'questions', collect())->count() ?? 0 }} Soal
                                                                </span>
                                                                <span class="text-slate-300">•</span>
                                                                <span class="inline-flex items-center gap-1">
                                                                    <i class="fas fa-stopwatch text-slate-400 text-xs"></i>
                                                                    {{ data_get($postTestBab, 'duration', 0) }} Menit
                                                                </span>
                                                            </div>

                                                            @if ($isPostTestBabCompleted)
                                                                <div class="mt-1.5 flex items-center gap-1.5">
                                                                    <span
                                                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-emerald-600 px-2.5 py-0.5 rounded-full border border-emerald-600">
                                                                        <i class="fas fa-check"></i> Selesai
                                                                    </span>
                                                                </div>
                                                            @elseif (!$isPostTestBabLocked)
                                                                <div class="mt-1.5 flex items-center gap-1.5">
                                                                    <span
                                                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-white bg-amber-500 px-2.5 py-0.5 rounded-full border border-amber-500">
                                                                        <i class="fas fa-clock text-[9px]"></i> Belum Selesai
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if ($isPostTestBabLocked)
                                                        <button type="button" disabled
                                                            class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 sm:py-2 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-bold text-center shrink-0 flex items-center justify-center gap-1.5 cursor-not-allowed select-none">
                                                            <i class="fas fa-lock text-[10px]"></i> Terkunci
                                                        </button>
                                                    @else
                                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                                            class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2 sm:py-2 {{ $isPostTestBabCompleted ? 'bg-white border border-emerald-600/30 text-emerald-700 hover:bg-emerald-50' : 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm' }} rounded-md text-xs font-bold transition-all text-center shrink-0 flex items-center justify-center gap-2">
                                                            <i
                                                                class="fas {{ $isPostTestBabCompleted ? 'fa-eye' : 'fa-pencil-alt' }}"></i>
                                                            {{ $isPostTestBabCompleted ? 'Lihat Hasil' : 'Kerjakan Evaluasi' }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
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
                                <i class="fas fa-layer-group mb-3 text-slate-300 text-3xl block"></i>
                                <p class="font-semibold text-sm">Belum ada modul pembelajaran.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Card Sesi Live Zoom Bimtek Online --}}
                @if ($activeBimtek && $activeBimtek->zoom_url)
                    <div data-tour="zoom"
                        class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-md bg-[#547996] text-white border border-[#446680] flex items-center justify-center shrink-0 text-2xl shadow-xs">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-slate-800">Tatap Muka Online</h3>
                                    @if ($isZoomUnlocked)
                                        @if ($isZoomAttended)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white shadow-xs">
                                                <i class="fas fa-check-circle"></i> Presensi Kehadiran Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white shadow-xs">
                                                <i class="fas fa-clock"></i> Belum Konfirmasi Hadir
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                @if ($isZoomUnlocked)
                                    <p class="text-xs text-slate-600 font-medium">
                                        <i class="far fa-calendar-alt mr-1 text-slate-400"></i> Jadwal: <span class="font-bold text-slate-800">{{ $activeBimtek->zoom_schedule_formatted }}</span>
                                    </p>
                                    @if ($activeBimtek->zoom_meeting_id)
                                        <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-500 font-mono">
                                            <span>Meeting ID: <strong class="text-slate-700 font-bold">{{ $activeBimtek->zoom_meeting_id }}</strong></span>
                                            @if ($activeBimtek->zoom_passcode)
                                                <span>•</span>
                                                <span>Passcode: <strong class="text-slate-700 font-bold">{{ $activeBimtek->zoom_passcode }}</strong></span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <p class="text-xs text-slate-500 leading-relaxed font-medium">
                                        Selesaikan seluruh materi topik pembelajaran mandiri untuk membuka tautan Sesi Live Zoom.
                                    </p>
                                    <p class="text-[11px] text-slate-500 font-bold pt-0.5">
                                        <i class="far fa-calendar-alt mr-1 text-slate-400"></i> Jadwal: <span class="text-slate-700">{{ $activeBimtek->zoom_schedule_formatted }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="relative z-10 shrink-0 flex flex-col sm:items-end gap-2 self-start md:self-center">
                            @if ($isZoomUnlocked)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ $activeBimtek->zoom_url }}" target="_blank"
                                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-[#13416B] text-white hover:bg-[#0f3354] transition-all shadow-sm">
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                        <span>Masuk Ruang Zoom</span>
                                    </a>
                                    @if (!$isZoomAttended)
                                        <form action="{{ route('user.course.my-course.attend-zoom', $courseSlug) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Konfirmasi Hadir</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-500 font-medium">Konfirmasi kehadiran diperlukan untuk membuka Evaluasi Akhir</span>
                            @else
                                <button type="button" disabled
                                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-slate-400 text-white border border-slate-400 cursor-not-allowed shadow-none">
                                    <i class="fas fa-lock text-xs"></i>
                                    <span>Ruang Zoom Terkunci</span>
                                </button>
                                <span class="text-[10px] text-slate-400 font-medium">Tuntaskan materi mandiri terlebih dahulu</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- CARD EVALUASI AKHIR --}}
                @php
                    $isEvaluasiAkhirLocked = !$isPreviousSectionDone;
                    if ($activeBimtek) {
                        $isEvaluasiAkhirLocked = $isEvaluasiAkhirLocked || !$isZoomAttended;
                    }
                @endphp

                @if ($evaluasiAkhir)
                    <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                    <div data-tour="evaluasi"
                        class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 overflow-hidden transition-all relative">
                        <div
                            class="px-4 py-4 sm:px-6 sm:py-5 {{ $isEvaluasiAkhirLocked ? 'bg-slate-600 text-white border-b border-slate-700' : 'bg-gradient-to-r from-[#13416B] to-[#0f3354] text-white' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6">
                            <div class="flex items-start sm:items-center gap-4 flex-1 min-w-0">
                                <div
                                    class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-white/15 text-white border border-white/25">
                                    <i
                                        class="fas {{ $isEvaluasiAkhirLocked ? 'fa-lock' : 'fa-graduation-cap' }} text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-[10px] sm:text-xs font-bold uppercase tracking-wider {{ $isEvaluasiAkhirLocked ? 'text-slate-200' : 'text-blue-200' }} mb-1">
                                        Tahap Akhir
                                    </p>
                                    <h3
                                        class="text-base sm:text-lg font-extrabold break-words text-white leading-tight">
                                        {{ data_get($evaluasiAkhir, 'title', 'Evaluasi Akhir Pelatihan') }}
                                    </h3>
                                </div>
                            </div>

                            @if ($isEvaluasiAkhirCompleted)
                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 rounded-md text-center shadow-sm transition-all flex items-center justify-center gap-2 border border-transparent whitespace-nowrap">
                                    <i class="fas fa-eye"></i> Lihat Hasil
                                </a>
                            @elseif (!$isEvaluasiAkhirLocked)
                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 rounded-md text-center shadow-sm transition-all flex items-center justify-center gap-2 border border-transparent whitespace-nowrap">
                                    <i class="fas fa-play"></i> Mulai Evaluasi
                                </a>
                            @endif
                        </div>

                        <div class="p-5 sm:p-6 bg-white">
                            <p
                                class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                {{ data_get($evaluasiAkhir, 'description', 'Ujian utama ini adalah syarat mutlak penyelesaian pelatihan. Pastikan Anda telah menguasai seluruh materi sebelum memulai. Nilai dari evaluasi ini akan menentukan kelayakan Anda untuk mendapatkan sertifikat kelulusan.') }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-2 sm:gap-3">
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-list-ol text-white"></i>
                                    {{ data_get($evaluasiAkhir, 'questions', collect())->count() ?? 0 }} Soal
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-stopwatch text-white"></i>
                                    {{ data_get($evaluasiAkhir, 'duration', 0) }} Menit
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-bullseye text-white"></i> KKM:
                                    {{ data_get($evaluasiAkhir, 'passing_score', 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Side Widget (Sertifikat & Info) --}}
            <div class="space-y-0 lg:sticky lg:top-24 lg:self-start">
                <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                <div data-tour="sertifikat"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-0 md:mb-4">

                    {{-- Header Widget --}}
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div
                            class="w-10 h-10 flex items-center justify-center bg-[#13416B] text-white border border-[#13416B] rounded-md shrink-0 shadow-xs">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <h3 class="text-base md:text-lg font-extrabold text-slate-800">Sertifikat Kelulusan</h3>
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
                                    <a href="{{ route('verify-certificate', data_get($course, 'certificate_code')) }}" target="_blank"
                                        class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-md font-semibold transition-all flex items-center justify-center gap-1.5 text-xs">
                                        <i class="fas fa-qrcode"></i> Cek Keabsahan Sertifikat
                                    </a>
                                </div>
                            </div>
                        @else
                            {{-- STATE 2: SELESAI, TAPI CEK APAKAH SEDANG DIAJUKAN KE PPSDM --}}
                            @php
                                $enrollmentData = \DB::table('course_student')
                                    ->where('course_id', data_get($course, 'id'))
                                    ->where('user_id', auth()->id())
                                    ->first();
                                $isSubmittedToPPSDM = ($enrollmentData?->cert_submission_status === 'diajukan_ke_ppsdm');
                            @endphp

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

                            @php
                                $remaining = $totalItems - $completedItems;
                            @endphp

                            <p class="text-xs text-slate-500 leading-relaxed max-w-[220px]">
                                @if ($remaining > 0)
                                    Akses akan terbuka secara otomatis setelah Anda menyelesaikan <span
                                        class="font-bold text-slate-800">{{ $remaining }} tahapan</span> yang
                                    tersisa.
                                @else
                                    Selesaikan seluruh tahapan hingga progress mencapai 100% untuk mengunduh dokumen
                                    sertifikat.
                                @endif
                            </p>
                        </div>
                    @endif
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
                        element: '[data-tour="progres"]',
                        popover: {
                            title: 'Pantau progres belajar',
                            description: 'Persentase ini menggabungkan penyelesaian materi mandiri, tatap muka online, dan evaluasi. Sertifikat terbuka saat progres 100%.',
                        },
                    },
                    {
                        element: '[data-tour="modul"]',
                        popover: {
                            title: '1. Materi pembelajaran',
                            description: 'Pelajari seluruh materi topik secara berurutan. Tekan Tandai Selesai di setiap topik untuk membuka materi berikutnya.',
                        },
                    },
                    @if ($activeBimtek && $activeBimtek->zoom_url)
                    {
                        element: '[data-tour="zoom"]',
                        popover: {
                            title: '2. Tatap muka online (Zoom)',
                            description: 'Setelah materi mandiri selesai, ikuti Sesi Live Zoom bersama narasumber. Konfirmasi kehadiran pada sesi ini diperlukan sebelum Anda dapat mengakses Evaluasi Akhir.',
                        },
                    },
                    @endif
                    {
                        element: '[data-tour="evaluasi"]',
                        popover: {
                            title: '{{ $activeBimtek ? "3." : "2." }} Evaluasi akhir',
                            description: '{{ $activeBimtek ? "Ujian penentu kelulusan. Terbuka setelah seluruh materi mandiri tuntas dan Anda mengikuti Sesi Live Zoom. Capai nilai KKM untuk lulus." : "Ujian akhir penentu kelulusan kursus. Terbuka setelah semua modul selesai. Lulus nilai KKM untuk menuntaskan kursus." }}',
                        },
                    },
                    {
                        element: '[data-tour="sertifikat"]',
                        popover: {
                            title: '{{ $activeBimtek ? "4." : "3." }} Sertifikat kelulusan',
                            description: 'Setelah progres mencapai 100% dan evaluasi akhir dinyatakan lulus, Anda dapat menerbitkan dan mengunduh sertifikat kelulusan resmi.',
                        },
                    },
                    {
                        popover: {
                            title: 'Siap belajar?',
                            description: '{{ $activeBimtek ? "Ikuti alur pembelajaran: Materi Mandiri → Tatap Muka Zoom → Evaluasi Akhir → Sertifikat Kelulusan. Tur ini bisa Anda buka kembali kapan saja melalui tombol Panduan." : "Ikuti alur: Materi Modul → Evaluasi Akhir → Sertifikat Kelulusan. Tur ini bisa Anda buka kembali kapan saja melalui tombol Panduan." }}',
                        },
                    },
                ];

                window.startCoursePlayerTour = function () {
                    window.SirenataTour.start(PAGE, STEPS);
                };

                document.addEventListener('DOMContentLoaded', function () {
                    window.SirenataTour.autoStart(PAGE, STEPS);
                });
            })();
        </script>
    @endpush
</x-dashboard::layouts.dashboard>