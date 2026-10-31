<x-dashboard::layouts.dashboard title="Rincian Pengajuan Sertifikat PPSDM - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-5"
         x-data="{
             openNumberModal: false,
             mode: 'pattern',
             pattern: '800.1.2/PPSDM/{{ date('Y') }}/{NUMBER}',
             startNumber: 1
         }">

        {{-- Top Navigasi & Aksi Utama --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-1">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Daftar Pengajuan</span>
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 font-mono">{{ $submission->submission_code }}</h1>
                    @php $badge = $submission->status_badge; @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Bimtek: <strong class="text-slate-800">{{ $submission->bimtekEvent?->batch_name }}</strong> | Kursus: <strong class="text-slate-800">{{ $submission->bimtekEvent?->course?->name }}</strong>
                </p>
            </div>

            {{-- 3 Tombol Aksi Utama Sesuai Alur PRD --}}
            <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                {{-- Aksi 1: Ekspor Excel untuk Nota Dinas PPSDM --}}
                <a href="{{ route('admin-pusat.management-course.certificate-submissions.export-excel', $submission->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors shadow-2xs">
                    <i class="fas fa-file-excel text-xs text-emerald-600"></i>
                    <span>1. Unduh Rekap Nota Dinas (Excel)</span>
                </a>

                {{-- Aksi 2: Input Nomor Resmi dari PPSDM --}}
                <button type="button" @click="openNumberModal = true"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors shadow-2xs">
                    <i class="fas fa-barcode text-xs text-blue-600"></i>
                    <span>2. Input Nomor Sertifikat PPSDM</span>
                </button>

                {{-- Aksi 3: Generate e-Sertifikat Otomatis --}}
                <form action="{{ route('admin-pusat.management-course.certificate-submissions.generate-pdfs', $submission->id) }}" method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin men-generate dokumen PDF e-Sertifikat resmi untuk seluruh peserta yang memiliki nomor PPSDM?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                        <i class="fas fa-certificate text-xs"></i>
                        <span>3. Terbitkan e-Sertifikat Resmi</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Kartu Info Pengajuan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nota Dinas Pengajuan</span>
                <div>
                    <p class="text-xs text-slate-500">Nomor Nota Dinas:</p>
                    <p class="text-sm font-bold text-slate-800">{{ $submission->submission_letter_number ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Diajukan Pada:</p>
                    <p class="text-xs font-semibold text-slate-700">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') . ' WIB' : '-' }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Surat Balasan / SK PPSDM</span>
                <div>
                    <p class="text-xs text-slate-500">Nomor SK / Ketetapan PPSDM:</p>
                    <p class="text-sm font-bold text-slate-800">{{ $submission->ppsdm_decree_number ?: '(Belum Diisi)' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nomor Terbit Pada:</p>
                    <p class="text-xs font-semibold text-slate-700">{{ $submission->numbered_at ? $submission->numbered_at->format('d M Y, H:i') . ' WIB' : '-' }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kelulusan ASN</span>
                <div>
                    <p class="text-xs text-slate-500">Jumlah Peserta yang Diajukan:</p>
                    <p class="text-2xl font-extrabold text-[#13416B] tabular-nums">{{ $submission->total_graduates }} ASN</p>
                </div>
            </div>
        </div>

        {{-- Tabel Peserta & Nomor Sertifikat --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-slate-800">
                        Daftar Peserta & Penomoran Sertifikat PPSDM
                    </h2>
                </div>
                <span class="text-xs text-slate-500">
                    Total {{ $students->count() }} peserta lulus
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center">No.</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Nama Peserta ASN</th>
                            <th class="py-3 px-3.5 min-w-[130px] text-center">Progress / Ujian</th>
                            <th class="py-3 px-3.5 min-w-[140px] text-center">Presensi Zoom</th>
                            <th class="py-3 px-3.5 min-w-[240px]">Nomor Sertifikat Resmi (PPSDM)</th>
                            <th class="py-3 px-3.5 min-w-[140px] text-center">Berkas PDF e-Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($students as $idx => $st)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3.5 text-center text-xs font-semibold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-900 text-xs">{{ $st->user_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $st->user_email }}</p>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                        {{ $st->progress }}% (Lulus)
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if ($st->zoom_attended)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                            <i class="fas fa-check-circle text-emerald-500"></i> Hadir
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5">
                                    @if ($st->official_cert_number)
                                        <div class="flex items-center gap-1.5">
                                            <i class="fas fa-shield-check text-emerald-600 text-xs"></i>
                                            <span class="font-mono text-xs font-extrabold text-slate-900">
                                                {{ $st->official_cert_number }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                                            Menunggu Input Nomor PPSDM
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @if ($st->certificate_file)
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ asset('storage/' . $st->certificate_file) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors">
                                                <i class="fas fa-file-pdf text-[11px]"></i>
                                                <span>Lihat PDF</span>
                                            </a>
                                            <a href="{{ route('verify-certificate', $st->certificate_code) }}" target="_blank"
                                               title="Validasi QR Code Publik"
                                               class="p-1 text-slate-400 hover:text-indigo-600 transition-colors">
                                                <i class="fas fa-qrcode text-sm"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum Di-generate</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-slate-500">
                                    Belum ada peserta dalam berkas pengajuan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL INPUT NOMOR SERTIFIKAT PPSDM (Alpine.js)           --}}
        {{-- ======================================================== --}}
        <div x-show="openNumberModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             @keydown.escape.window="openNumberModal = false">

            <div class="fixed inset-0" @click="openNumberModal = false"></div>

            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] z-10"
                 @click.stop>

                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-barcode text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Input Nomor Sertifikat Resmi PPSDM</h3>
                            <p class="text-[11px] text-slate-500">Masukkan nomor yang diterbitkan oleh PPSDM untuk seluruh peserta</p>
                        </div>
                    </div>
                    <button type="button" @click="openNumberModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form action="{{ route('admin-pusat.management-course.certificate-submissions.save-numbers', $submission->id) }}" method="POST" class="p-6 space-y-4 overflow-y-auto">
                    @csrf

                    <div>
                        <label for="ppsdm_decree_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nomor Surat Balasan / SK Penetapan PPSDM
                        </label>
                        <input type="text" id="ppsdm_decree_number" name="ppsdm_decree_number"
                               value="{{ $submission->ppsdm_decree_number }}"
                               placeholder="Contoh: SK-PPSDM/800.1.2/X/2026"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                    </div>

                    {{-- Tab Pilihan Mode Input --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Metode Penomoran
                        </label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-lg text-xs font-bold">
                            <button type="button" @click="mode = 'pattern'"
                                    :class="mode === 'pattern' ? 'bg-white text-[#13416B] shadow-2xs' : 'text-slate-600'"
                                    class="py-2 rounded-md transition-all">
                                Pola Berurutan Otomatis (Rekomendasi)
                            </button>
                            <button type="button" @click="mode = 'manual'"
                                    :class="mode === 'manual' ? 'bg-white text-[#13416B] shadow-2xs' : 'text-slate-600'"
                                    class="py-2 rounded-md transition-all">
                                Input Manual Per Peserta
                            </button>
                        </div>
                        <input type="hidden" name="mode" :value="mode">
                    </div>

                    {{-- Mode 1: Pola Otomatis --}}
                    <div x-show="mode === 'pattern'" class="p-4 rounded-xl bg-blue-50/50 border border-blue-200/60 space-y-3">
                        <div>
                            <label for="number_pattern" class="block text-xs font-semibold text-slate-700 mb-1">
                                Format Template Nomor PPSDM
                            </label>
                            <input type="text" id="number_pattern" name="number_pattern" x-model="pattern"
                                   placeholder="Contoh: 800.1.2/PPSDM/2026/{NUMBER}"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white font-mono">
                            <p class="text-[11px] text-slate-500 mt-1">
                                Gunakan tag <code>{NUMBER}</code> sebagai posisi angka urut yang akan di-generate otomatis.
                            </p>
                        </div>

                        <div>
                            <label for="start_number" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nomor Awal (Urutan Pertama)
                            </label>
                            <input type="number" id="start_number" name="start_number" x-model="startNumber" min="1"
                                   class="w-32 px-3 py-1.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white">
                        </div>

                        <div class="p-2.5 rounded-lg bg-white border border-blue-200 text-xs text-slate-600">
                            <span>Contoh Hasil Peserta Pertama: </span>
                            <strong class="font-mono text-[#13416B]" x-text="pattern.replace('{NUMBER}', String(startNumber).padStart(4, '0'))"></strong>
                        </div>
                    </div>

                    {{-- Mode 2: Input Manual Per Peserta --}}
                    <div x-show="mode === 'manual'" class="space-y-2 max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-3">
                        @foreach ($students as $st)
                            <div class="flex items-center justify-between gap-3 text-xs py-1 border-b border-slate-100 last:border-0">
                                <span class="font-semibold text-slate-800 truncate w-1/2">{{ $st->user_name }}</span>
                                <input type="text" name="student_numbers[{{ $st->user_id }}]"
                                       value="{{ $st->official_cert_number }}"
                                       placeholder="Nomor sertifikat..."
                                       class="px-2.5 py-1 rounded border border-slate-300 text-xs font-mono w-1/2">
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openNumberModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg shadow-2xs">
                            Simpan Nomor PPSDM
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-dashboard::layouts.dashboard>
