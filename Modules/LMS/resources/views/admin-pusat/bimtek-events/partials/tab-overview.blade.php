{{-- TAB 1: RINGKASAN TIMELINE VERTIKAL 3 TAHAPAN EVENT --}}
@php
    $regState = ($regStart && $regEnd && $now->between($regStart, $regEnd))
        ? 'active'
        : (($regEnd && $now->greaterThan($regEnd)) ? 'done' : 'upcoming');

    $execState = ($execStart && $execEnd && $now->between($execStart, $execEnd))
        ? 'active'
        : (($execEnd && $now->greaterThan($execEnd)) ? 'done' : 'upcoming');

    $evalState = ($evalStart && $evalEnd && $now->between($evalStart, $evalEnd))
        ? 'active'
        : (($evalEnd && $now->greaterThan($evalEnd)) ? 'done' : 'upcoming');
@endphp

<div class="relative space-y-5">
    {{-- Garis Vertikal Penghubung Timeline --}}
    <div class="absolute left-7 top-6 bottom-6 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>

    {{-- TAHAP 1: PERIODE PENDAFTARAN --}}
    <div class="relative flex items-start gap-3.5 sm:gap-5">
        <div class="relative z-10 w-14 h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
            {{ $regState === 'done' ? 'bg-emerald-600 text-white border-emerald-600' : ($regState === 'active' ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') }}">
            <span class="text-[10px] font-bold uppercase tracking-normal {{ $regState === 'upcoming' ? 'text-slate-400' : 'text-white/80' }} leading-none">TAHAP</span>
            <span class="text-lg sm:text-xl font-black leading-tight mt-1">1</span>
        </div>

        <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Periode Pendaftaran</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Calon peserta melakukan pendaftaran mandiri melalui katalog pelatihan.
                    </p>
                </div>
                @if ($regState === 'active')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Sedang Dibuka</span>
                @elseif ($regState === 'done')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-600">Selesai</span>
                @else
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-500">Mendatang</span>
                @endif
            </div>

            <div class="inline-flex flex-wrap items-center gap-x-2 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm font-bold text-[#13416B]">
                @if ($regStart && $regEnd)
                    {{ $regStart->translatedFormat('d M Y') }} s/d {{ $regEnd->translatedFormat('d M Y') }}
                @else
                    Otomatis sebelum pelaksanaan
                @endif
            </div>
        </div>
    </div>

    {{-- TAHAP 2: PERIODE PELAKSANAAN & LIVE ZOOM --}}
    <div class="relative flex items-start gap-3.5 sm:gap-5">
        <div class="relative z-10 w-14 h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
            {{ $execState === 'done' ? 'bg-emerald-600 text-white border-emerald-600' : ($execState === 'active' ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') }}">
            <span class="text-[10px] font-bold uppercase tracking-normal {{ $execState === 'upcoming' ? 'text-slate-400' : 'text-white/80' }} leading-none">TAHAP</span>
            <span class="text-lg sm:text-xl font-black leading-tight mt-1">2</span>
        </div>

        <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-3.5">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Periode Pelaksanaan &amp; Live Zoom</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Peserta mengikuti rangkaian pembelajaran materi, sesi tatap muka Live Zoom, serta presensi dan pengumpulan evaluasi harian.
                    </p>
                </div>
                @if ($execState === 'active')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-blue-50 text-[#13416B] border border-blue-200">Sedang Berlangsung</span>
                @elseif ($execState === 'done')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-600">Selesai</span>
                @else
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-500">Mendatang</span>
                @endif
            </div>

            <div class="inline-flex flex-wrap items-center gap-x-2 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm font-bold text-[#13416B]">
                @if ($execStart && $execEnd)
                    {{ $execStart->translatedFormat('d M Y') }} s/d {{ $execEnd->translatedFormat('d M Y') }}
                @else
                    Jadwal pelaksanaan belum ditentukan
                @endif
            </div>

            {{-- Detail Ruang & Jadwal Sesi Zoom --}}
            <div class="p-4 rounded-md bg-[#1B568A] text-white space-y-3 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-white">
                        {{ $event->sessions->isNotEmpty() ? 'Jadwal Sesi Zoom Harian (' . $event->sessions->count() . ' Sesi)' : 'Sesi Tatap Muka Zoom Utama' }}
                    </span>
                    @if ($event->zoom_url)
                        <a href="{{ $event->zoom_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-bold bg-white text-[#13416B] hover:bg-blue-50 transition-colors shrink-0">
                            <i class="fas fa-video text-xs"></i>
                            <span>Buka Ruang Zoom</span>
                        </a>
                    @endif
                </div>

                @if ($event->sessions->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
                        @foreach ($event->sessions as $s)
                            <div class="p-3 rounded-md bg-white/10 border border-white/15 space-y-0.5">
                                <p class="font-bold text-white">{{ $s->title }}</p>
                                <p class="text-blue-100">{{ $s->schedule_formatted }}</p>
                                @if ($s->attendance_start_at || $s->attendance_end_at)
                                    <p class="text-amber-200">Presensi: {{ $s->attendance_schedule_formatted }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-blue-100">
                        <span>Jadwal: <strong class="text-white">{{ $event->zoom_schedule_formatted }}</strong></span>
                        @if ($event->zoom_meeting_id)
                            <span>Meeting ID: <strong class="text-white">{{ $event->zoom_meeting_id }}</strong></span>
                        @endif
                        @if ($event->zoom_passcode)
                            <span>Passcode: <strong class="text-white">{{ $event->zoom_passcode }}</strong></span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAHAP 3: PERIODE EVALUASI & PENGAJUAN SERTIFIKASI --}}
    <div class="relative flex items-start gap-3.5 sm:gap-5">
        <div class="relative z-10 w-14 h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
            {{ $evalState === 'done' ? 'bg-emerald-600 text-white border-emerald-600' : ($evalState === 'active' ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') }}">
            <span class="text-[10px] font-bold uppercase tracking-normal {{ $evalState === 'upcoming' ? 'text-slate-400' : 'text-white/80' }} leading-none">TAHAP</span>
            <span class="text-lg sm:text-xl font-black leading-tight mt-1">3</span>
        </div>

        <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Periode Evaluasi &amp; Pengajuan Sertifikasi</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Masa penyelesaian evaluasi akhir, serta pengajuan penerbitan sertifikat peserta yang lulus ke PPSDM.
                    </p>
                </div>
                @if ($evalState === 'active')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-purple-50 text-purple-700 border border-purple-200">Masa Evaluasi</span>
                @elseif ($evalState === 'done')
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-600">Selesai</span>
                @else
                    <span class="px-3 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-500">Mendatang</span>
                @endif
            </div>

            <div class="inline-flex flex-wrap items-center gap-x-2 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm font-bold text-[#13416B]">
                @if ($evalStart && $evalEnd)
                    {{ $evalStart->translatedFormat('d M Y') }} s/d {{ $evalEnd->translatedFormat('d M Y') }}
                @else
                    Jadwal evaluasi &amp; sertifikasi belum ditentukan
                @endif
            </div>
        </div>
    </div>
</div>
