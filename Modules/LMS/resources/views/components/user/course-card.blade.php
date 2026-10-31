@props([
    'course',
])

@php
    $pct = (int) ($course->progress ?? 0);
    $isDone = ($course->status ?? '') === 'completed' || $pct >= 100;
    if ($isDone) {
        $barClass = 'bg-emerald-500 dark:bg-emerald-400';
        $pctText = 'text-emerald-600 dark:text-emerald-400';
    } else {
        $barClass = 'bg-amber-500 dark:bg-amber-400';
        $pctText = 'text-amber-600 dark:text-amber-400';
    }
@endphp

<a href="{{ route('user.course.my-course.detail', $course->slug) }}"
    class="group flex flex-col bg-transparent sm:bg-white sm:dark:bg-slate-800/90 rounded-none sm:rounded-md border-0 sm:border border-slate-200 dark:border-slate-700/80 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 dark:hover:border-slate-500 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

    {{-- Thumbnail & Badge (Persegi Panjang 16:9 Aspect Video) --}}
    <div class="relative w-full aspect-video overflow-hidden bg-slate-100 dark:bg-slate-900/50 rounded-t-md">
        @if (!empty($course->thumbnail_url))
            <img src="{{ $course->thumbnail_url }}"
                alt="{{ $course->course_name ?? $course->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-image text-2xl sm:text-4xl text-slate-300 dark:text-slate-600"></i>
            </div>
        @endif

        @if (!empty($course->has_bimtek))
            {{-- Badge Bimtek Online (Mojok Kiri Atas) --}}
            <span class="absolute top-0 left-0 z-10 px-2.5 sm:px-3 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-blue-600 shadow-xs flex items-center gap-1 rounded-none rounded-br-md rounded-tl-md">
                <i class="fas fa-video text-[8px] sm:text-[9px]"></i>
                <span>Bimtek Online</span>
            </span>
        @endif

        {{-- Badge Kategori (Mojok Kanan Atas seperti di card katalog) --}}
        <span class="absolute top-0 right-0 z-10 max-w-[80%] px-2.5 sm:px-3 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-white bg-[#13416B] shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
            <i class="fas fa-tag text-[8px] sm:text-[9px] flex-shrink-0"></i>
            <span class="truncate">{{ $course->category->name ?? 'Umum' }}</span>
        </span>
    </div>

    {{-- Konten Text --}}
    <div class="p-0 pt-2.5 sm:p-5 flex flex-col flex-1">
        <h3 class="text-xs sm:text-base font-bold text-slate-800 dark:text-white leading-snug mb-1.5 sm:mb-2 group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors line-clamp-2"
            title="{{ $course->course_name ?? $course->name }}">
            {{ $course->course_name ?? $course->name }}
        </h3>

        {{-- INFO COURSE: Modul & Peserta (Kalem & Profesional) --}}
        <div class="flex items-center gap-2 sm:gap-2.5 mb-2 sm:mb-2.5 text-[10px] sm:text-xs font-medium text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5">
                <i class="far fa-folder-open text-slate-400 dark:text-slate-500 text-[10px] sm:text-xs"></i>
                <span>{{ $course->total_modul ?? 0 }} Modul</span>
            </span>
            <span class="text-slate-300 dark:text-slate-600 select-none">•</span>
            <span class="inline-flex items-center gap-1.5">
                <i class="far fa-user text-slate-400 dark:text-slate-500 text-[10px] sm:text-xs"></i>
                <span>{{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta</span>
            </span>
        </div>

        <p class="hidden sm:line-clamp-2 text-xs text-slate-500 dark:text-slate-200 mb-5 leading-relaxed flex-1">
            {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
        </p>

        <div class="mt-auto pt-3 sm:pt-4 border-t border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2 text-[9px] sm:text-[11px] font-bold uppercase tracking-wider">
                <span class="text-slate-500 dark:text-slate-300">Progress</span>
                <span class="{{ $pctText }}">{{ $pct }}%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700/80 rounded-full h-1.5 overflow-hidden mb-2.5 sm:mb-4">
                <div class="{{ $barClass }} h-full rounded-full transition-all duration-700 shadow-xs"
                    style="width: {{ $pct }}%"></div>
            </div>

            @if ($isDone)
                <div class="w-full py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-center rounded-md sm:rounded-lg transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-xs group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-award mr-1"></i> Lihat Sertifikat
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
