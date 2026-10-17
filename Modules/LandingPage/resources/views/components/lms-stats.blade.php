@props([
    'lmsStats' => [],
    'stats' => [],
    'courses' => collect(),
])

@php
    $lmsCourseCount = $lmsStats['courseCount'] ?? $courses->count();
    $lmsModuleCount = $lmsStats['moduleCount'] ?? 0;
    $lmsCategoryCount = $lmsStats['categoryCount'] ?? 0;
    $lmsParticipantCount = $lmsStats['participantCount'] ?? ($stats['participants'] ?? 1200);

    // Dekorasi halftone 1/4 elips
    $lmsW = 640;
    $lmsH = 260;
    $lmsStep = 22;
    $lmsRowH = $lmsStep * 0.866;
    $lmsRx = $lmsW - 10;
    $lmsRy = $lmsH - 10;
    $lmsDots = [];
    $lmsRows = (int) ceil($lmsH / $lmsRowH);

    for ($j = 0; $j < $lmsRows; $j++) {
        $y = $lmsH - $lmsStep / 2 - $j * $lmsRowH;
        $shift = $j % 2 ? $lmsStep / 2 : 0;
        for ($i = 0; $i <= (int) ceil($lmsW / $lmsStep); $i++) {
            $x = $lmsW - $lmsStep / 2 - $shift - $i * $lmsStep;
            if ($x < 0 || $y < 0) {
                continue;
            }
            $t = sqrt((($lmsW - $x) / $lmsRx) ** 2 + (($lmsH - $y) / $lmsRy) ** 2);
            if ($t > 1) {
                continue;
            }
            $r = round(0.6 + 3.8 * pow(1 - $t, 1.2), 2);
            $o = round(pow(1 - $t, 1.3), 2);
            if ($o < 0.04) {
                continue;
            }
            $lmsDots[] = ['x' => round($x, 2), 'y' => round($y, 2), 'r' => $r, 'o' => $o];
        }
    }
@endphp

<section class="py-8 sm:py-10 lg:py-10 bg-slate-900 relative overflow-hidden border-t border-slate-800">
    <!-- Dekorasi Halftone 1/4 Elips -->
    <div class="absolute bottom-0 right-0 w-[320px] h-[130px] sm:w-[640px] sm:h-[260px] pointer-events-none opacity-40"
        aria-hidden="true">
        <svg class="w-full h-full" viewBox="0 0 {{ $lmsW }} {{ $lmsH }}" fill="#ffffff"
            xmlns="http://www.w3.org/2000/svg">
            @foreach ($lmsDots as $dot)
                <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['r'] }}"
                    fill-opacity="{{ $dot['o'] }}" />
            @endforeach
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal-up">
        <div class="mb-6 text-left">
            <span class="text-xs font-semibold tracking-widest text-slate-400 uppercase block mb-1">Capaian Pelatihan</span>
            <h3 class="section-title section-title-stat text-white tracking-tight">
                Ekosistem Pembelajaran SIRENATA
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            <!-- Item 1 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Total Kursus</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $lmsCourseCount }}" data-suffix=""
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Kursus aktif siap dipelajari oleh aparatur daerah.</p>
            </div>

            <!-- Item 2 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Total Modul Pembelajaran</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $lmsModuleCount }}" data-suffix=""
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Modul dan topik terstruktur yang tersedia di seluruh kursus.</p>
            </div>

            <!-- Item 3 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Kategori Topik</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $lmsCategoryCount }}" data-suffix=""
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Bidang kompetensi mulai dari makro, mikro, hingga kebijakan ketenagakerjaan.</p>
            </div>

            <!-- Item 4 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Peserta Terdaftar</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $lmsParticipantCount }}" data-suffix="+"
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Aparatur dan perencana yang aktif meningkatkan kompetensi.</p>
            </div>
        </div>
    </div>
</section>
