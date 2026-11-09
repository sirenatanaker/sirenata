<x-dashboard::layouts.dashboard
    title="{{ ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date)) ? 'Hari ' : 'Modul ' }}{{ $sectionIndex + 1 }}: {{ data_get($section, 'name') }} | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    @php
        $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
        $courseSlug = $slug ?? data_get($course, 'slug');
        $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
        $rawSectionName = data_get($section, 'name', '');
        $cleanSectionName = preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $rawSectionName);
        $isBimtekEvent = (bool) ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date));
        $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
        $adminDriveLink = $activeBimtek?->submission_drive_link ?? null;
        $defaultPrefix = $isBimtekEvent ? 'Hari ' : 'Modul ';
        $sectionName = !empty(trim($cleanSectionName)) ? trim($cleanSectionName) : ($rawSectionName ?: $defaultPrefix . ($sectionIndex + 1));
        $sectionContents = data_get($section, 'section_contents', []) ?: data_get($section, 'contents', []);
        $totalCount = count($sectionContents);
        $completedCount = data_get($section, 'completed_count', 0);
        $postTestBab = $postTestBab ?? (data_get($section, 'post_test_bab') ?: \Modules\LMS\Models\PostTest::where('course_section_id', $sectionId)->first());
        $isPostTestBabCompleted = data_get($section, 'is_post_test_bab_completed', false);
        $isContentCompleted = data_get($section, 'is_content_completed', false);
        $isSectionCompleted = data_get($section, 'is_section_completed', false);
        $canReviewAll = data_get($course, 'can_review_all', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);

        $dayDate = null;
        try {
            if ($activeBimtek && $activeBimtek->start_date) {
                $dayDate = \Carbon\Carbon::parse($activeBimtek->start_date)->addDays($sectionIndex);
            }
        } catch (\Exception $e) {}
    @endphp

    <div class="px-0 py-2 sm:p-6 max-w-full mx-auto scroll-smooth">

        {{-- Breadcrumb Navigation (Adaptif: Bimtek vs Modul Mandiri) --}}
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
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
                        <a href="{{ route('user.course.my-course.detail', $courseSlug) }}"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors truncate max-w-[160px] sm:max-w-xs"
                            title="{{ $courseName }}">
                            {{ $courseName }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-[10px]"></i>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug truncate max-w-[200px] sm:max-w-none">
                            {{ $isBimtekEvent ? 'Hari ke-' . ($sectionIndex + 1) : 'Modul ' . ($sectionIndex + 1) }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Card Agenda Utama (Membungkus Header dan Seluruh Bagian Agenda) --}}
        <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 lg:p-7 transition-all">
            
            {{-- Header Agenda Modul / Hari --}}
            <div class="flex items-start gap-3.5 sm:gap-4 min-w-0 pb-5 sm:pb-6 border-b border-slate-100">
                {{-- Badge Hari / Modul seperti Kalender --}}
                <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-md bg-[#13416B] text-white border border-[#13416B] border-r-2 border-b-2 border-r-[#092035] border-b-[#092035] shadow-2xs shrink-0">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-300 leading-none">
                        {{ $isBimtekEvent ? 'HARI' : 'MODUL' }}
                    </span>
                    <span class="text-base sm:text-lg font-black leading-tight mt-0.5">{{ $sectionIndex + 1 }}</span>
                </div>

                <div class="space-y-1 min-w-0 flex-1">
                    <div>
                        <span class="text-xs font-bold text-[#13416B]">
                            {{ $isBimtekEvent ? 'Agenda Hari ke-' . ($sectionIndex + 1) : 'Modul Pembelajaran ' . ($sectionIndex + 1) }}
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-slate-800 leading-tight break-words">
                        {{ $sectionName }}
                    </h1>
                </div>
            </div>

            @php
                // Perhitungan status dan progres aktivitas pada halaman ini
                $completedContentsCount = collect($sectionContents)->filter(fn($c) => (bool)data_get($c, 'is_completed', false))->count();
                $isMateriDone = $totalCount > 0 ? ($completedContentsCount >= $totalCount) : (bool)$isContentCompleted;
                $isZoomDone = (bool)$isZoomAttended;
                $isBuktiDone = (bool)($zoomProof && ($zoomProof->file_path || $zoomProof->notes));

                $currentEvalType = match ($postTestBab?->type) {
                    'essay'       => 'uraian',
                    'file_upload' => 'upload',
                    default       => ($sectionIndex % 2 === 0 ? 'uraian' : 'upload'),
                };
                $isAssignmentDone = (bool) ($evalResult && ($evalResult->file_path || $evalResult->status === 'submitted' || $evalResult->completed_at));
                $isEssayDone = (bool) ($evalResult && ($evalResult->answers || $evalResult->status === 'submitted' || $evalResult->completed_at));

                $isEvaluasiDone = (bool) ($isPostTestBabCompleted || ($evalResult && $evalResult->completed_at));

                $now = now();
                $zoomStart = $activeBimtek?->zoom_scheduled_at ?? $activeBimtek?->start_date;
                $zoomEnd = $activeBimtek?->zoom_end_at;
                $zoomScheduleFormatted = $activeBimtek?->zoom_schedule_formatted ?? null;

                $isPresensiOpen = true;
                $presensiLockReason = null;
                if ($zoomStart && $now->lessThan($zoomStart)) {
                    $isPresensiOpen = false;
                    $presensiLockReason = 'Presensi dibuka saat jadwal sesi tatap muka (' . ($zoomScheduleFormatted ?? $zoomStart->translatedFormat('d M Y, H:i') . ' WIB') . ')';
                } elseif ($zoomEnd && $now->greaterThan($zoomEnd)) {
                    $isPresensiOpen = false;
                    $presensiLockReason = 'Waktu presensi tatap muka telah berakhir pada ' . $zoomEnd->translatedFormat('d M Y, H:i') . ' WIB';
                }

                $totalActivities = 2 + ($hasZoom ? 2 : 0);
                $completedActivities = ($isMateriDone ? 1 : 0)
                    + ($hasZoom ? (($isZoomDone ? 1 : 0) + ($isBuktiDone ? 1 : 0)) : 0)
                    + ($isEvaluasiDone ? 1 : 0);
                $progressPercent = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;
            @endphp

            {{-- Layout Kiri Kanan: Konten Aktivitas (Kiri) & On This Page (Kanan) --}}
            <div class="mt-5 sm:mt-6 flex flex-col lg:flex-row items-start gap-6 lg:gap-7">

                {{-- Kolom Kiri: Seluruh Card Konten Agenda --}}
                <div class="flex-1 min-w-0 space-y-5 sm:space-y-6 w-full">

                    {{-- 1. CARD PEMBELAJARAN MANDIRI (DILENGKAPI TIMELINE BERUPA ANGKA) --}}
                    <div id="pembelajaran-mandiri" class="scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200/80">
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight">Pembelajaran Mandiri</h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pelajari seluruh materi secara mandiri dan bertahap hingga selesai.</p>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-md shadow-2xs">
                            {{ $totalCount }} Materi
                        </span>
                    </div>

                    <div class="relative space-y-3.5 before:absolute before:left-3.5 sm:before:left-4 before:top-4 before:bottom-4 before:w-0.5 before:bg-slate-200">
                        @php $isPreviousContentDone = true; @endphp
                        @forelse ($sectionContents as $content)
                            @php
                                $contentId = data_get($content, 'id');
                                $contentName = data_get($content, 'name', 'Materi Tanpa Judul');
                                $isContentItemCompleted = data_get($content, 'is_completed', false);
                                $isContentLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isPreviousContentDone && !$isContentItemCompleted));
                            @endphp

                            <div class="relative flex items-center gap-3 sm:gap-4">
                                {{-- Timeline Node Badge Berupa Angka --}}
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs sm:text-sm font-black shrink-0 z-10 transition-all select-none {{ $isContentItemCompleted ? 'bg-emerald-600 text-white shadow-xs ring-4 ring-slate-50' : ($isContentLocked ? 'bg-slate-100 text-slate-400 border border-slate-300 ring-4 ring-slate-50' : 'bg-[#13416B] text-white shadow-xs ring-4 ring-slate-50') }}">
                                    {{ $loop->iteration }}
                                </div>

                                {{-- Card Konten Materi --}}
                                <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs transition-all gap-3 sm:gap-4">
                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                        <p class="font-bold text-slate-800 text-sm sm:text-[15px] leading-snug break-words line-clamp-2" title="{{ $contentName }}">
                                            {{ $contentName }}
                                        </p>
                                        @if ($isContentItemCompleted)
                                            <span class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded shrink-0">
                                                <i class="fas fa-check text-[9px]"></i> Selesai
                                            </span>
                                        @endif
                                    </div>

                                    <div class="shrink-0 self-start sm:self-center w-full sm:w-auto">
                                        @if ($isContentLocked)
                                            <button type="button" disabled
                                                title="Pelajari materi sebelumnya terlebih dahulu"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-semibold cursor-not-allowed select-none">
                                                <span>Terkunci</span>
                                            </button>
                                        @else
                                            <a href="{{ route('user.course.content.show', ['slug' => $courseSlug, 'content' => $contentId]) }}"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 {{ $isContentItemCompleted ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200' : 'bg-[#13416B] text-white hover:bg-[#0f3354] shadow-2xs' }} rounded-md text-xs font-bold transition-all text-center">
                                                <span>{{ $isContentItemCompleted ? 'Pelajari Lagi' : 'Mulai Belajar' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php
                                $isPreviousContentDone = $isContentItemCompleted;
                            @endphp
                        @empty
                            <div class="p-6 text-center text-slate-400 bg-white rounded-md border border-slate-200/60">
                                <i class="fas fa-folder-open mb-2 text-slate-300 text-2xl block"></i>
                                <p class="text-sm font-medium">Materi pembelajaran sedang disiapkan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 2. CARD SESI LIVE ZOOM --}}
                @if ($hasZoom)
                    @php
                        $now = now();
                        $zoomStart = $activeBimtek->zoom_scheduled_at ?? $activeBimtek->start_date;
                        $zoomEnd = $activeBimtek->zoom_end_at;
                        $zoomScheduleFormatted = $activeBimtek->zoom_schedule_formatted ?? null;

                        $isPresensiOpen = true;
                        $presensiLockReason = null;

                        if ($zoomStart && $now->lessThan($zoomStart)) {
                            $isPresensiOpen = false;
                            $presensiLockReason = 'Presensi dibuka saat jadwal sesi tatap muka (' . ($zoomScheduleFormatted ?? $zoomStart->translatedFormat('d M Y, H:i') . ' WIB') . ')';
                        } elseif ($zoomEnd && $now->greaterThan($zoomEnd)) {
                            $isPresensiOpen = false;
                            $presensiLockReason = 'Waktu presensi tatap muka telah berakhir pada ' . $zoomEnd->translatedFormat('d M Y, H:i') . ' WIB';
                        }
                    @endphp
                    <div id="sesi-zoom"
                        class="scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-3 flex-1 min-w-0">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-md bg-[#1E62D0] text-white flex items-center justify-center shrink-0 text-xl sm:text-2xl shadow-xs">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div class="space-y-1 min-w-0">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">Sesi Tatap Muka Online</h3>
                                        @if ($isZoomAttended)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                                <i class="fas fa-check-circle"></i> Presensi Kehadiran Terverifikasi
                                            </span>
                                        @elseif ($isPresensiOpen)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-bold bg-[#0B5CFF] text-white shadow-xs">
                                                <i class="fas fa-door-open"></i> Presensi Dibuka
                                            </span>
                                        @elseif ($zoomEnd && $now->greaterThan($zoomEnd))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-bold bg-slate-600 text-white shadow-xs">
                                                <i class="fas fa-times-circle"></i> Waktu Presensi Berakhir
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-bold bg-amber-500 text-white shadow-xs">
                                                <i class="fas fa-clock"></i> Presensi Belum Dibuka
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                    @if ($isZoomAttended)
                                        Kehadiran Anda pada sesi tatap muka online telah terverifikasi.
                                    @elseif ($isPresensiOpen)
                                        Sesi tatap muka daring sedang dibuka. Silakan masuk ke ruang pertemuan dan klik tombol Konfirmasi Kehadiran di bawah.
                                    @elseif ($zoomEnd && $now->greaterThan($zoomEnd))
                                        Sesi tatap muka daring dan batas waktu konfirmasi kehadiran telah berakhir.
                                    @else
                                        Bergabunglah ke ruang pertemuan daring bersama pemateri sesuai jadwal. Tombol konfirmasi kehadiran akan aktif saat jam presensi dibuka oleh panitia.
                                    @endif
                                </p>

                                {{-- Info Sesi Tatap Muka --}}
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 pt-1">
                                    @if ($activeBimtek->zoom_scheduled_at)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-semibold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-calendar-alt text-[#1E62D0]"></i>
                                            {{ $zoomScheduleFormatted ?? $activeBimtek->zoom_scheduled_at->translatedFormat('d F Y, H:i') . ' WIB' }}
                                        </span>
                                    @endif
                                    @if ($activeBimtek->zoom_meeting_id)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-hashtag text-slate-400"></i> ID: {{ $activeBimtek->zoom_meeting_id }}
                                        </span>
                                    @endif
                                    @if ($activeBimtek->zoom_passcode)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-key text-slate-400"></i> Passcode: {{ $activeBimtek->zoom_passcode }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Tombol Aksi Tatap Muka --}}
                            <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-2.5 self-start md:self-center w-full md:w-auto">
                                <a href="{{ route('user.course.my-course.join-zoom', $courseSlug) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-[#13416B] text-white hover:bg-[#0f3354] transition-all shadow-sm">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                    <span>Masuk Tatap Muka</span>
                                </a>

                                @if (!$isZoomAttended)
                                    @if ($isPresensiOpen)
                                        <form action="{{ route('user.course.my-course.attend-zoom', $courseSlug) }}" method="POST" class="inline w-full sm:w-auto md:w-full">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm cursor-pointer">
                                                <i class="fas fa-clipboard-check text-xs"></i>
                                                <span>Konfirmasi Kehadiran</span>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                            title="{{ $presensiLockReason }}"
                                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md font-bold text-xs sm:text-sm border border-slate-300 bg-slate-100 text-slate-400 cursor-not-allowed select-none">
                                            <i class="fas fa-lock text-xs"></i>
                                            <span>{{ $zoomEnd && $now->greaterThan($zoomEnd) ? 'Presensi Berakhir' : 'Presensi Belum Dibuka' }}</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. CARD BUKTI PENDUKUNG PRESENSI (DIBAWAH CARD ZOOM) --}}
                    <div id="bukti-presensi" class="scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-200/80">
                            <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 text-lg shadow-xs">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">Bukti Pendukung Presensi</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Google Drive Penyelenggara & Unggah Tangkapan Layar</p>
                            </div>
                        </div>

                        <p class="text-sm sm:text-[15px] text-slate-700 leading-relaxed">
                            Unggah tangkapan layar sesi Zoom ke Google Drive panitia atau lampirkan berkas foto langsung di sini sebagai bukti kehadiran.
                        </p>

                        {{-- Banner Folder Google Drive Presensi dari Penyelenggara --}}
                        @if ($adminDriveLink)
                            <div class="p-4 rounded-md bg-white border border-blue-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-md bg-blue-50 text-[#0B5CFF] border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                        <i class="fab fa-google-drive text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-slate-800">Folder Google Drive Presensi Penyelenggara</p>
                                        <p class="text-xs text-slate-600 mt-0.5">Kumpulkan bukti foto kehadiran sesi Zoom Anda ke folder Google Drive panitia berikut.</p>
                                    </div>
                                </div>

                                <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 transition-all shadow-2xs shrink-0 whitespace-nowrap">
                                    <i class="fab fa-google-drive"></i>
                                    <span>Buka Drive Presensi Panitia</span>
                                    <i class="fas fa-external-link-alt text-[10px] ml-0.5"></i>
                                </a>
                            </div>
                        @endif

                        {{-- Form & Status Bukti Presensi --}}
                        @if ($zoomProof && ($zoomProof->file_path || $zoomProof->notes) && (!isset($errors) || !$errors->any()))
                            <div x-data="{ isEditingPhoto: false }" class="space-y-3">
                                <div x-show="!isEditingPhoto" class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-md bg-white border border-emerald-200/90 shadow-2xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                                            <i class="fas fa-check-circle text-base"></i>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-xs sm:text-sm font-bold text-slate-800">Bukti Presensi Telah Dikonfirmasi</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $zoomProof->submitted_at ? $zoomProof->submitted_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Tersimpan' }}
                                                @if ($zoomProof->notes)
                                                    • <span class="italic text-slate-600">"{{ $zoomProof->notes }}"</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($zoomProof->file_path)
                                            <a href="{{ $zoomProof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold text-[#13416B] bg-slate-50 hover:bg-slate-100 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat Foto</span>
                                        </a>
                                    @endif
                                    <button type="button" @click="isEditingPhoto = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200 cursor-pointer">
                                        <i class="fas fa-redo text-xs"></i>
                                        <span>Perbarui Bukti</span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="isEditingPhoto" class="pt-1">
                                <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="space-y-3 p-4 rounded-md bg-white border border-slate-200 shadow-2xs">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                Unggah Foto / Screenshot (Opsional)
                                            </label>
                                            <input type="file"
                                                name="proof_file"
                                                accept=".jpg,.jpeg,.png,.webp"
                                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                Catatan Presensi / Nama Berkas di Drive Panitia
                                            </label>
                                            <input type="text"
                                                name="notes"
                                                value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                                placeholder="Contoh: Bukti hadir diunggah ke Drive panitia nama Budi_Presensi.png"
                                                class="w-full px-3 py-2 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                                        <button type="button" @click="isEditingPhoto = false"
                                            class="px-3.5 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200 cursor-pointer">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs cursor-pointer">
                                            <i class="fas fa-save text-xs"></i>
                                            <span>Simpan Bukti</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="space-y-3 p-4 rounded-md bg-white border border-slate-200 shadow-2xs">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Unggah Foto / Screenshot (Opsional)
                                    </label>
                                    <input type="file"
                                        name="proof_file"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Catatan Presensi / Nama Berkas di Drive Panitia
                                    </label>
                                    <input type="text"
                                        name="notes"
                                        value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                        placeholder="Contoh: Bukti hadir diunggah ke Drive panitia nama Budi_Presensi.png"
                                        class="w-full px-3 py-2 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-1 border-t border-slate-100">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs cursor-pointer">
                                    <i class="fas fa-check text-xs"></i>
                                    <span>Konfirmasi Bukti Presensi</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
                @endif

                {{-- 4. CARD EVALUASI HARIAN / MODUL (MENDUKUNG PILGAN, URAIAN, & UPLOAD FILE SOAL-JAWABAN) --}}
                @php
                    $postTestQuestionsCount = $postTestBab ? data_get($postTestBab, 'questions', collect())->count() : 0;
                    $hasPostTestQuestions = $postTestQuestionsCount > 0;
                    $isPostTestBabLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isContentCompleted && !$isPostTestBabCompleted));
                    $postTestTitle = data_get($postTestBab, 'title', ($isBimtekEvent ? 'Evaluasi Hari ke-' : 'Evaluasi Modul ') . ($sectionIndex + 1) . ': ' . $sectionName);
                @endphp

                <div id="evaluasi-modul" class="scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 shadow-2xs transition-all relative overflow-hidden">
                    <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                        {{-- Kolom Kiri: Informasi Evaluasi & Aksi --}}
                        <div class="flex-1 min-w-0 sm:py-1 space-y-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-[#13416B]">
                                        {{ $isBimtekEvent ? 'Evaluasi & Penugasan Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }}
                                    </span>
                                    @if ($isEvaluasiDone)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                            <i class="fas fa-check-circle text-xs text-emerald-600"></i> Selesai
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 leading-snug max-w-full lg:max-w-[85%]">
                                    @if ($currentEvalType === 'uraian')
                                        {{ $isBimtekEvent ? 'Evaluasi Uraian Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Uraian Modul ' . ($sectionIndex + 1) }}: {{ $sectionName }}
                                    @elseif ($currentEvalType === 'upload')
                                        {{ $isBimtekEvent ? 'Penugasan Berkas Hari ke-' . ($sectionIndex + 1) : 'Penugasan Berkas Modul ' . ($sectionIndex + 1) }}: {{ $sectionName }}
                                    @else
                                        {{ $postTestTitle }}
                                    @endif
                                </h3>

                                <p class="text-sm sm:text-[15px] text-slate-700 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                    @if ($currentEvalType === 'uraian')
                                        Jawab pertanyaan studi kasus dan pemahaman konsep secara mendalam melalui lembar evaluasi uraian.
                                    @elseif ($currentEvalType === 'upload')
                                        Unduh berkas soal dari penyelenggara, kerjakan sesuai panduan, lalu unggah dokumen jawaban Anda di bawah ini.
                                    @else
                                        Ujian evaluasi materi pada {{ $isBimtekEvent ? 'hari ini' : 'modul ini' }} untuk menguji pemahaman dan capaian pembelajaran Anda.
                                    @endif
                                </p>

                                {{-- Badges Spesifikasi Sesuai Tipe Evaluasi --}}
                                <div class="mt-3 flex flex-wrap items-center gap-2.5 sm:gap-3">
                                    @if ($currentEvalType === 'uraian')
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-feather-alt text-xs text-white"></i>
                                            {{ $postTestQuestionsCount ?: 2 }} Soal Uraian / Esai
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-user-check text-xs text-white"></i>
                                            Penilaian Instruktur
                                        </span>
                                    @elseif ($currentEvalType === 'upload')
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-file-upload text-xs text-white"></i>
                                            Penugasan Berkas Mandiri
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-cloud-download-alt text-xs text-white"></i>
                                            Unduh Soal & Upload Jawaban
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-file-pdf text-xs text-white"></i>
                                            PDF / DOCX / ZIP (Maks. 10 MB)
                                        </span>
                                    @else
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-question-circle text-xs text-white"></i>
                                            {{ $postTestQuestionsCount }} Butir Pilihan Ganda
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-clock text-xs text-white"></i>
                                            {{ data_get($postTestBab, 'duration', 15) }} Menit
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-white bg-[#547996] px-3 py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-award text-xs text-white"></i>
                                            KKM: {{ data_get($postTestBab, 'passing_score', 0) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- TAMPILAN KHUSUS TIPE 3: UPLOAD FILE PENUGASAN BERKAS    --}}
                            {{-- (Admin Upload File Soal -> User Download & Upload Tugas) --}}
                            {{-- ======================================================== --}}
                            @if ($currentEvalType === 'upload')
                                @php
                                    $adminFileName = data_get($postTestBab, 'admin_file_name', 'Soal_Tugas_Hari_' . ($sectionIndex + 1) . '.pdf');
                                    $adminFileSize = data_get($postTestBab, 'admin_file_size', '2.4 MB');
                                    $adminFileUrl = data_get($postTestBab, 'admin_file_url') ?: asset('storage/' . data_get($postTestBab, 'admin_file_path', 'assignments/soal/Soal_Tugas_Studi_Kasus.pdf'));
                                    $instructions = data_get($postTestBab, 'instructions', 'Disediakan oleh Panitia Penyelenggara sebagai panduan pengerjaan studi kasus dan lembar kerja modul.');
                                @endphp
                                <div class="space-y-3.5">
                                    {{-- 1. Berkas Soal dari Admin/Penyelenggara --}}
                                    <div class="p-4 rounded-md bg-blue-50/70 border border-blue-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-md bg-white text-red-500 border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                                <i class="fas fa-file-pdf text-2xl"></i>
                                            </div>
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 bg-blue-100 px-2 py-0.5 rounded">
                                                        Berkas Soal dari Admin
                                                    </span>
                                                    <span class="text-[11px] text-slate-500 font-medium">{{ $adminFileSize }} • PDF Document</span>
                                                </div>
                                                <p class="text-xs sm:text-sm font-bold text-slate-800">
                                                    {{ $adminFileName }}
                                                </p>
                                                <p class="text-xs text-slate-600">
                                                    {{ $instructions }}
                                                </p>
                                            </div>
                                        </div>

                                        <a href="{{ $adminFileUrl }}" download="{{ $adminFileName }}" target="_blank"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 transition-all shadow-2xs shrink-0 whitespace-nowrap cursor-pointer">
                                            <i class="fas fa-download text-xs"></i>
                                            <span>Unduh Berkas Soal</span>
                                        </a>
                                    </div>

                                    {{-- 2. Formulir / Status Penyerahan Berkas Jawaban dari User --}}
                                    <div x-data="{ isReuploading: false }">
                                        @if ($isAssignmentDone && (!isset($errors) || !$errors->any()))
                                            <div x-show="!isReuploading" class="p-4 rounded-md bg-white border border-emerald-200/90 shadow-2xs space-y-3">
                                                <div class="flex flex-wrap items-center justify-between gap-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                                                            <i class="fas fa-check-circle text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <p class="text-xs sm:text-sm font-bold text-slate-800">Tugas Berhasil Dikumpulkan</p>
                                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Diserahkan</span>
                                                            </div>
                                                            <p class="text-xs text-slate-600 mt-0.5">
                                                                Berkas: <span class="font-semibold text-slate-800">{{ $evalResult?->file_name ?? 'Dokumen_Tugas.pdf' }}</span>
                                                                • {{ $evalResult?->completed_at ? $evalResult->completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Hari ini' }}
                                                            </p>
                                                            @if (!empty($evalResult?->notes))
                                                                <p class="text-xs text-slate-500 italic mt-0.5">"{{ $evalResult->notes }}"</p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        @if (!empty($evalResult?->file_url))
                                                            <a href="{{ $evalResult->file_url }}" target="_blank"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold text-[#13416B] bg-slate-50 hover:bg-slate-100 border border-slate-200 shadow-2xs">
                                                                <i class="fas fa-eye text-xs"></i>
                                                                <span>Lihat Berkas Saya</span>
                                                            </a>
                                                        @endif
                                                        <button type="button" @click="isReuploading = true"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200 cursor-pointer">
                                                            <i class="fas fa-redo text-xs"></i>
                                                            <span>Unggah Ulang (Revisi)</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div x-show="{{ ($isAssignmentDone && (!isset($errors) || !$errors->any())) ? 'isReuploading' : 'true' }}"
                                            class="{{ ($isAssignmentDone && (!isset($errors) || !$errors->any())) ? 'pt-2' : '' }}">
                                            <form action="{{ route('user.course.section.submit-assignment', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                                method="POST"
                                                enctype="multipart/form-data"
                                                class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs space-y-3">
                                                @csrf
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                            Unggah Berkas Jawaban / Laporan Tugas <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="file"
                                                            name="assignment_file"
                                                            required
                                                            accept=".pdf,.docx,.doc,.zip,.rar"
                                                            class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                                        <span class="block text-[11px] text-slate-400 mt-1">Format: PDF, DOCX, ZIP (Maks. 10 MB)</span>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                            Catatan Penyerahan Tugas (Opsional)
                                                        </label>
                                                        <input type="text"
                                                            name="notes"
                                                            value="{{ old('notes', $evalResult?->notes ?? '') }}"
                                                            placeholder="Contoh: Berkas lembar kerja tugas studi kasus kelompok/peserta"
                                                            class="w-full px-3 py-2 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 flex-wrap gap-2">
                                                    <span class="text-xs text-slate-500">
                                                        <i class="fas fa-info-circle text-blue-500 mr-1"></i> Pastikan dokumen sesuai instruksi soal admin sebelum mengirim.
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        @if ($isAssignmentDone)
                                                            <button type="button" @click="isReuploading = false"
                                                                class="px-3.5 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200 cursor-pointer">
                                                                Batal
                                                            </button>
                                                        @endif
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs cursor-pointer">
                                                            <i class="fas fa-paper-plane text-xs"></i>
                                                            <span>Kirim Berkas Jawaban</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Folder Drive Tambahan dari Penyelenggara (Jika Dikonfigurasi) --}}
                            @if ($adminDriveLink && $currentEvalType !== 'upload')
                                <div class="p-3.5 rounded-md bg-white border border-blue-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-md bg-blue-50 text-[#0B5CFF] border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                            <i class="fab fa-google-drive text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-slate-800">Folder Google Drive Tugas dari Penyelenggara</p>
                                            <p class="text-xs text-slate-600 mt-0.5">Akses folder Drive panitia untuk unduh lembar kerja atau kumpulkan berkas tambahan.</p>
                                        </div>
                                    </div>

                                    <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 transition-all shadow-2xs shrink-0 whitespace-nowrap">
                                        <i class="fab fa-google-drive"></i>
                                        <span>Buka Folder Drive</span>
                                        <i class="fas fa-external-link-alt text-[10px] ml-0.5"></i>
                                    </a>
                                </div>
                            @endif

                            {{-- Tombol Aksi untuk Membuka Lembar Evaluasi / Ujian (Konsep Sama untuk Uraian & Pilgan) --}}
                            @if ($postTestBab && $currentEvalType === 'uraian')
                                <div class="pt-1 flex flex-wrap items-center gap-3">
                                    @if ($isEvaluasiDone)
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-xs">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat Lembar Jawaban Uraian</span>
                                        </a>
                                    @elseif ($isPostTestBabLocked)
                                        <button type="button" disabled
                                            title="Tuntaskan semua materi pada modul ini terlebih dahulu"
                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                            <i class="fas fa-lock text-xs"></i>
                                            <span>Evaluasi Terkunci</span>
                                        </button>
                                    @else
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                            <i class="fas fa-play text-xs"></i>
                                            <span>Mulai Kerjakan Uraian</span>
                                        </a>
                                    @endif
                                </div>
                            @elseif ($postTestBab && $currentEvalType === 'pilgan')
                                <div class="pt-1 flex flex-wrap items-center gap-3">
                                    @if ($isEvaluasiDone)
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-xs">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat Hasil Evaluasi</span>
                                        </a>
                                    @elseif ($isPostTestBabLocked)
                                        <button type="button" disabled
                                            title="Tuntaskan semua materi pada modul ini terlebih dahulu"
                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                            <i class="fas fa-lock text-xs"></i>
                                            <span>Evaluasi Terkunci</span>
                                        </button>
                                    @else
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                            <i class="fas fa-play text-xs"></i>
                                            <span>Mulai Evaluasi</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Kanan: Ilustrasi Gambar nempel bawah --}}
                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-3.5 lg:-mb-4">
                            <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                alt="Ilustrasi Evaluasi Modul"
                                class="sm:h-36 md:h-40 lg:h-44 w-auto object-contain object-bottom select-none pointer-events-none" />
                        </div>
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan: Widget "On This Page" / Daftar Aktivitas --}}
            <div class="w-full lg:w-72 xl:w-80 shrink-0">
                <div class="sticky top-6 rounded-md border border-slate-200 bg-slate-50/70 p-4 sm:p-5 shadow-2xs">
                    {{-- Header Widget --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/90">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-list-check text-[#13416B] text-xs"></i>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">On This Page</span>
                        </div>
                        <span class="text-[11px] font-bold {{ $progressPercent === 100 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-600 bg-white border-slate-200' }} border px-2 py-0.5 rounded shadow-2xs">
                            {{ $completedActivities }}/{{ $totalActivities }} Selesai
                        </span>
                    </div>

                    {{-- Bar Progres Singkat --}}
                    <div class="py-3 border-b border-slate-200/70">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 mb-1.5">
                            <span>Progres Agenda</span>
                            <span class="font-bold {{ $progressPercent === 100 ? 'text-emerald-600' : 'text-[#13416B]' }}">{{ $progressPercent }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full {{ $progressPercent === 100 ? 'bg-emerald-600' : 'bg-[#13416B]' }} transition-all duration-300 rounded-full"
                                style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>

                    {{-- List Aktivitas On This Page --}}
                    <nav class="space-y-1.5 pt-3">
                        {{-- 1. Pembelajaran Mandiri --}}
                        <a href="#pembelajaran-mandiri"
                            class="group flex items-start gap-2.5 p-2 rounded-md hover:bg-white hover:shadow-2xs transition-all border border-transparent hover:border-slate-200">
                            <div class="mt-0.5 shrink-0">
                                @if ($isMateriDone)
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                        1
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                    Pembelajaran Mandiri
                                </p>
                                <p class="text-[11px] {{ $isMateriDone ? 'text-emerald-600 font-semibold' : 'text-slate-500' }} mt-0.5">
                                    {{ $completedContentsCount }}/{{ $totalCount }} materi selesai
                                </p>
                            </div>
                        </a>

                        {{-- 2. Sesi Tatap Muka Online (Jika ada Zoom) --}}
                        @if ($hasZoom)
                            <a href="#sesi-zoom"
                                class="group flex items-start gap-2.5 p-2 rounded-md hover:bg-white hover:shadow-2xs transition-all border border-transparent hover:border-slate-200">
                                <div class="mt-0.5 shrink-0">
                                    @if ($isZoomDone)
                                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                            2
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                        Sesi Tatap Muka Online
                                    </p>
                                    <p class="text-[11px] {{ $isZoomDone ? 'text-emerald-600 font-semibold' : ($isPresensiOpen ? 'text-blue-600 font-semibold' : 'text-slate-500') }} mt-0.5">
                                        {{ $isZoomDone ? 'Presensi terverifikasi' : ($isPresensiOpen ? 'Presensi dibuka' : ($zoomEnd && $now->greaterThan($zoomEnd) ? 'Presensi berakhir' : 'Belum dibuka')) }}
                                    </p>
                                </div>
                            </a>

                            {{-- 3. Bukti Pendukung Presensi --}}
                            <a href="#bukti-presensi"
                                class="group flex items-start gap-2.5 p-2 rounded-md hover:bg-white hover:shadow-2xs transition-all border border-transparent hover:border-slate-200">
                                <div class="mt-0.5 shrink-0">
                                    @if ($isBuktiDone)
                                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                            3
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                        Bukti Pendukung Presensi
                                    </p>
                                    <p class="text-[11px] {{ $isBuktiDone ? 'text-emerald-600 font-semibold' : 'text-slate-500' }} mt-0.5">
                                        {{ $isBuktiDone ? 'Bukti tersimpan' : 'Belum unggah' }}
                                    </p>
                                </div>
                            </a>
                        @endif

                        {{-- 4. Evaluasi Harian / Modul --}}
                        <a href="#evaluasi-modul"
                            class="group flex items-start gap-2.5 p-2 rounded-md hover:bg-white hover:shadow-2xs transition-all border border-transparent hover:border-slate-200">
                            <div class="mt-0.5 shrink-0">
                                @if ($isEvaluasiDone)
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                        {{ $hasZoom ? 4 : 2 }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                    {{ $isBimtekEvent ? 'Evaluasi Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }} ({{ $currentEvalType === 'uraian' ? 'Uraian' : ($currentEvalType === 'upload' ? 'Penugasan Berkas' : 'Pilihan Ganda') }})
                                </p>
                                <p class="text-[11px] {{ $isEvaluasiDone ? 'text-emerald-600 font-semibold' : 'text-slate-500' }} mt-0.5">
                                    {{ $isEvaluasiDone ? 'Selesai dikerjakan' : 'Belum selesai' }}
                                </p>
                            </div>
                        </a>
                    </nav>
                </div>
            </div>

        </div>
        </div>

    </div>

</x-dashboard::layouts.dashboard>