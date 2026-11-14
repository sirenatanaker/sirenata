<x-dashboard::layouts.dashboard title="Monitoring Gelombang Bimtek - Admin Pusat">
    <div class="w-full p-8 space-y-6">

        {{-- Breadcrumb Navigation (2 Teks) --}}
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        Periode Event
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 mx-1.5"></i>
                    <span class="text-xs sm:text-sm font-bold text-slate-900">
                        Detail & Monitoring
                    </span>
                </li>
            </ol>
        </nav>

        {{-- ======================================================== --}}
        {{-- STRUKTUR TIMELINE KESELURUHAN & RINCIAN 3 PERIODE BIMTEK --}}
        {{-- ======================================================== --}}
        @php
            $now = now();
            $regStart = $event->registration_start_at ?? $event->start_date?->copy()->subDays(7);
            $regEnd = $event->registration_end_at ?? $event->start_date?->copy()->subDay();
            $execStart = $event->start_date;
            $execEnd = $event->end_date;
            $evalStart = $event->assessment_start_at ?? $event->end_date;
            $evalEnd = $event->assessment_end_at ?? $event->end_date?->copy()->addDays(3);

            $overallStart = $event->overall_start_at;
            $overallEnd = $event->overall_end_at;
            $totalDays = ($overallStart && $overallEnd) ? max(1, $overallStart->diffInDays($overallEnd)) : 0;
            $isUnlimitedQuota = is_null($event->quota) || $event->quota <= 0;
            $quotaPercent = (!$isUnlimitedQuota && $event->quota > 0) ? min(100, round(($totalRegistered / $event->quota) * 100)) : 0;
            $badge = $event->status_badge;
        @endphp

        {{-- Header Banner Utama Linimasa Program (Desain Sirenata Terpadu) --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-4">
            {{-- Baris Atas: Info Gelombang Bimtek & Tombol Aksi Rata Kiri --}}
            <div class="border-b border-white/15 pb-4 space-y-3">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">{{ $event->batch_name }}</h1>
                        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-200">
                        Kode Gelombang: <strong class="font-mono text-white">{{ $event->batch_code }}</strong> | Master Kursus: <strong class="text-white">{{ $event->course?->name }}</strong>
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap pt-0.5">
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.edit', $event->id) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-md transition-colors shadow-2xs">
                        <i class="fas fa-pen text-[10px]"></i>
                        <span>Edit Linimasa & Kuota</span>
                    </a>
                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.create', ['bimtek_event_id' => $event->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-[#13416B] bg-white hover:bg-slate-100 rounded-md transition-colors shadow-2xs">
                        <i class="fas fa-file-signature text-xs"></i>
                        <span>Ajukan Sertifikat ke PPSDM</span>
                    </a>
                </div>
            </div>

            {{-- Baris Bawah: Rentang Waktu Keseluruhan & Keterisian Kuota --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Linimasa Keseluruhan Program</span>
                        @if ($totalDays > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-white/15 border border-white/20 text-white">
                                Durasi: {{ $totalDays }} Hari
                            </span>
                        @endif
                    </div>
                    <p class="text-base sm:text-xl font-extrabold text-white">
                        {{ $event->overall_timeline_formatted }}
                    </p>
                </div>

                <div class="w-full md:w-72 space-y-1.5">
                    @if ($isUnlimitedQuota)
                        <div class="flex items-center justify-between text-xs text-slate-200">
                            <span>Kapasitas Pendaftaran</span>
                            <span class="font-bold text-amber-300 flex items-center gap-1">
                                <i class="fas fa-infinity text-xs"></i> Tanpa Batas
                            </span>
                        </div>
                        <div class="px-3 py-1.5 rounded-md bg-white/10 border border-white/15 text-xs text-white flex items-center justify-between">
                            <span class="text-slate-300">Pendaftar Terdaftar:</span>
                            <strong class="font-bold text-white">{{ $totalRegistered }} Peserta</strong>
                        </div>
                    @else
                        <div class="flex justify-between text-xs text-slate-200">
                            <span>Keterisian Kuota ({{ $totalRegistered }} / {{ $event->quota }} Peserta)</span>
                            <span class="font-bold text-amber-300">{{ $quotaPercent }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-400 h-2 rounded-full transition-all" style="width: {{ $quotaPercent }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Grid Rincian 3 Tahapan Timeline --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Tahap 1: Periode Pendaftaran --}}
            <div class="bg-white rounded-md border border-slate-200 p-4.5 space-y-3 shadow-2xs relative flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pendaftaran</span>
                        </div>
                        @if ($regStart && $regEnd && $now->between($regStart, $regEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Buka
                            </span>
                        @elseif ($regEnd && $now->greaterThan($regEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                Selesai
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                Mendatang
                            </span>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] text-slate-400 font-medium">Jadwal Buka & Tutup Pendaftaran:</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 mt-0.5">
                            @if ($event->registration_start_at && $event->registration_end_at)
                                {{ $event->registration_start_at->translatedFormat('d M Y') }} s/d {{ $event->registration_end_at->translatedFormat('d M Y') }}
                            @elseif ($event->registration_start_at)
                                Mulai {{ $event->registration_start_at->translatedFormat('d M Y') }}
                            @else
                                <span class="text-slate-400 italic">Otomatis H-7 sebelum pelaksanaan</span>
                            @endif
                        </p>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        Calon peserta melakukan pendaftaran mandiri melalui katalog pelatihan.
                    </p>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Pendaftar Terdaftar:</span>
                    <strong class="text-slate-900 font-bold">
                        @if ($event->quota && $event->quota > 0)
                            {{ $totalRegistered }} / {{ $event->quota }} Peserta
                        @else
                            {{ $totalRegistered }} Peserta (Tanpa Batas)
                        @endif
                    </strong>
                </div>
            </div>

            {{-- Tahap 2: Periode Pelaksanaan & Sesi Live Zoom --}}
            <div class="bg-white rounded-md border border-slate-200 p-4.5 space-y-3 shadow-2xs relative flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pelaksanaan</span>
                        </div>
                        @if ($execStart && $execEnd && $now->between($execStart, $execEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Berlangsung
                            </span>
                        @elseif ($execEnd && $now->greaterThan($execEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                Selesai
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                Mendatang
                            </span>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] text-slate-400 font-medium">Rentang Waktu Pelatihan:</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 mt-0.5">
                            {{ $event->start_date ? $event->start_date->translatedFormat('d M Y') : '-' }} s/d {{ $event->end_date ? $event->end_date->translatedFormat('d M Y') : '-' }}
                        </p>
                    </div>

                    {{-- Detail Sesi Zoom Terpadu di Pelaksanaan (Biru Sirenata Kalem & Teks White) --}}
                    @if ($event->sessions->isNotEmpty())
                        <div class="p-3.5 sm:p-4 rounded-md bg-[#1E40AF] text-white shadow-sm space-y-3">
                            <div class="flex items-center justify-between gap-1 border-b border-white/20 pb-2">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-calendar-check text-amber-300 text-xs"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider text-white">
                                        Daftar Sesi Harian ({{ $event->sessions->count() }} Sesi)
                                    </span>
                                </div>
                                @if ($event->zoom_url)
                                    <a href="{{ $event->zoom_url }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold text-[#1E40AF] bg-white hover:bg-blue-50 transition-colors shadow-2xs">
                                        <span>Buka Zoom Utama</span>
                                        <i class="fas fa-external-link-alt text-[9px]"></i>
                                    </a>
                                @endif
                            </div>

                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                @foreach ($event->sessions as $s)
                                    <div class="p-2.5 rounded bg-black/20 border border-white/10 space-y-1 text-xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-slate-900">
                                                    Hari {{ $s->session_number }}
                                                </span>
                                                <strong class="text-white">{{ $s->title }}</strong>
                                            </div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white">
                                                {{ $s->attendances->count() }} / {{ $totalRegistered }} Hadir
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] text-blue-100">
                                            <div>
                                                <span class="text-slate-300">Zoom:</span>
                                                <span class="text-white font-medium">{{ $s->schedule_formatted }}</span>
                                            </div>
                                            <div>
                                                <span class="text-amber-200">Presensi:</span>
                                                <span class="text-white font-medium">{{ $s->attendance_schedule_formatted }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($event->zoom_meeting_id || $event->zoom_passcode)
                                <div class="text-[11px] text-blue-100 font-mono flex items-center gap-3 pt-2 border-t border-white/15">
                                    @if ($event->zoom_meeting_id) <span>ID: <strong class="text-white">{{ $event->zoom_meeting_id }}</strong></span> @endif
                                    @if ($event->zoom_passcode) <span>Passcode: <strong class="text-white">{{ $event->zoom_passcode }}</strong></span> @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3.5 sm:p-4 rounded-md bg-[#1E40AF] text-white shadow-sm space-y-2.5">
                            <div class="flex items-center justify-between gap-1 border-b border-white/20 pb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-white">Sesi Live Zoom</span>
                                @if ($event->zoom_url)
                                    <a href="{{ $event->zoom_url }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold text-[#1E40AF] bg-white hover:bg-blue-50 transition-colors shadow-2xs">
                                        <span>Buka Zoom</span>
                                        <i class="fas fa-external-link-alt text-[9px]"></i>
                                    </a>
                                @endif
                            </div>

                            <div>
                                <span class="text-[11px] text-blue-100 block">Jadwal Sesi Zoom:</span>
                                <p class="text-xs sm:text-sm font-semibold text-white mt-0.5">
                                    {{ $event->zoom_schedule_formatted }}
                                </p>
                            </div>

                            @if ($event->zoom_attendance_start_at || $event->zoom_attendance_end_at)
                                <div class="pt-1.5 border-t border-white/15">
                                    <span class="text-[11px] text-amber-200 block font-semibold">Jadwal Presensi Dibuka:</span>
                                    <p class="text-xs sm:text-sm font-bold text-white mt-0.5">
                                        {{ $event->attendance_schedule_formatted }}
                                    </p>
                                </div>
                            @endif

                            @if ($event->zoom_meeting_id || $event->zoom_passcode || $event->zoom_attendance_code)
                                <div class="text-[11px] text-blue-100 font-mono space-y-1 pt-2 border-t border-white/15">
                                    @if ($event->zoom_meeting_id) <p>Meeting ID: <strong class="text-white font-mono">{{ $event->zoom_meeting_id }}</strong></p> @endif
                                    @if ($event->zoom_passcode) <p>Passcode: <strong class="text-white font-mono">{{ $event->zoom_passcode }}</strong></p> @endif
                                    @if ($event->zoom_attendance_code)
                                        <p class="pt-0.5 text-white font-sans">
                                            Kode Presensi: <strong class="px-2 py-0.5 rounded-md bg-white text-[#1E40AF] font-mono text-xs font-bold ml-1">{{ $event->zoom_attendance_code }}</strong>
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Kehadiran Zoom:</span>
                    <strong class="text-slate-900 font-bold">{{ $totalAttendedZoom }} / {{ $totalRegistered }} Peserta</strong>
                </div>
            </div>

            {{-- Tahap 3: Periode Evaluasi & Sertifikat --}}
            <div class="bg-white rounded-md border border-slate-200 p-4.5 space-y-3 shadow-2xs relative flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Evaluasi & Sertifikat</span>
                        </div>
                        @if ($evalStart && $evalEnd && $now->between($evalStart, $evalEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Evaluasi
                            </span>
                        @elseif ($evalEnd && $now->greaterThan($evalEnd))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                Selesai
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                Menunggu
                            </span>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] text-slate-400 font-medium">Batas Evaluasi & Sertifikasi:</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 mt-0.5">
                            @if ($event->assessment_start_at && $event->assessment_end_at)
                                {{ $event->assessment_start_at->translatedFormat('d M Y') }} s/d {{ $event->assessment_end_at->translatedFormat('d M Y') }}
                            @elseif ($event->assessment_start_at)
                                Mulai {{ $event->assessment_start_at->translatedFormat('d M Y') }}
                            @else
                                <span class="text-slate-400 italic">Setelah periode pelaksanaan selesai</span>
                            @endif
                        </p>
                    </div>

                    <div class="p-3 rounded-md bg-slate-50 border border-slate-200 space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between items-center">
                            <span>Pengajuan Nota Dinas:</span>
                            <strong class="font-bold text-slate-800">{{ $event->certificateSubmissions->count() }} Berkas</strong>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Nomor PPSDM Terbit:</span>
                            <strong class="font-bold text-emerald-700">{{ $totalCertified }} dari {{ $totalRegistered }} Peserta</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.create', ['bimtek_event_id' => $event->id]) }}"
                       class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] transition-colors">
                        <span>Proses Pengajuan PPSDM</span>
                    </a>
                </div>
            </div>

        </div>


        {{-- Tabel Monitoring Peserta --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-slate-800">
                        Monitoring Peserta Bimtek & Presensi
                    </h2>
                </div>
                <span class="text-xs text-slate-500">
                    Total {{ $participants->count() }} peserta terdaftar
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center">No.</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Nama Peserta</th>
                            <th class="py-3 px-3.5 min-w-[150px]">Progres Modul</th>
                            <th class="py-3 px-3.5 min-w-[140px] text-center">Presensi Zoom</th>
                            <th class="py-3 px-3.5 min-w-[130px] text-center">Evaluasi Narasumber</th>
                            <th class="py-3 px-3.5 min-w-[160px]">Status Sertifikasi PPSDM</th>
                            <th class="py-3 px-3.5 min-w-[160px]">Nomor Resmi Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($participants as $idx => $p)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3.5 text-center text-xs font-semibold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-900 text-xs">{{ $p->user_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $p->user_email }}</p>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $p->progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700 tabular-nums">{{ $p->progress }}%</span>
                                    </div>
                                    @if ($p->status === 'completed')
                                        <span class="text-[10px] text-emerald-600 font-semibold">Tuntas (Lulus Ujian)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if ($totalSessionsCount > 0)
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold
                                                {{ $p->attended_sessions_count >= $totalSessionsCount ? 'bg-emerald-600 text-white' : ($p->attended_sessions_count > 0 ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-700') }}">
                                                <i class="fas {{ $p->attended_sessions_count >= $totalSessionsCount ? 'fa-check-double' : ($p->attended_sessions_count > 0 ? 'fa-check' : 'fa-times') }} text-[10px]"></i>
                                                <span>{{ $p->attended_sessions_count }} / {{ $totalSessionsCount }} Sesi</span>
                                            </span>
                                            <form action="{{ route('admin-pusat.management-course.bimtek-events.toggle-attendance', [$event->id, $p->user_id]) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        title="Override status presensi"
                                                        class="text-[10px] text-slate-400 hover:text-slate-600 underline cursor-pointer">
                                                    {{ $p->zoom_attended ? 'Tandai Belum' : 'Tandai Hadir' }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('admin-pusat.management-course.bimtek-events.toggle-attendance', [$event->id, $p->user_id]) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    title="Klik untuk ubah status kehadiran Zoom"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-transform active:scale-95 cursor-pointer
                                                    {{ $p->zoom_attended ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                                <i class="fas {{ $p->zoom_attended ? 'fa-check' : 'fa-times' }} text-[10px]"></i>
                                                <span>{{ $p->zoom_attended ? 'Hadir' : 'Belum Hadir' }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if ($p->has_evaluated)
                                        <div class="inline-flex flex-col items-center gap-0.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Materi: {{ $p->evaluation?->rating_materi }}★, Penyampaian: {{ $p->evaluation?->rating_penyampaian }}★, Fasilitasi: {{ $p->evaluation?->rating_fasilitasi }}★">
                                                <i class="fas fa-star text-amber-400 text-[10px]"></i>
                                                <span>{{ number_format($p->evaluation?->average_rating, 1) }}</span>
                                            </span>
                                            <span class="text-[10px] text-slate-400">Sudah Mengisi</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium text-slate-400 bg-slate-100">
                                            <i class="far fa-clock text-[9px]"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5">
                                    @if ($p->official_cert_number)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-check-double text-[10px]"></i>
                                            <span>Sertifikat Terbit</span>
                                        </span>
                                    @elseif ($p->cert_submission_status === 'diajukan_ke_ppsdm')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fas fa-clock text-[10px]"></i>
                                            <span>Diajukan ke PPSDM</span>
                                        </span>
                                    @elseif ($p->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            Siap Diajukan
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum Memenuhi Syarat</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5">
                                    @if ($p->official_cert_number)
                                        <span class="font-mono text-xs font-bold text-slate-800 block">
                                            {{ $p->official_cert_number }}
                                        </span>
                                        <a href="{{ route('verify-certificate', $p->certificate_code) }}" target="_blank"
                                           class="text-[10px] text-indigo-600 hover:underline">
                                            Cek Validasi QR
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-xs text-slate-500">
                                    Belum ada peserta yang terdaftar pada kursus ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION EVALUASI NARASUMBER & PENYELENGGARAAN --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">
                            Hasil Evaluasi Narasumber & Penyelenggaraan
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Kompilasi penilaian mutu pengajar dan masukan evaluasi dari peserta bimtek
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold bg-blue-50 text-[#13416B] border border-blue-200">
                        <i class="fas fa-users text-xs"></i>
                        <span>{{ $evaluationsCount }} dari {{ $totalRegistered }} Responden</span>
                    </span>
                    <a href="{{ route('admin-pusat.management-course.evaluation-questions.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors">
                        <i class="fas fa-sliders-h text-xs"></i>
                        <span>Kelola Kuesioner</span>
                    </a>
                </div>
            </div>

            {{-- 4 Metric Score Cards --}}
            <div class="p-5 sm:p-6 bg-slate-50/30 border-b border-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1. Skor Keseluruhan --}}
                    <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">Skor Rata-Rata</span>
                            <span class="w-7 h-7 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center text-xs">
                                <i class="fas fa-star"></i>
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($avgOverall, 1) }}</span>
                            <span class="text-xs font-medium text-slate-400">/ 5.0</span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-400 text-xs">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($avgOverall) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                            @endfor
                            <span class="text-[11px] text-slate-500 font-medium ml-1">Overall</span>
                        </div>
                    </div>

                    {{-- 2. Penguasaan Materi --}}
                    <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">1. Penguasaan Materi</span>
                            <span class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                <i class="fas fa-book-reader"></i>
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($avgMateri, 1) }}</span>
                            <span class="text-xs font-medium text-slate-400">/ 5.0</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-tight">
                            Kejelasan paparan & bobot materi narasumber
                        </p>
                    </div>

                    {{-- 3. Metode Penyampaian --}}
                    <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">2. Penyampaian Sesi</span>
                            <span class="w-7 h-7 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                <i class="fas fa-comments"></i>
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($avgPenyampaian, 1) }}</span>
                            <span class="text-xs font-medium text-slate-400">/ 5.0</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-tight">
                            Interaktivitas metode diskusi & tanya jawab
                        </p>
                    </div>

                    {{-- 4. Ketepatan Waktu & Fasilitasi --}}
                    <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500">3. Fasilitasi Panitia</span>
                            <span class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fas fa-user-clock"></i>
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($avgFasilitasi, 1) }}</span>
                            <span class="text-xs font-medium text-slate-400">/ 5.0</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-tight">
                            Ketepatan jadwal & fasilitasi teknis bimtek
                        </p>
                    </div>
                </div>
            </div>

            {{-- Daftar Ulasan & Catatan Masukan Peserta --}}
            <div class="p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-comment-alt text-[#13416B]"></i>
                        <span>Daftar Ulasan & Masukan Peserta</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">
                        Identitas peserta disamarkan (Anonim)
                    </span>
                </div>

                @if ($evaluations->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs sm:text-sm bg-slate-50 rounded-md border border-dashed border-slate-200">
                        <i class="far fa-clipboard text-2xl mb-2 text-slate-300 block"></i>
                        <span>Belum ada peserta yang mengisi kuesioner evaluasi narasumber untuk bimtek ini.</span>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($evaluations as $index => $eval)
                            <div class="p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 transition-colors space-y-2.5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-800">Peserta Bimtek (Responden Anonim)</span>
                                            <span class="text-[11px] text-slate-400 ml-1.5">• {{ $eval->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Badges Per Aspek --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                            <span>Materi:</span>
                                            <span class="font-bold text-amber-500">{{ $eval->rating_materi }}★</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                            <span>Penyampaian:</span>
                                            <span class="font-bold text-amber-500">{{ $eval->rating_penyampaian }}★</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                            <span>Fasilitasi:</span>
                                            <span class="font-bold text-amber-500">{{ $eval->rating_fasilitasi }}★</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                            <span>Rata-rata: {{ $eval->average_rating }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Isi Catatan / Masukan --}}
                                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50/60 p-3 rounded-md border border-slate-150">
                                    @if (!empty(trim($eval->feedback)))
                                        <p class="italic">“{{ $eval->feedback }}”</p>
                                    @else
                                        <p class="text-slate-400 italic">Peserta tidak meninggalkan catatan tertulis tambahan.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-dashboard::layouts.dashboard>
