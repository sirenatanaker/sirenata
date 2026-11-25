@props([
    'course',
])

@php
    $pct = min(100, max(0, (int) ($course->progress ?? 0)));
    $isDone = ($course->status ?? '') === 'completed' || $pct >= 100;
    $curPhase = $course->bimtek_event?->current_timeline_phase ?? null;
    $phaseKey = $curPhase['phase'] ?? null;
    $isEventEnded = $phaseKey === 'completed';
    $isWaiting = !$isDone && !$isEventEnded && (!empty($course->is_waiting_for_start) || !empty($course->is_registration_phase) || $phaseKey === 'registration');
    $isRegistrationPhase = ($phaseKey === 'registration') || !empty($course->is_registration_phase);

    $execStart = $course->bimtek_event?->start_date;
    $execEnd = $course->bimtek_event?->end_date ?? $execStart;
    $evalEnd = $course->bimtek_event?->assessment_end_at ?? $execEnd?->copy()->addDays(3);
    $startDateLabel = $execStart ? $execStart->translatedFormat('d M Y') : null;
    $execEndLabel = $execEnd ? $execEnd->translatedFormat('d M Y') : null;
    $evalEndLabel = $evalEnd ? $evalEnd->translatedFormat('d M Y') : null;

    // Subjudul standar: "Gelombang [nomor] [wilayah] [tahun]"
    $waveSubtitle = $course->bimtek_event?->formatted_wave_subtitle;
    $fullBatchName = !empty($course->bimtek_event?->batch_name)
        ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $course->bimtek_event->batch_name)
        : $waveSubtitle;
    $participantsCount = (int) ($course->batch_students_count ?? $course->students_count ?? 0);
    $quotaTotal = (int) ($course->bimtek_event?->quota ?? 0);
@endphp

<a href="{{ route('user.course.my-course.detail', $course->slug) }}"
    class="group flex flex-col bg-transparent sm:bg-white sm:dark:bg-slate-800/90 rounded-none sm:rounded-md border-0 sm:border border-slate-200 dark:border-slate-700/80 shadow-none sm:shadow-sm hover:shadow-lg hover:border-[#13416B]/30 dark:hover:border-slate-500 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">

    {{-- Thumbnail Bersih + 1 Badge Status (Tanpa Overlay Teks di Dasar Gambar) --}}
    <div class="relative w-full aspect-[4/3] sm:aspect-video overflow-hidden bg-slate-100 dark:bg-slate-900/50 rounded-t-md">
        @if (!empty($course->thumbnail_url))
            <img src="{{ $course->thumbnail_url }}"
                alt="{{ $course->course_name ?? $course->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-image text-2xl sm:text-4xl text-slate-300 dark:text-slate-600"></i>
            </div>
        @endif

        {{-- Badge Status Event di Pojok Kanan Atas --}}
        @if ($isDone)
            <span class="absolute top-0 right-0 z-10 px-2.5 sm:px-3 py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-emerald-600 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="fas fa-check-circle text-[7px] sm:text-[9px]"></i>
                <span>Selesai</span>
            </span>
        @elseif ($isEventEnded)
            <span class="absolute top-0 right-0 z-10 px-2.5 sm:px-3 py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-slate-600 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="far fa-clock text-[7px] sm:text-[9px]"></i>
                <span>Periode Berakhir</span>
            </span>
        @elseif ($curPhase)
            <span class="absolute top-0 right-0 z-10 px-2.5 sm:px-3 py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white {{ $curPhase['badge_bg'] ?? 'bg-[#0B5CFF]' }} shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="far fa-clock text-[7px] sm:text-[9px]"></i>
                <span>{{ $curPhase['badge'] ?? 'Bimtek Online' }}</span>
            </span>
        @elseif ($pct > 0)
            <span class="absolute top-0 right-0 z-10 px-2.5 sm:px-3 py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-amber-500 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="far fa-clock text-[7px] sm:text-[9px]"></i>
                <span>Sedang Berlangsung</span>
            </span>
        @endif
    </div>

    {{-- Konten Text Ringkas & Lega --}}
    <div class="p-0 pt-3.5 sm:p-5 flex flex-col flex-1">
        {{-- Label Gelombang Standar di Atas Judul: Gelombang [nomor] [wilayah] [tahun] --}}
        @if ($waveSubtitle)
            <p class="text-xs sm:text-sm font-semibold text-[#547996] truncate mb-1.5" title="{{ $fullBatchName }}">
                {{ $waveSubtitle }}
            </p>
        @endif

        {{-- 1 Judul Utama (Nama Kursus) --}}
        <h3 class="text-xs sm:text-base font-bold text-slate-800 dark:text-white leading-snug group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors line-clamp-2 min-h-[2rem] sm:min-h-[2.75rem]"
            title="{{ $course->course_name ?? $course->name }}">
            {{ $course->course_name ?? $course->name }}
        </h3>

        {{-- Info Ringkas Kontekstual (Timeline + Jumlah Peserta + Progress Bar Tipis saat Berlangsung) --}}
        <div class="mt-3.5 sm:mt-4 flex flex-col gap-2 sm:gap-2.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
            @if ($isDone)
                <p class="inline-flex items-center gap-1.5 font-medium text-emerald-700 truncate">
                    <i class="fas fa-check-circle shrink-0 text-xs"></i>
                    <span class="truncate">Pelatihan tuntas — e-Sertifikat tersedia</span>
                </p>
            @elseif ($isEventEnded)
                <p class="inline-flex items-center gap-1.5 font-medium text-slate-500 truncate">
                    <i class="far fa-calendar-alt shrink-0 text-xs text-slate-400"></i>
                    <span class="truncate">{{ $execEndLabel ? 'Berakhir ' . $execEndLabel : 'Periode telah berakhir' }}</span>
                </p>
            @elseif ($isWaiting)
                <p class="inline-flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-300 truncate">
                    <i class="far fa-calendar-alt shrink-0 text-xs text-slate-400"></i>
                    <span class="truncate">{{ $startDateLabel ? 'Pelaksanaan mulai ' . $startDateLabel : 'Menunggu jadwal' }}</span>
                </p>
            @else
                <p class="inline-flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-300 truncate max-w-full">
                    <i class="far fa-calendar-alt shrink-0 text-xs text-slate-400"></i>
                    <span class="truncate">
                        @if ($phaseKey === 'assessment' && $evalEndLabel)
                            Evaluasi s/d {{ $evalEndLabel }}
                        @elseif ($execEndLabel)
                            Pelaksanaan s/d {{ $execEndLabel }}
                        @else
                            Sedang berlangsung
                        @endif
                    </span>
                </p>
            @endif

            {{-- Jumlah Peserta Bergabung --}}
            <span class="inline-flex items-center gap-1.5 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap truncate">
                <i class="far fa-user shrink-0 text-xs text-slate-400"></i>
                @if ($quotaTotal > 0)
                    <span>{{ $participantsCount }} dari {{ $quotaTotal }} peserta</span>
                @else
                    <span>{{ $participantsCount }} peserta bergabung</span>
                @endif
            </span>
        </div>

        {{-- Footer Tombol Aksi --}}
        <div class="mt-auto pt-3.5 sm:pt-4">
            @if ($isDone)
                <div class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-bold text-center rounded-md transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-2xs group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-award text-[10px] sm:text-xs"></i>
                    <span>Lihat Sertifikat</span>
                </div>
            @elseif ($isEventEnded)
                <div class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-bold text-center rounded-md transition-colors bg-slate-600 text-white border border-slate-600 shadow-2xs group-hover:bg-slate-700 group-hover:border-slate-700">
                    <i class="far fa-folder-open text-[10px] sm:text-xs"></i>
                    <span>Lihat Materi</span>
                </div>
            @elseif ($isWaiting)
                <div class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-bold text-center rounded-md transition-colors bg-[#0B5CFF] text-white border border-[#0B5CFF] shadow-2xs group-hover:bg-blue-700 group-hover:border-blue-700">
                    <i class="far fa-calendar-check text-[10px] sm:text-xs"></i>
                    <span>Menunggu Pelaksanaan</span>
                </div>
            @elseif ($pct <= 0)
                <div class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-bold text-center rounded-md transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-2xs group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-play text-[10px] sm:text-xs"></i>
                    <span>Mulai Belajar</span>
                </div>
            @else
                <div class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 text-xs sm:text-sm font-bold text-center rounded-md transition-colors bg-amber-500 text-white border border-amber-500 shadow-2xs group-hover:bg-amber-600 group-hover:border-amber-600">
                    <span>Lanjutkan Belajar</span>
                </div>
            @endif
        </div>
    </div>
</a>
