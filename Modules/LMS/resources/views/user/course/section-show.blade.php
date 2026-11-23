<x-dashboard::layouts.dashboard
    title="{{ ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date)) ? 'Hari ' : 'Modul ' }}{{ $sectionIndex + 1 }}: {{ data_get($section, 'name') }} | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    @php
        $courseName = $courseName ?? data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
        $courseSlug = $slug ?? data_get($course, 'slug');
        $canReviewAll = data_get($course, 'can_review_all', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);
        $activeSession = $currentSession ?? null;
        $activeSessionId = data_get($activeSession, 'id');
        $activeSessionTitle = data_get($activeSession, 'title');
        $activeSessionNumber = data_get($activeSession, 'session_number');
        $sessionZoomUrl = data_get($activeSession, 'effective_zoom_url') ?? data_get($activeBimtek, 'zoom_url');
        $sessionMeetingId = data_get($activeSession, 'zoom_meeting_id') ?: data_get($activeBimtek, 'zoom_meeting_id');
        $sessionPasscode = data_get($activeSession, 'zoom_passcode') ?: data_get($activeBimtek, 'zoom_passcode');
        $sessionSchedule = data_get($activeSession, 'schedule_formatted') ?? ($zoomScheduleFormatted ?? null);
        $sessionAttendanceSchedule = data_get($activeSession, 'attendance_schedule_formatted') ?? ($attendanceScheduleFormatted ?? null);
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
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-2xs"></i>
                        <a href="{{ route('user.course.my-course.detail', $courseSlug) }}"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors truncate max-w-40 sm:max-w-xs"
                            title="{{ $courseName }}">
                            {{ $courseName }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-2xs"></i>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug truncate max-w-50 sm:max-w-none">
                            {{ $isBimtekEvent ? 'Hari ke-' . ($sectionIndex + 1) : 'Modul ' . ($sectionIndex + 1) }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Card Agenda Utama (Membungkus Header dan Seluruh Bagian Agenda) --}}
        <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 lg:p-7 transition-all">
            
            {{-- Header Agenda Modul / Hari --}}
            <div class="flex items-center justify-between gap-3.5 sm:gap-4 pb-5 sm:pb-6 border-b border-slate-100">
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
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-medium text-slate-700 tracking-wide leading-tight wrap-break-word" style="font-family: 'Oswald', sans-serif;">
                            {{ $sectionName }}
                        </h1>
                    </div>
                </div>
            </div>

            {{-- Layout Kiri Kanan: Konten Aktivitas (Kiri di Desktop / Bawah di Mobile) & On This Page (Kanan di Desktop / Atas di Mobile) --}}
            <div class="mt-5 sm:mt-6 flex flex-col lg:flex-row items-start gap-6 lg:gap-7">

                {{-- Kolom Kiri: Seluruh Card Konten Agenda dengan Timeline Alur Kegiatan --}}
                <div class="flex-1 min-w-0 w-full order-2 lg:order-1">

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
                                <h2 class="text-base sm:text-lg lg:text-xl font-medium text-slate-700 tracking-wide leading-tight" style="font-family: 'Oswald', sans-serif;">Pembelajaran Mandiri</h2>
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
                                                <p class="font-bold text-slate-800 text-sm sm:text-[15px] leading-snug wrap-break-word line-clamp-2" title="{{ $contentName }}">
                                                    {{ $contentName }}
                                                </p>
                                                @if ($isContentItemCompleted)
                                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xs font-bold shrink-0" title="Selesai">
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
                                <h2 class="text-base sm:text-lg lg:text-xl font-medium text-slate-700 tracking-wide leading-tight" style="font-family: 'Oswald', sans-serif;">
                                    Sesi Tatap Muka Online {{ $activeSessionTitle ? '— ' . $activeSessionTitle : '' }}
                                </h2>

                                <p class="text-sm sm:text-[15px] text-slate-600 mt-1 leading-relaxed">
                                    @if ($isZoomAttended)
                                        Terima kasih, Anda telah mengisi presensi kehadiran sesi {{ $activeSessionTitle ?: 'tatap muka online' }}.
                                    @elseif ($isPresensiOpen)
                                        Silakan masuk ke ruang tatap muka, lalu klik tombol Konfirmasi Kehadiran setelah Anda bergabung.
                                    @elseif ($isZoomEnded)
                                        Sesi tatap muka online dan batas waktu konfirmasi kehadiran telah berakhir.
                                    @else
                                        Bergabunglah ke ruang tatap muka sesuai jadwal. Tombol konfirmasi akan aktif saat presensi dibuka oleh panitia.
                                    @endif
                                </p>
                            </div>

                            {{-- Agenda Multi-Sesi Harian jika terdapat lebih dari 1 sesi --}}
                            @if (isset($bimtekSessions) && $bimtekSessions->count() > 1)
                                <div class="p-3 sm:p-3.5 rounded-md bg-white border border-slate-200 shadow-2xs space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                            <i class="fas fa-calendar-check text-[#13416B]"></i>
                                            Agenda Multi-Sesi Harian ({{ $bimtekSessions->count() }} Hari)
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-500">
                                            {{ $attendedSessionIds->count() }} / {{ $bimtekSessions->count() }} Sesi Terkonfirmasi
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach ($bimtekSessions as $sIdx => $sessionItem)
                                            @php
                                                $isItemAttended = $attendedSessionIds->contains($sessionItem->id);
                                                $isCurrent = !empty($activeSessionId) && $activeSessionId === $sessionItem->id;
                                            @endphp
                                            <div class="flex items-center justify-between p-2 rounded-md border text-xs {{ $isCurrent ? 'border-blue-400 bg-blue-50/60 ring-1 ring-blue-300' : 'border-slate-200 bg-slate-50/50' }}">
                                                <div class="min-w-0 pr-1">
                                                    <p class="font-bold text-slate-800 truncate text-[11px]">
                                                        {{ $sessionItem->title }}
                                                        @if ($isCurrent)
                                                            <span class="ml-1 text-[9px] font-bold text-blue-700 uppercase">(Hari Ini)</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-2xs text-slate-500 truncate">{{ $sessionItem->schedule_formatted }}</p>
                                                </div>
                                                @if ($isItemAttended)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-2xs font-bold bg-emerald-100 text-emerald-700 shrink-0">
                                                        <i class="fas fa-check"></i> Hadir
                                                    </span>
                                                @elseif ($sessionItem->is_presensi_open)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-2xs font-bold bg-amber-100 text-amber-800 shrink-0 animate-pulse">
                                                        <i class="fas fa-clock"></i> Buka
                                                    </span>
                                                @elseif ($sessionItem->is_zoom_ended)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-2xs font-semibold bg-slate-200 text-slate-600 shrink-0">
                                                        Lewat
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-2xs font-semibold bg-slate-100 text-slate-400 shrink-0">
                                                        Nanti
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Info Sesi (Jadwal, Meeting ID, Passcode) - 2 Kolom Soft Solid Setema --}}
                            @if ($sessionSchedule || $sessionMeetingId || $sessionPasscode || $sessionAttendanceSchedule)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                    @if ($sessionSchedule)
                                        <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 p-3 rounded-md bg-[#EBF3FA] border border-[#C5DBEE] shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 text-xs sm:text-sm mt-0.5 sm:mt-0 shadow-2xs">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-2xs font-bold uppercase tracking-wider text-[#13416B]/75">Jadwal Zoom</p>
                                                <p class="text-xs font-bold text-slate-800 leading-snug wrap-break-word mt-0.5" title="{{ $sessionSchedule }}">
                                                    {{ $sessionSchedule }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($sessionAttendanceSchedule)
                                        <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 p-3 rounded-md bg-[#EBF3FA] border border-[#C5DBEE] shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 text-xs sm:text-sm mt-0.5 sm:mt-0 shadow-2xs">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-2xs font-bold uppercase tracking-wider text-[#13416B]/75">Waktu Presensi</p>
                                                <p class="text-xs font-bold text-slate-800 leading-snug wrap-break-word mt-0.5" title="{{ $sessionAttendanceSchedule }}">
                                                    {{ $sessionAttendanceSchedule }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($sessionMeetingId)
                                        <div class="flex items-center gap-2.5 sm:gap-3 p-3 rounded-md bg-[#EBF3FA] border border-[#C5DBEE] shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 text-xs sm:text-sm shadow-2xs">
                                                <i class="fas fa-hashtag"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-2xs font-bold uppercase tracking-wider text-[#13416B]/75">Meeting ID</p>
                                                <p class="text-xs font-mono font-bold text-slate-800 tracking-wide select-all break-all mt-0.5" title="{{ $sessionMeetingId }}">{{ $sessionMeetingId }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($sessionPasscode)
                                        <div class="flex items-center gap-2.5 sm:gap-3 p-3 rounded-md bg-[#EBF3FA] border border-[#C5DBEE] shadow-2xs">
                                            <div class="w-8 h-8 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 text-xs sm:text-sm shadow-2xs">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-2xs font-bold uppercase tracking-wider text-[#13416B]/75">Passcode</p>
                                                <p class="text-xs font-mono font-bold text-slate-800 tracking-wide select-all break-all mt-0.5" title="{{ $sessionPasscode }}">{{ $sessionPasscode }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Tombol Aksi: Masuk Tatap Muka & Presensi Berdampingan --}}
                            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 pt-1">
                                {{-- 1. Tombol Masuk Tatap Muka --}}
                                @if ($isZoomEnded)
                                    <button type="button" disabled title="Jadwal sesi tatap muka telah berakhir"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm border border-slate-300 bg-slate-100 text-slate-400 cursor-not-allowed select-none text-center">
                                        <i class="fas fa-video-slash text-xs"></i>
                                        <span>Sesi Tatap Muka Berakhir</span>
                                    </button>
                                @else
                                    <a href="{{ route('user.course.my-course.join-zoom', array_filter(['slug' => $courseSlug, 'session_id' => $activeSessionId])) }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all text-center">
                                        <i class="fas fa-video text-xs"></i>
                                        <span>Masuk Tatap Muka {{ $activeSessionNumber ? 'Hari ' . $activeSessionNumber : '' }}</span>
                                        <i class="fas fa-external-link-alt text-2xs opacity-75"></i>
                                    </a>
                                @endif

                                {{-- 2. Tombol Presensi Kehadiran --}}
                                @if ($isZoomAttended)
                                    <button type="button" disabled
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 text-white shadow-sm select-none cursor-default text-center">
                                        <i class="fas fa-check-circle text-xs"></i>
                                        <span>Sudah Presensi {{ $activeSessionNumber ? 'Hari ' . $activeSessionNumber : '' }}</span>
                                    </button>
                                @elseif ($isPresensiOpen)
                                    <form action="{{ route('user.course.my-course.attend-zoom', $courseSlug) }}" method="POST" class="w-full sm:w-auto inline-flex">
                                        @csrf
                                        @if ($activeSessionId)
                                            <input type="hidden" name="session_id" value="{{ $activeSessionId }}">
                                        @endif
                                        <button type="submit"
                                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer animate-pulse text-center">
                                            <i class="fas fa-clipboard-check text-xs"></i>
                                            <span>Konfirmasi Kehadiran {{ $activeSessionNumber ? 'Hari ' . $activeSessionNumber : '' }}</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled title="{{ $presensiLockReason }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm border border-slate-300 bg-slate-100 text-slate-400 cursor-not-allowed select-none text-center">
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
                            @if ($hasProof || $isEvaluasiDone)
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

                        {{-- Card Konten Tahap 3: Upload Screenshot Bukti Zoom + Auto-Kompresi di Browser --}}
                        <div id="bukti-presensi" data-tour="bukti-presensi"
                            x-data="{
                                showUploadForm: {{ $hasProof ? 'false' : 'true' }},
                                compressing: false,
                                previewUrl: '',
                                sizeInfo: '',
                                errorMsg: '',
                                formatBytes(bytes) {
                                    if (!bytes) return '0 KB';
                                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                                },
                                async handleFileChange(event) {
                                    const input = event.target;
                                    const file = input.files && input.files[0];
                                    this.errorMsg = '';
                                    this.sizeInfo = '';
                                    if (this.previewUrl) {
                                        URL.revokeObjectURL(this.previewUrl);
                                        this.previewUrl = '';
                                    }
                                    if (!file) return;
                                    if (!file.type.startsWith('image/')) {
                                        this.errorMsg = 'Harap pilih berkas gambar (JPG, PNG, atau WEBP).';
                                        input.value = '';
                                        return;
                                    }

                                    this.compressing = true;
                                    const origSize = file.size;

                                    try {
                                        const img = await new Promise((resolve, reject) => {
                                            const image = new Image();
                                            image.onload = () => resolve(image);
                                            image.onerror = reject;
                                            image.src = URL.createObjectURL(file);
                                        });

                                        const maxWidth = 1280;
                                        let width = img.naturalWidth || img.width;
                                        let height = img.naturalHeight || img.height;
                                        if (width > maxWidth) {
                                            height = Math.round((height * maxWidth) / width);
                                            width = maxWidth;
                                        }

                                        const canvas = document.createElement('canvas');
                                        canvas.width = width;
                                        canvas.height = height;
                                        const ctx = canvas.getContext('2d');
                                        ctx.drawImage(img, 0, 0, width, height);
                                        URL.revokeObjectURL(img.src);

                                        let blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', 0.78));
                                        let ext = 'webp';
                                        let mime = 'image/webp';
                                        if (!blob || blob.type !== 'image/webp') {
                                            blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.78));
                                            ext = 'jpg';
                                            mime = 'image/jpeg';
                                        }

                                        if (blob && blob.size < origSize && typeof DataTransfer !== 'undefined') {
                                            const baseName = file.name.replace(/\.[^/.]+$/, '');
                                            const compressedFile = new File([blob], baseName + '.' + ext, { type: mime, lastModified: Date.now() });
                                            const dt = new DataTransfer();
                                            dt.items.add(compressedFile);
                                            input.files = dt.files;
                                            this.previewUrl = URL.createObjectURL(compressedFile);
                                            this.sizeInfo = 'Dikompres otomatis: ' + this.formatBytes(origSize) + ' → ' + this.formatBytes(compressedFile.size);
                                        } else {
                                            this.previewUrl = URL.createObjectURL(file);
                                            this.sizeInfo = 'Ukuran berkas: ' + this.formatBytes(origSize);
                                        }
                                    } catch (e) {
                                        this.previewUrl = URL.createObjectURL(file);
                                        this.sizeInfo = 'Ukuran berkas: ' + this.formatBytes(origSize);
                                    } finally {
                                        this.compressing = false;
                                    }
                                }
                            }"
                            class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all space-y-4">
                            <div>
                                <h2 class="text-base sm:text-lg lg:text-xl font-medium text-slate-700 tracking-wide leading-tight" style="font-family: 'Oswald', sans-serif;">
                                    Bukti Pendukung Presensi
                                </h2>

                                <p class="text-sm sm:text-[15px] text-slate-600 mt-1 leading-relaxed">
                                    Unggah tangkapan layar (screenshot) saat mengikuti sesi tatap muka Zoom. Gambar akan dikompres otomatis di perangkat Anda sebelum dikirim agar unggahan lebih cepat.
                                </p>
                            </div>

                            <div class="p-4 sm:p-5 rounded-md bg-white border border-slate-200 space-y-4 shadow-2xs">
                                @if ($hasProof)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-start gap-3.5 min-w-0">
                                            @if ($zoomProof->file_url)
                                                <a href="{{ $zoomProof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                   class="w-16 h-16 rounded-md border border-slate-200 overflow-hidden shrink-0 bg-slate-100 block hover:opacity-90 transition-opacity"
                                                   title="Klik untuk melihat ukuran penuh">
                                                    <img src="{{ $zoomProof->file_url }}" alt="Bukti Zoom" class="w-full h-full object-cover">
                                                </a>
                                            @else
                                                <div class="w-12 h-12 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-check-circle text-xl"></i>
                                                </div>
                                            @endif
                                            <div class="space-y-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Bukti Zoom Terkumpul</span>
                                                    </span>
                                                    @if ($zoomProof->submitted_at)
                                                        <span class="text-xs text-slate-500">
                                                            Diunggah {{ $zoomProof->submitted_at->translatedFormat('d M Y, H:i') }} WIB
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($zoomProof->file_url)
                                                    <a href="{{ $zoomProof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#13416B] hover:underline">
                                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                                        <span>Lihat Screenshot Penuh</span>
                                                    </a>
                                                @else
                                                    <p class="text-xs text-slate-500">
                                                        Riwayat pengumpulan bukti sudah tercatat (berkas gambar telah diarsipkan otomatis).
                                                    </p>
                                                @endif
                                                @if ($zoomProof->notes)
                                                    <p class="text-xs text-slate-600">{{ $zoomProof->notes }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($zoomProof->file_url)
                                            <button type="button" @click="showUploadForm = !showUploadForm"
                                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors shrink-0 cursor-pointer">
                                                <span x-text="showUploadForm ? 'Batal' : 'Ganti Screenshot'"></span>
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                <form x-show="showUploadForm" x-cloak
                                      action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                      method="POST"
                                      enctype="multipart/form-data"
                                      class="space-y-3.5 {{ $hasProof ? 'pt-4 border-t border-slate-100' : '' }}">
                                    @csrf

                                    <div class="space-y-2">
                                        <label class="block text-xs sm:text-sm font-bold text-slate-700">
                                            Pilih Screenshot Bukti Mengikuti Zoom <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="file"
                                               name="proof_file"
                                               accept="image/jpeg,image/png,image/webp"
                                               required
                                               @change="handleFileChange($event)"
                                               class="block w-full text-xs sm:text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs sm:file:text-sm file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] border border-slate-300 rounded-md p-1.5 bg-slate-50 cursor-pointer">
                                        <p class="text-[11px] sm:text-xs text-slate-500">
                                            Format JPG, PNG, atau WEBP. Otomatis dikompres menjadi ringan (~100 KB) di browser sebelum dikirim.
                                        </p>
                                        <template x-if="errorMsg">
                                            <p class="text-xs font-semibold text-rose-600" x-text="errorMsg"></p>
                                        </template>
                                        <template x-if="compressing">
                                            <p class="text-xs font-semibold text-[#13416B]">Sedang mengompres gambar...</p>
                                        </template>
                                        <template x-if="previewUrl && !compressing">
                                            <div class="flex items-center gap-3 p-2.5 rounded-md bg-emerald-50/70 border border-emerald-200">
                                                <img :src="previewUrl" alt="Preview" class="w-14 h-14 rounded object-cover border border-emerald-200 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-bold text-emerald-800">Siap diunggah</p>
                                                    <p class="text-xs text-emerald-700" x-text="sizeInfo"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Keterangan <span class="text-slate-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="notes" value="{{ old('notes') }}"
                                               placeholder="Contoh: Hadir sesi Zoom pagi hingga selesai"
                                               class="w-full px-3 py-2 text-xs sm:text-sm rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit"
                                                :disabled="compressing"
                                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 transition-colors shadow-2xs cursor-pointer">
                                            <i class="fas fa-upload text-xs"></i>
                                            <span x-text="compressing ? 'Mengompres...' : '{{ $hasProof ? 'Simpan Screenshot Baru' : 'Unggah Bukti Zoom' }}'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
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
                    <div id="evaluasi-modul" data-tour="evaluasi"
                        x-data="{ showStartEvalModal: false }"
                        class="flex-1 min-w-0 scroll-mt-6 rounded-md border border-[#446680] bg-[#547996] text-white p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                        <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                            {{-- Kolom Kiri: Informasi Evaluasi & Aksi --}}
                            <div class="flex-1 min-w-0 sm:py-1 space-y-4">
                                <div>
                                    <h2 class="text-base sm:text-lg lg:text-xl font-medium text-white tracking-wide leading-tight max-w-full lg:max-w-[85%]" style="font-family: 'Oswald', sans-serif;">
                                        {{ $isBimtekEvent ? 'Evaluasi Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }}
                                    </h2>

                                    <p class="text-sm sm:text-[15px] text-white/85 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                        Selesaikan evaluasi pembelajaran pada {{ $isBimtekEvent ? 'hari ini' : 'modul ini' }} untuk menguji pemahaman dan capaian pembelajaran Anda.
                                    </p>
                                </div>

                            {{-- Folder Drive Tambahan dari Penyelenggara (Jika Dikonfigurasi) --}}
                            @if ($adminDriveLink && $currentEvalType !== 'upload')
                                <div class="p-3.5 rounded-md bg-[#EBF3FA] border border-[#C5DBEE] shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0 shadow-2xs text-base">
                                            <i class="fab fa-google-drive"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-[#13416B]">Folder Google Drive Tugas dari Penyelenggara</p>
                                            <p class="text-xs text-slate-600 mt-0.5">Akses folder Drive panitia untuk unduh lembar kerja atau kumpulkan berkas tambahan.</p>
                                        </div>
                                    </div>

                                    <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-xs shrink-0 whitespace-nowrap cursor-pointer">
                                        <i class="fab fa-google-drive"></i>
                                        <span>Buka Folder Drive</span>
                                        <i class="fas fa-external-link-alt text-2xs ml-0.5"></i>
                                    </a>
                                </div>
                            @endif

                            {{-- Tombol Aksi untuk Membuka Halaman Evaluasi (Uraian, Unggah Berkas, & Pilihan Ganda) --}}
                            @if ($postTestBab && in_array($currentEvalType, ['uraian', 'upload', 'pilgan']))
                                @php
                                    $evalDoneLabel = match ($currentEvalType) {
                                        'uraian' => 'Lihat Lembar Jawaban Uraian',
                                        'upload' => 'Lihat Lembar Penugasan Berkas',
                                        default  => 'Lihat Hasil Evaluasi',
                                    };
                                    $evalStartLabel = match ($currentEvalType) {
                                        'uraian' => 'Mulai Kerjakan Uraian',
                                        'upload' => 'Buka Lembar Penugasan Berkas',
                                        default  => 'Mulai Evaluasi',
                                    };
                                @endphp
                                <div class="pt-1 flex flex-wrap items-center gap-3">
                                    @if ($isEvaluasiDone)
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] transition-all shadow-sm">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>{{ $evalDoneLabel }}</span>
                                        </a>
                                    @elseif ($isPostTestBabLocked)
                                        <button type="button" disabled
                                            title="Tuntaskan semua materi pada modul ini terlebih dahulu"
                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white/15 text-white/75 border border-white/25 cursor-not-allowed select-none">
                                            <i class="fas fa-lock text-xs"></i>
                                            <span>Evaluasi Terkunci</span>
                                        </button>
                                    @else
                                        <button type="button" @click="showStartEvalModal = true"
                                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-[#13416B] shadow-sm transition-all cursor-pointer">
                                            <i class="fas {{ $currentEvalType === 'upload' ? 'fa-file-upload' : 'fa-play' }} text-xs"></i>
                                            <span>{{ $evalStartLabel }}</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Kanan: Ilustrasi Gambar nempel bawah --}}
                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-6 sm:-mb-6 lg:-mb-7">
                            <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                alt="Ilustrasi Evaluasi Modul"
                                class="sm:h-40 md:h-46 lg:h-52 w-auto object-contain object-bottom translate-y-1 select-none pointer-events-none" />
                        </div>
                    </div>

                    {{-- MODAL / BOTTOM SHEET KONFIRMASI MULAI EVALUASI HARIAN / MODUL --}}
                    <template x-teleport="body">
                        <div x-show="showStartEvalModal" x-cloak
                            @keydown.escape.window="showStartEvalModal = false"
                            class="fixed inset-0 z-100 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">

                            {{-- Overlay Click to Dismiss --}}
                            <div class="fixed inset-0" @click="showStartEvalModal = false"></div>

                            {{-- Panel Konten: Bottom Sheet di Mobile, Modal di Tablet/Desktop --}}
                            <div x-show="showStartEvalModal"
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
                                            <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                                                {{ $isBimtekEvent ? 'Evaluasi Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }} — {{ $sectionName }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showStartEvalModal = false"
                                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-md transition-colors cursor-pointer shrink-0">
                                        <i class="fas fa-times text-base"></i>
                                    </button>
                                </div>

                                {{-- Ringkasan Informasi Evaluasi --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-md divide-y divide-slate-200/80 text-xs sm:text-[13px]">
                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                            <i class="fas fa-tags text-slate-400 w-4 text-center"></i>
                                            Jenis Soal
                                        </span>
                                        <span class="font-bold text-slate-800 text-right">
                                            {{ $currentEvalType === 'uraian' ? 'Soal Uraian / Esai' : ($currentEvalType === 'upload' ? 'Penugasan Berkas Mandiri' : 'Pilihan Ganda (CBT)') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                            <i class="fas fa-list-ol text-slate-400 w-4 text-center"></i>
                                            Jumlah Soal
                                        </span>
                                        <span class="font-bold text-slate-800 text-right">
                                            {{ $currentEvalType === 'uraian' ? ($postTestQuestionsCount ?: 2) . ' Soal Uraian' : ($currentEvalType === 'upload' ? '1 Berkas Penugasan' : $postTestQuestionsCount . ' Butir Soal') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                        <span class="text-slate-500 font-medium flex items-center gap-2">
                                            <i class="fas fa-stopwatch text-slate-400 w-4 text-center"></i>
                                            Waktu Pengerjaan
                                        </span>
                                        <span class="font-bold text-slate-800 text-right">
                                            {{ ($currentEvalType === 'pilgan' || data_get($postTestBab, 'duration', 0) > 0) ? (data_get($postTestBab, 'duration', 15) ?: 15) . ' Menit' : 'Tanpa Batas Waktu' }}
                                        </span>
                                    </div>
                                    @if (in_array($currentEvalType, ['uraian', 'upload']))
                                        <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                            <span class="text-slate-500 font-medium flex items-center gap-2">
                                                <i class="fas fa-user-check text-slate-400 w-4 text-center"></i>
                                                Metode Penilaian
                                            </span>
                                            <span class="font-bold text-slate-800 text-right">
                                                {{ ($postTestBab && $postTestBab->has_auto_grading) ? 'Koreksi Otomatis Sistem' : 'Penilaian Instruktur' }}
                                            </span>
                                        </div>
                                    @elseif (data_get($postTestBab, 'passing_score', 0) > 0)
                                        <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                                            <span class="text-slate-500 font-medium flex items-center gap-2">
                                                <i class="fas fa-bullseye text-slate-400 w-4 text-center"></i>
                                                Ambang Batas (KKM)
                                            </span>
                                            <span class="font-bold text-slate-800 text-right">
                                                {{ data_get($postTestBab, 'passing_score') }} Poin
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Pesan Kesiapan --}}
                                <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed">
                                    {{ $currentEvalType === 'upload'
                                        ? 'Anda akan diarahkan ke halaman penugasan berkas untuk mengunduh berkas soal dan mengunggah hasil pengerjaan Anda.'
                                        : 'Apakah Anda yakin ingin memulai pengerjaan evaluasi sekarang? Pastikan koneksi internet stabil sebelum melanjutkan.' }}
                                </p>

                                {{-- Footer Tombol Aksi --}}
                                <div class="pt-1 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                                    <button type="button" @click="showStartEvalModal = false"
                                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-md text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors cursor-pointer">
                                        Batal
                                    </button>
                                    <a href="{{ $postTestBab ? route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) : '#' }}"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#1E568A] hover:bg-[#13416B] text-white shadow-sm transition-all">
                                        <i class="fas {{ $currentEvalType === 'upload' ? 'fa-file-upload' : 'fa-play' }} text-xs"></i>
                                        <span>{{ $currentEvalType === 'upload' ? 'Ya, Buka Lembar Penugasan' : 'Ya, Kerjakan Sekarang' }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            </div>

            {{-- Widget "On This Page" / Daftar Aktivitas (Atas di Mobile, Kanan di Desktop) --}}
            <div class="w-full lg:w-72 xl:w-80 shrink-0 order-1 lg:order-2">
                <div class="lg:sticky lg:top-6 rounded-md border border-slate-200 bg-slate-50/70 p-4 sm:p-5 shadow-2xs">
                    {{-- Header Widget --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/90">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-list-check text-[#13416B] text-xs"></i>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">On This Page</span>
                        </div>
                        <button type="button" onclick="window.startSectionTour && window.startSectionTour()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-slate-700 bg-white hover:bg-slate-100 hover:text-[#13416B] border border-slate-200 rounded-md transition-all shadow-2xs cursor-pointer shrink-0">
                            <i class="fas fa-route text-[#13416B] text-xs"></i>
                            <span>Panduan</span>
                        </button>
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
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xs font-bold">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-2xs font-bold">
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
                                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xs font-bold">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-2xs font-bold">
                                            2
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-slate-800 group-hover:text-[#13416B] transition-colors leading-snug">
                                        Sesi Tatap Muka Online
                                    </p>
                                    <p class="text-[11px] {{ $isZoomDone ? 'text-emerald-600 font-semibold' : ($isPresensiOpen ? 'text-blue-600 font-semibold' : 'text-slate-500') }} mt-0.5">
                                        {{ $isZoomDone ? 'Presensi terverifikasi' : ($isPresensiOpen ? 'Presensi dibuka' : (($isPresensiEnded ?? $isZoomEnded) ? 'Presensi berakhir' : 'Belum dibuka')) }}
                                    </p>
                                </div>
                            </a>

                            {{-- 3. Bukti Pendukung Presensi --}}
                            <a href="#bukti-presensi"
                                class="group flex items-start gap-2.5 p-2 rounded-md hover:bg-white hover:shadow-2xs transition-all border border-transparent hover:border-slate-200">
                                <div class="mt-0.5 shrink-0">
                                    <div class="w-5 h-5 rounded-full bg-blue-100 text-[#0B5CFF] flex items-center justify-center text-2xs">
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
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xs font-bold">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-2xs font-bold">
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
