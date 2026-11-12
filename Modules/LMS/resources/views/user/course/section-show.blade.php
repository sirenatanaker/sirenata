<x-dashboard::layouts.dashboard
    title="{{ ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date)) ? 'Hari ' : 'Modul ' }}{{ $sectionIndex + 1 }}: {{ data_get($section, 'name') }} | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    @php
        $courseName = $courseName ?? data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
        $courseSlug = $slug ?? data_get($course, 'slug');
        $canReviewAll = data_get($course, 'can_review_all', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 pb-5 sm:pb-6 border-b border-slate-100">
                <div class="flex items-start gap-3.5 sm:gap-4 min-w-0 flex-1">
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

                {{-- Tombol Panduan di Kanan Header --}}
                <div class="shrink-0 self-start sm:self-center">
                    <button type="button" onclick="window.startSectionTour && window.startSectionTour()"
                        class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-[#13416B] border border-slate-200 rounded-md transition-all shadow-2xs cursor-pointer shrink-0">
                        <i class="fas fa-route text-[#13416B] text-xs sm:text-sm"></i>
                        <span>Panduan</span>
                    </button>
                </div>
            </div>

            {{-- Layout Kiri Kanan: Konten Aktivitas (Kiri) & On This Page (Kanan) --}}
            <div class="mt-5 sm:mt-6 flex flex-col lg:flex-row items-start gap-6 lg:gap-7">

                {{-- Kolom Kiri: Seluruh Card Konten Agenda dengan Timeline Alur Kegiatan --}}
                <div class="flex-1 min-w-0 w-full">

                    {{-- 1. TAHAP: PEMBELAJARAN MANDIRI --}}
                    <div class="relative flex items-stretch gap-3.5 sm:gap-5 pb-6 sm:pb-8">
                        {{-- Node Timeline Tahap 1 & Garis Penghubung --}}
                        <div class="relative w-8 sm:w-10 shrink-0 flex flex-col items-center select-none">
                            @if ($isMateriDone)
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-xs ring-4 ring-white shrink-0" title="Tahap 1 Selesai">
                                    <i class="fas fa-check"></i>
                                </div>
                            @else
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#13416B] text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md ring-4 ring-blue-100 ring-offset-2 ring-offset-white shrink-0" title="Tahap 1">
                                    1
                                </div>
                            @endif
                            <div class="absolute top-4 sm:top-5 -bottom-8 sm:-bottom-10 left-1/2 -translate-x-1/2 w-0.5 {{ $isMateriDone ? 'bg-emerald-500' : 'bg-slate-200' }} z-0"></div>
                        </div>

                        {{-- Card Konten Tahap 1 --}}
                        <div id="pembelajaran-mandiri" data-tour="pembelajaran-mandiri" class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all">
                            <div class="pb-3.5 mb-4 border-b border-slate-200/80">
                                <h2 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 tracking-tight leading-tight">Pembelajaran Mandiri</h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pelajari seluruh materi secara mandiri dan bertahap hingga selesai.</p>
                            </div>

                            <div class="space-y-3">
                                @php $isPreviousContentDone = true; @endphp
                                @forelse ($sectionContents as $content)
                                    @php
                                        $contentId = data_get($content, 'id');
                                        $contentName = data_get($content, 'name', 'Materi Tanpa Judul');
                                        $isContentItemCompleted = data_get($content, 'is_completed', false);
                                        $isContentLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isPreviousContentDone && !$isContentItemCompleted));
                                    @endphp

                                    {{-- Card Konten Materi --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs transition-all gap-3 sm:gap-4">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            {{-- Badge Bulatan Nomor --}}
                                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 select-none {{ $isContentItemCompleted ? 'bg-emerald-600 text-white' : ($isContentLocked ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-[#13416B] text-white shadow-2xs') }}">
                                                {{ $loop->iteration }}
                                            </div>

                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                                <p class="font-bold text-slate-800 text-sm sm:text-[15px] leading-snug break-words line-clamp-2" title="{{ $contentName }}">
                                                    {{ $contentName }}
                                                </p>
                                                @if ($isContentItemCompleted)
                                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold shrink-0" title="Selesai">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                @endif
                                            </div>
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
                    </div>

                {{-- 2. CARD SESI LIVE ZOOM --}}
                @if ($hasZoom)
                    @php
                        $now = now();
                        $zoomStart = $activeBimtek->zoom_scheduled_at ?? $activeBimtek->start_date;
                        $zoomEnd = $activeBimtek->zoom_end_at;
                        $zoomScheduleFormatted = $activeBimtek->zoom_schedule_formatted ?? null;

                        $isZoomEnded = $zoomEnd ? $now->greaterThan($zoomEnd) : ($zoomStart ? $now->greaterThan($zoomStart->copy()->endOfDay()) : false);

                        $isPresensiOpen = true;
                        $presensiLockReason = null;

                        if ($zoomStart && $now->lessThan($zoomStart)) {
                            $isPresensiOpen = false;
                            $presensiLockReason = 'Presensi dibuka saat jadwal sesi tatap muka (' . ($zoomScheduleFormatted ?? $zoomStart->translatedFormat('d M Y, H:i') . ' WIB') . ')';
                        } elseif ($isZoomEnded) {
                            $isPresensiOpen = false;
                            $presensiLockReason = 'Waktu presensi tatap muka telah berakhir' . ($zoomEnd ? ' pada ' . $zoomEnd->translatedFormat('d M Y, H:i') . ' WIB' : '');
                        }
                    @endphp
                    <div class="relative flex items-stretch gap-3.5 sm:gap-5 pb-6 sm:pb-8">
                        {{-- Node Timeline Tahap 2 & Garis Penghubung --}}
                        <div class="relative w-8 sm:w-10 shrink-0 flex flex-col items-center select-none">
                            @if ($isZoomDone)
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-xs ring-4 ring-white shrink-0" title="Tahap 2 Selesai">
                                    <i class="fas fa-check"></i>
                                </div>
                            @elseif ($isMateriDone)
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#13416B] text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md ring-4 ring-blue-100 ring-offset-2 ring-offset-white shrink-0" title="Tahap 2">
                                    2
                                </div>
                            @else
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white text-slate-400 border-2 border-slate-300 flex items-center justify-center font-bold text-xs sm:text-sm shadow-2xs ring-4 ring-white shrink-0" title="Tahap 2">
                                    2
                                </div>
                            @endif
                            <div class="absolute top-4 sm:top-5 -bottom-8 sm:-bottom-10 left-1/2 -translate-x-1/2 w-0.5 {{ $isZoomDone ? 'bg-emerald-500' : 'bg-slate-200' }} z-0"></div>
                        </div>

                        {{-- Card Konten Tahap 2 --}}
                        <div id="sesi-zoom" data-tour="zoom"
                            class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all space-y-4">
                            <div>
                                <h2 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 tracking-tight leading-tight">
                                    Sesi Tatap Muka Online
                                </h2>

                                <p class="text-sm sm:text-[15px] text-slate-600 mt-1 leading-relaxed">
                                    @if ($isZoomAttended)
                                        Terima kasih, Anda telah mengisi presensi kehadiran sesi tatap muka online.
                                    @elseif ($isPresensiOpen)
                                        Silakan masuk ke ruang tatap muka, lalu klik tombol Konfirmasi Kehadiran setelah Anda bergabung.
                                    @elseif ($isZoomEnded)
                                        Sesi tatap muka online dan batas waktu konfirmasi kehadiran telah berakhir.
                                    @else
                                        Bergabunglah ke ruang tatap muka sesuai jadwal. Tombol konfirmasi akan aktif saat presensi dibuka oleh panitia.
                                    @endif
                                </p>
                            </div>

                            {{-- Info Sesi (Jadwal, Meeting ID, Passcode) --}}
                            @if ($activeBimtek->zoom_scheduled_at || $activeBimtek->zoom_meeting_id || $activeBimtek->zoom_passcode)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                                    @if ($activeBimtek->zoom_scheduled_at)
                                        <div class="flex items-center gap-2.5 p-2.5 rounded-md bg-white border border-slate-200 shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-slate-100 text-[#13416B] flex items-center justify-center shrink-0 text-sm">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jadwal Zoom</p>
                                                <p class="text-xs font-bold text-slate-800 truncate" title="{{ $zoomScheduleFormatted ?? $activeBimtek->zoom_scheduled_at->translatedFormat('d F Y, H:i') . ' WIB' }}">
                                                    {{ $zoomScheduleFormatted ?? $activeBimtek->zoom_scheduled_at->translatedFormat('d M Y, H:i') . ' WIB' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($activeBimtek->zoom_attendance_start_at || $activeBimtek->zoom_attendance_end_at)
                                        <div class="flex items-center gap-2.5 p-2.5 rounded-md bg-amber-50/70 border border-amber-200 shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 text-sm">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Waktu Presensi</p>
                                                <p class="text-xs font-bold text-amber-900 truncate" title="{{ $activeBimtek->attendance_schedule_formatted }}">
                                                    {{ $activeBimtek->attendance_schedule_formatted }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($activeBimtek->zoom_meeting_id)
                                        <div class="flex items-center gap-2.5 p-2.5 rounded-md bg-white border border-slate-200 shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-slate-100 text-[#13416B] flex items-center justify-center shrink-0 text-sm">
                                                <i class="fas fa-hashtag"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Meeting ID</p>
                                                <p class="text-xs font-mono font-bold text-slate-800 truncate">{{ $activeBimtek->zoom_meeting_id }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($activeBimtek->zoom_passcode)
                                        <div class="flex items-center gap-2.5 p-2.5 rounded-md bg-white border border-slate-200 shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-slate-100 text-[#13416B] flex items-center justify-center shrink-0 text-sm">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Passcode</p>
                                                <p class="text-xs font-mono font-bold text-slate-800 truncate">{{ $activeBimtek->zoom_passcode }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Tombol Aksi: Masuk Tatap Muka & Presensi Berdampingan --}}
                            <div class="flex flex-wrap items-center gap-2.5 pt-1">
                                {{-- 1. Tombol Masuk Tatap Muka --}}
                                @if ($isZoomEnded)
                                    <button type="button" disabled title="Jadwal sesi tatap muka telah berakhir"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm border border-slate-300 bg-slate-100 text-slate-400 cursor-not-allowed select-none">
                                        <i class="fas fa-video-slash text-xs"></i>
                                        <span>Sesi Tatap Muka Berakhir</span>
                                    </button>
                                @else
                                    <a href="{{ route('user.course.my-course.join-zoom', $courseSlug) }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                        <i class="fas fa-video text-xs"></i>
                                        <span>Masuk Tatap Muka</span>
                                        <i class="fas fa-external-link-alt text-[10px] opacity-75"></i>
                                    </a>
                                @endif

                                {{-- 2. Tombol Presensi Kehadiran --}}
                                @if ($isZoomAttended)
                                    <button type="button" disabled
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 text-white shadow-sm select-none cursor-default">
                                        <i class="fas fa-check-circle text-xs"></i>
                                        <span>Sudah Presensi</span>
                                    </button>
                                @elseif ($isPresensiOpen)
                                    <form action="{{ route('user.course.my-course.attend-zoom', $courseSlug) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer animate-pulse">
                                            <i class="fas fa-clipboard-check text-xs"></i>
                                            <span>Konfirmasi Kehadiran</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled title="{{ $presensiLockReason }}"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm border border-slate-300 bg-slate-100 text-slate-400 cursor-not-allowed select-none">
                                        <i class="fas fa-lock text-xs"></i>
                                        <span>{{ ($isPresensiEnded ?? $isZoomEnded) ? 'Presensi Berakhir' : 'Presensi Belum Dibuka' }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. CARD BUKTI PENDUKUNG PRESENSI (DIBAWAH CARD ZOOM) --}}
                    <div class="relative flex items-stretch gap-3.5 sm:gap-5 pb-6 sm:pb-8">
                        {{-- Node Timeline Tahap 3 & Garis Penghubung --}}
                        <div class="relative w-8 sm:w-10 shrink-0 flex flex-col items-center select-none">
                            @if ($isEvaluasiDone)
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-xs ring-4 ring-white shrink-0" title="Tahap 3 Selesai">
                                    <i class="fas fa-check"></i>
                                </div>
                            @elseif ($isZoomDone)
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#13416B] text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md ring-4 ring-blue-100 ring-offset-2 ring-offset-white shrink-0" title="Tahap 3">
                                    3
                                </div>
                            @else
                                <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white text-slate-400 border-2 border-slate-300 flex items-center justify-center font-bold text-xs sm:text-sm shadow-2xs ring-4 ring-white shrink-0" title="Tahap 3">
                                    3
                                </div>
                            @endif
                            <div class="absolute top-4 sm:top-5 -bottom-8 sm:-bottom-10 left-1/2 -translate-x-1/2 w-0.5 {{ ($isZoomDone && $isMateriDone) ? 'bg-emerald-500' : 'bg-slate-200' }} z-0"></div>
                        </div>

                        {{-- Card Konten Tahap 3 --}}
                        <div id="bukti-presensi" data-tour="bukti-presensi"
                            class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all space-y-4">
                            <div>
                                <h2 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 tracking-tight leading-tight">
                                    Bukti Pendukung Presensi
                                </h2>

                                <p class="text-sm sm:text-[15px] text-slate-600 mt-1 leading-relaxed">
                                    Kumpulkan bukti kehadiran (screenshot sesi tatap muka) langsung ke folder Google Drive panitia.
                                </p>
                            </div>

                            {{-- Akses Folder Google Drive Penyelenggara --}}
                            @if ($adminDriveLink)
                                <div class="p-4 sm:p-5 rounded-md bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-md bg-blue-50 text-[#0B5CFF] flex items-center justify-center shrink-0 text-xl shadow-xs">
                                            <i class="fab fa-google-drive"></i>
                                        </div>
                                        <div class="min-w-0 space-y-0.5">
                                            <h4 class="text-sm sm:text-base font-bold text-slate-800">Folder Google Drive Panitia</h4>
                                            <p class="text-xs sm:text-[13px] text-slate-500 leading-snug">
                                                Kumpulkan berkas foto atau tangkapan layar sesi tatap muka Anda ke folder Google Drive panitia berikut.
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 shadow-sm transition-all shrink-0 whitespace-nowrap">
                                        <i class="fab fa-google-drive text-sm"></i>
                                        <span>Buka Google Drive</span>
                                        <i class="fas fa-external-link-alt text-[10px] ml-0.5 opacity-80"></i>
                                    </a>
                                </div>
                            @else
                                <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs flex items-center gap-3.5 text-slate-500">
                                    <div class="w-10 h-10 rounded-md bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-lg">
                                        <i class="fab fa-google-drive"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-slate-700">Tautan Google Drive Belum Tersedia</p>
                                        <p class="text-xs text-slate-500">Tautan folder Google Drive untuk pengumpulan bukti presensi belum diatur oleh penyelenggara.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 4. CARD EVALUASI HARIAN / MODUL (MENDUKUNG PILGAN, URAIAN, & UPLOAD FILE SOAL-JAWABAN) --}}
                @php
                    $postTestQuestionsCount = $postTestBab ? data_get($postTestBab, 'questions', collect())->count() : 0;
                    $hasPostTestQuestions = $postTestQuestionsCount > 0;
                    $isPostTestBabLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isContentCompleted && !$isPostTestBabCompleted));
                    $postTestTitle = data_get($postTestBab, 'title', ($isBimtekEvent ? 'Evaluasi Hari ke-' : 'Evaluasi Modul ') . ($sectionIndex + 1) . ': ' . $sectionName);
                @endphp

                <div class="relative flex items-start gap-3.5 sm:gap-5">
                    {{-- Node Timeline Tahap Akhir --}}
                    <div class="relative w-8 sm:w-10 shrink-0 flex flex-col items-center select-none">
                        @if ($isEvaluasiDone)
                            <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-xs ring-4 ring-white shrink-0" title="Evaluasi Selesai">
                                <i class="fas fa-check"></i>
                            </div>
                        @elseif ($isMateriDone && (!$hasZoom || $isZoomDone))
                            <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#13416B] text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md ring-4 ring-blue-100 ring-offset-2 ring-offset-white shrink-0" title="Tahap Evaluasi (Saat Ini)">
                                {{ $hasZoom ? 4 : 2 }}
                            </div>
                        @else
                            <div class="relative z-10 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white text-slate-400 border-2 border-slate-300 flex items-center justify-center font-bold text-xs sm:text-sm shadow-2xs ring-4 ring-white shrink-0" title="Tahap Terkunci">
                                {{ $hasZoom ? 4 : 2 }}
                            </div>
                        @endif
                    </div>

                    {{-- Card Konten Tahap 4 --}}
                    <div id="evaluasi-modul" data-tour="evaluasi" class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 shadow-2xs transition-all relative overflow-hidden">
                        <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                            {{-- Kolom Kiri: Informasi Evaluasi & Aksi --}}
                            <div class="flex-1 min-w-0 sm:py-1 space-y-4">
                                <div>
                                <h2 class="text-base sm:text-lg lg:text-xl font-bold text-slate-700 tracking-tight leading-tight max-w-full lg:max-w-[85%]">
                                    {{ $isBimtekEvent ? 'Evaluasi Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }}
                                </h2>

                                <p class="text-sm sm:text-[15px] text-slate-600 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                    Selesaikan evaluasi pembelajaran pada {{ $isBimtekEvent ? 'hari ini' : 'modul ini' }} untuk menguji pemahaman dan capaian pembelajaran Anda.
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
                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-5 lg:-mb-6">
                            <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                alt="Ilustrasi Evaluasi Modul"
                                class="sm:h-40 md:h-46 lg:h-52 w-auto object-contain object-bottom select-none pointer-events-none" />
                        </div>
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
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">On This Page</span>
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
                                    <div class="w-5 h-5 rounded-full bg-blue-100 text-[#0B5CFF] flex items-center justify-center text-[10px]">
                                        <i class="fab fa-google-drive"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                        Bukti Pendukung Presensi
                                    </p>
                                    <p class="text-[11px] text-[#0B5CFF] font-medium mt-0.5">
                                        Google Drive Panitia
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

    @push('scripts')
        @include('lms::user.course.partials.guide-assets')
        <script>
            (function () {
                const PAGE = 'section_agenda_{{ $sectionId }}';
                const STEPS = [
                    {
                        element: '[data-tour="pembelajaran-mandiri"]',
                        popover: {
                            title: 'Tahap 1: Pembelajaran Mandiri',
                            description: 'Pelajari seluruh <strong>materi pembelajaran</strong> secara mandiri hingga selesai. Tuntaskan semua materi untuk membuka akses <strong>tahap berikutnya</strong>.',
                            side: 'bottom',
                            align: 'start',
                        },
                    },
                    @if ($hasZoom)
                    {
                        element: '[data-tour="zoom"]',
                        popover: {
                            title: 'Tahap 2: Sesi Tatap Muka Online',
                            description: 'Ikuti <strong>sesi Zoom interaktif</strong> sesuai jadwal dengan menekan <strong>Masuk Tatap Muka</strong>, lalu tekan <strong>Konfirmasi Kehadiran</strong> untuk mencatat presensi Anda.',
                            side: 'bottom',
                            align: 'start',
                        },
                    },
                    {
                        element: '[data-tour="bukti-presensi"]',
                        popover: {
                            title: 'Tahap 3: Bukti Pendukung Presensi',
                            description: 'Ambil <strong>screenshot sesi Zoom</strong> dan tekan <strong>Buka Google Drive</strong> untuk mengunggah <strong>bukti kehadiran</strong> ke folder Google Drive panitia.',
                            side: 'bottom',
                            align: 'start',
                        },
                    },
                    @endif
                    {
                        element: '[data-tour="evaluasi"]',
                        popover: {
                            title: '{{ $hasZoom ? "Tahap 4: " : "Tahap 2: " }}{{ $isBimtekEvent ? "Evaluasi Hari ke-" . ($sectionIndex + 1) : "Evaluasi Modul " . ($sectionIndex + 1) }}',
                            description: '{!! addslashes($currentEvalType === "uraian"
                                ? "Jawab <strong>pertanyaan studi kasus</strong> pada formulir jawaban. Evaluasi terbuka otomatis setelah seluruh <strong>materi mandiri tuntas</strong>."
                                : ($currentEvalType === "upload"
                                    ? "Unduh <strong>lembar soal penugasan</strong>, kerjakan studi kasus, lalu unggah <strong>dokumen jawaban</strong> (PDF/DOCX/ZIP) sebelum batas waktu."
                                    : "Kerjakan butir soal <strong>pilihan ganda</strong> dan raih nilai di atas <strong>KKM kelulusan</strong> untuk menuntaskan evaluasi ini.")) !!}',
                            side: 'top',
                            align: 'start',
                        },
                    },
                ];

                window.startSectionTour = function () {
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
