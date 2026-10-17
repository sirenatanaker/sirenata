@props([
    'course',
])

@php
    $pct = (int) ($course->progress ?? 0);
    $isDone = ($course->status ?? '') === 'completed' || $pct >= 100;
    if ($isDone) {
        $barClass = 'bg-emerald-500';
        $pctText = 'text-emerald-600';
    } elseif ($pct >= 50) {
        $barClass = 'bg-[#13416B]';
        $pctText = 'text-[#13416B]';
    } else {
        $barClass = 'bg-amber-500';
        $pctText = 'text-amber-600';
    }
@endphp

<a href="{{ route('user.course.my-course.detail', $course->slug) }}"
    class="group flex flex-col bg-white rounded-md border border-slate-200 shadow-sm hover:shadow-xl hover:border-[#13416B]/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">

    {{-- Thumbnail & Badge --}}
    <div class="relative h-24 sm:h-48 overflow-hidden bg-slate-100">
        @if (!empty($course->thumbnail_url))
            <img src="{{ $course->thumbnail_url }}"
                alt="{{ $course->course_name ?? $course->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-image text-2xl sm:text-4xl text-slate-300"></i>
            </div>
        @endif

        {{-- Badge Kategori --}}
        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 max-w-[60%] sm:max-w-none">
            <span class="block truncate px-1.5 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-800 bg-white/90 backdrop-blur-sm rounded shadow-sm">
                {{ $course->category->name ?? 'Umum' }}
            </span>
        </div>

        {{-- Badge Status (Selesai) --}}
        @if ($isDone)
            <div class="absolute top-2 right-2 sm:top-3 sm:right-3">
                <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 border border-emerald-200 rounded shadow-sm flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> <span class="hidden sm:inline">Selesai</span>
                </span>
            </div>
        @endif
    </div>

    {{-- Konten Text --}}
    <div class="p-3 sm:p-5 flex flex-col flex-1">
        <h3 class="text-xs sm:text-base font-bold text-slate-800 leading-snug mb-1.5 sm:mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2"
            title="{{ $course->course_name ?? $course->name }}">
            {{ $course->course_name ?? $course->name }}
        </h3>

        {{-- INFO COURSE: Modul & Materi --}}
        <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-2.5 sm:mb-3 text-[9px] sm:text-xs font-medium text-slate-500">
            <span class="flex items-center gap-1 sm:gap-1.5 bg-slate-50 px-1.5 py-0.5 sm:px-2 sm:py-1 rounded border border-slate-100">
                <i class="fas fa-layer-group text-slate-400"></i> {{ $course->total_modul ?? 0 }} Modul
            </span>
            <span class="flex items-center gap-1 sm:gap-1.5 bg-[#13416B]/5 text-[#13416B] px-1.5 py-0.5 sm:px-2 sm:py-1 rounded border border-[#13416B]/10">
                <i class="fas fa-users"></i> {{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta
            </span>
        </div>

        <p class="hidden sm:line-clamp-2 text-xs text-slate-500 mb-5 leading-relaxed flex-1">
            {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
        </p>

        <div class="mt-auto pt-3 sm:pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2 text-[9px] sm:text-[11px] font-bold uppercase tracking-wider">
                <span class="text-slate-500">Progress</span>
                <span class="{{ $pctText }}">{{ $pct }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-2.5 sm:mb-4">
                <div class="{{ $barClass }} h-full rounded-full transition-all duration-700"
                    style="width: {{ $pct }}%"></div>
            </div>

            @if ($isDone)
                <div class="w-full py-1.5 sm:py-2.5 text-[10px] sm:text-xs font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white border border-emerald-200 group-hover:border-emerald-600">
                    <i class="fas fa-award mr-1"></i> Lihat Sertifikat
                </div>
            @elseif ($pct <= 0)
                <div class="w-full py-1.5 sm:py-2.5 text-[10px] sm:text-xs font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-emerald-600 text-white border border-emerald-600 shadow-sm group-hover:bg-emerald-700 group-hover:border-emerald-700">
                    <i class="fas fa-play mr-1"></i> Mulai Belajar
                </div>
            @else
                <div class="w-full py-1.5 sm:py-2.5 text-[10px] sm:text-xs font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-slate-50 text-slate-700 border border-slate-200 group-hover:bg-[#13416B] group-hover:text-white group-hover:border-[#13416B]">
                    Lanjutkan Belajar
                </div>
            @endif
        </div>
    </div>
</a>
