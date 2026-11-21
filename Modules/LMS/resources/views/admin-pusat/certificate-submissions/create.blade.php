<x-dashboard::layouts.dashboard title="Buat Pengajuan Sertifikat ke PPSDM - Admin Pusat">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    @endpush

    <div class="w-full p-4 sm:p-6 lg:p-8 max-w-400 mx-auto space-y-6">

        {{-- Breadcrumb Navigation --}}
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                       class="text-sm sm:text-[15px] font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        Pengajuan Sertifikat PPSDM
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-xs text-slate-300 mx-1.5"></i>
                    <span class="text-sm sm:text-[15px] font-bold text-slate-900">
                        Buat Berkas Pengajuan
                    </span>
                </li>
            </ol>
        </nav>

        {{-- Header Banner Utama Navy (#13416B) --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1.5 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-white tracking-wide" style="font-family: 'Oswald', sans-serif;">
                        Buat Berkas Pengajuan ke PPSDM
                    </h1>
                    <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                        Pilih gelombang periode event untuk menyaring otomatis peserta yang telah tuntas dan siap diajukan penerbitan nomor sertifikat resminya.
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap shrink-0">
                    @if ($selectedEvent)
                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $selectedEvent->id, 'tab' => 'graduation']) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-white/15 hover:bg-white/25 border border-white/20 rounded-md transition-colors shadow-2xs">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Kembali ke Periode Event</span>
                        </a>
                    @else
                        <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-white/15 hover:bg-white/25 border border-white/20 rounded-md transition-colors shadow-2xs">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Kembali ke Daftar</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Container Master Pengajuan --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            {{-- 1. Pilih Gelombang Bimtek --}}
            <form method="GET" action="{{ route('admin-pusat.management-course.certificate-submissions.create') }}"
                  class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 space-y-2.5">
                <label for="bimtek_event_id" class="block text-xs font-bold text-[#13416B] uppercase tracking-wider">
                    1. Pilih Gelombang Periode Event <span class="text-rose-500">*</span>
                </label>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <select id="bimtek_event_id" name="bimtek_event_id" required onchange="this.form.submit()"
                            class="flex-1 px-3.5 py-2.5 rounded-md border border-slate-300 bg-white text-sm sm:text-[15px] focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                        <option value="">-- Pilih Gelombang Bimtek --</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev->id }}" @selected($selectedEventId == $ev->id)>
                                {{ $ev->batch_name }} — {{ $ev->course?->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shrink-0 cursor-pointer">
                        Saring Lulusan
                    </button>
                </div>
            </form>

            @if ($selectedEvent)
                {{-- 2. Banner Info Event Terpilih (Terintegrasi) --}}
                <div class="bg-[#1B568A] text-white px-4 sm:px-5 py-4 border-b border-[#13416B] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-bold text-white truncate">
                            {{ $selectedEvent->batch_name }}
                        </p>
                        <p class="text-xs sm:text-sm text-blue-100 mt-0.5">
                            Master Kursus: <strong class="text-white">{{ $selectedEvent->course?->name ?? '-' }}</strong>
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-md bg-white/15 border border-white/20 text-xs sm:text-sm font-bold text-white shrink-0 self-start sm:self-auto">
                        {{ $eligibleStudents->count() }} Peserta Memenuhi Syarat
                    </span>
                </div>

                {{-- 3. Formulir Nota Dinas & Daftar Calon Lulusan --}}
                @php
                    $allEligibleIds = $eligibleStudents->pluck('user_id')->values()->all();
                @endphp
                <form action="{{ route('admin-pusat.management-course.certificate-submissions.store') }}"
                      method="POST"
                      x-data="{
                          allIds: @js($allEligibleIds),
                          selectedIds: @js($allEligibleIds),
                          get allSelected() {
                              return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
                          },
                          toggleAll() {
                              this.selectedIds = this.allSelected ? [] : [...this.allIds];
                          }
                      }">
                    @csrf
                    <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">

                    {{-- Input Nomor Nota Dinas & Catatan --}}
                    <div class="p-4 sm:p-5 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="submission_letter_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Surat / Nota Dinas Pengajuan
                            </label>
                            <input type="text" id="submission_letter_number" name="submission_letter_number"
                                   value="{{ old('submission_letter_number') }}"
                                   placeholder="Contoh: ND-145/D.II/KP.02/X/2026"
                                   class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                            <p class="text-xs sm:text-sm text-slate-400 mt-1">Nomor surat resmi dari unit kerja Anda yang diajukan ke Kepala PPSDM.</p>
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan Pengantar
                            </label>
                            <input type="text" id="notes" name="notes"
                                   value="{{ old('notes') }}"
                                   placeholder="Contoh: Usulan penerbitan sertifikat Bimtek Batch 1"
                                   class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                            <p class="text-xs sm:text-sm text-slate-400 mt-1">Opsional — keterangan singkat mengenai berkas pengajuan ini.</p>
                        </div>
                    </div>

                    {{-- Sub-Header Daftar Calon Lulusan --}}
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Daftar Calon Lulusan yang Memenuhi Syarat</h3>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Peserta yang telah menuntaskan modul 100%, lulus evaluasi, dan belum pernah diajukan ke PPSDM.
                            </p>
                        </div>
                        @if ($eligibleStudents->isNotEmpty())
                            <span class="text-xs sm:text-sm font-bold text-[#13416B] shrink-0">
                                <span x-text="selectedIds.length"></span> / {{ $eligibleStudents->count() }} dipilih
                            </span>
                        @endif
                    </div>

                    @if ($eligibleStudents->isNotEmpty())
                        <div class="overflow-x-auto max-h-[28rem] overflow-y-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="sticky top-0 z-10">
                                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                        <th class="py-3 px-4 w-12 text-center">
                                            <input type="checkbox"
                                                   :checked="allSelected"
                                                   @change="toggleAll()"
                                                   title="Pilih Semua"
                                                   class="rounded text-[#13416B] focus:ring-[#13416B] w-4 h-4 cursor-pointer">
                                        </th>
                                        <th class="py-3 px-4 min-w-[220px]">Nama Lengkap &amp; Email</th>
                                        <th class="py-3 px-4 text-center min-w-[130px]">Progres Modul</th>
                                        <th class="py-3 px-4 text-center min-w-[140px]">Presensi Zoom</th>
                                        <th class="py-3 px-4 text-center min-w-[140px]">Status Kelulusan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-base">
                                    @foreach ($eligibleStudents as $st)
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td class="py-3.5 px-4 text-center">
                                                <input type="checkbox"
                                                       name="student_ids[]"
                                                       value="{{ $st->user_id }}"
                                                       x-model="selectedIds"
                                                       class="rounded text-[#13416B] focus:ring-[#13416B] w-4 h-4 cursor-pointer">
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <p class="font-bold text-slate-900">{{ $st->user_name }}</p>
                                                <p class="text-sm text-slate-500">{{ $st->user_email }}</p>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="text-sm font-bold text-emerald-600 tabular-nums">{{ $st->progress }}%</span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                @if ($st->zoom_attended)
                                                    <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Hadir
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                        Belum Hadir
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-bold bg-blue-50 text-[#13416B] border border-blue-200">
                                                    Lulus &amp; Tuntas
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Aksi --}}
                        <div class="px-4 sm:px-5 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="text-xs sm:text-sm text-slate-500">
                                Berkas screenshot bukti Zoom peserta yang diajukan akan dibersihkan otomatis dari penyimpanan server setelah pengajuan dibuat.
                            </p>
                            <div class="flex items-center justify-end gap-2.5 shrink-0">
                                <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                                   class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-md transition-colors">
                                    Batal
                                </a>
                                <button type="submit"
                                        :disabled="selectedIds.length === 0"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors shadow-2xs cursor-pointer">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                    <span>Ajukan ke PPSDM (<span x-text="selectedIds.length"></span> Peserta)</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-10 text-center space-y-1.5">
                            <p class="text-base font-bold text-slate-700">Belum Ada Peserta yang Memenuhi Syarat</p>
                            <p class="text-sm text-slate-500 max-w-xl mx-auto">
                                Peserta pada gelombang ini belum ada yang menyelesaikan 100% modul dan lulus evaluasi, atau seluruh peserta yang lulus sudah pernah diajukan ke PPSDM.
                            </p>
                        </div>
                    @endif
                </form>
            @else
                <div class="p-10 text-center space-y-1.5">
                    <p class="text-base font-bold text-slate-700">Pilih Gelombang Periode Event Terlebih Dahulu</p>
                    <p class="text-sm text-slate-500">
                        Pilih salah satu gelombang di atas untuk menampilkan daftar peserta yang lulus dan siap diajukan ke PPSDM.
                    </p>
                </div>
            @endif
        </div>

    </div>
</x-dashboard::layouts.dashboard>
