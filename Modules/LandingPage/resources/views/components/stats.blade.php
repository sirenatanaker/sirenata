@props([
    'stats' => [],
])

@php
    $parseStat = function ($val, $defaultSuffix = '') {
        $val = (string) $val;
        $num = floatval(preg_replace('/[^0-9.]/', '', $val));
        $suf = preg_replace('/[0-9.]/', '', $val);
        if (strpos($val, '+') !== false && strpos($suf, '+') === false) {
            $suf .= '+';
        }
        return ['num' => $num ?: 0, 'suf' => $suf ?: $defaultSuffix];
    };

    $sProv = $parseStat($stats['provinces'] ?? 38);
    $sReg = $parseStat($stats['regencies'] ?? 514);
    $sRtk = $parseStat($stats['rtk'] ?? '1.2K', '+');
    $sCourse = $parseStat($stats['courses'] ?? 15);

    // Dekorasi halftone 1/4 elips
    $W = 760;
    $H = 420;
    $step = 22;
    $rowH = $step * 0.866;
    $Rx = $W - 10;
    $Ry = $H - 10;
    $dots = [];
    $rows = (int) ceil($H / $rowH);

    for ($j = 0; $j < $rows; $j++) {
        $y = $H - $step / 2 - $j * $rowH;
        $shift = $j % 2 ? $step / 2 : 0;
        for ($i = 0; $i <= (int) ceil($W / $step); $i++) {
            $x = $W - $step / 2 - $shift - $i * $step;
            if ($x < 0 || $y < 0) {
                continue;
            }
            $t = sqrt((($W - $x) / $Rx) ** 2 + (($H - $y) / $Ry) ** 2);
            if ($t > 1) {
                continue;
            }
            $r = round(0.6 + 3.8 * pow(1 - $t, 1.2), 2);
            $o = round(pow(1 - $t, 1.3), 2);
            if ($o < 0.04) {
                continue;
            }
            $dots[] = ['x' => round($x, 2), 'y' => round($y, 2), 'r' => $r, 'o' => $o];
        }
    }
@endphp

<section class="py-10 sm:py-14 lg:py-12 bg-slate-900 relative overflow-hidden">
    <!-- Dekorasi Halftone 1/4 Elips -->
    <div class="absolute bottom-0 right-0 w-[380px] h-[210px] sm:w-[760px] sm:h-[420px] pointer-events-none opacity-40"
        aria-hidden="true">
        <svg class="w-full h-full" viewBox="0 0 {{ $W }} {{ $H }}" fill="#ffffff"
            xmlns="http://www.w3.org/2000/svg">
            @foreach ($dots as $dot)
                <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['r'] }}"
                    fill-opacity="{{ $dot['o'] }}" />
            @endforeach
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal-up">
        <!-- Header Kecil Section -->
        <div class="mb-10 text-left">
            <span class="text-xs font-semibold tracking-widest text-slate-400 uppercase block mb-1">Statistik</span>
            <h3 class="section-title section-title-stat text-white tracking-tight">
                SIRENATA dalam Angka
            </h3>
        </div>

        <!-- Grid Statistik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Item 1 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Jangkauan Wilayah</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $sProv['num'] }}" data-suffix="{{ $sProv['suf'] }}"
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Provinsi yang telah terintegrasi dalam sistem perencanaan.</p>
            </div>

            <!-- Item 2 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Pemerintah Daerah</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $sReg['num'] }}" data-suffix="{{ $sReg['suf'] }}"
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Kabupaten/Kota yang mengunggah dan menyusun dokumen RTK.</p>
            </div>

            <!-- Item 3 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Dokumen Terarsip</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $sRtk['num'] }}" data-suffix="{{ $sRtk['suf'] }}"
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Total Rencana Tenaga Kerja yang telah diverifikasi.</p>
            </div>

            <!-- Item 4 -->
            <div class="border-l-2 border-slate-700 pl-5 py-2 flex flex-col justify-between hover:border-amber-400 transition-colors duration-300">
                <p class="text-xs font-semibold text-slate-400 mb-2">Peningkatan Kompetensi</p>
                <h4 class="text-4xl sm:text-5xl font-extrabold text-white mb-2 stat-counter tracking-tight"
                    data-target="{{ $sCourse['num'] }}" data-suffix="{{ $sCourse['suf'] }}"
                    style="font-family: 'Oswald', sans-serif;">0</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Pelatihan aktif di e-learning LMS untuk aparatur daerah.</p>
            </div>
        </div>
    </div>
</section>
