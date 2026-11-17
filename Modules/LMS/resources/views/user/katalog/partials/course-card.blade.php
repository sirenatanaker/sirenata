@php
    $activeBimtek = $course->bimtekEvents->first();
    if (!$activeBimtek) {
        return;
    }

    $isEnrolledInCourse = in_array($course->id, $enrolledCourseIds ?? []);
    $isEnrolledInThisBimtek = in_array($activeBimtek->id, $userBimtekEventIds ?? []);

    $thumbnailUrl = !empty($course->thumbnail_url) 
        ? $course->thumbnail_url 
        : (!empty($course->thumbnail) 
            ? (str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail)) 
            : null);

    // Timeline Phase & Periode
    $curPhase = $activeBimtek->current_timeline_phase ?? [];
    $phaseKey = $curPhase['phase'] ?? ($activeBimtek->status === 'open_registration' ? 'registration' : 'learning');
    $isRegistrationOpen = ($phaseKey === 'registration' && $activeBimtek->status === 'open_registration');

    $execStart = $activeBimtek->start_date;
    $execEnd   = $activeBimtek->end_date;
    $execDates = ($execStart || $execEnd)
        ? (($execStart ? $execStart->translatedFormat('d M') : '-') . ($execEnd ? ' - ' . $execEnd->translatedFormat('d M Y') : ''))
        : null;

    $zoomScheduleText = null;
    if ($activeBimtek->zoom_scheduled_at) {
        $startStr = $activeBimtek->zoom_scheduled_at->translatedFormat('d M Y, H:i');
        $endStr   = $activeBimtek->zoom_end_at ? $activeBimtek->zoom_end_at->translatedFormat('H:i') . ' WIB' : 'WIB';
        $zoomScheduleText = $startStr . ' - ' . $endStr;
    } elseif ($activeBimtek->zoom_url) {
        $zoomScheduleText = 'Sesi Live Zoom Terjadwal';
    }

    if ($phaseKey === 'registration') {
        $periodeLabel = 'Periode Pendaftaran';
        $regStart = $activeBimtek->registration_start_at ?? $activeBimtek->start_date?->copy()->subDays(7);
        $regEnd   = $activeBimtek->registration_end_at ?? $activeBimtek->start_date?->copy()->subDay();
        $periodeDates = ($regStart ? $regStart->translatedFormat('d M') : '-') . ' - ' . ($regEnd ? $regEnd->translatedFormat('d M Y') : '-');
    } elseif ($phaseKey === 'assessment') {
        $periodeLabel = 'Periode Evaluasi';
        $evalStart = $activeBimtek->assessment_start_at ?? $activeBimtek->end_date;
        $evalEnd   = $activeBimtek->assessment_end_at ?? $activeBimtek->end_date?->copy()->addDays(3);
        $periodeDates = ($evalStart ? $evalStart->translatedFormat('d M') : '-') . ' - ' . ($evalEnd ? $evalEnd->translatedFormat('d M Y') : '-');
    } else {
        $periodeLabel = 'Periode Pelaksanaan';
        $periodeDates = $execDates;
    }
@endphp

<div class="group flex flex-col h-full bg-transparent sm:bg-white sm:dark:bg-slate-800/90 rounded-none sm:rounded-md border-0 sm:border border-slate-200 dark:border-slate-700/80 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 dark:hover:border-slate-500 hover:-translate-y-1 transition-all duration-300 overflow-hidden w-full">

    {{-- Thumbnail & Badge (Mobile: aspect-[4/3] Lebih Tinggi, Desktop: sm:aspect-video 16:9) --}}
    <div class="relative w-full aspect-[4/3] sm:aspect-video overflow-hidden bg-slate-100 dark:bg-slate-900/50 rounded-t-md">
        @if (!empty($thumbnailUrl))
            <img src="{{ $thumbnailUrl }}"
                alt="{{ $course->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-image text-2xl sm:text-4xl text-slate-300 dark:text-slate-600"></i>
            </div>
        @endif

        {{-- Badge Status Event di Pojok Kanan Atas --}}
        @if ($curPhase)
            @php $isDonePhase = ($curPhase['phase'] ?? '') === 'completed'; @endphp
            <span class="absolute top-0 right-0 z-10 {{ $isDonePhase ? 'px-3 sm:px-3.5 py-1 sm:py-1.5 text-[9px] sm:text-[11px]' : 'px-2.5 sm:px-3 py-0.5 sm:py-1 text-[8px] sm:text-[10px]' }} font-bold uppercase tracking-wider text-white {{ $curPhase['badge_bg'] ?? 'bg-[#0B5CFF]' }} shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="{{ $isDonePhase ? 'fas fa-check-circle' : 'far fa-clock' }} text-[7px] sm:text-[9px]"></i>
                <span>{{ $curPhase['badge'] ?? 'Pendaftaran Buka' }}</span>
            </span>
        @endif

        {{-- Nama Event di Dasar Thumbnail (Tanpa Icon) --}}
        @if (!empty($activeBimtek?->batch_name))
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent px-2.5 sm:px-3.5 pt-4 pb-1.5 sm:pb-2 flex items-center text-white z-10">
                <span class="text-[11px] sm:text-[13px] font-medium truncate tracking-wide text-white drop-shadow-xs"
                      title="{{ preg_replace('/\bbatch\b/i', 'Gelombang', $activeBimtek->batch_name) }}">
                    {{ preg_replace('/\bbatch\b/i', 'Gelombang', $activeBimtek->batch_name) }}
                </span>
            </div>
        @endif
    </div>

    {{-- Konten Text --}}
    <div class="p-0 pt-3 sm:p-5 flex flex-col flex-1">
        {{-- Judul Kursus (Tampil Utama & Bersih) --}}
        <h3 class="text-xs sm:text-[17px] font-bold text-slate-800 dark:text-white leading-snug mb-2.5 sm:mb-3.5 group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors line-clamp-2 min-h-[2rem] sm:min-h-[2.75rem]"
            title="{{ $course->name }}">
            {{ $course->name }}
        </h3>

        {{-- Info Ringkas (Kontekstual 2 Baris: Pendaftaran + Pelaksanaan / Pelaksanaan + Jadwal Zoom) --}}
        <div class="flex flex-col gap-2.5 sm:gap-3.5 mb-3.5 sm:mb-4">
            {{-- Baris 1: Periode Fase Aktif --}}
            @if (!empty($periodeDates))
                <div class="flex flex-col gap-0.5 min-w-0">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-medium">
                        <i class="far fa-calendar-alt shrink-0 text-[11px] sm:text-[13px]"></i>
                        <span>{{ $periodeLabel }}</span>
                    </div>
                    <div class="text-xs sm:text-[15px] font-semibold text-slate-700 dark:text-slate-200 truncate pl-3.5 sm:pl-4">
                        {{ $periodeDates }}
                    </div>
                </div>
            @endif

            {{-- Baris 2: Kontekstual (Periode Pelaksanaan saat Pendaftaran, atau Jadwal Zoom saat Berlangsung/Selesai) --}}
            @if ($phaseKey === 'registration' && !empty($execDates))
                <div class="flex flex-col gap-0.5 min-w-0">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-medium">
                        <i class="far fa-calendar-check shrink-0 text-[11px] sm:text-[13px]"></i>
                        <span>Periode Pelaksanaan</span>
                    </div>
                    <div class="text-xs sm:text-[15px] font-semibold text-slate-700 dark:text-slate-200 truncate pl-3.5 sm:pl-4">
                        {{ $execDates }}
                    </div>
                </div>
            @elseif ($phaseKey !== 'registration' && !empty($zoomScheduleText))
                @php $isZoomNeutral = ($phaseKey === 'completed'); @endphp
                <div class="flex flex-col gap-0.5 min-w-0">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm {{ $isZoomNeutral ? 'text-slate-400 dark:text-slate-500' : 'text-blue-500' }} font-medium">
                        <i class="fas fa-video shrink-0 text-[11px] sm:text-[13px]"></i>
                        <span>Jadwal Sesi Zoom</span>
                    </div>
                    <div class="text-xs sm:text-[15px] font-semibold {{ $isZoomNeutral ? 'text-slate-700 dark:text-slate-200' : 'text-blue-600 dark:text-blue-400' }} truncate pl-3.5 sm:pl-4">
                        {{ $zoomScheduleText }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Aksi (Tombol Detail & Tombol Daftar / Sudah Terdaftar / Disable Menunggu Batch Berikutnya) --}}
        <div class="mt-auto pt-2.5 sm:pt-3.5 border-t border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row gap-1.5 sm:gap-2">
            @if ($isEnrolledInThisBimtek)
                <button type="button" @click="show('{{ $course->id }}')"
                    class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2 text-xs sm:text-[15px] font-bold text-center rounded-md sm:rounded-lg transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                    <span class="sm:hidden">Detail</span>
                    <span class="hidden sm:inline">Lihat Detail</span>
                </button>
                <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                   class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2 text-xs sm:text-[15px] inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-md sm:rounded-lg transition-colors bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs">
                    <span>Sudah Terdaftar</span>
                    <i class="fas fa-arrow-up-right-from-square text-[8px] sm:text-[10px]"></i>
                </a>
            @elseif (!$isRegistrationOpen)
                {{-- Sedang berlangsung dan setelahnya: Munculkan tombol Lihat Detail saja --}}
                <button type="button" @click="show('{{ $course->id }}')"
                    class="w-full py-1.5 sm:py-2 text-xs sm:text-[15px] font-bold text-center rounded-md sm:rounded-lg transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                    <span class="sm:hidden">Detail</span>
                    <span class="hidden sm:inline">Lihat Detail</span>
                </button>
            @else
                <button type="button" @click="show('{{ $course->id }}')"
                    class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2 text-xs sm:text-[15px] font-bold text-center rounded-md sm:rounded-lg transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                    <span class="sm:hidden">Detail</span>
                    <span class="hidden sm:inline">Lihat Detail</span>
                </button>
                <button type="button" @click="askEnroll('{{ $course->id }}')"
                    class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2 text-xs sm:text-[15px] inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-md sm:rounded-lg transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-xs">
                    <span>Daftar</span>
                    <i class="fas fa-arrow-up-right-from-square text-[8px] sm:text-[10px]"></i>
                </button>
            @endif
        </div>
    </div>
</div>
