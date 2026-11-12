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

        @php
            $initialStep = 1;
            if ($errors->has('assessment_start_at') || $errors->has('assessment_end_at')) {
                $initialStep = 3;
            } elseif ($errors->has('start_date') || $errors->has('end_date') || $errors->has('zoom_url') || $errors->has('sessions') || $errors->has('sessions.*')) {
                $initialStep = 2;
            }
        @endphp

        {{-- Form Card dengan Stepper Wizard (Bertahap Tanpa Disable Aneh) --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs p-6 sm:p-8"
             x-data="bimtekFormWizard({
                 initialStep: {{ $initialStep }},
                 isUnlimitedQuota: {{ old('is_unlimited_quota') ? 'true' : 'false' }},
                 isEdit: false
             })">

            {{-- Stepper Progress Bar (Identik dengan Super Admin) --}}
            <div class="mb-8 border-b border-slate-100 pb-6">
                <div class="flex items-center justify-between gap-2 sm:gap-4 max-w-2xl mx-auto">
                    <!-- Step 1 Button -->
                    <button type="button" @click="goToStep(1)"
                            class="flex items-center gap-2.5 text-left group cursor-pointer focus:outline-hidden">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all shadow-xs"
                             :class="step === 1 ? 'bg-[#13416B] text-white ring-4 ring-blue-100' : (step > 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600')">
                            <template x-if="step > 1">
                                <i class="fas fa-check text-xs"></i>
                            </template>
                            <template x-if="step <= 1">
                                <span>1</span>
                            </template>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Langkah 1</p>
                            <p class="text-xs font-bold" :class="step === 1 ? 'text-[#13416B]' : (step > 1 ? 'text-slate-800' : 'text-slate-400')">
                                Info & Pendaftaran
                            </p>
                        </div>
                    </button>

                    <!-- Connector Line 1-2 -->
                    <div class="h-0.5 flex-1 rounded-full transition-all"
                         :class="step > 1 ? 'bg-emerald-500' : 'bg-slate-200'"></div>

                    <!-- Step 2 Button -->
                    <button type="button" @click="goToStep(2)"
                            class="flex items-center gap-2.5 text-left group cursor-pointer focus:outline-hidden">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all shadow-xs"
                             :class="step === 2 ? 'bg-[#13416B] text-white ring-4 ring-blue-100' : (step > 2 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600')">
                            <template x-if="step > 2">
                                <i class="fas fa-check text-xs"></i>
                            </template>
                            <template x-if="step <= 2">
                                <span>2</span>
                            </template>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Langkah 2</p>
                            <p class="text-xs font-bold" :class="step === 2 ? 'text-[#13416B]' : (step > 2 ? 'text-slate-800' : 'text-slate-400')">
                                Pelaksanaan & Zoom
                            </p>
                        </div>
                    </button>

                    <!-- Connector Line 2-3 -->
                    <div class="h-0.5 flex-1 rounded-full transition-all"
                         :class="step > 2 ? 'bg-emerald-500' : 'bg-slate-200'"></div>

                    <!-- Step 3 Button -->
                    <button type="button" @click="goToStep(3)"
                            class="flex items-center gap-2.5 text-left group cursor-pointer focus:outline-hidden">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all shadow-xs"
                             :class="step === 3 ? 'bg-[#13416B] text-white ring-4 ring-blue-100' : 'bg-slate-200 text-slate-600'">
                            <span>3</span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Langkah 3</p>
                            <p class="text-xs font-bold" :class="step === 3 ? 'text-[#13416B]' : 'text-slate-400'">
                                Evaluasi & Selesai
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            <x-validation-errors />

            <form action="{{ route('admin-pusat.management-course.bimtek-events.store') }}" method="POST" id="bimtekForm" novalidate>
                @csrf

                {{-- ======================================================== --}}
                {{-- LANGKAH 1: INFORMASI GELOMBANG & PERIODE PENDAFTARAN     --}}
                {{-- ======================================================== --}}
                <div x-show="step === 1" x-cloak class="space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Langkah 1: Informasi Kursus, Gelombang & Pendaftaran
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Pilih master kursus yang dibuka, tentukan nama gelombang, kapasitas kuota, dan jadwal pendaftaran.
                        </p>
                    </div>

                    {{-- Pilih Master Kursus --}}
                    <div>
                        <label for="course_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Master Kursus / Kurikulum <span class="text-rose-500">*</span>
                        </label>
                        <select id="course_id" name="course_id"
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
                                   placeholder="Contoh: Bimtek SPBE Angkatan I 2026"
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
                        <div class="md:col-span-1">
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

                    {{-- Periode Pendaftaran Peserta --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-plus text-[#13416B] text-xs"></i>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Pendaftaran Peserta</h3>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Jadwal calon peserta mendaftar mandiri di katalog
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Buka Pendaftaran
                                </label>
                                <input type="date" id="registration_start_at" name="registration_start_at"
                                       value="{{ old('registration_start_at') }}"
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
                                       value="{{ old('registration_end_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('registration_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Footer Step 1 --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                           class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </a>
                        <button type="button" @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-2.5 sm:px-8 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer">
                            <span>Lanjutkan ke Pelaksanaan & Zoom</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- LANGKAH 2: PERIODE PELAKSANAAN & LIVE ZOOM MULTI-SESI     --}}
                {{-- ======================================================== --}}
                <div x-show="step === 2" x-cloak class="space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Langkah 2: Periode Pelaksanaan & Live Zoom Harian
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Atur rentang tanggal pelaksanaan pelatihan, link ruang Zoom utama, dan jadwal sesi tatap muka harian beserta jam presensinya.
                        </p>
                    </div>

                    {{-- Periode Pelaksanaan --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-[#13416B] text-xs"></i>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rentang Tanggal Pelaksanaan</h3>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Jadwal harus setelah periode pendaftaran
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Mulai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('start_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Selesai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('end_date')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SUB-CARD: DETAIL SESI ZOOM & MULTI-SESI (Biru Sirenata Kalem & Teks White - 100% Vibrant, Normal) --}}
                    <div id="card-zoom" class="p-4 sm:p-5 rounded-md bg-[#1E40AF] text-white shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-white/20 pb-2.5">
                            <div>
                                <span class="text-xs font-bold text-white uppercase tracking-wider block">
                                    Pengaturan Sesi Live Zoom & Presensi Harian (Multi-Sesi)
                                </span>
                                <span class="text-[11px] text-blue-100 font-medium">
                                    Setiap hari pelaksanaan dapat memiliki jadwal tatap muka Zoom dan jam presensi tersendiri
                                </span>
                            </div>
                            <span id="zoom-status-badge" class="text-[11px] text-blue-100 font-medium">
                                Jadwal berada di dalam rentang tanggal pelaksanaan
                            </span>
                        </div>

                        {{-- Ruang Pertemuan Utama / Global Meeting Room --}}
                        <div class="space-y-3 bg-black/15 p-3.5 rounded-lg border border-white/10">
                            <span class="text-xs font-bold text-white uppercase tracking-wider block border-b border-white/10 pb-1.5">
                                Informasi Ruang Meeting Zoom Utama
                            </span>

                            <div>
                                <label for="zoom_url" class="block text-xs font-semibold text-white mb-1">
                                    Link URL Meeting Zoom (Tautan Utama)
                                </label>
                                <input type="url" id="zoom_url" name="zoom_url" value="{{ old('zoom_url') }}"
                                       placeholder="https://zoom.us/j/1234567890?pwd=..."
                                       class="w-full px-3 py-2 rounded-lg border-0 text-xs sm:text-sm bg-white text-slate-900 focus:ring-2 focus:ring-white transition-colors">
                                @error('zoom_url')
                                    <p class="text-xs text-rose-200 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                            </div>
                        </div>

                        {{-- SEKSI MULTI-SESI ZOOM & PRESENSI PER HARI --}}
                        <div class="space-y-3 pt-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/20 pb-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-check text-amber-300"></i>
                                    <span class="text-xs font-bold text-white uppercase tracking-wider">
                                        Daftar Sesi Zoom & Presensi Per Hari
                                    </span>
                                    <span id="sessions-count-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white">
                                        0 Sesi
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <button type="button" id="btn-auto-generate-sessions"
                                            title="Buat sesi Zoom dan presensi otomatis per hari berdasarkan rentang tanggal pelaksanaan"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold text-[#13416B] bg-amber-300 hover:bg-amber-200 shadow-2xs transition-all active:scale-95 cursor-pointer">
                                        <i class="fas fa-bolt text-xs"></i>
                                        <span>Generate Sesi Otomatis (Sesuai Hari)</span>
                                    </button>
                                    <button type="button" id="btn-add-session"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold text-[#1E40AF] bg-white hover:bg-blue-50 shadow-2xs transition-all active:scale-95 cursor-pointer">
                                        <i class="fas fa-plus text-xs"></i>
                                        <span>Tambah Sesi</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Container Daftar Sesi Dinamis --}}
                            <div id="sessions-container" class="space-y-3">
                                {{-- Empty State jika belum ada sesi --}}
                                <div id="sessions-empty-state" class="p-4 rounded-md border border-dashed border-white/30 text-center space-y-1.5 bg-black/10">
                                    <p class="text-xs text-white/90 font-medium">
                                        Belum ada sesi harian yang ditambahkan.
                                    </p>
                                    <p class="text-[11px] text-blue-100/80">
                                        Klik <strong>"Generate Sesi Otomatis"</strong> di atas untuk membuat jadwal Zoom & presensi secara otomatis sesuai rentang hari pelaksanaan bimtek.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Footer Step 2 --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button type="button" @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Kembali ke Langkah 1</span>
                        </button>
                        <button type="button" @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-2.5 sm:px-8 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer">
                            <span>Lanjutkan ke Evaluasi & Konfirmasi</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- LANGKAH 3: EVALUASI, SERTIFIKAT & KONFIRMASI AKHIR       --}}
                {{-- ======================================================== --}}
                <div x-show="step === 3" x-cloak class="space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Langkah 3: Periode Evaluasi, Sertifikat & Ringkasan Akhir
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Atur batas waktu pengerjaan evaluasi peserta dan tinjau linimasa gelombang secara keseluruhan sebelum menyimpan data.
                        </p>
                    </div>

                    {{-- Periode Evaluasi & Sertifikat --}}
                    <div class="p-4 rounded-md border border-slate-200 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-certificate text-[#13416B] text-xs"></i>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Periode Evaluasi & Batas Kelulusan</h3>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Jadwal pengerjaan ujian dan penerbitan sertifikat
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="assessment_start_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Mulai Evaluasi
                                </label>
                                <input type="date" id="assessment_start_at" name="assessment_start_at"
                                       value="{{ old('assessment_start_at') }}"
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
                                       value="{{ old('assessment_end_at') }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('assessment_end_at')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Ringkasan Linimasa & Estimasi Status Otomatis (Background Biru Sirenata #13416B) --}}
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

                        {{-- Step Flow Review --}}
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

                    {{-- Navigation Footer Step 3 --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button type="button" @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Kembali ke Langkah 2</span>
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-7 py-3 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer">
                            <i class="fas fa-check-circle text-xs"></i>
                            <span>Simpan Gelombang Baru</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            function bimtekFormWizard(config = {}) {
                return {
                    step: config.initialStep || 1,
                    maxStepReached: config.isEdit ? 3 : (config.initialStep || 1),
                    isUnlimited: config.isUnlimitedQuota || false,

                    goToStep(targetStep) {
                        if (targetStep < this.step) {
                            this.step = targetStep;
                            this.scrollToTop();
                            return;
                        }
                        if (targetStep === this.step) return;

                        if (this.validateStep(this.step)) {
                            if (targetStep > this.step + 1 && !config.isEdit) {
                                if (this.step === 1 && !this.validateStep(2)) return;
                            }
                            this.step = targetStep;
                            if (targetStep > this.maxStepReached) {
                                this.maxStepReached = targetStep;
                            }
                            this.scrollToTop();
                        }
                    },

                    nextStep() {
                        if (this.validateStep(this.step)) {
                            if (this.step < 3) {
                                this.step++;
                                if (this.step > this.maxStepReached) {
                                    this.maxStepReached = this.step;
                                }
                                this.scrollToTop();
                            }
                        }
                    },

                    prevStep() {
                        if (this.step > 1) {
                            this.step--;
                            this.scrollToTop();
                        }
                    },

                    scrollToTop() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },

                    validateStep(currentStepNum) {
                        if (currentStepNum === 1) {
                            const courseId = document.getElementById('course_id');
                            const batchName = document.getElementById('batch_name');
                            const quota = document.getElementById('quota');
                            const regStart = document.getElementById('registration_start_at');
                            const regEnd = document.getElementById('registration_end_at');

                            if (courseId && !courseId.value) {
                                alert('Silakan pilih Master Kursus / Kurikulum terlebih dahulu.');
                                courseId.focus();
                                return false;
                            }
                            if (batchName && !batchName.value.trim()) {
                                alert('Silakan isi Nama Gelombang / Angkatan.');
                                batchName.focus();
                                return false;
                            }
                            if (!this.isUnlimited && quota && (!quota.value || parseInt(quota.value) < 1)) {
                                alert('Silakan tentukan kuota peserta (minimal 1) atau centang Tanpa Batas.');
                                quota.focus();
                                return false;
                            }
                            if (regStart?.value && regEnd?.value) {
                                if (regEnd.value < regStart.value) {
                                    alert('Tanggal tutup pendaftaran tidak boleh sebelum tanggal buka pendaftaran.');
                                    regEnd.focus();
                                    return false;
                                }
                            }
                            return true;
                        }

                        if (currentStepNum === 2) {
                            const execStart = document.getElementById('start_date');
                            const execEnd = document.getElementById('end_date');
                            const regEnd = document.getElementById('registration_end_at');

                            if (!execStart?.value) {
                                alert('Silakan tentukan Tanggal Mulai Pelaksanaan.');
                                execStart?.focus();
                                return false;
                            }
                            if (!execEnd?.value) {
                                alert('Silakan tentukan Tanggal Selesai Pelaksanaan.');
                                execEnd?.focus();
                                return false;
                            }
                            if (execEnd.value < execStart.value) {
                                alert('Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.');
                                execEnd.focus();
                                return false;
                            }
                            if (regEnd?.value && execStart.value < regEnd.value) {
                                alert('Tanggal mulai pelaksanaan harus setelah atau sama dengan tanggal tutup pendaftaran (' + regEnd.value + ').');
                                execStart.focus();
                                return false;
                            }

                            // Cek sesi harian jika ada
                            const sessionCards = document.querySelectorAll('#sessions-container .session-card');
                            for (let card of sessionCards) {
                                const titleInput = card.querySelector('.session-title-input');
                                const dateInput = card.querySelector('.session-date-input');
                                const startInput = card.querySelector('.session-start-input');

                                if (!titleInput?.value.trim()) {
                                    alert('Judul sesi tidak boleh kosong.');
                                    titleInput?.focus();
                                    return false;
                                }
                                if (!dateInput?.value) {
                                    alert('Tanggal sesi tidak boleh kosong.');
                                    dateInput?.focus();
                                    return false;
                                }
                                if (!startInput?.value) {
                                    alert('Jam mulai Zoom sesi tidak boleh kosong.');
                                    startInput?.focus();
                                    return false;
                                }
                            }

                            return true;
                        }

                        return true;
                    }
                };
            }

            document.addEventListener('DOMContentLoaded', function () {
                const regStart = document.getElementById('registration_start_at');
                const regEnd = document.getElementById('registration_end_at');
                const execStart = document.getElementById('start_date');
                const execEnd = document.getElementById('end_date');
                const evalStart = document.getElementById('assessment_start_at');
                const evalEnd = document.getElementById('assessment_end_at');

                const summaryText = document.getElementById('summary-timeline-text');
                const durationTag = document.getElementById('summary-duration-tag');
                const durationText = document.getElementById('summary-duration-text');
                const statusText = document.getElementById('summary-status-text');
                const flowReg = document.getElementById('flow-reg-text');
                const flowExec = document.getElementById('flow-exec-text');
                const flowEval = document.getElementById('flow-eval-text');

                // ==========================================
                // MULTI-SESI ZOOM & PRESENSI (SKENARIO 1)
                // ==========================================
                const sessionsContainer = document.getElementById('sessions-container');
                const sessionsEmptyState = document.getElementById('sessions-empty-state');
                const sessionsCountBadge = document.getElementById('sessions-count-badge');
                const btnAutoGenerate = document.getElementById('btn-auto-generate-sessions');
                const btnAddSession = document.getElementById('btn-add-session');
                const initialSessions = @json(old('sessions', []));

                function updateSessionNumbersAndBadges() {
                    const cards = sessionsContainer.querySelectorAll('.session-card');
                    if (sessionsCountBadge) {
                        sessionsCountBadge.textContent = `${cards.length} Sesi`;
                    }

                    if (cards.length === 0) {
                        if (sessionsEmptyState) sessionsEmptyState.style.display = 'block';
                    } else {
                        if (sessionsEmptyState) sessionsEmptyState.style.display = 'none';
                    }

                    cards.forEach((card, index) => {
                        card.dataset.index = index;
                        const badge = card.querySelector('.session-num-badge');
                        if (badge) badge.textContent = `Sesi Hari ${index + 1}`;

                        card.querySelectorAll('input').forEach(input => {
                            if (input.name) {
                                input.name = input.name.replace(/sessions\[\d*\]/, `sessions[${index}]`);
                            }
                        });
                    });
                }

                function createSessionCard(data = {}, index = 0) {
                    const card = document.createElement('div');
                    card.className = 'session-card p-3.5 sm:p-4 rounded-lg bg-black/25 border border-white/20 space-y-3 transition-all';
                    card.dataset.index = index;

                    const titleVal = data.title || `Sesi Hari ${index + 1}`;
                    const dateVal = data.session_date || (execStart?.value || '');
                    const startVal = data.start_time || (dateVal ? `${dateVal}T09:00` : '');
                    const endVal = data.end_time || (dateVal ? `${dateVal}T12:00` : '');
                    const attStartVal = data.attendance_start_at || (dateVal ? `${dateVal}T09:15` : '');
                    const attEndVal = data.attendance_end_at || (dateVal ? `${dateVal}T10:00` : '');
                    const sessionId = data.id || '';

                    card.innerHTML = `
                        ${sessionId ? `<input type="hidden" name="sessions[${index}][id]" value="${sessionId}">` : ''}
                        <div class="flex items-center justify-between border-b border-white/15 pb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="session-num-badge px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-slate-900">
                                    Sesi Hari ${index + 1}
                                </span>
                                <input type="text" name="sessions[${index}][title]" value="${titleVal}" placeholder="Judul Sesi (contoh: Sesi Hari ${index + 1})" required
                                       class="session-title-input px-2.5 py-1 text-xs font-semibold rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white w-48 sm:w-64">
                            </div>
                            <button type="button" class="btn-remove-session text-[11px] font-semibold text-rose-300 hover:text-rose-100 flex items-center gap-1 transition-colors cursor-pointer">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                                <span>Hapus Sesi</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-medium text-blue-100 mb-1">
                                    Tanggal Sesi <span class="text-amber-300">*</span>
                                </label>
                                <input type="date" name="sessions[${index}][session_date]" value="${dateVal}" required
                                       class="session-date-input w-full px-2.5 py-1.5 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-blue-100 mb-1">
                                    Mulai Zoom <span class="text-amber-300">*</span>
                                </label>
                                <input type="datetime-local" name="sessions[${index}][start_time]" value="${startVal}" required
                                       class="session-start-input w-full px-2.5 py-1.5 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-blue-100 mb-1">
                                    Selesai Zoom (Opsional)
                                </label>
                                <input type="datetime-local" name="sessions[${index}][end_time]" value="${endVal}"
                                       class="session-end-input w-full px-2.5 py-1.5 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white">
                            </div>
                        </div>

                        {{-- Rentang Jam Pembukaan Presensi Sesi Ini --}}
                        <div class="p-2.5 rounded bg-black/20 border border-white/10 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px]">
                                <span class="font-bold text-amber-300 flex items-center gap-1">
                                    <i class="fas fa-clock text-[10px]"></i> Jadwal Presensi Sesi Ini
                                </span>
                                <span class="text-blue-100 text-[10px]">Terkunci di dalam jam Zoom sesi ini</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] text-blue-100 mb-0.5">Buka Presensi</label>
                                    <input type="datetime-local" name="sessions[${index}][attendance_start_at]" value="${attStartVal}"
                                           class="session-att-start w-full px-2 py-1 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-blue-100 mb-0.5">Tutup Presensi</label>
                                    <input type="datetime-local" name="sessions[${index}][attendance_end_at]" value="${attEndVal}"
                                           class="session-att-end w-full px-2 py-1 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-white">
                                </div>
                            </div>
                        </div>
                    `;

                    card.querySelector('.btn-remove-session')?.addEventListener('click', function () {
                        card.remove();
                        updateSessionNumbersAndBadges();
                        updateTimelineSummary();
                    });

                    const dateInput = card.querySelector('.session-date-input');
                    const startInput = card.querySelector('.session-start-input');
                    const endInput = card.querySelector('.session-end-input');
                    const attStartInput = card.querySelector('.session-att-start');
                    const attEndInput = card.querySelector('.session-att-end');

                    function syncCardBounds() {
                        if (execStart?.value && dateInput) {
                            dateInput.min = execStart.value;
                        }
                        if (execEnd?.value && dateInput) {
                            dateInput.max = execEnd.value;
                        }

                        const dVal = dateInput?.value;
                        if (dVal) {
                            const minStart = `${dVal}T00:00`;
                            const maxEnd = `${dVal}T23:59`;

                            if (startInput) {
                                startInput.min = minStart;
                                startInput.max = maxEnd;
                            }
                            if (endInput) {
                                endInput.min = startInput?.value || minStart;
                                endInput.max = maxEnd;
                            }
                            if (attStartInput) {
                                attStartInput.min = startInput?.value || minStart;
                                attStartInput.max = endInput?.value || maxEnd;
                            }
                            if (attEndInput) {
                                attEndInput.min = attStartInput?.value || startInput?.value || minStart;
                                attEndInput.max = endInput?.value || maxEnd;
                            }
                        }
                    }

                    [dateInput, startInput, endInput, attStartInput, attEndInput].forEach(el => {
                        el?.addEventListener('change', syncCardBounds);
                        el?.addEventListener('input', syncCardBounds);
                    });

                    syncCardBounds();
                    return card;
                }

                function addSession(data = {}) {
                    const cards = sessionsContainer.querySelectorAll('.session-card');
                    const newCard = createSessionCard(data, cards.length);
                    sessionsContainer.appendChild(newCard);
                    updateSessionNumbersAndBadges();
                    updateTimelineSummary();
                }

                btnAddSession?.addEventListener('click', function () {
                    if (!execStart?.value || !execEnd?.value) {
                        alert('Silakan tentukan tanggal mulai dan tanggal selesai pelaksanaan terlebih dahulu.');
                        execStart?.focus();
                        return;
                    }
                    addSession();
                });

                btnAutoGenerate?.addEventListener('click', function () {
                    if (!execStart?.value || !execEnd?.value) {
                        alert('Silakan tentukan tanggal mulai dan tanggal selesai pelaksanaan terlebih dahulu sebelum membuat sesi otomatis.');
                        execStart?.focus();
                        return;
                    }

                    const dStartParts = execStart.value.split('-');
                    const dEndParts = execEnd.value.split('-');
                    const dStart = new Date(dStartParts[0], dStartParts[1] - 1, dStartParts[2]);
                    const dEnd = new Date(dEndParts[0], dEndParts[1] - 1, dEndParts[2]);

                    if (isNaN(dStart.getTime()) || isNaN(dEnd.getTime()) || dStart > dEnd) {
                        alert('Format rentang tanggal pelaksanaan tidak valid.');
                        return;
                    }

                    sessionsContainer.querySelectorAll('.session-card').forEach(el => el.remove());

                    let count = 0;
                    const cur = new Date(dStart.getTime());
                    while (cur <= dEnd) {
                        count++;
                        const y = cur.getFullYear();
                        const m = String(cur.getMonth() + 1).padStart(2, '0');
                        const d = String(cur.getDate()).padStart(2, '0');
                        const dateStr = `${y}-${m}-${d}`;

                        const isLast = (cur.getTime() === dEnd.getTime());
                        const title = `Sesi Hari ${count}`;
                        const start = `${dateStr}T09:00`;
                        const end = (isLast && count > 1) ? `${dateStr}T11:30` : `${dateStr}T12:00`;
                        const aStart = `${dateStr}T09:15`;
                        const aEnd = `${dateStr}T10:00`;

                        addSession({
                            title: title,
                            session_date: dateStr,
                            start_time: start,
                            end_time: end,
                            attendance_start_at: aStart,
                            attendance_end_at: aEnd,
                        });

                        cur.setDate(cur.getDate() + 1);
                    }
                });

                if (Array.isArray(initialSessions) && initialSessions.length > 0) {
                    initialSessions.forEach((s, idx) => {
                        addSession(s);
                    });
                }

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

                // Sinkronisasi batas tanggal min/max secara natural (tanpa disable aneh)
                function syncDateBounds() {
                    if (regStart?.value && regEnd) {
                        regEnd.min = regStart.value;
                    }
                    if (regEnd?.value && execStart) {
                        execStart.min = regEnd.value;
                    } else if (regStart?.value && execStart) {
                        execStart.min = regStart.value;
                    }
                    if (execStart?.value && execEnd) {
                        execEnd.min = execStart.value;
                    }
                    if (execEnd?.value && evalStart) {
                        evalStart.min = execEnd.value;
                    }
                    if (evalStart?.value && evalEnd) {
                        evalEnd.min = evalStart.value;
                    } else if (execEnd?.value && evalEnd) {
                        evalEnd.min = execEnd.value;
                    }
                }

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
                    syncDateBounds();

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
                        summaryText.textContent = 'Silakan atur tanggal periode di atas...';
                        if (durationTag) durationTag.classList.add('hidden');
                    }

                    if (execStart?.value && execEnd?.value) {
                        const est = calculateEstimatedStatus(regStart?.value, regEnd?.value, execStart.value, execEnd.value, evalEnd?.value);
                        if (statusText) statusText.textContent = est;
                    } else {
                        if (statusText) statusText.textContent = 'Menyesuaikan Tanggal';
                    }

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

                [regStart, regEnd, execStart, execEnd, evalStart, evalEnd].forEach(el => {
                    el?.addEventListener('change', updateTimelineSummary);
                    el?.addEventListener('input', updateTimelineSummary);
                });

                updateTimelineSummary();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
