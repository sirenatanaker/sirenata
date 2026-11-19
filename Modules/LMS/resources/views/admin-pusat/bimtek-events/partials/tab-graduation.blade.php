{{-- TAB 4: REKAP KELULUSAN LENGKAP & PENGAJUAN SERTIFIKAT KE PPSDM --}}
<div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
    {{-- 1. Header Ringkasan & Aksi Ajukan PPSDM (Terintegrasi di Container Master) --}}
    <div class="bg-[#1B568A] text-white px-4 sm:px-5 py-4 sm:py-5 border-b border-[#13416B] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-sm sm:text-base font-bold text-white">
                Rekap Kelulusan &amp; Pengajuan Sertifikat PPSDM
            </h2>
            <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                <span>{{ $totalCompleted }} dari {{ $totalRegistered }} Peserta Tuntas</span>
                <span class="text-white/40 mx-1.5 select-none">—</span>
                <span class="font-semibold text-emerald-200">{{ $readyForPpsdmCount }} Siap Diajukan ke PPSDM</span>
                <span class="text-white/40 mx-1.5 select-none">—</span>
                <span>{{ $totalCertified }} Sertifikat Terbit</span>
            </p>
        </div>

        <a href="{{ route('admin-pusat.management-course.certificate-submissions.create', ['bimtek_event_id' => $event->id]) }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs shrink-0">
            <i class="fas fa-file-signature text-xs sm:text-sm"></i>
            <span>Ajukan Peserta Lulus ke PPSDM</span>
        </a>
    </div>

    {{-- 2. Sub-Header & Tabel Final Rekap Syarat Kelulusan --}}
    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
        <h3 class="text-base font-bold text-slate-800">Daftar Kelulusan Peserta</h3>
        <span class="text-xs sm:text-sm text-slate-500">Total {{ $participants->count() }} peserta terdaftar</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3 px-4 w-12 text-center">No.</th>
                    <th class="py-3 px-4 min-w-[220px]">Nama Peserta</th>
                    <th class="py-3 px-4 min-w-[155px]">Progres Materi</th>
                    <th class="py-3 px-4 min-w-[175px] text-center">Absensi &amp; Bukti</th>
                    <th class="py-3 px-4 min-w-[130px] text-center">Evaluasi</th>
                    <th class="py-3 px-4 min-w-[140px] text-center">Survei Narasumber</th>
                    <th class="py-3 px-4 min-w-[180px]">Status PPSDM</th>
                    <th class="py-3 px-4 min-w-[175px]">Nomor Sertifikat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-base">
                @forelse ($participants as $idx => $p)
                    @php
                        $dayTarget = max(1, $totalSessionsCount ?: $totalAgendaDays);
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center text-sm sm:text-base font-semibold text-slate-400">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-900">{{ $p->user_name }}</p>
                            <p class="text-sm text-slate-500">{{ $p->user_email }}</p>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $p->progress }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-slate-700 tabular-nums">{{ $p->progress }}%</span>
                            </div>
                            @if ($p->status === 'completed')
                                <span class="text-sm text-emerald-600 font-semibold">Tuntas</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex flex-col items-center gap-1 text-sm">
                                <span class="font-bold {{ $p->attended_sessions_count >= $dayTarget ? 'text-emerald-600' : ($p->attended_sessions_count > 0 ? 'text-amber-600' : 'text-slate-500') }}">
                                    Presensi: {{ $p->attended_sessions_count }} / {{ $dayTarget }} Hari
                                </span>
                                <span class="font-semibold {{ $p->proof_submitted_count >= $dayTarget ? 'text-[#13416B]' : 'text-slate-500' }}">
                                    Bukti Zoom: {{ $p->proof_submitted_count }} / {{ max(1, $totalAgendaDays) }} Hari
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-sm font-bold {{ $p->eval_total_count > 0 && $p->eval_passed_count >= $p->eval_total_count ? 'text-emerald-600' : 'text-slate-600' }}">
                                {{ $p->eval_passed_count }} / {{ $p->eval_total_count }} Lulus
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($p->has_evaluated)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-star text-amber-400 text-xs"></i>
                                    <span>{{ number_format($p->evaluation?->average_rating, 1) }}</span>
                                </span>
                            @else
                                <span class="text-sm text-slate-400">Belum</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if ($p->official_cert_number)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Sertifikat Terbit
                                </span>
                            @elseif ($p->cert_submission_status === 'diajukan_ke_ppsdm')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Diajukan ke PPSDM
                                </span>
                            @elseif ($p->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-blue-50 text-[#13416B] border border-blue-200">
                                    Siap Diajukan
                                </span>
                            @else
                                <span class="text-sm text-slate-400 italic">Belum Memenuhi Syarat</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if ($p->official_cert_number)
                                <span class="font-mono text-sm font-bold text-slate-800 block">{{ $p->official_cert_number }}</span>
                                @if ($p->certificate_code)
                                    <a href="{{ route('verify-certificate', $p->certificate_code) }}" target="_blank" class="text-sm text-[#13416B] hover:underline">
                                        Cek Validasi QR
                                    </a>
                                @endif
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-base text-slate-400">
                            Belum ada peserta yang mendaftar pada gelombang event ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
