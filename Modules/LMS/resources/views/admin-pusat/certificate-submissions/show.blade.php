<x-dashboard::layouts.dashboard title="Rincian Pengajuan Sertifikat PPSDM - Admin Pusat">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
        <style>
            @media (min-width: 1024px) {
                .timeline-arrow-start {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%);
                    padding-left: 1rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-mid {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-end {
                    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1rem;
                }
            }
        </style>
    @endpush

    @php
        $totalStudents = $students->count();
        $numberedCount = $students->filter(fn($s) => !empty($s->official_cert_number))->count();
        $pdfCount = $students->filter(fn($s) => !empty($s->certificate_file))->count();

        $requestedStep = request('step');
        if (!in_array($requestedStep, ['excel', 'numbers', 'publish'], true)) {
            $requestedStep = $numberedCount > 0 ? 'publish' : 'excel';
        }
    @endphp

    <div class="w-full p-4 sm:p-6 lg:p-8 max-w-400 mx-auto space-y-6"
         x-data="{
             activeStep: '{{ $requestedStep }}',
             setStep(step) {
                 this.activeStep = step;
                 const url = new URL(window.location.href);
                 url.searchParams.set('step', step);
                 window.history.replaceState({}, '', url.toString());
             }
         }">

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
                        Rincian Pengajuan
                    </span>
                </li>
            </ol>
        </nav>

        {{-- Header Banner Utama Navy (#13416B) + Navigasi Tab 3 Tahap Card Panah --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 border-b border-white/15 pb-4">
                <div class="space-y-2 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-white tracking-wide" style="font-family: 'Oswald', sans-serif;">
                        {{ $submission->bimtekEvent?->batch_name ?? $submission->submission_code }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5 text-sm sm:text-base text-slate-200">
                        <span>Kursus: <strong class="text-white">{{ $submission->bimtekEvent?->course?->name ?? '-' }}</strong></span>
                        <span class="text-white/40 select-none">•</span>
                        <span>Kode Pengajuan: <strong class="text-white font-mono">{{ $submission->submission_code }}</strong></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-blue-200 pt-0.5">
                        @if ($submission->submitted_at)
                            <span>Diajukan: <strong class="text-white">{{ $submission->submitted_at->translatedFormat('d M Y, H:i') }} WIB</strong></span>
                        @endif
                        @if ($submission->submission_letter_number)
                            <span class="text-white/35 select-none">•</span>
                            <span>Nota Dinas: <strong class="text-white">{{ $submission->submission_letter_number }}</strong></span>
                        @endif
                        @if ($submission->ppsdm_decree_number)
                            <span class="text-white/35 select-none">•</span>
                            <span>SK PPSDM: <strong class="text-white">{{ $submission->ppsdm_decree_number }}</strong></span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap shrink-0">
                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
                       class="inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-white/15 hover:bg-white/25 border border-white/20 rounded-md transition-colors shadow-2xs">
                        <span>Daftar Pengajuan</span>
                    </a>
                </div>
            </div>

            {{-- Navigasi 3 Tab Berurutan Desain Card Panah (Sama Seperti Periode Event) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 lg:gap-1.5">
                {{-- Tab 1: Unduh Rekap Excel --}}
                <button type="button"
                        @click="setStep('excel')"
                        :class="activeStep === 'excel' ? 'bg-white text-slate-900 shadow-xs' : 'bg-white/10 hover:bg-white/20 text-white'"
                        class="timeline-arrow-start rounded-md lg:rounded-none lg:rounded-l-md flex items-center gap-3 py-3 px-4 text-left transition-colors cursor-pointer">
                    <span :class="activeStep === 'excel' ? 'bg-[#13416B] text-white' : 'bg-white/15 text-white'"
                          class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center shrink-0">
                        1
                    </span>
                    <div class="min-w-0 flex-1">
                        <span :class="activeStep === 'excel' ? 'text-slate-900' : 'text-white'"
                              class="block text-sm font-bold truncate">
                            Unduh Rekap Excel
                        </span>
                        <p :class="activeStep === 'excel' ? 'text-slate-500' : 'text-blue-100'"
                           class="text-xs truncate mt-0.5">
                            Lampiran Nota Dinas ke PPSDM
                        </p>
                    </div>
                </button>

                {{-- Tab 2: Input Nomor PPSDM --}}
                <button type="button"
                        @click="setStep('numbers')"
                        :class="activeStep === 'numbers' ? 'bg-white text-slate-900 shadow-xs' : 'bg-white/10 hover:bg-white/20 text-white'"
                        class="timeline-arrow-mid rounded-md lg:rounded-none flex items-center gap-3 py-3 px-4 text-left transition-colors cursor-pointer">
                    <span :class="activeStep === 'numbers' ? 'bg-[#13416B] text-white' : 'bg-white/15 text-white'"
                          class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center shrink-0">
                        2
                    </span>
                    <div class="min-w-0 flex-1">
                        <span :class="activeStep === 'numbers' ? 'text-slate-900' : 'text-white'"
                              class="block text-sm font-bold truncate">
                            Input Nomor PPSDM
                        </span>
                        <p :class="activeStep === 'numbers' ? 'text-slate-500' : 'text-blue-100'"
                           class="text-xs truncate mt-0.5">
                            {{ $numberedCount > 0 ? "{$numberedCount} / {$totalStudents} sudah bernomor" : 'Pola otomatis atau input manual' }}
                        </p>
                    </div>
                </button>

                {{-- Tab 3: Terbitkan e-Sertifikat --}}
                <button type="button"
                        @click="setStep('publish')"
                        :class="activeStep === 'publish' ? 'bg-white text-slate-900 shadow-xs' : 'bg-white/10 hover:bg-white/20 text-white'"
                        class="timeline-arrow-end rounded-md lg:rounded-none lg:rounded-r-md flex items-center gap-3 py-3 px-4 text-left transition-colors cursor-pointer">
                    <span :class="activeStep === 'publish' ? 'bg-[#13416B] text-white' : 'bg-white/15 text-white'"
                          class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center shrink-0">
                        3
                    </span>
                    <div class="min-w-0 flex-1">
                        <span :class="activeStep === 'publish' ? 'text-slate-900' : 'text-white'"
                              class="block text-sm font-bold truncate">
                            Terbitkan e-Sertifikat
                        </span>
                        <p :class="activeStep === 'publish' ? 'text-slate-500' : 'text-blue-100'"
                           class="text-xs truncate mt-0.5">
                            {{ $pdfCount > 0 ? "{$pdfCount} / {$totalStudents} PDF diterbitkan" : 'Generate PDF & QR Validasi Resmi' }}
                        </p>
                    </div>
                </button>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- KONTEKS TAB 1: UNDUH REKAP EXCEL UNTUK LAMPIRAN NOTA DINAS PPSDM     --}}
        {{-- ===================================================================== --}}
        <div x-show="activeStep === 'excel'" x-cloak class="space-y-5">
            {{-- Card Tahap 1 --}}
            <div class="bg-[#1B568A] text-white rounded-md p-4 sm:p-5 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-sm sm:text-base font-bold text-white">
                        Tahap 1 — Unduh Rekap Daftar Lulusan (Excel)
                    </h2>
                    <p class="text-xs sm:text-sm text-blue-100 leading-relaxed max-w-3xl">
                        Berkas Excel ini berisi daftar lengkap {{ $totalStudents }} peserta yang telah tuntas pelatihan untuk dilampirkan pada Nota Dinas atau surat pengajuan permintaan nomor registrasi sertifikat ke PPSDM.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.export-excel', $submission->id) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs">
                        <i class="fas fa-file-excel text-emerald-600 text-sm"></i>
                        <span>Unduh Rekap Excel ({{ $totalStudents }} Peserta)</span>
                    </a>
                </div>
            </div>

            {{-- Card Tabel Pratinjau Peserta --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Pratinjau Daftar Peserta yang Akan Diekspor</h3>
                        @if ($submission->notes)
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan pengajuan: {{ $submission->notes }}</p>
                        @endif
                    </div>
                    <span class="text-xs sm:text-sm font-semibold text-slate-500 shrink-0">
                        Total {{ $totalStudents }} peserta lulus
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No.</th>
                                <th class="py-3 px-4 min-w-[220px]">Nama Peserta</th>
                                <th class="py-3 px-4 min-w-[140px] text-center">Progres</th>
                                <th class="py-3 px-4 min-w-[140px] text-center">Presensi Zoom</th>
                                <th class="py-3 px-4 min-w-[160px]">Tanggal Lulus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-base">
                            @forelse ($students as $idx => $st)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-sm sm:text-base font-semibold text-slate-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-slate-900">{{ $st->user_name }}</p>
                                        <p class="text-sm text-slate-500">{{ $st->user_email }}</p>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 tabular-nums">
                                            {{ $st->progress }}% (Lulus)
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($st->zoom_attended)
                                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-600">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Hadir</span>
                                            </span>
                                        @else
                                            <span class="text-sm text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-sm font-semibold text-slate-700 tabular-nums">
                                        {{ $st->completed_at ? \Carbon\Carbon::parse($st->completed_at)->translatedFormat('d M Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-base text-slate-400">
                                        Belum ada peserta dalam berkas pengajuan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- KONTEKS TAB 2: DAFTAR NOMOR SERTIFIKAT PPSDM (FORM VIA MODAL/SHEET)   --}}
        {{-- ===================================================================== --}}
        <div x-show="activeStep === 'numbers'" x-cloak class="space-y-5">
            {{-- Card Tahap 2 --}}
            <div class="bg-[#1B568A] text-white rounded-md p-4 sm:p-5 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-sm sm:text-base font-bold text-white">
                        Tahap 2 — Input Nomor Sertifikat Resmi dari PPSDM
                    </h2>
                    <p class="text-xs sm:text-sm text-blue-100 leading-relaxed max-w-3xl">
                        Setelah menerima surat balasan atau SK penetapan nomor registrasi dari PPSDM, klik tombol di samping untuk mengisi atau memperbarui nomor sertifikat peserta secara otomatis maupun manual.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <button type="button"
                            @click="$dispatch('open-ppsdm-number-sheet')"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs cursor-pointer">
                        <i class="fas {{ $numberedCount > 0 ? 'fa-pen' : 'fa-plus' }} text-xs"></i>
                        <span>{{ $numberedCount > 0 ? 'Edit Nomor' : 'Tambah Nomor' }}</span>
                    </button>
                </div>
            </div>

            {{-- Card Tabel Daftar Nomor Sertifikat --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
                    <h3 class="text-base font-bold text-slate-800">
                        Daftar Nomor Sertifikat Peserta
                    </h3>
                    <span class="text-xs sm:text-sm font-semibold text-slate-500">
                        {{ $numberedCount }} / {{ $totalStudents }} peserta sudah bernomor
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No.</th>
                                <th class="py-3 px-4 min-w-[220px]">Nama Peserta</th>
                                <th class="py-3 px-4 min-w-[280px]">Nomor Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-base">
                            @forelse ($students as $idx => $st)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-sm sm:text-base font-semibold text-slate-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-slate-900">{{ $st->user_name }}</p>
                                        <p class="text-sm text-slate-500">{{ $st->user_email }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($st->official_cert_number)
                                            <span class="font-mono text-sm font-bold text-slate-900">
                                                {{ $st->official_cert_number }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200">
                                                Belum Diisi
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-10 text-center text-base text-slate-400">
                                        Belum ada peserta dalam berkas pengajuan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- KONTEKS TAB 3: TERBITKAN E-SERTIFIKAT RESMI & UNDUH PDF               --}}
        {{-- ===================================================================== --}}
        <div x-show="activeStep === 'publish'" x-cloak class="space-y-5">
            {{-- Card Tahap 3 --}}
            <div class="bg-[#1B568A] text-white rounded-md p-4 sm:p-5 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-sm sm:text-base font-bold text-white">
                        Tahap 3 — Terbitkan e-Sertifikat
                    </h2>
                    <p class="text-xs sm:text-sm text-blue-100 leading-relaxed max-w-3xl">
                        Generate dokumen PDF e-Sertifikat resmi secara otomatis untuk seluruh peserta yang sudah memiliki nomor PPSDM. Setelah diterbitkan, peserta dapat langsung mengunduh sertifikat dari dashboard masing-masing.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    @if ($numberedCount > 0)
                        <button type="button"
                                @click="$dispatch('open-modal', 'confirm-generate-pdfs')"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs cursor-pointer">
                            <i class="fas fa-award text-sm"></i>
                            <span>{{ $pdfCount > 0 ? 'Terbitkan Ulang / Lengkapi PDF' : 'Terbitkan e-Sertifikat Resmi' }}</span>
                        </button>
                    @else
                        <button type="button"
                                @click="setStep('numbers')"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs cursor-pointer">
                            <span>Isi Nomor PPSDM Terlebih Dahulu</span>
                            <i class="fas fa-arrow-left text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Card Tabel Daftar e-Sertifikat --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
                    <h3 class="text-base font-bold text-slate-800">
                        Daftar e-Sertifikat Peserta
                    </h3>
                    <span class="text-xs sm:text-sm font-semibold text-slate-500 shrink-0">
                        {{ $pdfCount }} / {{ $totalStudents }} PDF diterbitkan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No.</th>
                                <th class="py-3 px-4 min-w-[220px]">Nama Peserta</th>
                                <th class="py-3 px-4 min-w-[250px]">Nomor Sertifikat</th>
                                <th class="py-3 px-4 min-w-[180px] text-center">Berkas PDF e-Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-base">
                            @forelse ($students as $idx => $st)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-sm sm:text-base font-semibold text-slate-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-slate-900">{{ $st->user_name }}</p>
                                        <p class="text-sm text-slate-500">{{ $st->user_email }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($st->official_cert_number)
                                            <span class="font-mono text-sm font-bold text-slate-900">
                                                {{ $st->official_cert_number }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200">
                                                Menunggu Input Nomor PPSDM
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($st->certificate_file)
                                            <div class="inline-flex items-center justify-center gap-2">
                                                <a href="{{ asset('storage/' . $st->certificate_file) }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors">
                                                    <i class="fas fa-file-pdf text-xs"></i>
                                                    <span>Lihat PDF</span>
                                                </a>
                                                <a href="{{ route('verify-certificate', $st->certificate_code) }}" target="_blank"
                                                   title="Validasi QR Code Publik"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:text-[#13416B] hover:bg-slate-100 border border-slate-200 transition-colors">
                                                    <i class="fas fa-qrcode text-sm"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400 italic">Belum Diterbitkan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-base text-slate-400">
                                        Belum ada peserta dalam berkas pengajuan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal (Desktop) / Bottom Sheet (Mobile): Input & Edit Nomor Sertifikat PPSDM --}}
        <div x-data="{
                 openSheet: false,
                 mode: 'pattern',
                 pattern: '800.1.2/PPSDM/{{ date('Y') }}/{NUMBER}',
                 startNumber: 1
             }"
             x-on:open-ppsdm-number-sheet.window="openSheet = true"
             x-on:keydown.escape.window="openSheet = false">
            <template x-teleport="body">
                <div x-show="openSheet"
                     x-cloak
                     class="fixed inset-0 z-[99] flex items-end sm:items-center justify-center sm:p-4">
                    {{-- Backdrop --}}
                    <div x-show="openSheet"
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="openSheet = false"
                         class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"></div>

                    {{-- Panel: Bottom Sheet di Mobile, Centered Modal di Desktop --}}
                    <div x-show="openSheet"
                         x-transition:enter="ease-out duration-250"
                         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                         @click.stop
                         class="relative z-10 w-full sm:max-w-2xl bg-white rounded-t-2xl sm:rounded-md border border-slate-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

                        {{-- Handle Bar (Khusus Mobile Bottom Sheet) --}}
                        <div class="sm:hidden flex justify-center pt-2.5 pb-1 bg-slate-50">
                            <span class="w-10 h-1.5 rounded-full bg-slate-300"></span>
                        </div>

                        {{-- Header --}}
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3 shrink-0">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900">
                                    {{ $numberedCount > 0 ? 'Edit Nomor Sertifikat Resmi PPSDM' : 'Tambah Nomor Sertifikat Resmi PPSDM' }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500">
                                    Atur penomoran sertifikat secara otomatis atau input manual per peserta.
                                </p>
                            </div>
                            <button type="button"
                                    @click="openSheet = false"
                                    class="w-8 h-8 rounded-md flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/70 transition-colors cursor-pointer shrink-0">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        {{-- Form Body (Scrollable) --}}
                        <form id="ppsdm-numbers-form"
                              action="{{ route('admin-pusat.management-course.certificate-submissions.save-numbers', $submission->id) }}"
                              method="POST"
                              class="p-5 space-y-5 overflow-y-auto flex-1">
                            @csrf
                            <input type="hidden" name="mode" :value="mode">

                            <div>
                                <label for="ppsdm_decree_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nomor Surat Balasan / SK Penetapan PPSDM
                                </label>
                                <input type="text" id="ppsdm_decree_number" name="ppsdm_decree_number"
                                       value="{{ old('ppsdm_decree_number', $submission->ppsdm_decree_number) }}"
                                       placeholder="Contoh: SK-PPSDM/800.1.2/X/2026"
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                <p class="text-xs text-slate-400 mt-1">Opsional — nomor surat resmi balasan dari PPSDM sebagai arsip pengajuan.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Metode Pengisian Nomor Sertifikat
                                </label>
                                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-md border border-slate-200 text-xs sm:text-sm font-bold">
                                    <button type="button" @click="mode = 'pattern'"
                                            :class="mode === 'pattern' ? 'bg-white text-[#13416B] shadow-2xs' : 'text-slate-600 hover:text-slate-800'"
                                            class="py-2 rounded-md transition-all cursor-pointer">
                                        Pola Otomatis (Rekomendasi)
                                    </button>
                                    <button type="button" @click="mode = 'manual'"
                                            :class="mode === 'manual' ? 'bg-white text-[#13416B] shadow-2xs' : 'text-slate-600 hover:text-slate-800'"
                                            class="py-2 rounded-md transition-all cursor-pointer">
                                        Input Manual Per Peserta
                                    </button>
                                </div>
                            </div>

                            {{-- Mode 1: Pola Otomatis --}}
                            <div x-show="mode === 'pattern'" class="p-4 rounded-md bg-[#DCEBF8]/55 border border-[#BBD6EE] space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                                    <div class="sm:col-span-8">
                                        <label for="number_pattern" class="block text-xs sm:text-sm font-bold text-slate-800 mb-1">
                                            Format Template Nomor PPSDM
                                        </label>
                                        <input type="text" id="number_pattern" name="number_pattern" x-model="pattern"
                                               placeholder="Contoh: 800.1.2/PPSDM/2026/{NUMBER}"
                                               class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm bg-white font-mono focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                        <p class="text-xs text-slate-600 mt-1">
                                            Gunakan tag <code class="px-1 py-0.5 bg-white rounded border border-slate-200 font-bold text-[#13416B]">{NUMBER}</code> sebagai posisi angka urut 4 digit otomatis.
                                        </p>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label for="start_number" class="block text-xs sm:text-sm font-bold text-slate-800 mb-1">
                                            Nomor Urut Awal
                                        </label>
                                        <input type="number" id="start_number" name="start_number" x-model="startNumber" min="1"
                                               class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm bg-white font-bold tabular-nums focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                        <p class="text-xs text-slate-600 mt-1">Untuk peserta #1.</p>
                                    </div>
                                </div>

                                <div class="p-3 rounded-md bg-white border border-[#BBD6EE] space-y-1 text-xs sm:text-sm">
                                    <span class="text-slate-600 block">Pratinjau Nomor Peserta Pertama s/d Ketiga:</span>
                                    <div class="flex flex-wrap items-center gap-1.5 font-mono font-bold text-[#13416B]">
                                        <span x-text="pattern.replace('{NUMBER}', String(Number(startNumber) || 1).padStart(4, '0'))"></span>
                                        <span class="text-slate-300">•</span>
                                        <span x-text="pattern.replace('{NUMBER}', String((Number(startNumber) || 1) + 1).padStart(4, '0'))"></span>
                                        <span class="text-slate-300">•</span>
                                        <span x-text="pattern.replace('{NUMBER}', String((Number(startNumber) || 1) + 2).padStart(4, '0'))"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mode 2: Input Manual Per Peserta --}}
                            <div x-show="mode === 'manual'" x-cloak class="space-y-2 border border-slate-200 rounded-md p-3 max-h-64 overflow-y-auto bg-slate-50/40">
                                @foreach ($students as $idx => $st)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 py-2 border-b border-slate-100 last:border-0">
                                        <div class="min-w-0 sm:w-1/2">
                                            <p class="text-sm font-bold text-slate-800 truncate">{{ $idx + 1 }}. {{ $st->user_name }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ $st->user_email }}</p>
                                        </div>
                                        <input type="text"
                                               name="student_numbers[{{ $st->user_id }}]"
                                               value="{{ $st->official_cert_number }}"
                                               placeholder="Masukkan nomor sertifikat..."
                                               class="w-full sm:w-1/2 px-3 py-1.5 rounded-md border border-slate-300 bg-white text-xs sm:text-sm font-mono focus:border-[#13416B] outline-none">
                                    </div>
                                @endforeach
                            </div>
                        </form>

                        {{-- Sticky Footer --}}
                        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2.5 shrink-0">
                            <p class="text-xs text-slate-500" x-show="mode === 'pattern'">
                                Menerapkan pola ke seluruh {{ $totalStudents }} peserta secara berurutan.
                            </p>
                            <p class="text-xs text-slate-500" x-show="mode === 'manual'" x-cloak>
                                Menyimpan nomor manual yang terisi pada daftar di atas.
                            </p>
                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <button type="button"
                                        @click="openSheet = false"
                                        class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-md transition-colors cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                        form="ppsdm-numbers-form"
                                        class="inline-flex items-center justify-center gap-1.5 px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md shadow-2xs transition-colors cursor-pointer">
                                    <i class="fas fa-save text-xs"></i>
                                    <span>Simpan Nomor PPSDM ({{ $totalStudents }} Peserta)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal Konfirmasi Penerbitan e-Sertifikat Resmi --}}
        <x-modal name="confirm-generate-pdfs" title="Konfirmasi Penerbitan e-Sertifikat Resmi" maxWidth="sm:max-w-md">
            <form action="{{ route('admin-pusat.management-course.certificate-submissions.generate-pdfs', $submission->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-start gap-3.5 py-1">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[#13416B]">
                        <i class="fas fa-award text-sm"></i>
                    </div>
                    <div class="flex-1 pt-0.5 space-y-1">
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed">
                            Terbitkan berkas PDF e-Sertifikat untuk seluruh peserta yang sudah memiliki nomor?
                        </p>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Saat ini terdapat <strong class="text-slate-800">{{ $numberedCount }} dari {{ $totalStudents }} peserta</strong> yang sudah memiliki nomor sertifikat PPSDM.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button"
                            @click="$dispatch('close-modal', 'confirm-generate-pdfs')"
                            class="px-4 py-2 rounded-md text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer">
                        <span>Ya, Terbitkan e-Sertifikat</span>
                    </button>
                </div>
            </form>
        </x-modal>

    </div>
</x-dashboard::layouts.dashboard>
