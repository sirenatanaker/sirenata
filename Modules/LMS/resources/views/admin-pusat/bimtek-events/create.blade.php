<x-dashboard::layouts.dashboard title="Buka Gelombang Bimtek Baru - Admin Pusat">
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
                        Buka Gelombang Baru
                    </span>
                </li>
            </ol>
        </nav>

        {{-- Form Card --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs p-6 sm:p-8">
            <form action="{{ route('admin-pusat.management-course.bimtek-events.store') }}" method="POST" id="bimtekForm" class="space-y-6">
                @csrf

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
                            <option value="">-- Pilih Kursus yang Akan Dijadikan Bimtek --</option>
                            @foreach ($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>
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
                            <input type="text" id="batch_name" name="batch_name" value="{{ old('batch_name') }}"
                                   placeholder="Contoh: Bimtek SPBE Angkatan I 2026" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('batch_name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kode Batch --}}
                        <div class="md:col-span-1">
                            <label for="batch_code" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kode Registrasi Gelombang (Opsional)
                            </label>
                            <input type="text" id="batch_code" name="batch_code" value="{{ old('batch_code') }}"
                                   placeholder="Otomatis jika dikosongkan (BIMTEK-XXXX)"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-mono text-xs">
                        </div>

                        {{-- Kuota Peserta dengan Opsi Tanpa Batasan --}}
                        <div class="md:col-span-1" x-data="{ isUnlimited: {{ old('is_unlimited_quota') ? 'true' : 'false' }} }">
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
                                       value="{{ old('quota', 100) }}" min="1"
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
                                Periode harus berurutan. Setiap periode berikutnya tidak dapat memilih tanggal kurang dari atau sama dengan periode sebelumnya.
                            </p>
                        </div>
                    </div>

                    {{-- Ringkasan Linimasa & Estimasi Status Otomatis (Background Sirenata) --}}
                    <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/15 pb-3">
                            <div>
                                <span class="text-[11px] font-semibold text-slate-200 block">Rentang Waktu Keseluruhan Program:</span>
                                <p id="summary-timeline-text" class="text-sm sm:text-lg font-extrabold text-white mt-0.5">
                                    Silakan atur tanggal periode di bawah...
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="summary-duration-tag" class="hidden px-2.5 py-1 rounded-md text-xs font-semibold bg-white/15 border border-white/20 text-white">
                                    <span id="summary-duration-text">-</span>
                                </span>
                                <span id="summary-status-tag" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white/15 border border-white/20 text-white">
                                    Status Otomatis: <strong id="summary-status-text" class="text-white">Menyesuaikan Tanggal</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Step Flow Sederhana --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">1. Periode Pendaftaran</span>
                                <span id="flow-reg-text" class="font-bold text-white mt-0.5 block">-</span>
                            </div>
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">2. Periode Pelaksanaan & Zoom</span>
                                <span id="flow-exec-text" class="font-bold text-white mt-0.5 block">-</span>
                            </div>
                            <div class="bg-white/10 rounded-md p-2.5 border border-white/15 text-white">
                                <span class="text-[11px] font-semibold text-slate-200 block">3. Evaluasi & Sertifikat</span>
                                <span id="flow-eval-text" class="font-bold text-white mt-0.5 block">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- 1. PERIODE PENDAFTARAN --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pendaftaran</h3>
                            </div>
                            <span class="text-[11px] text-slate-400">Jadwal pembukaan dan penutupan pendaftaran peserta</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Buka Pendaftaran
                                </label>
                                <input type="datetime-local" id="registration_start_at" name="registration_start_at"
                                       value="{{ old('registration_start_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('registration_start_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="registration_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Tutup Pendaftaran
                                </label>
                                <input type="datetime-local" id="registration_end_at" name="registration_end_at"
                                       value="{{ old('registration_end_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('registration_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. PERIODE PELAKSANAAN & LIVE ZOOM --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pelaksanaan & Live Zoom</h3>
                            </div>
                            <span class="text-[11px] text-slate-400">Harus setelah periode pendaftaran berakhir</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Mulai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('start_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Selesai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('end_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- SUB-CARD: DETAIL SESI ZOOM (Biru Kalem & Teks White) --}}
                        <div class="p-4 sm:p-5 rounded-md bg-[#1E40AF] text-white shadow-sm space-y-3.5">
                            <div class="flex items-center justify-between border-b border-white/20 pb-2.5">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                    Detail Sesi Live Zoom
                                </span>
                                <span class="text-[11px] text-blue-100 font-medium">
                                    Jadwal harus di dalam rentang waktu pelaksanaan
                                </span>
                            </div>

                            <div>
                                <label for="zoom_url" class="block text-xs font-semibold text-white mb-1">
                                    Link URL Meeting Zoom
                                </label>
                                <input type="url" id="zoom_url" name="zoom_url" value="{{ old('zoom_url') }}"
                                       placeholder="https://zoom.us/j/1234567890?pwd=..."
                                       class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white">
                                @error('zoom_url')
                                    <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="zoom_scheduled_at" class="block text-xs font-semibold text-white mb-1">
                                        Tanggal & Jam Mulai Sesi Zoom
                                    </label>
                                    <input type="datetime-local" id="zoom_scheduled_at" name="zoom_scheduled_at" value="{{ old('zoom_scheduled_at') }}"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white">
                                    @error('zoom_scheduled_at')
                                        <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="zoom_end_at" class="block text-xs font-semibold text-white mb-1">
                                        Tanggal & Jam Selesai Sesi Zoom (Opsional)
                                    </label>
                                    <input type="datetime-local" id="zoom_end_at" name="zoom_end_at" value="{{ old('zoom_end_at') }}"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white">
                                    @error('zoom_end_at')
                                        <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <div>
                                    <label for="zoom_meeting_id" class="block text-xs font-semibold text-white mb-1">
                                        Meeting ID (Opsional)
                                    </label>
                                    <input type="text" id="zoom_meeting_id" name="zoom_meeting_id" value="{{ old('zoom_meeting_id') }}"
                                           placeholder="Contoh: 839 2039 1234"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white">
                                </div>
                                <div>
                                    <label for="zoom_passcode" class="block text-xs font-semibold text-white mb-1">
                                        Passcode Zoom (Opsional)
                                    </label>
                                    <input type="text" id="zoom_passcode" name="zoom_passcode" value="{{ old('zoom_passcode') }}"
                                           placeholder="Contoh: SPBE2026"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white">
                                </div>
                                <div>
                                    <label for="zoom_attendance_code" class="block text-xs font-semibold text-white mb-1">
                                        Kode Presensi Zoom
                                    </label>
                                    <input type="text" id="zoom_attendance_code" name="zoom_attendance_code" value="{{ old('zoom_attendance_code') }}"
                                           placeholder="Kosongkan = otomatis (HADIR-XXXX)"
                                           class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 uppercase font-mono focus:ring-2 focus:ring-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. PERIODE EVALUASI & SERTIFIKAT --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Evaluasi & Sertifikat</h3>
                            </div>
                            <span class="text-[11px] text-slate-400">Harus dijadwalkan setelah pelaksanaan selesai</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="assessment_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Mulai Evaluasi
                                </label>
                                <input type="datetime-local" id="assessment_start_at" name="assessment_start_at"
                                       value="{{ old('assessment_start_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('assessment_start_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="assessment_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal & Jam Batas Akhir Evaluasi / Sertifikat
                                </label>
                                <input type="datetime-local" id="assessment_end_at" name="assessment_end_at"
                                       value="{{ old('assessment_end_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                                @error('assessment_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="px-5 py-2.5 sm:px-6 sm:py-3 text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 sm:px-8 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-xl transition-all shadow-sm active:scale-95">
                        Simpan Gelombang Baru
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
                const zoomStart = document.getElementById('zoom_scheduled_at');
                const zoomEnd = document.getElementById('zoom_end_at');
                const evalStart = document.getElementById('assessment_start_at');
                const evalEnd = document.getElementById('assessment_end_at');

                const summaryText = document.getElementById('summary-timeline-text');
                const durationTag = document.getElementById('summary-duration-tag');
                const durationText = document.getElementById('summary-duration-text');
                const statusText = document.getElementById('summary-status-text');
                const flowReg = document.getElementById('flow-reg-text');
                const flowExec = document.getElementById('flow-exec-text');
                const flowEval = document.getElementById('flow-eval-text');

                function formatDateShort(val) {
                    if (!val) return null;
                    const d = new Date(val);
                    if (isNaN(d.getTime())) return null;
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                }

                // Fungsi sinkronisasi constraint min/max berurutan (Strict Chronological Order)
                function syncDateConstraints() {
                    // 1. regEnd tidak boleh <= regStart
                    if (regStart?.value) {
                        regEnd.min = regStart.value;
                        if (regEnd.value && regEnd.value <= regStart.value) {
                            regEnd.value = '';
                        }
                    } else {
                        regEnd.removeAttribute('min');
                    }

                    // 2. execStart tidak boleh <= regEnd (atau regStart)
                    const regLimit = regEnd?.value || regStart?.value;
                    if (regLimit) {
                        execStart.min = regLimit;
                        if (execStart.value && execStart.value <= regLimit) {
                            execStart.value = '';
                        }
                    } else {
                        execStart.removeAttribute('min');
                    }

                    // 3. execEnd tidak boleh <= execStart
                    if (execStart?.value) {
                        execEnd.min = execStart.value;
                        if (execEnd.value && execEnd.value <= execStart.value) {
                            execEnd.value = '';
                        }

                        // Zoom min di dalam execStart
                        if (zoomStart) zoomStart.min = execStart.value;
                    } else {
                        execEnd.removeAttribute('min');
                        if (zoomStart) zoomStart.removeAttribute('min');
                    }

                    // 4. Zoom bounds di dalam execEnd
                    if (execEnd?.value) {
                        if (zoomStart) zoomStart.max = execEnd.value;
                        if (zoomEnd) zoomEnd.max = execEnd.value;

                        // evalStart tidak boleh < execEnd
                        if (evalStart) {
                            evalStart.min = execEnd.value;
                            if (evalStart.value && evalStart.value < execEnd.value) {
                                evalStart.value = '';
                            }
                        }
                    } else {
                        if (zoomStart) zoomStart.removeAttribute('max');
                        if (zoomEnd) zoomEnd.removeAttribute('max');
                        if (evalStart) evalStart.removeAttribute('min');
                    }

                    // 5. zoomEnd tidak boleh <= zoomStart
                    if (zoomStart?.value) {
                        if (zoomEnd) {
                            zoomEnd.min = zoomStart.value;
                            if (zoomEnd.value && zoomEnd.value <= zoomStart.value) {
                                zoomEnd.value = '';
                            }
                        }
                    } else if (execStart?.value && zoomEnd) {
                        zoomEnd.min = execStart.value;
                    }

                    // 6. evalEnd tidak boleh <= evalStart
                    const evalLimit = evalStart?.value || execEnd?.value;
                    if (evalLimit && evalEnd) {
                        evalEnd.min = evalLimit;
                        if (evalEnd.value && evalEnd.value <= evalLimit) {
                            evalEnd.value = '';
                        }
                    } else if (evalEnd) {
                        evalEnd.removeAttribute('min');
                    }
                }

                // Fungsi hitung estimasi status otomatis secara real-time
                function calculateEstimatedStatus(rStartVal, rEndVal, eStartVal, eEndVal, evEndVal) {
                    const now = new Date();
                    const eStart = eStartVal ? new Date(eStartVal) : null;
                    const eEnd = eEndVal ? new Date(eEndVal) : null;
                    const rStart = rStartVal ? new Date(rStartVal) : null;
                    const rEnd = rEndVal ? new Date(rEndVal) : null;
                    const evEnd = evEndVal ? new Date(evEndVal) : eEnd;

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
                        const dStart = new Date(overallStartVal);
                        const dEnd = new Date(overallEndVal);

                        if (!isNaN(dStart.getTime()) && !isNaN(dEnd.getTime())) {
                            summaryText.textContent = `${formatDateShort(overallStartVal)} s/d ${formatDateShort(overallEndVal)}`;

                            const diffTime = dEnd - dStart;
                            const diffDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
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

                [regStart, regEnd, execStart, execEnd, zoomStart, zoomEnd, evalStart, evalEnd].forEach(el => {
                    el?.addEventListener('change', updateTimelineSummary);
                    el?.addEventListener('input', updateTimelineSummary);
                });

                updateTimelineSummary();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
