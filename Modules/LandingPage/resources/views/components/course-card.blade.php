@props([
    'course',
])

@php
    $thumbUrl = $course->thumbnail
        ? (filter_var($course->thumbnail, FILTER_VALIDATE_URL)
            ? $course->thumbnail
            : Storage::url($course->thumbnail))
        : null;

    $initials = '';
    if (!$thumbUrl) {
        $words = explode(' ', trim($course->name ?? ''));
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }
        if (strlen($initials) < 2) {
            $initials = substr(strtoupper($course->name ?? 'K'), 0, 2);
        }
    }

    $modulesCount = $course->sections_count ?? (isset($course->sections) ? collect($course->sections)->count() : 0);
@endphp

<a href="{{ route('user.course.my-course.detail', $course->slug) }}"
    class="flex h-full w-full flex-col overflow-hidden rounded-md border border-slate-200/80 bg-white transition-all duration-300 group hover:-translate-y-1 hover:border-[#13416B]/30 hover:shadow-xl">

    <!-- Thumbnail Kursus -->
    <div class="relative h-[140px] bg-[#184A78] flex items-center justify-center overflow-hidden lg:h-[180px]">
        @if ($thumbUrl)
            <img src="{{ $thumbUrl }}" alt="{{ $course->name }}"
                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy">
        @else
            <h2 class="text-3xl md:text-2xl lg:text-4xl font-extrabold text-white/90 leading-none tracking-wider select-none">
                {{ $initials }}
            </h2>
        @endif
    </div>

    <!-- Detail Konten -->
    <div class="flex min-h-[110px] flex-col justify-center p-3 md:p-2 lg:min-h-[150px] lg:p-5 text-left">
        <h3 class="mb-1.5 line-clamp-2 font-bold text-slate-800 text-sm md:text-xs lg:mb-2 lg:text-base group-hover:text-[#13416B] transition-colors">
            {{ $course->name }}
        </h3>
        <p class="line-clamp-3 text-[11px] md:text-[10px] lg:text-xs leading-relaxed text-slate-500">
            {{ $course->description ?? "Modul pelatihan komprehensif untuk mendalami materi {$course->name} secara terstruktur." }}
        </p>
    </div>

    <!-- Card Footer -->
    <div class="flex items-center justify-between border-t border-slate-100 px-3 pb-3 pt-2 md:px-2 md:pb-2 lg:px-5 lg:pb-5 lg:pt-3 text-[11px] md:text-[10px] lg:text-xs font-medium text-slate-500">
        <span class="flex items-center gap-1.5 rounded-md border border-slate-100 bg-slate-50 px-2 py-1 text-slate-600">
            <i class="far fa-folder-open text-slate-400 text-[10px] sm:text-xs"></i>
            <span>{{ $modulesCount }} Modul</span>
        </span>
    </div>
</a>
