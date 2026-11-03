@props([
    'course',
])

@php
    $pct = (int) ($course->progress ?? 0);
    $isDone = ($course->status ?? '') === 'completed' || $pct >= 100;
@endphp

<a href="{{ route('user.course.my-course.detail', $course->slug) }}"
    class="group flex flex-col bg-transparent sm:bg-white sm:dark:bg-slate-800/90 rounded-none sm:rounded-md border-0 sm:border border-slate-200 dark:border-slate-700/80 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 dark:hover:border-slate-500 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

    {{-- Thumbnail & Badge (Mobile: aspect-[4/3] Lebih Tinggi, Desktop: sm:aspect-video 16:9) --}}
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
        @if (!empty($course->bimtek_event))
            @php
                $curPhase = $course->bimtek_event->current_timeline_phase ?? null;
                $isDonePhase = ($curPhase['phase'] ?? '') === 'completed';
            @endphp
            @if ($curPhase)
                <span class="absolute top-0 right-0 z-10 {{ $isDonePhase ? 'px-3 sm:px-3.5 py-1 sm:py-1.5 text-[9px] sm:text-[11px]' : 'px-2.5 sm:px-3 py-0.5 sm:py-1 text-[8px] sm:text-[10px]' }} font-bold uppercase tracking-wider text-white {{ $curPhase['badge_bg'] ?? 'bg-[#0B5CFF]' }} shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                    <i class="{{ $isDonePhase ? 'fas fa-check-circle' : 'far fa-clock' }} text-[7px] sm:text-[9px]"></i>
                    <span>{{ $curPhase['badge'] ?? 'Bimtek Online' }}</span>
                </span>
            @endif
        @elseif ($isDone)
            <span class="absolute top-0 right-0 z-10 px-3 sm:px-3.5 py-1 sm:py-1.5 text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-white bg-emerald-600 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="fas fa-check-circle text-[7px] sm:text-[9px]"></i>
                <span>Selesai</span>
            </span>
        @elseif ($pct > 0)
            <span class="absolute top-0 right-0 z-10 px-2.5 sm:px-3 py-0.5 sm:py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-amber-500 shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="far fa-clock text-[7px] sm:text-[9px]"></i>
                <span>Sedang Berlangsung</span>
            </span>
        @endif

        {{-- Nama Event di Dasar Thumbnail (Overlay Lembut & Icon Putih) --}}
        @if (!empty($course->bimtek_event?->batch_name))
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 via-black/25 to-transparent px-2.5 sm:px-3 pt-4 pb-1.5 sm:pb-2 flex items-center gap-1.5 text-white z-10">
                <i class="fas fa-satellite-dish text-[9px] sm:text-[10px] text-white shrink-0"></i>
                <span class="text-[10px] sm:text-xs font-medium truncate tracking-wide text-white drop-shadow-xs"
                      title="{{ $course->bimtek_event->batch_name }}">
                    {{ $course->bimtek_event->batch_name }}
                </span>
            </div>
        @endif
    </div>

    {{-- Konten Text --}}
    <div class="p-0 pt-2.5 sm:p-5 flex flex-col flex-1">
        
        {{-- Judul Kursus (Tampil Utama & Bersih) --}}
        <h3 class="text-xs sm:text-base font-bold text-slate-800 dark:text-white leading-snug mb-2 sm:mb-2.5 group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors line-clamp-2 min-h-[2rem] sm:min-h-[3rem] flex items-start"
            title="{{ $course->course_name ?? $course->name }}">
            {{ $course->course_name ?? $course->name }}
        </h3>

        {{-- Info Ringkas (Jadwal Periode & Zoom dengan Data di Bawah Label agar Tidak Kepotong) --}}
        <div class="flex flex-col gap-2 sm:gap-2.5 mb-3 sm:mb-4">
            {{-- Jadwal per Periode (Data di Baris Bawah) --}}
            @if (!empty($course->periode_dates))
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                        <i class="far fa-calendar-alt shrink-0 text-[9px] sm:text-[10px]"></i>
                        <span>{{ $course->periode_label ?? 'Jadwal' }}</span>
                    </div>
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-200 truncate pl-3.5 sm:pl-4">
                        {{ $course->periode_dates }}
                    </div>
                </div>
            @elseif (!empty($course->bimtek_dates))
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                        <i class="far fa-calendar-alt shrink-0 text-[9px] sm:text-[10px]"></i>
                        <span>Periode Pelaksanaan</span>
                    </div>
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-200 truncate pl-3.5 sm:pl-4">
                        {{ $course->bimtek_dates }}
                    </div>
                </div>
            @else
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                        <i class="far fa-clock shrink-0 text-[9px] sm:text-[10px]"></i>
                        <span>Akses Pelatihan</span>
                    </div>
                    <div class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-200 truncate pl-3.5 sm:pl-4">
                        Fleksibel (Kapan Saja)
                    </div>
                </div>
            @endif

            {{-- Jadwal Zoom Khusus Saat Status Sedang Berlangsung (Data di Baris Bawah) --}}
            @if (!empty($course->zoom_schedule_text))
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-blue-500 font-medium">
                        <i class="fas fa-video shrink-0 text-[9px] sm:text-[10px]"></i>
                        <span>Jadwal Sesi Zoom</span>
                    </div>
                    <div class="text-[11px] sm:text-xs font-semibold text-blue-600 dark:text-blue-400 truncate pl-3.5 sm:pl-4">
                        {{ $course->zoom_schedule_text }}
                    </div>
                </div>
            @endif

            {{-- Info Dinamis: Jumlah Peserta saat Pendaftaran / Topik saat Selesai atau Mandiri --}}
            @if (!empty($course->is_registration_phase))
                {{-- Saat Pendaftaran: Tampilkan Jumlah Peserta menggantikan modul dan topik --}}
                <div class="flex items-center gap-1.5 min-w-0 text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">
                    <i class="far fa-user text-slate-400 shrink-0 text-[10px] sm:text-xs"></i>
                    <span>{{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta Terdaftar</span>
                </div>
            @elseif ($isDone)
                {{-- Saat Selesai: Tampilkan Kelulusan & Modul --}}
                <div class="flex items-center gap-1.5 min-w-0 text-[10px] sm:text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                    <i class="fas fa-check-circle text-emerald-500 shrink-0 text-[10px] sm:text-xs"></i>
                    <span>{{ $course->total_modul ?? 0 }} Modul</span>
                    <span class="text-slate-300 dark:text-slate-600 select-none">•</span>
                    <span>Semua Topik Selesai</span>
                </div>
            @elseif (empty($course->bimtek_event))
                {{-- Kursus Mandiri Belum Selesai: Tampilkan Progres Modul & Topik --}}
                <div class="flex items-center gap-1.5 min-w-0 text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">
                    <i class="far fa-folder-open text-slate-400 shrink-0 text-[10px] sm:text-xs"></i>
                    <span>{{ $course->total_modul ?? 0 }} Modul</span>
                    <span class="text-slate-300 dark:text-slate-600 select-none">•</span>
                    <span>{{ $course->completed_materi ?? 0 }}/{{ $course->total_materi ?? 0 }} Topik Selesai</span>
                </div>
            @endif
            {{-- Saat Bimtek Sedang Berlangsung: modul & topik tidak ditampilkan agar ringkas dan fokus ke jadwal zoom --}}
        </div>

        {{-- Footer Tombol Aksi (Progress Bar Dihapus) --}}
        <div class="mt-auto pt-2.5 sm:pt-3.5 border-t border-slate-100 dark:border-slate-700/60">
            @if ($isDone)
                <div class="w-full py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-center rounded-md sm:rounded-lg transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-xs group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-award mr-1"></i> Lihat Sertifikat
                </div>
            @elseif (!empty($course->is_waiting_for_start) || !empty($course->is_registration_phase))
                <div class="w-full py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-center rounded-md sm:rounded-lg transition-colors bg-[#0B5CFF] text-white border border-[#0B5CFF] shadow-xs group-hover:bg-blue-700 group-hover:border-blue-700">
                    <i class="far fa-calendar-check mr-1.5"></i> Menunggu Pelaksanaan
                </div>
            @elseif ($pct <= 0)
                <div class="w-full py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-center rounded-md sm:rounded-lg transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-xs group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-play mr-1"></i> Mulai Belajar
                </div>
            @else
                <div class="w-full py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-center rounded-md sm:rounded-lg transition-colors bg-amber-500 text-white border border-amber-500 shadow-xs group-hover:bg-amber-600 group-hover:border-amber-600">
                    Lanjutkan Belajar
                </div>
            @endif
        </div>
    </div>
</a>
