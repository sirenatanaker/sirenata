<x-dashboard::layouts.dashboard
    title="Kursus Saya | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    <!-- PERBAIKAN: Menghapus padding horizontal di mobile (px-0) -->
    <div class="px-0 py-4 sm:p-6 max-w-full mx-auto">

        {{-- Breadcrumb Custom (Responsif & Tanpa Icon Home) --}}
        <nav class="hidden md:flex mb-5 sm:mb-6 mt-4 px-4 sm:px-0" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-y-1.5 gap-x-2">
                <li>
                    <a href="{{ route('user.course.my-course') }}"
                        class="text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors whitespace-nowrap">
                        Kursus Saya
                    </a>
                </li>
                <li>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[10px]"></i>
                        <span class="text-sm font-medium text-slate-800 leading-snug">
                            {{ data_get($course, 'course_name') ?? data_get($course, 'name') }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- KALKULASI PROGRESS REAL (Materi + Evaluasi Bab + Evaluasi Akhir) --}}
        @php
            $thumbnailUrl = data_get($course, 'thumbnail_url');
            $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
            $courseSlug = data_get($course, 'slug') ?? request()->route('slug');
            $courseId = data_get($course, 'id') ?? data_get($course, 'course_id');

            // 1. Hitung Total Materi (Teks/Video/Dokumen)
            $totalContents = collect(data_get($course, 'sections', []))->sum(
                fn($s) => count(data_get($s, 'section_contents', [])),
            );
            $completedContents = data_get($course, 'completed_count', 0);

            // 2. Hitung Total Post Test & Kelulusannya
            $totalTests = 0;
            $passedTests = 0;

            foreach (data_get($course, 'sections', []) as $section) {
                $postTestBab = \Modules\LMS\Models\PostTest::where(
                    'course_section_id',
                    data_get($section, 'id', data_get($section, 'section_id')),
                )->first();
                if ($postTestBab) {
                    $totalTests++;
                    $isPassed = \Illuminate\Support\Facades\DB::table('post_test_results')
                        ->where('user_id', auth()->id())
                        ->where('post_test_id', $postTestBab->id)
                        ->where('is_passed', 1)
                        ->exists();
                    if ($isPassed) {
                        $passedTests++;
                    }
                }
            }

            // 3. Hitung Evaluasi Akhir
            $evaluasiAkhir = $courseId
                ? \Modules\LMS\Models\PostTest::where('course_id', $courseId)->whereNull('course_section_id')->first()
                : null;
            $isEvaluasiAkhirCompleted = false;

            if ($evaluasiAkhir) {
                $totalTests++;
                $isEvaluasiAkhirCompleted = \Illuminate\Support\Facades\DB::table('post_test_results')
                    ->where('user_id', auth()->id())
                    ->where('post_test_id', $evaluasiAkhir->id)
                    ->where('is_passed', 1)
                    ->exists();
                if ($isEvaluasiAkhirCompleted) {
                    $passedTests++;
                }
            }

            // 4. Kalkulasi Persentase Aktual
            $totalItems = $totalContents + $totalTests;
            $completedItems = $completedContents + $passedTests;
            $currentProgress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

            // Kunci Sertifikat & Kelulusan (Wajib 100% DAN lulus evaluasi akhir jika evaluasinya ada)
            $isFullyCompleted = $currentProgress >= 100 && (!$evaluasiAkhir || $isEvaluasiAkhirCompleted);

            if (empty($thumbnailUrl)) {
                $encodedName = urlencode($courseName);
                $thumbnailUrl = "https://ui-avatars.com/api/?name={$encodedName}&background=eff6ff&color=1e3a8a&size=512&font-size=0.33&bold=true";
            }
        @endphp

        {{-- Header Card --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 lg:p-8 mb-6 flex flex-col md:flex-row gap-6 lg:gap-8 items-start transition-all">
            {{-- Bagian Kiri: Thumbnail --}}
            <div
                class="w-full md:w-1/3 lg:w-1/4 shrink-0 rounded-xl overflow-hidden bg-slate-100 aspect-video md:aspect-[4/3] relative border border-slate-100">
                <img src="{{ $thumbnailUrl }}" alt="{{ $courseName }}" class="w-full h-full object-cover" />
            </div>

            {{-- Bagian Kanan: Informasi Kursus --}}
            <div class="flex-1 flex flex-col w-full h-full">
                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/10 border border-[#13416B]/20 rounded-md">
                        {{ data_get($course, 'category.name', 'Tanpa Kategori') }}
                    </span>
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $isFullyCompleted ? 'text-emerald-700 bg-emerald-100 border border-emerald-200' : 'text-amber-700 bg-amber-100 border border-amber-200' }} rounded-md flex items-center gap-1">
                        <i class="fas {{ $isFullyCompleted ? 'fa-check-circle' : 'fa-clock' }}"></i>
                        {{ $isFullyCompleted ? 'Selesai' : 'Sedang Berjalan' }}
                    </span>
                </div>

                {{-- Judul --}}
                <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 mb-3 tracking-tight leading-tight">
                    {{ $courseName }}
                </h1>

                {{-- Deskripsi --}}
                <div class="prose prose-sm text-slate-600 mb-6">
                    <p class="text-xs sm:text-sm leading-relaxed">
                        {{ data_get($course, 'description', 'Tidak ada deskripsi tersedia untuk course ini.') }}
                    </p>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-auto pt-5 border-t border-slate-100 max-w-md w-full">
                    <div class="flex justify-between items-center mb-2.5">
                        <span class="text-xs font-semibold text-slate-500">Progress Pembelajaran</span>
                        <span
                            class="text-sm font-bold {{ $currentProgress >= 100 ? 'text-[#13416B]' : 'text-amber-600' }}">
                            {{ $currentProgress }}%
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="{{ $currentProgress >= 100 ? 'bg-[#13416B]' : 'bg-amber-500' }} h-full rounded-full transition-all duration-700"
                            style="width: {{ $currentProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- WIDGET STATISTIK BELAJAR --}}
        <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
        <div
            class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-6">
            <div class="flex items-center gap-3 mb-4 sm:mb-5 pb-3 sm:pb-4 border-b border-slate-100">
                <div
                    class="w-10 h-10 flex items-center justify-center bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 rounded-xl shrink-0">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-extrabold text-slate-800">Statistik Belajar</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pantau ringkasan progres dan pencapaian Anda</p>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-info-circle"></i> Status
                    </span>
                    <span
                        class="inline-flex items-center justify-center px-2 py-1.5 rounded-md text-[10px] sm:text-xs font-bold uppercase tracking-wider {{ $isFullyCompleted ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                        {{ $isFullyCompleted ? 'Selesai' : 'Berjalan' }}
                    </span>
                </div>
                <div class="p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-layer-group"></i> Modul
                    </span>
                    <span
                        class="text-base sm:text-lg font-extrabold text-slate-800">{{ count(data_get($course, 'sections', [])) }}</span>
                </div>
                <div class="p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-file-alt"></i> Total Tahapan
                    </span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-800">{{ $totalItems }} <span
                            class="text-[10px] sm:text-xs font-medium text-slate-500">(Materi & Ujian)</span></span>
                </div>
                <div class="p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                    <span
                        class="text-[10px] sm:text-xs uppercase font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-check-double"></i> Selesai
                    </span>
                    <span class="text-base sm:text-lg font-extrabold text-[#13416B]">{{ $completedItems }}
                        <span class="text-xs sm:text-sm text-slate-400 font-medium">/ {{ $totalItems }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- INFO PANDUAN (VERSI MOBILE) --}}
        @if (!$isFullyCompleted)
            <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
            <div
                class="block lg:hidden bg-slate-50 border-y border-x-0 sm:border sm:border-x border-slate-200 rounded-none sm:rounded-xl p-4 mb-6 flex items-start gap-3 shadow-sm mx-0">
                <i class="fas fa-info-circle text-slate-400 mt-0.5 text-base shrink-0"></i>
                <p class="text-[11px] sm:text-xs text-slate-600 leading-relaxed font-medium">
                    Materi dipelajari berurutan. Buka kunci modul berikutnya dengan menekan <strong
                        class="text-slate-800">Tandai Selesai</strong> di setiap materi dan lulus <strong
                        class="text-slate-800">Evaluasi</strong>.
                </p>
            </div>
        @endif

        {{-- Grid Layout (Materi & Sertifikat) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Left column: List Modul & Evaluasi Akhir --}}
            <div class="lg:col-span-2 space-y-6">
                <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                <div
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6">

                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between mb-5 sm:mb-6 pb-4 border-b border-slate-100 gap-3 sm:gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-slate-50 text-slate-600 rounded-xl shrink-0 mt-0">
                                <i class="fas fa-list-ul text-lg"></i>
                            </div>
                            <h2 class="text-md sm:text-lg font-extrabold text-slate-800 leading-tight">Daftar Modul
                                Pembelajaran</h2>
                        </div>
                        <span
                            class="text-[11px] sm:text-xs text-slate-600 font-bold bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 shrink-0 self-start sm:self-auto">
                            {{ count(data_get($course, 'sections', [])) }} Modul
                        </span>
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
                                        if (section.querySelector('.fa-lock')) {
                                            continue;
                                        }
                    
                                        /*
                                         * Cari bagian yang belum selesai.
                                         */
                                        const completedIcon =
                                            section.querySelector('.fa-check');
                    
                                        if (!completedIcon) {
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
                                $completedCount = 0;
                                foreach ($sectionContentsRaw as $content) {
                                    if (data_get($content, 'is_completed')) {
                                        $completedCount++;
                                    }
                                }

                                $isContentCompleted = $totalCount === 0 || $completedCount === $totalCount;

                                $postTestBab = $sectionId
                                    ? \Modules\LMS\Models\PostTest::where('course_section_id', $sectionId)->first()
                                    : null;

                                $isPostTestBabCompleted = true;
                                if ($postTestBab) {
                                    $isPostTestBabCompleted = \Illuminate\Support\Facades\DB::table('post_test_results')
                                        ->where('user_id', auth()->id())
                                        ->where('post_test_id', $postTestBab->id)
                                        ->where('is_passed', 1)
                                        ->exists();
                                }

                                $isSectionCompleted = $isContentCompleted && $isPostTestBabCompleted;
                                $isLocked = !$isPreviousSectionDone;
                            @endphp

                            <div x-data="{ id: 'section-{{ $index }}', locked: {{ $isLocked ? 'true' : 'false' }} }" id="section-{{ $index }}"
                                data-section-id="{{ $sectionId }}"
                                class="border {{ $isLocked ? 'border-slate-100 bg-slate-50' : 'border-slate-200 bg-white' }} rounded-xl overflow-hidden shadow-sm transition-all duration-500"
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
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-200 text-slate-400 text-xs mt-0.5 shrink-0 border border-slate-300">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        @elseif($isSectionCompleted)
                                            <span
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-50 text-emerald-600 text-xs mt-0.5 shrink-0 border border-emerald-200">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @else
                                            <span
                                                class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-100 text-slate-600 text-xs font-bold mt-0.5 shrink-0 border border-slate-200">
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
                                                        <i class="fas fa-clipboard-list text-slate-400"></i> 1 Evaluasi
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
                                                        class="text-[11px] sm:text-xs font-semibold {{ $isSectionCompleted ? 'text-[#13416B]' : 'text-gray-500' }} whitespace-nowrap">
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

                                        {{-- Looping Materi --}}
                                        @forelse ($sectionContentsRaw as $content)
                                            @php
                                                $contentId = data_get($content, 'id', Str::random(5));
                                                $contentName = data_get($content, 'name', 'Materi Tanpa Judul');
                                                $videoUrlRaw = data_get($content, 'video_url');
                                                $documentUrlRaw = data_get($content, 'document_url');
                                                $isContentItemCompleted = data_get($content, 'is_completed', false);
                                            @endphp

                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-xl border border-slate-200 bg-white hover:border-[#13416B]/40 hover:shadow-sm transition-all duration-200 gap-3 sm:gap-4">
                                                <div
                                                    class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                                    @if (!empty($videoUrlRaw))
                                                        <span
                                                            class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-blue-50 text-blue-600 shrink-0 border border-blue-100  mt-0.5 sm:mt-0">
                                                            <i class="fas fa-play text-sm"></i>
                                                        </span>
                                                    @elseif(!empty($documentUrlRaw))
                                                        <span
                                                            class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-blue-50 text-blue-600 shrink-0  border border-blue-100 mt-0.5 sm:mt-0">
                                                            <i class="fas fa-file-pdf text-lg"></i>
                                                        </span>
                                                    @else
                                                        <span
                                                            class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-blue-50 text-blue-600 shrink-0 border border-blue-100 mt-0.5 sm:mt-0">
                                                            <i class="fas fa-file-alt text-lg"></i>
                                                        </span>
                                                    @endif

                                                    <div class="flex-1 min-w-0">
                                                        <p
                                                            class="font-bold text-slate-800 text-sm leading-tight break-words">
                                                            {{ $contentName }}
                                                        </p>
                                                        <div class="mt-2 flex items-center gap-1.5">
                                                            @if ($isContentItemCompleted)
                                                                <span
                                                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                                                    <i class="fas fa-check"></i> Selesai
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="inline-flex items-center text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                                                                    Belum Selesai
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <a href="{{ route('user.course.content.show', ['slug' => $courseSlug, 'content' => $contentId]) }}"
                                                    class="w-full sm:w-auto mt-2 sm:mt-0 px-4 py-2.5 sm:py-2 {{ $isContentItemCompleted ? 'bg-white border border-[#13416B]/30 text-[#13416B] hover:bg-[#13416B]/10' : 'bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm' }} rounded-xl text-xs font-bold transition-all text-center shrink-0 flex items-center justify-center gap-2">
                                                    <i
                                                        class="fas {{ $isContentItemCompleted ? 'fa-eye' : 'fa-play' }}"></i>
                                                    {{ $isContentItemCompleted ? 'Lihat Kembali' : 'Pelajari Topik' }}
                                                </a>
                                            </div>
                                        @empty
                                            <div
                                                class="p-4 text-center text-slate-500 text-sm bg-white rounded-xl border border-dashed border-slate-200">
                                                <i class="fas fa-folder-open mb-2 text-slate-300 text-xl block"></i>
                                                Materi sedang disiapkan.
                                            </div>
                                        @endforelse

                                        {{-- Post Test per Bab --}}
                                        @if ($postTestBab)
                                            <div class="mt-4 pt-4 border-t border-slate-200/80">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-5 rounded-xl border bg-[#13416B] border-[#13416B] hover:shadow-sm transition-all gap-4">
                                                    <div
                                                        class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                                        <span
                                                            class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/20 text-white border border-white/30 shrink-0 mt-0.5 sm:mt-0">
                                                            <i class="fas fa-clipboard-check text-lg"></i>
                                                        </span>
                                                        <div class="flex-1 min-w-0">
                                                            <p
                                                                class="font-bold text-sm leading-tight break-words text-white">
                                                                {{ data_get($postTestBab, 'title', 'Post Test: ' . $sectionName) }}
                                                            </p>
                                                            @if (data_get($postTestBab, 'description'))
                                                                <p
                                                                    class="text-xs mt-1.5 line-clamp-2 text-blue-100">
                                                                    {{ data_get($postTestBab, 'description') }}
                                                                </p>
                                                            @endif
                                                            @if ($isPostTestBabCompleted)
                                                                <div class="mt-2 flex items-center gap-1.5">
                                                                    <span
                                                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                                        <i class="fas fa-check"></i> Selesai
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                                        class="w-full sm:w-auto mt-1 sm:mt-0 px-5 py-2.5 {{ $isPostTestBabCompleted ? 'bg-white border border-[#13416B]/30 text-[#13416B] hover:bg-slate-100' : 'bg-amber-400 hover:bg-amber-500 text-gray-800 shadow-sm font-bold' }} rounded-xl text-xs transition-all text-center shrink-0 flex items-center justify-center gap-2">
                                                        <i
                                                            class="fas {{ $isPostTestBabCompleted ? 'fa-eye' : 'fa-pencil-alt' }}"></i>
                                                        {{ $isPostTestBabCompleted ? 'Lihat Hasil' : 'Kerjakan Evaluasi' }}
                                                    </a>
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
                                class="p-8 text-center text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <i class="fas fa-layer-group mb-3 text-slate-300 text-3xl block"></i>
                                <p class="font-semibold text-sm">Belum ada modul pembelajaran.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- CARD EVALUASI AKHIR --}}
                @php
                    $isEvaluasiAkhirLocked = !$isPreviousSectionDone;
                @endphp

                @if ($evaluasiAkhir)
                    <!-- PERBAIKAN: rounded-none border-x-0 di mobile -->
                    <div
                        class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 overflow-hidden transition-all relative">
                        <div
                            class="px-4 py-4 sm:px-6 sm:py-5 {{ $isEvaluasiAkhirLocked ? 'bg-slate-50 border-b border-slate-200' : 'bg-gradient-to-r from-[#13416B] to-[#0f3354] text-white' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6">
                            <div class="flex items-start sm:items-center gap-4 flex-1 min-w-0">
                                <div
                                    class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $isEvaluasiAkhirLocked ? 'bg-slate-200 text-slate-400' : 'bg-white/20 text-white border border-white/30' }}">
                                    <i
                                        class="fas {{ $isEvaluasiAkhirLocked ? 'fa-lock' : 'fa-graduation-cap' }} text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-[10px] sm:text-xs font-bold uppercase tracking-wider {{ $isEvaluasiAkhirLocked ? 'text-slate-400' : 'text-blue-100' }} mb-1">
                                        Tahap Akhir
                                    </p>
                                    <h3
                                        class="text-base sm:text-lg font-extrabold break-words {{ $isEvaluasiAkhirLocked ? 'text-slate-800' : 'text-white' }} leading-tight">
                                        {{ data_get($evaluasiAkhir, 'title', 'Evaluasi Akhir Course') }}
                                    </h3>
                                </div>
                            </div>

                            @if ($isEvaluasiAkhirLocked)
                                <span
                                    class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 px-4 py-2.5 text-[11px] sm:text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-xl text-center shadow-sm">
                                    <i class="fas fa-lock text-[10px]" aria-hidden="true"></i>
                                    Selesaikan Modul Dahulu
                                </span>
                            @elseif($isEvaluasiAkhirCompleted)
                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-[#13416B] bg-white hover:bg-slate-50 rounded-xl text-center shadow-sm transition-all flex items-center justify-center gap-2 border border-transparent whitespace-nowrap">
                                    <i class="fas fa-eye"></i> Lihat Hasil
                                </a>
                            @else
                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $evaluasiAkhir->id]) }}"
                                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-[#13416B] bg-white hover:bg-slate-50 rounded-xl text-center shadow-sm transition-all flex items-center justify-center gap-2 border border-transparent whitespace-nowrap">
                                    <i class="fas fa-play"></i> Mulai Evaluasi
                                </a>
                            @endif
                        </div>

                        <div class="p-5 sm:p-6 bg-white">
                            <p
                                class="text-xs sm:text-sm {{ $isEvaluasiAkhirLocked ? 'text-slate-500' : 'text-slate-600' }} leading-relaxed">
                                {{ data_get($evaluasiAkhir, 'description', 'Ujian utama ini adalah syarat mutlak penyelesaian kursus. Pastikan Anda telah menguasai seluruh materi sebelum memulai. Nilai dari evaluasi ini akan menentukan kelayakan Anda untuk mendapatkan sertifikat kelulusan.') }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-2 sm:gap-3">
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-list-ol text-slate-400"></i>
                                    {{ data_get($evaluasiAkhir, 'questions', collect())->count() ?? 0 }} Soal
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-stopwatch text-slate-400"></i>
                                    {{ data_get($evaluasiAkhir, 'duration', 0) }} Menit
                                </span>
                                <span
                                    class="text-[11px] sm:text-xs font-semibold text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-bullseye text-slate-400"></i> KKM:
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
                <div
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border sm:border-x border-slate-200 p-4 sm:p-6 mb-0 md:mb-4">

                    {{-- Header Widget --}}
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                        <div
                            class="w-10 h-10 flex items-center justify-center bg-[#13416B]/10 text-[#13416B] border border-[#13416B]/20 rounded-xl shrink-0">
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
                                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-center">
                                    <div
                                        class="w-14 h-14 shrink-0 bg-white text-emerald-600 rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-2xl border border-emerald-100">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <p class="font-bold text-emerald-800 text-sm">Sertifikat Siap Diunduh</p>
                                    <p class="text-xs text-emerald-600 mt-1.5 leading-relaxed">
                                        Selamat! Anda berhak mendapatkan sertifikat ini sebagai bukti sah telah
                                        menguasai seluruh materi.
                                    </p>
                                </div>

                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3 text-xs">
                                    <div class="flex justify-between border-b border-slate-200 pb-2">
                                        <span class="text-slate-500 font-semibold">Nomor</span>
                                        <span
                                            class="font-mono font-bold text-slate-800">{{ data_get($course, 'certificate_code') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 font-semibold">Tgl Terbit</span>
                                        <span
                                            class="font-bold text-slate-800">{{ \Carbon\Carbon::parse(data_get($course, 'certificate_issued_at'))->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                                    <i class="fas fa-info-circle text-amber-500 mt-0.5 text-lg shrink-0"></i>
                                    <div>
                                        <p class="text-[11px] text-amber-800 leading-relaxed font-medium">
                                            Penulisan nama dan gelar tidak sesuai? Anda bisa mengubahnya di profil.
                                        </p>
                                        <a href="http://localhost:8000/user/profile" target="_blank"
                                            class="inline-flex items-center gap-1 mt-2 text-[11px] font-bold text-amber-700 hover:text-amber-900 border-b border-amber-300 hover:border-amber-700 transition-colors pb-0.5">
                                            <i class="fas fa-user-edit"></i> Ubah Nama di Profil
                                        </a>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2.5">
                                    <a href="{{ data_get($course, 'certificate_file') }}" target="_blank" download
                                        class="w-full bg-[#13416B] hover:bg-[#0f3354] text-white py-3 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm">
                                        <i class="fas fa-download"></i> Unduh PDF
                                    </a>

                                    <form
                                        action="{{ route('user.course.my-course.generate-certificate', ['slug' => request()->route('slug')]) }}"
                                        method="POST" x-data="{ loading: false }" @submit="loading = true">
                                        @csrf
                                        <button type="submit" :disabled="loading"
                                            class="w-full bg-white hover:bg-slate-50 text-[#13416B] border border-[#13416B]/30 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 text-xs shadow-sm">
                                            <template x-if="!loading">
                                                <span class="flex items-center gap-2"><i class="fas fa-sync-alt"></i>
                                                    Terapkan Nama Baru</span>
                                            </template>
                                            <template x-if="loading">
                                                <span class="flex items-center gap-2"><i
                                                        class="fas fa-spinner fa-spin"></i> Memperbarui...</span>
                                            </template>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- STATE 2: SELESAI, TAPI BELUM DITERBITKAN --}}
                            <div class="space-y-5">
                                <div class="bg-[#13416B]/5 border border-[#13416B]/20 rounded-xl p-5 text-center">
                                    <div
                                        class="w-14 h-14 shrink-0 bg-white text-[#13416B] rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-2xl border border-[#13416B]/20">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <p class="font-bold text-[#13416B] text-sm">Anda Memenuhi Syarat!</p>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                        Seluruh materi dan evaluasi telah diselesaikan dengan baik. Sistem siap
                                        menerbitkan sertifikat kelulusan atas nama Anda.
                                    </p>
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                                    <i class="fas fa-info-circle text-amber-500 mt-0.5 text-lg shrink-0"></i>
                                    <div>
                                        <p class="text-[11px] text-amber-800 leading-relaxed font-medium">
                                            Nama pada sertifikat akan menyesuaikan dengan data profil Anda saat ini.
                                        </p>
                                        <a href="http://localhost:8000/user/profile" target="_blank"
                                            class="inline-flex items-center gap-1 mt-2 text-[11px] font-bold text-amber-700 hover:text-amber-900 border-b border-amber-300 hover:border-amber-700 transition-colors pb-0.5">
                                            <i class="fas fa-user-edit"></i> Periksa / Ubah Nama
                                        </a>
                                    </div>
                                </div>

                                <form
                                    action="{{ route('user.course.my-course.generate-certificate', ['slug' => request()->route('slug')]) }}"
                                    method="POST" x-data="{ loading: false }" @submit="loading = true">
                                    @csrf
                                    <button type="submit" :disabled="loading"
                                        class="w-full bg-[#13416B] hover:bg-[#0f3354] text-white py-3 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-sm disabled:opacity-70 disabled:cursor-not-allowed">
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
                            </div>
                        @endif
                    @else
                        {{-- STATE 3: BELUM SELESAI (TERKUNCI) --}}
                        <div class="py-6 flex flex-col items-center justify-center text-center px-2">
                            <div
                                class="w-16 h-16 shrink-0 bg-slate-50 border border-slate-200 text-slate-300 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-lock text-2xl"></i>
                            </div>
                            <p class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-800 mb-2">
                                <i class="fas fa-lock text-xs text-slate-400" aria-hidden="true"></i>
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

                {{-- KOTAK INFO PANDUAN (Versi Desktop: Hanya Tampil di Layar Besar) --}}
                @if (!$isFullyCompleted)
                    <!-- PERBAIKAN: rounded-none border-x-0 di mobile (meski disembunyikan di mobile, tetap dikonsistensikan kelasnya) -->
                    <div
                        class="hidden lg:flex bg-slate-50 border-y sm:border border-x-0 sm:border-x border-slate-200 rounded-none sm:rounded-md p-4 items-start gap-3 shadow-sm mx-0 sm:mx-auto">
                        <i class="fas fa-info-circle text-[#13416B]/60 mt-0.5 text-base shrink-0"></i>
                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                            Materi dipelajari berurutan. Buka kunci modul berikutnya dengan menekan <strong
                                class="text-slate-800">Tandai Selesai</strong> di setiap materi dan lulus <strong
                                class="text-slate-800">Evaluasi</strong>.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-dashboard::layouts.dashboard>
