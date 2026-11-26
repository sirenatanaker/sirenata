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
    if ($phaseKey === 'completed') {
        return;
    }
    $isRegistrationOpen = ($phaseKey === 'registration' && $activeBimtek->status === 'open_registration');

    $execStart = $activeBimtek->start_date;
    $execEnd   = $activeBimtek->end_date ?? $activeBimtek->start_date;
    $regEnd    = $activeBimtek->registration_end_at ?? $activeBimtek->start_date?->copy()->subDay();
    $evalEnd   = $activeBimtek->assessment_end_at ?? $execEnd?->copy()->addDays(3);

    $waveSubtitle = $activeBimtek->formatted_wave_subtitle;
    $fullBatchName = $activeBimtek->batch_name
        ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $activeBimtek->batch_name)
        : $waveSubtitle;

    if ($phaseKey === 'registration') {
        $timelineLine = $regEnd ? 'Daftar s/d ' . $regEnd->translatedFormat('d M Y') : 'Pendaftaran dibuka';
    } elseif ($phaseKey === 'assessment') {
        $timelineLine = $evalEnd ? 'Evaluasi s/d ' . $evalEnd->translatedFormat('d M Y') : 'Periode evaluasi';
    } elseif ($phaseKey === 'completed') {
        $timelineLine = $execEnd ? 'Selesai ' . $execEnd->translatedFormat('d M Y') : 'Pelatihan selesai';
    } else {
        $timelineLine = $execEnd ? 'Pelaksanaan s/d ' . $execEnd->translatedFormat('d M Y') : 'Sedang berlangsung';
    }

    // Sisa kuota (untuk pendaftaran buka)
    $quotaTotal = (int) ($activeBimtek->quota ?? 0);
    $participantsCount = (int) ($activeBimtek->participants_count ?? 0);
    $remainingQuota = $quotaTotal > 0 ? max(0, $quotaTotal - $participantsCount) : null;
    $isQuotaLow = $remainingQuota !== null && ($remainingQuota <= 10 || ($quotaTotal > 0 && ($participantsCount / $quotaTotal) >= 0.8));
@endphp

<div @click="show('{{ $course->id }}')"
    class="group flex flex-col h-full bg-transparent sm:bg-white sm:dark:bg-slate-800/90 rounded-none sm:rounded-md border-0 sm:border border-slate-200 dark:border-slate-700/80 shadow-none sm:shadow-sm hover:shadow-lg hover:border-[#13416B]/30 dark:hover:border-slate-500 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden w-full cursor-pointer">

    {{-- Thumbnail Bersih + 1 Badge Status (Tanpa Overlay Teks di Dasar Gambar) --}}
    <div class="relative w-full aspect-[4/3] sm:aspect-video overflow-hidden bg-slate-100 dark:bg-slate-900/50 rounded-md sm:rounded-b-none sm:rounded-t-md">
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
            <span class="absolute top-0 right-0 z-10 px-2 sm:px-3 py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white {{ $curPhase['badge_bg'] ?? 'bg-[#0B5CFF]' }} shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="{{ $isDonePhase ? 'fas fa-check-circle' : 'far fa-clock' }} text-[7px] sm:text-[9px]"></i>
                <span>{{ $curPhase['badge'] ?? 'Pendaftaran Buka' }}</span>
            </span>
        @endif
    </div>

    {{-- Konten Text Ringkas & Lega --}}
    <div class="px-0 pt-4 pb-0 sm:p-5 flex flex-col flex-1">
        {{-- Label Gelombang Standar di Atas Judul: Gelombang [nomor] [wilayah] [tahun] --}}
        <p class="text-[11px] sm:text-sm font-semibold text-[#547996] truncate mb-2" title="{{ $fullBatchName }}">
            {{ $waveSubtitle }}
        </p>

        {{-- 1 Judul Utama (Nama Kursus) --}}
        <h3 class="text-xs sm:text-base font-bold text-slate-800 dark:text-white leading-snug group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors line-clamp-2 min-h-[2rem] sm:min-h-[2.75rem]"
            title="{{ $course->name }}">
            {{ $course->name }}
        </h3>

        {{-- Info Jadwal Inti + Kuota / Jumlah Peserta di Bawahnya --}}
        <div class="mt-3.5 sm:mt-4 flex flex-col gap-2 sm:gap-2.5 text-[11px] sm:text-sm text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5 min-w-0 truncate">
                <i class="far fa-calendar-alt shrink-0 text-slate-400 text-xs"></i>
                <span class="truncate font-medium text-slate-600 dark:text-slate-300">{{ $timelineLine }}</span>
            </span>
            @if ($isRegistrationOpen)
                @if ($remainingQuota !== null)
                    <span class="inline-flex items-center gap-1.5 shrink-0 font-semibold {{ $isQuotaLow ? 'text-amber-600' : 'text-slate-500' }} whitespace-nowrap">
                        <i class="far fa-user shrink-0 text-xs {{ $isQuotaLow ? 'text-amber-500' : 'text-slate-400' }}"></i>
                        <span>Sisa {{ $remainingQuota }} kuota</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 shrink-0 font-medium text-slate-500 whitespace-nowrap">
                        <i class="far fa-user shrink-0 text-xs text-slate-400"></i>
                        <span>{{ $participantsCount }} peserta terdaftar</span>
                    </span>
                @endif
            @else
                <span class="inline-flex items-center gap-1.5 shrink-0 font-medium text-slate-500 whitespace-nowrap">
                    <i class="far fa-user shrink-0 text-xs text-slate-400"></i>
                    @if ($quotaTotal > 0)
                        <span>{{ $participantsCount }} dari {{ $quotaTotal }} peserta</span>
                    @else
                        <span>{{ $participantsCount }} peserta terdaftar</span>
                    @endif
                </span>
            @endif
        </div>

        {{-- Footer Aksi --}}
        <div class="mt-auto pt-4 sm:pt-5 flex flex-wrap items-center justify-start gap-1.5 sm:gap-2">
            @if ($isEnrolledInThisBimtek)
                <button type="button" @click.stop="show('{{ $course->id }}')"
                    class="px-2.5 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-sm font-bold text-center rounded-md transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5 cursor-pointer">
                    Lihat detail
                </button>
                <a href="{{ route('user.course.my-course.detail', $course->slug) }}" @click.stop
                   class="px-2.5 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-md transition-colors bg-emerald-600 text-white hover:bg-emerald-700 shadow-2xs">
                    <span>Sudah Terdaftar</span>
                    <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                </a>
            @elseif (!$isRegistrationOpen)
                <button type="button" @click.stop="show('{{ $course->id }}')"
                    class="px-3 sm:px-5 py-1.5 sm:py-2 text-[11px] sm:text-sm font-bold text-center rounded-md transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5 cursor-pointer">
                    Lihat detail
                </button>
            @else
                <button type="button" @click.stop="askEnroll('{{ $course->id }}')"
                    class="px-3.5 sm:px-5 py-1.5 sm:py-2 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-md transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-2xs cursor-pointer">
                    <span>Daftar</span>
                    <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                </button>
            @endif
        </div>
    </div>
</div>
