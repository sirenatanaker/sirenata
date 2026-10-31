<x-dashboard::layouts.dashboard title="Buat Pengajuan Sertifikat ke PPSDM - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-4xl mx-auto space-y-5">

        {{-- Header & Navigasi Kembali --}}
        <div>
            <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-1">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Daftar Pengajuan</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">Buat Berkas Pengajuan ke PPSDM</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pilih gelombang Bimtek untuk menyaring otomatis ASN yang lulus dan siap diajukan nomor sertifikat resminya.</p>
        </div>

        {{-- Langkah 1: Pilih Gelombang Bimtek --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
            <form method="GET" action="{{ route('admin-pusat.management-course.certificate-submissions.create') }}">
                <label for="bimtek_event_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    1. Pilih Gelombang Bimtek yang Telah Selesai <span class="text-rose-500">*</span>
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <select id="bimtek_event_id" name="bimtek_event_id" required onchange="this.form.submit()"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                        <option value="">-- Pilih Gelombang Bimtek --</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev->id }}" @selected($selectedEventId == $ev->id)>
                                {{ $ev->batch_name }} (Kursus: {{ $ev->course?->name }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-white bg-[#13416B] rounded-lg shrink-0">
                        Pilih & Saring Lulusan
                    </button>
                </div>
            </form>
        </div>

        @if ($selectedEvent)
            {{-- Langkah 2: Formulir Pengajuan & Daftar Peserta Lolos --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 sm:p-6">
                <form action="{{ route('admin-pusat.management-course.certificate-submissions.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="submission_letter_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Surat / Nota Dinas Pengajuan
                            </label>
                            <input type="text" id="submission_letter_number" name="submission_letter_number"
                                   placeholder="Contoh: ND-145/D.II/KP.02/X/2026"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                            <p class="text-[11px] text-slate-400 mt-1">Nomor surat resmi dari unit kerja Anda yang diajukan ke Kepala PPSDM.</p>
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan Pengantar
                            </label>
                            <input type="text" id="notes" name="notes"
                                   placeholder="Contoh: Usulan penerbitan sertifikat Bimtek SPBE Batch 1"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                        </div>
                    </div>

                    {{-- Daftar Peserta yang Memenuhi Syarat --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Daftar Calon Lulusan yang Memenuhi Syarat
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Ditemukan <strong class="text-emerald-600 font-bold">{{ $eligibleStudents->count() }}</strong> peserta yang tuntas modul 100% dan lulus Post-Test.
                                </p>
                            </div>
                        </div>

                        @if ($eligibleStudents->isNotEmpty())
                            <div class="border border-slate-200 rounded-xl overflow-hidden max-h-80 overflow-y-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-600 uppercase tracking-wider sticky top-0 z-10">
                                        <tr>
                                            <th class="p-3 w-10 text-center">Pilih</th>
                                            <th class="p-3">Nama Lengkap & Email</th>
                                            <th class="p-3 text-center">Progres Modul</th>
                                            <th class="p-3 text-center">Presensi Zoom</th>
                                            <th class="p-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($eligibleStudents as $st)
                                            <tr class="hover:bg-slate-50">
                                                <td class="p-3 text-center">
                                                    <input type="checkbox" name="student_ids[]" value="{{ $st->user_id }}" checked
                                                           class="rounded text-[#13416B] focus:ring-[#13416B] w-4 h-4 cursor-pointer">
                                                </td>
                                                <td class="p-3">
                                                    <p class="font-bold text-slate-800">{{ $st->user_name }}</p>
                                                    <p class="text-[11px] text-slate-400">{{ $st->user_email }}</p>
                                                </td>
                                                <td class="p-3 text-center font-bold text-emerald-600 tabular-nums">
                                                    {{ $st->progress }}%
                                                </td>
                                                <td class="p-3 text-center">
                                                    @if ($st->zoom_attended)
                                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                            Hadir
                                                        </span>
                                                    @else
                                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                            Belum Hadir
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="p-3 text-center">
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">
                                                        Lulus Ujian
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                                   class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg">
                                    Batal
                                </a>
                                <button type="submit"
                                        class="px-5 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                                    <i class="fas fa-paper-plane mr-1 text-[11px]"></i>
                                    Buat Pengajuan ke PPSDM ({{ $eligibleStudents->count() }} ASN)
                                </button>
                            </div>
                        @else
                            <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-sm font-semibold text-slate-700">Belum Ada Peserta yang Memenuhi Syarat</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Peserta pada gelombang ini belum ada yang menyelesaikan 100% modul dan lulus Post-Test. Pastikan peserta telah menyelesaikan seluruh materi dan ujian terlebih dahulu.
                                </p>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        @endif

    </div>
</x-dashboard::layouts.dashboard>
