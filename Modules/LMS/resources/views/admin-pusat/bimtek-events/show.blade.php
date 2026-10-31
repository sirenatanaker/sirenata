<x-dashboard::layouts.dashboard title="Monitoring Gelombang Bimtek - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-5">

        {{-- Top Navigasi & Aksi Utama --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-1">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Daftar Bimtek</span>
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ $event->batch_name }}</h1>
                    @php $badge = $event->status_badge; @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Kode Gelombang: <strong class="font-mono text-slate-700">{{ $event->batch_code }}</strong> | Master Kursus: <strong class="text-slate-800">{{ $event->course?->name }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <a href="{{ route('admin-pusat.management-course.bimtek-events.edit', $event->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-2xs">
                    <i class="fas fa-pen text-xs text-amber-500"></i>
                    <span>Edit Jadwal</span>
                </a>
                <a href="{{ route('admin-pusat.management-course.certificate-submissions.create', ['bimtek_event_id' => $event->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                    <i class="fas fa-file-signature text-xs"></i>
                    <span>Ajukan Sertifikat ke PPSDM</span>
                </a>
            </div>
        </div>

        {{-- Kartu Info Detail & Jadwal Zoom --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2.5 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Jadwal & Kuota</span>
                <div>
                    <p class="text-xs text-slate-500">Rentang Tanggal:</p>
                    <p class="text-sm font-bold text-slate-800">
                        {{ $event->start_date ? $event->start_date->format('d M Y, H:i') : '-' }} s/d {{ $event->end_date ? $event->end_date->format('d M Y, H:i') : '-' }} WIB
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Kapasitas Kuota:</p>
                    <p class="text-sm font-bold text-slate-800">{{ $event->quota }} Peserta (Terisi: {{ $totalRegistered }})</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2.5 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Sesi Live Zoom (Online)</span>
                @if ($event->zoom_url)
                    <div>
                        <p class="text-xs text-slate-500">Jadwal Sesi Zoom:</p>
                        <p class="text-xs font-bold text-slate-800">{{ $event->zoom_schedule_formatted }}</p>
                    </div>
                    <div>
                        <a href="{{ $event->zoom_url }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-video text-xs"></i>
                            <span>Buka Ruang Zoom</span>
                        </a>
                    </div>
                    <div class="text-xs text-slate-600 font-mono space-y-0.5">
                        @if ($event->zoom_meeting_id) <p>Meeting ID: <strong>{{ $event->zoom_meeting_id }}</strong></p> @endif
                        @if ($event->zoom_passcode) <p>Passcode: <strong>{{ $event->zoom_passcode }}</strong></p> @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tautan Zoom belum diatur.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2.5 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Integrasi Sertifikasi PPSDM</span>
                <div>
                    <p class="text-xs text-slate-500">Pengajuan Terdaftar:</p>
                    <p class="text-sm font-bold text-slate-800">{{ $event->certificateSubmissions->count() }} Berkas Nota Dinas</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Peserta Bersertifikat Resmi:</p>
                    <p class="text-sm font-bold text-emerald-600">{{ $totalCertified }} dari {{ $totalRegistered }} Peserta</p>
                </div>
            </div>
        </div>

        {{-- 4 Indikator Progres Peserta --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-slate-500">Total Peserta</p>
                    <p class="text-xl font-bold text-slate-800">{{ $totalRegistered }}</p>
                </div>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
                    <i class="fas fa-users"></i>
                </span>
            </div>
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-slate-500">Tuntas Modul</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $totalCompleted }}</p>
                </div>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-check-circle"></i>
                </span>
            </div>
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-slate-500">Hadir Live Zoom</p>
                    <p class="text-xl font-bold text-blue-600">{{ $totalAttendedZoom }}</p>
                </div>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-video"></i>
                </span>
            </div>
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-slate-500">Nomor PPSDM Terbit</p>
                    <p class="text-xl font-bold text-[#13416B]">{{ $totalCertified }}</p>
                </div>
                <span class="w-8 h-8 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center text-xs">
                    <i class="fas fa-certificate"></i>
                </span>
            </div>
        </div>

        {{-- Tabel Monitoring Peserta --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
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
                                <td colspan="6" class="py-10 text-center text-xs text-slate-500">
                                    Belum ada peserta yang terdaftar pada kursus ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-dashboard::layouts.dashboard>
