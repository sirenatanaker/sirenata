{{-- TAB 2: ABSENSI & BUKTI SCREENSHOT ZOOM HARIAN (TIMELINE -> CONTAINER MASTER DETAIL) --}}
<div class="space-y-5">
    @if (!$activeAttendanceDay)
        {{-- Timeline Agenda Harian --}}
        <div class="relative space-y-4 sm:space-y-5">
            @if ($attendanceDays->count() > 1)
                <div class="absolute left-7 top-6 bottom-6 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
            @endif

            @forelse ($attendanceDays as $day)
                @php
                    $hasActivity = $day->attended_count > 0 || $day->proof_count > 0;
                    $allAttended = $day->total_participants > 0 && $day->attended_count >= $day->total_participants;
                @endphp
                <div class="relative flex items-start gap-3.5 sm:gap-5">
                    <div class="relative z-10 w-14 h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
                        {{ $hasActivity ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300' }}">
                        <span class="text-[10px] font-bold uppercase tracking-normal {{ $hasActivity ? 'text-blue-200' : 'text-slate-400' }} leading-none">HARI</span>
                        <span class="text-lg sm:text-xl font-black leading-tight mt-1">{{ $day->day_number }}</span>
                        @if ($allAttended)
                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] ring-2 ring-white shadow-2xs">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2 min-w-0 flex-1">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-[#13416B]">
                                    Agenda Hari ke-{{ $day->day_number }}{{ $day->day_date ? ' — ' . $day->day_date : '' }}
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                                    {{ $day->title }}
                                </h3>
                            </div>

                            <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                <span class="font-bold text-emerald-600">{{ $day->attended_count }} / {{ $day->total_participants }} Sudah Presensi</span>
                                <span class="text-[#13416B]/35 select-none">—</span>
                                <span class="font-bold text-[#13416B]">{{ $day->proof_count }} / {{ $day->total_participants }} Unggah Bukti Zoom</span>
                            </div>
                        </div>

                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'attendance', 'section_id' => $day->section->id]) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white transition-colors shrink-0">
                            <span>Lihat Daftar Peserta</span>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-md border border-slate-200 p-10 text-center text-sm text-slate-500">
                    Belum ada agenda harian pada master kursus ini.
                </div>
            @endforelse
        </div>
    @else
        {{-- Container Master Detail Absensi Harian (Header Agenda + Filter + Daftar Peserta + Paginasi) --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            {{-- 1. Sub-Header Agenda Terpilih --}}
            <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#13416B]">
                        Absensi Harian{{ $activeAttendanceDay->day_date ? ' — ' . $activeAttendanceDay->day_date : '' }}
                    </p>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 wrap-break-word">
                        Absensi Hari ke-{{ $activeAttendanceDay->day_number }}: {{ $activeAttendanceDay->title }}
                    </h2>
                </div>
                <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'attendance']) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 border border-slate-200 transition-colors shrink-0">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali ke Daftar Agenda</span>
                </a>
            </div>

            {{-- 2. Bar Pencarian & Filter Peserta --}}
            <form action="{{ route('admin-pusat.management-course.bimtek-events.show', $event->id) }}" method="GET"
                  class="p-4 border-b border-slate-200 bg-white flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <input type="hidden" name="tab" value="attendance">
                <input type="hidden" name="section_id" value="{{ $activeAttendanceDay->section->id }}">

                <input type="text"
                       name="att_search"
                       value="{{ $attSearch }}"
                       placeholder="Cari nama, email, atau instansi peserta..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">

                <select name="att_filter" onchange="this.form.submit()"
                        class="px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                    <option value="all" @selected($attFilter === 'all')>Semua Peserta ({{ $attendanceSummary['all'] }})</option>
                    <option value="complete" @selected($attFilter === 'complete')>Lengkap: Presensi &amp; Bukti ({{ $attendanceSummary['complete'] }})</option>
                    <option value="proof_not_attended" @selected($attFilter === 'proof_not_attended')>Sudah Bukti, Belum Presensi ({{ $attendanceSummary['proof_not_attended'] ?? 0 }})</option>
                    <option value="attended" @selected($attFilter === 'attended')>Sudah Presensi Zoom ({{ $attendanceSummary['attended'] }})</option>
                    <option value="not_attended" @selected($attFilter === 'not_attended')>Belum Presensi Zoom ({{ $attendanceSummary['not_attended'] }})</option>
                    <option value="proof_submitted" @selected($attFilter === 'proof_submitted')>Sudah Unggah Bukti ({{ $attendanceSummary['proof_submitted'] }})</option>
                    <option value="proof_missing" @selected($attFilter === 'proof_missing')>Belum Unggah Bukti ({{ $attendanceSummary['proof_missing'] }})</option>
                </select>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($attSearch !== '' || ($attFilter !== 'all' && $attFilter !== ''))
                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'attendance', 'section_id' => $activeAttendanceDay->section->id]) }}"
                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md border border-slate-200">
                            Reset
                        </a>
                    @endif
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </form>

            {{-- 2b. Bar Aksi Massal Presensi untuk Peserta yang Sudah Unggah Bukti --}}
            @php
                $proofNotAttendedCount = (int) ($attendanceSummary['proof_not_attended'] ?? 0);
            @endphp
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                <p class="text-xs sm:text-sm text-slate-600">
                    @if ($proofNotAttendedCount > 0)
                        <span class="font-bold text-slate-800">{{ $proofNotAttendedCount }} peserta</span> sudah mengunggah screenshot bukti Zoom namun belum tercatat presensi.
                    @else
                        Seluruh peserta yang sudah mengunggah bukti Zoom pada agenda ini telah tercatat presensi.
                    @endif
                </p>
                @if ($proofNotAttendedCount > 0)
                    <button type="button"
                            @click="$dispatch('open-modal', 'confirm-bulk-presence')"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer shrink-0">
                        <i class="fas fa-check-double"></i>
                        <span>Tandai Hadir Semua yang Sudah Unggah Bukti ({{ $proofNotAttendedCount }})</span>
                    </button>
                @else
                    <button type="button" disabled
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed shrink-0">
                        <i class="fas fa-check-double"></i>
                        <span>Tandai Hadir yang Sudah Unggah Bukti (0)</span>
                    </button>
                @endif
            </div>

            {{-- 3. Daftar Peserta Bernomor --}}
            <div class="divide-y divide-slate-200">
                @forelse ($attendanceRows as $row)
                    @php
                        $rowNumber = ($attendanceRows->firstItem() ?? 1) + $loop->index;
                        $proof = $row->proof;
                    @endphp
                    <div x-data="{ expanded: false }">
                        <div @click="expanded = !expanded"
                             class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:bg-slate-50/70 transition-colors select-none">
                            <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                                <span class="text-base sm:text-lg font-bold text-slate-500 tabular-nums shrink-0 leading-snug w-7 text-right">
                                    {{ $rowNumber }}.
                                </span>

                                <div class="space-y-2 min-w-0 flex-1">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $row->name }}</h3>
                                            @if ($row->instansi)
                                                <span class="text-xs sm:text-sm text-slate-500">({{ $row->instansi }})</span>
                                            @endif
                                        </div>
                                        <p class="text-sm sm:text-[15px] font-medium text-slate-600 mt-0.5">{{ $row->email }}</p>
                                    </div>

                                    <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                        @if ($row->is_attended)
                                            <span class="font-bold text-emerald-600">
                                                Sudah Presensi{{ $row->attended_at ? ' (' . $row->attended_at->translatedFormat('d M Y, H:i') . ' WIB)' : '' }}
                                            </span>
                                        @else
                                            <span class="font-bold text-amber-600">Belum Presensi</span>
                                        @endif

                                        <span class="text-[#13416B]/35 select-none">—</span>

                                        @if ($row->has_proof)
                                            <span class="font-bold text-emerald-600">Bukti Zoom Terkumpul</span>
                                        @else
                                            <span class="font-bold text-amber-600">Belum Unggah Bukti</span>
                                        @endif

                                        @if ($proof?->submitted_at)
                                            <span class="text-[#13416B]/35 select-none">—</span>
                                            <span class="font-semibold text-[#1E4468]">Diunggah {{ $proof->submitted_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors">
                                    <span x-text="expanded ? 'Tutup' : 'Detail & Aksi'"></span>
                                    <i class="fas fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                                </span>
                            </div>
                        </div>

                        <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/60 p-4 sm:p-6">
                            <div class="w-full bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-4 shadow-2xs">
                                <div class="space-y-3.5">
                                    <h4 class="text-sm sm:text-base font-bold text-slate-800 pb-2 border-b border-slate-100">
                                        Riwayat Presensi &amp; Screenshot Bukti Zoom
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                                        <div class="p-3 rounded-md bg-slate-50 border border-slate-200">
                                            <p class="text-slate-500">Status Presensi Zoom</p>
                                            <p class="font-bold mt-0.5 {{ $row->is_attended ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $row->is_attended ? 'Sudah Melakukan Presensi' : 'Belum Melakukan Presensi' }}
                                            </p>
                                            @if ($row->attended_at)
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $row->attended_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                            @elseif ($row->joined_at)
                                                <p class="text-xs text-slate-500 mt-0.5">Klik Zoom: {{ $row->joined_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                            @endif
                                        </div>

                                        <div class="p-3 rounded-md bg-slate-50 border border-slate-200">
                                            <p class="text-slate-500">Status Bukti Screenshot</p>
                                            <p class="font-bold mt-0.5 {{ $row->has_proof ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $row->has_proof ? 'Sudah Mengunggah Bukti' : 'Belum Mengunggah Bukti' }}
                                            </p>
                                            @if ($proof?->submitted_at)
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $proof->submitted_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($proof && $proof->file_url)
                                        <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3.5">
                                            <a href="{{ $proof->file_url }}" target="_blank" rel="noopener noreferrer"
                                               class="w-28 h-20 rounded-md border border-slate-200 overflow-hidden bg-slate-100 shrink-0 block hover:opacity-90 transition-opacity">
                                                <img src="{{ $proof->file_url }}" alt="Screenshot Zoom {{ $row->name }}" class="w-full h-full object-cover">
                                            </a>
                                            <div class="space-y-1.5 min-w-0">
                                                <p class="text-xs font-bold text-slate-700">Screenshot Bukti Mengikuti Zoom</p>
                                                <a href="{{ $proof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors">
                                                    <i class="fas fa-image"></i>
                                                    <span>Lihat Screenshot Penuh</span>
                                                    <i class="fas fa-external-link-alt text-[11px] opacity-80"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @elseif ($proof && empty($proof->file_path))
                                        <div class="p-3 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-600 flex items-center gap-2.5">
                                            <i class="fas fa-archive text-slate-400"></i>
                                            <span>Berkas screenshot telah dibersihkan otomatis dari penyimpanan server setelah peserta diajukan ke PPSDM.</span>
                                        </div>
                                    @endif

                                    @if ($proof?->notes)
                                        <div class="p-3 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
                                            <span class="font-bold text-slate-600">Keterangan:</span>
                                            <span>{{ $proof->notes }}</span>
                                        </div>
                                    @elseif (!$proof)
                                        <p class="text-xs sm:text-sm text-slate-500">
                                            Peserta belum mengunggah screenshot bukti mengikuti Zoom pada agenda hari ini.
                                        </p>
                                    @endif
                                </div>

                                <div class="w-full pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">Presensi Kehadiran Zoom</h4>
                                        <p class="text-xs text-slate-500">Konfirmasi atau ubah status kehadiran peserta secara manual.</p>
                                    </div>
                                    <form action="{{ route('admin-pusat.management-course.attendances.toggle-presence') }}" method="POST" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="bimtek_event_id" value="{{ $event->id }}">
                                        <input type="hidden" name="section_id" value="{{ $activeAttendanceDay->section->id }}">
                                        <input type="hidden" name="user_id" value="{{ $row->user_id }}">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold transition-colors cursor-pointer {{ $row->is_attended ? 'text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200' : 'text-white bg-emerald-600 hover:bg-emerald-700' }}">
                                            <i class="fas {{ $row->is_attended ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                            <span>{{ $row->is_attended ? 'Batalkan Status Presensi' : 'Tandai Sudah Presensi' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-500 space-y-1">
                        <p class="text-base font-bold text-slate-700">Peserta tidak ditemukan</p>
                        <p class="text-sm text-slate-400">Tidak ada peserta yang sesuai dengan pencarian atau filter Anda.</p>
                    </div>
                @endforelse
            </div>

            @if ($attendanceRows && $attendanceRows->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/40">
                    {{ $attendanceRows->links() }}
                </div>
            @endif
        </div>

        @if ($proofNotAttendedCount > 0)
            <x-modal name="confirm-bulk-presence" title="Konfirmasi Tandai Hadir Massal" maxWidth="sm:max-w-md">
                <div class="space-y-4">
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Anda akan menandai <span class="font-bold text-slate-900">{{ $proofNotAttendedCount }} peserta</span> yang sudah mengunggah screenshot bukti Zoom pada
                        <span class="font-bold text-slate-900">Agenda Hari ke-{{ $activeAttendanceDay->day_number }}</span> sebagai <span class="font-bold text-emerald-700">Sudah Presensi</span> secara sekaligus.
                    </p>
                    <p class="text-xs text-slate-500">
                        Peserta yang belum mengunggah bukti screenshot tidak akan terpengaruh.
                    </p>
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button"
                                @click="$dispatch('close-modal', 'confirm-bulk-presence')"
                                class="px-4 py-2 rounded-md text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <form action="{{ route('admin-pusat.management-course.attendances.bulk-mark-presence-with-proof') }}" method="POST">
                            @csrf
                            <input type="hidden" name="bimtek_event_id" value="{{ $event->id }}">
                            <input type="hidden" name="section_id" value="{{ $activeAttendanceDay->section->id }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer">
                                <i class="fas fa-check-double"></i>
                                <span>Ya, Tandai Hadir ({{ $proofNotAttendedCount }})</span>
                            </button>
                        </form>
                    </div>
                </div>
            </x-modal>
        @endif
    @endif
</div>
