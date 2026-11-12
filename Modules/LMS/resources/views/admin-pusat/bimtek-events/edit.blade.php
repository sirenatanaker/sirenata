<x-dashboard::layouts.dashboard title="Edit Gelombang Bimtek - Admin Pusat">
    <div class="w-full p-8 space-y-6">

        {{-- Breadcrumb Navigation (2 Teks) --}}
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        Periode Event
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 mx-1.5"></i>
                    <span class="text-sm font-bold text-slate-900">
                        Edit Gelombang
                    </span>
                </li>
            </ol>
        </nav>

        {{-- Form Card --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs p-6 sm:p-8">
            <form action="{{ route('admin-pusat.management-course.bimtek-events.update', $event->id) }}" method="POST" id="bimtekForm" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Section: Informasi Pokok Gelombang --}}
                <div class="space-y-4">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                        Informasi Kursus & Kapasitas
                    </h2>

                    {{-- Pilih Master Kursus --}}
                    <div>
                        <label for="course_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Master Kursus / Kurikulum <span class="text-rose-500">*</span>
                        </label>
                        <select id="course_id" name="course_id" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                            @foreach ($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('course_id', $event->course_id) == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Nama Gelombang --}}
                        <div class="md:col-span-1">
                            <label for="batch_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Gelombang / Angkatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="batch_name" name="batch_name" value="{{ old('batch_name', $event->batch_name) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('batch_name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kode Batch --}}
                        <div class="md:col-span-1">
                            <label for="batch_code" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kode Registrasi Gelombang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="batch_code" name="batch_code" value="{{ old('batch_code', $event->batch_code) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-mono text-xs">
                            @error('batch_code')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kuota Peserta dengan Opsi Tanpa Batasan --}}
                        <div class="md:col-span-1" x-data="{ isUnlimited: {{ old('is_unlimited_quota', is_null($event->quota) || $event->quota <= 0 ? 'true' : 'false') }} }">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="quota" class="block text-xs font-semibold text-slate-700">
                                    Kapasitas Kuota <span class="text-rose-500" x-show="!isUnlimited">*</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_unlimited_quota" value="1"
                                           x-model="isUnlimited"
                                           class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] h-3.5 w-3.5">
                                    <span class="text-xs text-slate-600 font-medium">Tanpa Batas</span>
                                </label>
                            </div>
                            <div x-show="!isUnlimited">
                                <input type="number" id="quota" name="quota"
                                       value="{{ old('quota', $event->quota ?: 100) }}" min="1"
                                       :required="!isUnlimited"
                                       placeholder="Contoh: 100"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                @error('quota')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div x-show="isUnlimited" x-cloak
                                 class="px-3 py-2 bg-slate-50 border border-dashed border-slate-300 rounded-lg text-xs text-slate-600 flex items-center gap-2">
                                <i class="fas fa-infinity text-[#0B5CFF] text-sm"></i>
                                <span>Kapasitas peserta terbuka <strong>tanpa batasan kuota</strong>.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- SEKSI PENGATURAN PERIODE & LINIMASA PROGRAM              --}}
                {{-- ======================================================== --}}
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-1">
                        <div>
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Pengaturan Periode Linimasa Pelatihan
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pengisian wajib berurutan (Pendaftaran &rarr; Pelaksanaan & Zoom &rarr; Evaluasi) untuk mencegah kesalahan jadwal (anti human-error).
                            </p>
                        </div>
                    </div>

                    {{-- Ringkasan Linimasa & Estimasi Status Otomatis (Background Sirenata) --}}
                    <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/15 pb-3">
                            <div>
                                <span class="text-[11px] font-semibold text-slate-200 block">Rentang Waktu Keseluruhan Program:</span>
                                <p id="summary-timeline-text" class="text-sm sm:text-lg font-extrabold text-white mt-0.5">
                                    {{ $event->overall_timeline_formatted }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="summary-duration-tag" class="hidden px-2.5 py-1 rounded-md text-xs font-semibold bg-white/15 border border-white/20 text-white">
                                    <span id="summary-duration-text">-</span>
                                </span>
                                <span id="summary-status-tag" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white/15 border border-white/20 text-white">
                                    Status Otomatis: <strong id="summary-status-text" class="text-white">{{ $event->status_badge['label'] }}</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Step Flow Sederhana --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">1. Periode Pendaftaran</span>
                                <span id="flow-reg-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->registration_start_at ? $event->registration_start_at->translatedFormat('d M') . ' - ' . ($event->registration_end_at ? $event->registration_end_at->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">2. Periode Pelaksanaan & Zoom</span>
                                <span id="flow-exec-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->start_date ? $event->start_date->translatedFormat('d M') . ' - ' . ($event->end_date ? $event->end_date->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">3. Evaluasi & Sertifikat</span>
                                <span id="flow-eval-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->assessment_start_at ? $event->assessment_start_at->translatedFormat('d M') . ' - ' . ($event->assessment_end_at ? $event->assessment_end_at->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 1. PERIODE PENDAFTARAN --}}
                    <div id="card-stage-1" class="p-4 rounded-md border border-slate-200 bg-white space-y-3 transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pendaftaran</h3>
                            </div>
                            <span id="stage-1-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Langkah 1: Atur Pendaftaran
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Buka Pendaftaran
                                </label>
                                <input type="date" id="registration_start_at" name="registration_start_at"
                                       value="{{ old('registration_start_at', $event->registration_start_at ? $event->registration_start_at->format('Y-m-d') : '') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('registration_start_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="registration_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Tutup Pendaftaran
                                </label>
                                <input type="date" id="registration_end_at" name="registration_end_at"
                                       value="{{ old('registration_end_at', $event->registration_end_at ? $event->registration_end_at->format('Y-m-d') : '') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('registration_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. PERIODE PELAKSANAAN & LIVE ZOOM --}}
                    <div id="card-stage-2" class="p-4 rounded-md border border-slate-200 bg-white space-y-4 transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span id="stage-2-num" class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pelaksanaan & Live Zoom</h3>
                            </div>
                            <span id="stage-2-badge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                Langkah 2
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Mulai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date"
                                       value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('start_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Selesai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="end_date" name="end_date"
                                       value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('end_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- SUB-CARD: DETAIL SESI ZOOM (Biru Kalem & Teks White, Wajib Mengatur Jam) --}}
                        <div id="card-zoom" class="p-4 sm:p-5 rounded-md bg-[#1E40AF] text-white shadow-sm space-y-3.5 transition-all">
                            <div class="flex items-center justify-between border-b border-white/20 pb-2.5">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                    Detail Sesi Live Zoom (Wajib Disertai Jam)
                                </span>
                                <span id="zoom-status-badge" class="text-[11px] text-blue-100 font-medium">
                                    Jadwal harus di dalam rentang tanggal pelaksanaan
                                </span>
                            </div>

                            <div>
                                <label for="zoom_url" class="block text-xs font-semibold text-white mb-1">
                                    Link URL Meeting Zoom
                                </label>
                                <input type="url" id="zoom_url" name="zoom_url" value="{{ old('zoom_url', $event->zoom_url) }}"
                                       placeholder="https://zoom.us/j/1234567890?pwd=..."
                                       class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                @error('zoom_url')
                                    <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="zoom_scheduled_at" class="block text-xs font-semibold text-white mb-1">
                                        Tanggal & Jam Mulai Sesi Zoom
                                    </label>
                                    <input type="datetime-local" id="zoom_scheduled_at" name="zoom_scheduled_at"
                                           value="{{ old('zoom_scheduled_at', $event->zoom_scheduled_at ? $event->zoom_scheduled_at->format('Y-m-d\TH:i') : '') }}"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                    @error('zoom_scheduled_at')
                                        <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="zoom_end_at" class="block text-xs font-semibold text-white mb-1">
                                        Tanggal & Jam Selesai Sesi Zoom (Opsional)
                                    </label>
                                    <input type="datetime-local" id="zoom_end_at" name="zoom_end_at"
                                           value="{{ old('zoom_end_at', $event->zoom_end_at ? $event->zoom_end_at->format('Y-m-d\TH:i') : '') }}"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                    @error('zoom_end_at')
                                        <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Pengaturan Rentang Jam Pembukaan Presensi (Terkunci Ketat di Rentang Zoom) --}}
                            <div id="card-attendance" class="p-3 rounded-lg bg-black/20 border border-white/15 space-y-2 mt-2 transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-300">
                                        <i class="fas fa-clock"></i>
                                        <span>Jadwal Buka & Tutup Presensi Peserta</span>
                                    </div>
                                    <span id="attendance-status-badge" class="text-[11px] text-blue-100">
                                        Terkunci ketat di dalam rentang sesi Zoom
                                    </span>
                                </div>
                                <p class="text-[11px] text-blue-100/90 leading-relaxed">
                                    Tombol presensi di akun peserta akan <strong>otomatis terbuka</strong> saat jam buka tiba dan terkunci kembali setelah jam tutup. Pilihan waktu dibatasi strictly di dalam jadwal sesi Zoom.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label for="zoom_attendance_start_at" class="block text-xs font-medium text-white mb-1">
                                            Tanggal & Jam Buka Presensi
                                        </label>
                                        <input type="datetime-local" id="zoom_attendance_start_at" name="zoom_attendance_start_at"
                                               value="{{ old('zoom_attendance_start_at', $event->zoom_attendance_start_at ? $event->zoom_attendance_start_at->format('Y-m-d\TH:i') : '') }}"
                                               class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                        @error('zoom_attendance_start_at')
                                            <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="zoom_attendance_end_at" class="block text-xs font-medium text-white mb-1">
                                            Tanggal & Jam Tutup Presensi
                                        </label>
                                        <input type="datetime-local" id="zoom_attendance_end_at" name="zoom_attendance_end_at"
                                               value="{{ old('zoom_attendance_end_at', $event->zoom_attendance_end_at ? $event->zoom_attendance_end_at->format('Y-m-d\TH:i') : '') }}"
                                               class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                        @error('zoom_attendance_end_at')
                                            <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label for="zoom_meeting_id" class="block text-xs font-semibold text-white mb-1">
                                        Meeting ID (Opsional)
                                    </label>
                                    <input type="text" id="zoom_meeting_id" name="zoom_meeting_id" value="{{ old('zoom_meeting_id', $event->zoom_meeting_id) }}"
                                           placeholder="Contoh: 839 2039 1234"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                </div>
                                <div>
                                    <label for="zoom_passcode" class="block text-xs font-semibold text-white mb-1">
                                        Passcode Zoom (Opsional)
                                    </label>
                                    <input type="text" id="zoom_passcode" name="zoom_passcode" value="{{ old('zoom_passcode', $event->zoom_passcode) }}"
                                           placeholder="Contoh: SPBE2026"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. PERIODE EVALUASI & SERTIFIKAT --}}
                    <div id="card-stage-3" class="p-4 rounded-md border border-slate-200 bg-white space-y-3 transition-all">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span id="stage-3-num" class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Evaluasi & Sertifikat</h3>
                            </div>
                            <span id="stage-3-badge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                Langkah 3
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="assessment_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Mulai Evaluasi
                                </label>
                                <input type="date" id="assessment_start_at" name="assessment_start_at"
                                       value="{{ old('assessment_start_at', $event->assessment_start_at ? $event->assessment_start_at->format('Y-m-d') : '') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('assessment_start_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="assessment_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Batas Akhir Evaluasi / Sertifikat
                                </label>
                                <input type="date" id="assessment_end_at" name="assessment_end_at"
                                       value="{{ old('assessment_end_at', $event->assessment_end_at ? $event->assessment_end_at->format('Y-m-d') : '') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('assessment_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.show', $event->id) }}"
                       class="px-5 py-2.5 sm:px-6 sm:py-3 text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 sm:px-8 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-xl transition-all shadow-sm active:scale-95">
                        Perbarui Gelombang
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const regStart = document.getElementById('registration_start_at');
                const regEnd = document.getElementById('registration_end_at');
                const execStart = document.getElementById('start_date');
                const execEnd = document.getElementById('end_date');
                const zoomUrl = document.getElementById('zoom_url');
                const zoomStart = document.getElementById('zoom_scheduled_at');
                const zoomEnd = document.getElementById('zoom_end_at');
                const zoomMeetingId = document.getElementById('zoom_meeting_id');
                const zoomPasscode = document.getElementById('zoom_passcode');
                const attStart = document.getElementById('zoom_attendance_start_at');
                const attEnd = document.getElementById('zoom_attendance_end_at');
                const evalStart = document.getElementById('assessment_start_at');
                const evalEnd = document.getElementById('assessment_end_at');

                const cardStage1 = document.getElementById('card-stage-1');
                const stage1Badge = document.getElementById('stage-1-badge');
                const cardStage2 = document.getElementById('card-stage-2');
                const stage2Num = document.getElementById('stage-2-num');
                const stage2Badge = document.getElementById('stage-2-badge');
                const cardZoom = document.getElementById('card-zoom');
                const zoomBadge = document.getElementById('zoom-status-badge');
                const cardAtt = document.getElementById('card-attendance');
                const attBadge = document.getElementById('attendance-status-badge');
                const cardStage3 = document.getElementById('card-stage-3');
                const stage3Num = document.getElementById('stage-3-num');
                const stage3Badge = document.getElementById('stage-3-badge');

                const summaryText = document.getElementById('summary-timeline-text');
                const durationTag = document.getElementById('summary-duration-tag');
                const durationText = document.getElementById('summary-duration-text');
                const statusText = document.getElementById('summary-status-text');
                const flowReg = document.getElementById('flow-reg-text');
                const flowExec = document.getElementById('flow-exec-text');
                const flowEval = document.getElementById('flow-eval-text');

                function formatDateShort(val) {
                    if (!val) return null;
                    const datePart = val.split('T')[0];
                    const parts = datePart.split('-');
                    if (parts.length === 3) {
                        const d = new Date(parts[0], parts[1] - 1, parts[2]);
                        if (!isNaN(d.getTime())) {
                            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        }
                    }
                    const d = new Date(val);
                    if (isNaN(d.getTime())) return null;
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                }

                // Fungsi sinkronisasi constraint min/max & pengisian berurutan (Strict Sequential Gating)
                function syncDateConstraints() {
                    // ==========================================
                    // 1. TAHAP 1: PENDAFTARAN
                    // ==========================================
                    if (regStart?.value) {
                        regEnd.disabled = false;
                        regEnd.classList.remove('bg-slate-100', 'cursor-not-allowed');
                        regEnd.min = regStart.value;
                        if (regEnd.value && regEnd.value <= regStart.value) {
                            regEnd.value = '';
                        }
                    } else {
                        regEnd.disabled = true;
                        regEnd.classList.add('bg-slate-100', 'cursor-not-allowed');
                        regEnd.value = '';
                        regEnd.removeAttribute('min');
                    }

                    const isStage1Complete = Boolean(regStart?.value && regEnd?.value);

                    if (stage1Badge) {
                        if (isStage1Complete) {
                            stage1Badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1';
                            stage1Badge.innerHTML = '<i class="fas fa-check-circle text-[10px]"></i> Tahap 1 Selesai';
                        } else if (regStart?.value) {
                            stage1Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1';
                            stage1Badge.innerHTML = '<i class="fas fa-clock text-[10px]"></i> Isi Tutup Pendaftaran';
                        } else {
                            stage1Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1';
                            stage1Badge.innerHTML = 'Langkah 1: Mulai Pendaftaran';
                        }
                    }

                    // ==========================================
                    // 2. TAHAP 2: PELAKSANAAN & LIVE ZOOM
                    // ==========================================
                    if (!isStage1Complete) {
                        // Kunci Tahap 2 jika Tahap 1 belum lengkap
                        execStart.disabled = true;
                        execStart.classList.add('bg-slate-100', 'cursor-not-allowed');
                        execStart.value = '';
                        execEnd.disabled = true;
                        execEnd.classList.add('bg-slate-100', 'cursor-not-allowed');
                        execEnd.value = '';

                        if (cardStage2) cardStage2.classList.add('opacity-60');
                        if (stage2Num) {
                            stage2Num.className = 'w-6 h-6 rounded-full bg-slate-300 text-slate-600 text-xs font-bold flex items-center justify-center shrink-0';
                        }
                        if (stage2Badge) {
                            stage2Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1';
                            stage2Badge.innerHTML = '<i class="fas fa-lock text-[9px]"></i> Lengkapi Tahap 1 Terlebih Dahulu';
                        }
                    } else {
                        // Buka Tahap 2
                        if (cardStage2) cardStage2.classList.remove('opacity-60');
                        if (stage2Num) {
                            stage2Num.className = 'w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0';
                        }

                        execStart.disabled = false;
                        execStart.classList.remove('bg-slate-100', 'cursor-not-allowed');
                        execStart.min = regEnd.value;
                        if (execStart.value && execStart.value <= regEnd.value) {
                            execStart.value = '';
                        }

                        if (execStart.value) {
                            execEnd.disabled = false;
                            execEnd.classList.remove('bg-slate-100', 'cursor-not-allowed');
                            execEnd.min = execStart.value;
                            if (execEnd.value && execEnd.value <= execStart.value) {
                                execEnd.value = '';
                            }
                        } else {
                            execEnd.disabled = true;
                            execEnd.classList.add('bg-slate-100', 'cursor-not-allowed');
                            execEnd.value = '';
                            execEnd.removeAttribute('min');
                        }

                        if (stage2Badge) {
                            if (execStart.value && execEnd.value) {
                                stage2Badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1';
                                stage2Badge.innerHTML = '<i class="fas fa-check-circle text-[10px]"></i> Pelaksanaan Ditentukan';
                            } else if (execStart.value) {
                                stage2Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1';
                                stage2Badge.innerHTML = '<i class="fas fa-clock text-[10px]"></i> Isi Selesai Pelaksanaan';
                            } else {
                                stage2Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1';
                                stage2Badge.innerHTML = 'Langkah 2: Isi Mulai Pelaksanaan';
                            }
                        }
                    }

                    const isExecComplete = Boolean(isStage1Complete && execStart?.value && execEnd?.value);

                    // ==========================================
                    // 2B. DETAIL SESI LIVE ZOOM
                    // ==========================================
                    const zoomInputs = [zoomUrl, zoomStart, zoomEnd, zoomMeetingId, zoomPasscode];
                    if (!isExecComplete) {
                        zoomInputs.forEach(el => {
                            if (el) {
                                el.disabled = true;
                                el.classList.add('opacity-50', 'cursor-not-allowed');
                                if (el === zoomStart || el === zoomEnd) el.value = '';
                            }
                        });
                        if (cardZoom) cardZoom.classList.add('opacity-60');
                        if (zoomBadge) zoomBadge.innerHTML = '<i class="fas fa-lock mr-1"></i> Buka setelah tanggal pelaksanaan diisi';
                    } else {
                        if (cardZoom) cardZoom.classList.remove('opacity-60');
                        [zoomUrl, zoomMeetingId, zoomPasscode, zoomStart].forEach(el => {
                            if (el) {
                                el.disabled = false;
                                el.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        });

                        const minZoomTime = `${execStart.value}T00:00`;
                        const maxZoomTime = `${execEnd.value}T23:59`;

                        zoomStart.min = minZoomTime;
                        zoomStart.max = maxZoomTime;

                        if (zoomStart.value) {
                            if (zoomStart.value < minZoomTime || zoomStart.value > maxZoomTime) {
                                zoomStart.value = '';
                            }
                        }

                        if (zoomStart.value) {
                            zoomEnd.disabled = false;
                            zoomEnd.classList.remove('opacity-50', 'cursor-not-allowed');
                            zoomEnd.min = zoomStart.value;
                            zoomEnd.max = maxZoomTime;
                            if (zoomEnd.value) {
                                if (zoomEnd.value <= zoomStart.value || zoomEnd.value > maxZoomTime) {
                                    zoomEnd.value = '';
                                }
                            }
                            if (zoomBadge) zoomBadge.textContent = 'Jadwal sesi Zoom aktif di rentang pelaksanaan';
                        } else {
                            zoomEnd.disabled = true;
                            zoomEnd.classList.add('opacity-50', 'cursor-not-allowed');
                            zoomEnd.value = '';
                            zoomEnd.removeAttribute('min');
                            zoomEnd.removeAttribute('max');
                            if (zoomBadge) zoomBadge.textContent = 'Atur tanggal & jam mulai sesi Zoom';
                        }
                    }

                    // ==========================================
                    // 2C. DETAIL PRESENSI (Strictly dalam rentang Zoom)
                    // ==========================================
                    const isZoomConfigured = Boolean(isExecComplete && zoomStart?.value);
                    const attMinLimit = zoomStart?.value || (execStart?.value ? `${execStart.value}T00:00` : null);
                    const attMaxLimit = zoomEnd?.value || (execEnd?.value ? `${execEnd.value}T23:59` : null);

                    if (!isZoomConfigured || !attMinLimit || !attMaxLimit) {
                        if (attStart) {
                            attStart.disabled = true;
                            attStart.classList.add('opacity-50', 'cursor-not-allowed');
                            attStart.value = '';
                            attStart.removeAttribute('min');
                            attStart.removeAttribute('max');
                        }
                        if (attEnd) {
                            attEnd.disabled = true;
                            attEnd.classList.add('opacity-50', 'cursor-not-allowed');
                            attEnd.value = '';
                            attEnd.removeAttribute('min');
                            attEnd.removeAttribute('max');
                        }
                        if (cardAtt) cardAtt.classList.add('opacity-60');
                        if (attBadge) attBadge.innerHTML = '<i class="fas fa-lock mr-1"></i> Buka setelah sesi Zoom diatur';
                    } else {
                        if (cardAtt) cardAtt.classList.remove('opacity-60');
                        if (attStart) {
                            attStart.disabled = false;
                            attStart.classList.remove('opacity-50', 'cursor-not-allowed');
                            attStart.min = attMinLimit;
                            attStart.max = attMaxLimit;

                            if (attStart.value) {
                                if (attStart.value < attMinLimit || attStart.value > attMaxLimit) {
                                    attStart.value = '';
                                }
                            }
                        }

                        if (attStart?.value) {
                            if (attEnd) {
                                attEnd.disabled = false;
                                attEnd.classList.remove('opacity-50', 'cursor-not-allowed');
                                attEnd.min = attStart.value;
                                attEnd.max = attMaxLimit;

                                if (attEnd.value) {
                                    if (attEnd.value <= attStart.value || attEnd.value > attMaxLimit) {
                                        attEnd.value = '';
                                    }
                                }
                            }
                            if (attBadge) attBadge.textContent = 'Rentang presensi terkunci ketat di dalam sesi Zoom';
                        } else {
                            if (attEnd) {
                                attEnd.disabled = true;
                                attEnd.classList.add('opacity-50', 'cursor-not-allowed');
                                attEnd.value = '';
                                attEnd.removeAttribute('min');
                                attEnd.removeAttribute('max');
                            }
                            if (attBadge) attBadge.textContent = 'Atur jam buka presensi (terkunci di rentang Zoom)';
                        }
                    }

                    // ==========================================
                    // 3. TAHAP 3: EVALUASI & SERTIFIKAT
                    // ==========================================
                    if (!isExecComplete) {
                        evalStart.disabled = true;
                        evalStart.classList.add('bg-slate-100', 'cursor-not-allowed');
                        evalStart.value = '';
                        evalEnd.disabled = true;
                        evalEnd.classList.add('bg-slate-100', 'cursor-not-allowed');
                        evalEnd.value = '';

                        if (cardStage3) cardStage3.classList.add('opacity-60');
                        if (stage3Num) {
                            stage3Num.className = 'w-6 h-6 rounded-full bg-slate-300 text-slate-600 text-xs font-bold flex items-center justify-center shrink-0';
                        }
                        if (stage3Badge) {
                            stage3Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1';
                            stage3Badge.innerHTML = '<i class="fas fa-lock text-[9px]"></i> Lengkapi Tahap 2 Terlebih Dahulu';
                        }
                    } else {
                        if (cardStage3) cardStage3.classList.remove('opacity-60');
                        if (stage3Num) {
                            stage3Num.className = 'w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0';
                        }

                        evalStart.disabled = false;
                        evalStart.classList.remove('bg-slate-100', 'cursor-not-allowed');
                        evalStart.min = execEnd.value;
                        if (evalStart.value && evalStart.value < execEnd.value) {
                            evalStart.value = '';
                        }

                        if (evalStart.value) {
                            evalEnd.disabled = false;
                            evalEnd.classList.remove('bg-slate-100', 'cursor-not-allowed');
                            evalEnd.min = evalStart.value;
                            if (evalEnd.value && evalEnd.value < evalStart.value) {
                                evalEnd.value = '';
                            }
                        } else {
                            evalEnd.disabled = true;
                            evalEnd.classList.add('bg-slate-100', 'cursor-not-allowed');
                            evalEnd.value = '';
                            evalEnd.removeAttribute('min');
                        }

                        if (stage3Badge) {
                            if (evalStart.value && evalEnd.value) {
                                stage3Badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1';
                                stage3Badge.innerHTML = '<i class="fas fa-check-circle text-[10px]"></i> Evaluasi Terjadwal';
                            } else if (evalStart.value) {
                                stage3Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1';
                                stage3Badge.innerHTML = '<i class="fas fa-clock text-[10px]"></i> Isi Batas Akhir Evaluasi';
                            } else {
                                stage3Badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1';
                                stage3Badge.innerHTML = 'Langkah 3: Atur Evaluasi';
                            }
                        }
                    }
                }

                // Fungsi hitung estimasi status otomatis secara real-time
                function calculateEstimatedStatus(rStartVal, rEndVal, eStartVal, eEndVal, evEndVal) {
                    const now = new Date();
                    const eStart = eStartVal ? new Date(`${eStartVal}T00:00:00`) : null;
                    const eEnd = eEndVal ? new Date(`${eEndVal}T23:59:59`) : null;
                    const rStart = rStartVal ? new Date(`${rStartVal}T00:00:00`) : null;
                    const rEnd = rEndVal ? new Date(`${rEndVal}T23:59:59`) : null;
                    const evEnd = evEndVal ? new Date(`${evEndVal}T23:59:59`) : eEnd;

                    if (evEnd && now > evEnd) {
                        return 'Selesai';
                    }
                    if (eStart && now >= eStart) {
                        return 'Sedang Berlangsung';
                    }
                    if (rEnd && now > rEnd) {
                        return 'Sedang Berlangsung';
                    }
                    if (rStart && rEnd && now >= rStart && now <= rEnd) {
                        return 'Pendaftaran Buka';
                    }
                    if (rStart && now < rStart) {
                        return 'Mendatang / Draft';
                    }
                    return 'Pendaftaran Buka';
                }

                function updateTimelineSummary() {
                    syncDateConstraints();

                    const overallStartVal = regStart?.value || execStart?.value;
                    const overallEndVal = evalEnd?.value || execEnd?.value || evalStart?.value;

                    if (overallStartVal && overallEndVal) {
                        const dStartParts = overallStartVal.split('T')[0].split('-');
                        const dEndParts = overallEndVal.split('T')[0].split('-');
                        const dStart = new Date(dStartParts[0], dStartParts[1] - 1, dStartParts[2]);
                        const dEnd = new Date(dEndParts[0], dEndParts[1] - 1, dEndParts[2]);

                        if (!isNaN(dStart.getTime()) && !isNaN(dEnd.getTime())) {
                            summaryText.textContent = `${formatDateShort(overallStartVal)} s/d ${formatDateShort(overallEndVal)}`;

                            const diffTime = dEnd - dStart;
                            const diffDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1);
                            if (durationTag && durationText) {
                                durationText.textContent = `${diffDays} Hari Program`;
                                durationTag.classList.remove('hidden');
                            }
                        }
                    } else if (overallStartVal) {
                        summaryText.textContent = `Mulai: ${formatDateShort(overallStartVal)}`;
                        if (durationTag) durationTag.classList.add('hidden');
                    } else {
                        summaryText.textContent = 'Silakan atur tanggal periode di bawah...';
                        if (durationTag) durationTag.classList.add('hidden');
                    }

                    // Tampilkan estimasi status
                    if (execStart?.value && execEnd?.value) {
                        const est = calculateEstimatedStatus(regStart?.value, regEnd?.value, execStart.value, execEnd.value, evalEnd?.value);
                        if (statusText) statusText.textContent = est;
                    } else {
                        if (statusText) statusText.textContent = 'Menyesuaikan Tanggal';
                    }

                    // Update mini flow text
                    if (flowReg) {
                        flowReg.textContent = (regStart?.value && regEnd?.value)
                            ? `${formatDateShort(regStart.value)} - ${formatDateShort(regEnd.value)}`
                            : (regStart?.value ? `Mulai ${formatDateShort(regStart.value)}` : '-');
                    }
                    if (flowExec) {
                        flowExec.textContent = (execStart?.value && execEnd?.value)
                            ? `${formatDateShort(execStart.value)} - ${formatDateShort(execEnd.value)}`
                            : '-';
                    }
                    if (flowEval) {
                        flowEval.textContent = (evalStart?.value && evalEnd?.value)
                            ? `${formatDateShort(evalStart.value)} - ${formatDateShort(evalEnd.value)}`
                            : (evalStart?.value ? `Mulai ${formatDateShort(evalStart.value)}` : '-');
                    }
                }

                [regStart, regEnd, execStart, execEnd, zoomStart, zoomEnd, attStart, attEnd, evalStart, evalEnd].forEach(el => {
                    el?.addEventListener('change', updateTimelineSummary);
                    el?.addEventListener('input', updateTimelineSummary);
                });

                // Form submit handler: un-disable all inputs to ensure full POST payload
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        form.querySelectorAll('input:disabled, select:disabled').forEach(function (el) {
                            el.disabled = false;
                        });
                    });
                }

                updateTimelineSummary();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
