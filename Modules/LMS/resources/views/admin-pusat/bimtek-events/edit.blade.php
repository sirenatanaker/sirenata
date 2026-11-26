<x-dashboard::layouts.dashboard title="Edit Gelombang Bimtek - Admin Pusat">
    <div class="w-full p-8 space-y-6">

        {{-- Breadcrumb Navigation (Minimal) --}}
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="text-sm sm:text-base font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        Periode Event
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <i class="fas fa-chevron-right text-xs text-slate-300 mx-1.5"></i>
                    <span class="text-sm sm:text-base font-bold text-slate-900">
                        Edit Gelombang
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

            $waveRegionOptions = $waveRegionOptions ?? ['Nasional'];
            $provinceOptions = array_values(array_filter($waveRegionOptions, fn($r) => $r !== 'Nasional'));

            $parsedNumber = 1;
            $parsedRegion = 'Nasional';
            $parsedYear = $event->start_date ? $event->start_date->format('Y') : now()->format('Y');
            $formattedWave = $event->formatted_wave_subtitle;
            if (preg_match('/^Gelombang\s+(\d+)\s+(.+?)\s+(20\d{2})$/i', $formattedWave, $wm)) {
                $parsedNumber = (int) $wm[1];
                $parsedRegion = trim($wm[2]);
                $parsedYear = $wm[3];
            }

            $isPresetRegion = in_array($parsedRegion, $waveRegionOptions, true);
            $oldWaveNumber = old('wave_number', $parsedNumber);
            $oldWaveRegion = old('wave_region', $isPresetRegion ? $parsedRegion : '__custom__');
            $oldWaveRegionCustom = old('wave_region_custom', $isPresetRegion ? '' : $parsedRegion);
            $oldWaveYear = old('wave_year', $parsedYear);
        @endphp

        {{-- Form Card dengan Header Biru & Stepper Wizard --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden"
             x-data="bimtekFormWizard({
                 initialStep: {{ $initialStep }},
                 isUnlimitedQuota: {{ old('is_unlimited_quota', is_null($event->quota) || $event->quota <= 0 ? 'true' : 'false') }},
                 isEdit: true,
                 waveNumber: {{ (int) $oldWaveNumber }},
                 waveRegion: @js($oldWaveRegion),
                 waveRegionCustom: @js($oldWaveRegionCustom),
                 waveYear: @js((string) $oldWaveYear)
             })">

            {{-- Header Biru Terpisah dari Form --}}
            <div class="bg-[#13416B] text-white px-6 sm:px-8 py-5 sm:py-6">
                <div class="mb-5">
                    <h1 class="text-lg sm:text-xl font-bold text-white tracking-wide">
                        Edit Periode Gelombang
                    </h1>
                    <p class="text-xs sm:text-sm text-blue-100/90 mt-1">
                        {{ $event->formatted_wave_subtitle }} — {{ $event->course?->name }}
                    </p>
                </div>

                {{-- Stepper 3 Langkah --}}
                <div class="pt-4 border-t border-white/15 flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(1)">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm sm:text-base font-bold transition-all"
                             :class="step === 1 ? 'bg-amber-400 text-slate-900 ring-4 ring-amber-400/20' : (step > 1 ? 'bg-emerald-500 text-white' : 'bg-white/15 text-blue-100 border border-white/25')">
                            1
                        </div>
                        <span class="text-xs sm:text-sm md:text-base transition-colors"
                              :class="step === 1 ? 'text-white font-bold' : (step > 1 ? 'text-blue-100 font-semibold' : 'text-blue-200/75 font-medium')">
                            Periode Pendaftaran
                        </span>
                    </div>

                    <div class="h-0.5 flex-1 rounded-full transition-colors"
                         :class="step >= 2 ? 'bg-emerald-400' : 'bg-white/20'"></div>

                    <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(2)">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm sm:text-base font-bold transition-all"
                             :class="step === 2 ? 'bg-amber-400 text-slate-900 ring-4 ring-amber-400/20' : (step > 2 ? 'bg-emerald-500 text-white' : 'bg-white/15 text-blue-100 border border-white/25')">
                            2
                        </div>
                        <span class="text-xs sm:text-sm md:text-base transition-colors"
                              :class="step === 2 ? 'text-white font-bold' : (step > 2 ? 'text-blue-100 font-semibold' : 'text-blue-200/75 font-medium')">
                            Periode Pelaksanaan
                        </span>
                    </div>

                    <div class="h-0.5 flex-1 rounded-full transition-colors"
                         :class="step >= 3 ? 'bg-emerald-400' : 'bg-white/20'"></div>

                    <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(3)">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm sm:text-base font-bold transition-all"
                             :class="step === 3 ? 'bg-amber-400 text-slate-900 ring-4 ring-amber-400/20' : 'bg-white/15 text-blue-100 border border-white/25'">
                            3
                        </div>
                        <span class="text-xs sm:text-sm md:text-base transition-colors"
                              :class="step === 3 ? 'text-white font-bold' : 'text-blue-200/75 font-medium'">
                            Periode Evaluasi
                        </span>
                    </div>
                </div>
            </div>

            {{-- Body Form Putih --}}
            <div class="p-6 sm:p-8 bg-white">
                <x-validation-errors />

                <form action="{{ route('admin-pusat.management-course.bimtek-events.update', $event->id) }}" method="POST" id="bimtekForm" novalidate>
                @csrf
                @method('PUT')

                {{-- ======================================================== --}}
                {{-- LANGKAH 1: INFORMASI GELOMBANG & PERIODE PENDAFTARAN     --}}
                {{-- ======================================================== --}}
                <div x-show="step === 1" x-cloak class="space-y-6">
                    {{-- Pilih Master Kursus --}}
                    <div>
                        <label for="course_id" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                            Master Kursus / Kurikulum <span class="text-rose-500">*</span>
                        </label>
                        <select id="course_id" name="course_id" required
                                class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white">
                            @foreach ($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('course_id', $event->course_id) == $c->id)>
                                    {{ $c->name }} ({{ count($courseAgendasMap[$c->id] ?? []) }} Agenda)
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Template Penamaan Event: Gelombang [Nomor] [Wilayah] [Tahun] (Flat) --}}
                    <div class="pt-6 border-t border-slate-100 space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                                    Penamaan Gelombang Event <span class="text-rose-500">*</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Format standar otomatis: <span class="font-semibold text-slate-700">Gelombang [Nomor] [Wilayah] [Tahun]</span>
                                </p>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#EBF3FA] text-xs sm:text-sm font-bold text-[#13416B] self-start sm:self-center">
                                <span class="text-[11px] font-medium text-[#547996]">Hasil:</span>
                                <span x-text="computedBatchName || 'Gelombang ...'"></span>
                            </div>
                        </div>

                        <input type="hidden" id="batch_name" name="batch_name" :value="computedBatchName">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Nomor Gelombang --}}
                            <div>
                                <label for="wave_number" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Nomor Gelombang <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center">
                                    <span class="px-3 py-2.5 rounded-l-md border border-r-0 border-slate-300 bg-slate-50 text-xs sm:text-sm font-semibold text-slate-600 select-none">
                                        Gelombang
                                    </span>
                                    <input type="number" id="wave_number" name="wave_number" x-model="waveNumber" min="1" max="99" required
                                           placeholder="1"
                                           class="w-full px-3 py-2.5 rounded-r-md border border-slate-300 text-sm sm:text-[15px] bg-white focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                            </div>

                            {{-- Wilayah --}}
                            <div>
                                <label for="wave_region" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Wilayah <span class="text-rose-500">*</span>
                                </label>
                                <select id="wave_region" name="wave_region" x-model="waveRegion" required
                                        class="w-full px-3 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                    <option value="Nasional">Nasional</option>
                                    <optgroup label="Provinsi">
                                        @foreach ($provinceOptions as $regionOpt)
                                            <option value="{{ $regionOpt }}">{{ $regionOpt }}</option>
                                        @endforeach
                                    </optgroup>
                                    <option value="__custom__">Wilayah Lainnya (Ketik Manual)...</option>
                                </select>
                                <div x-show="waveRegion === '__custom__'" x-cloak class="mt-2">
                                    <input type="text" id="wave_region_custom" name="wave_region_custom" x-model="waveRegionCustom"
                                           placeholder="Contoh: Jawa Barat, Jakarta, Aceh"
                                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                            </div>

                            {{-- Tahun --}}
                            <div>
                                <label for="wave_year" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tahun <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" id="wave_year" name="wave_year" x-model="waveYear" min="2020" max="2099" required
                                       placeholder="{{ now()->format('Y') }}"
                                       class="w-full px-3 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            </div>
                        </div>

                        @error('batch_name')
                            <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kuota Peserta dengan Opsi Tanpa Batasan (Flat) --}}
                    <div class="pt-6 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="quota" class="block text-xs sm:text-sm font-semibold text-slate-700">
                                Kapasitas Kuota <span class="text-rose-500" x-show="!isUnlimited">*</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" name="is_unlimited_quota" value="1"
                                       x-model="isUnlimited"
                                       class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] h-3.5 w-3.5">
                                <span class="text-xs sm:text-sm text-slate-600 font-medium">Tanpa Batas</span>
                            </label>
                        </div>
                        <div x-show="!isUnlimited">
                            <input type="number" id="quota" name="quota"
                                   value="{{ old('quota', $event->quota ?: 100) }}" min="1"
                                   placeholder="Contoh: 100"
                                   class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            @error('quota')
                                <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="isUnlimited" x-cloak
                             class="px-3.5 py-2.5 bg-slate-50 border border-dashed border-slate-300 rounded-md text-xs sm:text-sm text-slate-600">
                            Kapasitas peserta terbuka <strong>tanpa batasan kuota</strong>.
                        </div>
                    </div>

                    {{-- Periode Pendaftaran Peserta (Flat) --}}
                    <div class="pt-6 border-t border-slate-100 space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Periode Pendaftaran Peserta</h3>
                            <span class="text-xs text-slate-500 font-medium">
                                Jadwal calon peserta mendaftar mandiri di katalog
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_start_at" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Buka Pendaftaran <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="registration_start_at" name="registration_start_at"
                                       value="{{ old('registration_start_at', $event->registration_start_at ? $event->registration_start_at->format('Y-m-d') : ($event->start_date ? $event->start_date->copy()->subDays(7)->format('Y-m-d') : '')) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('registration_start_at')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="registration_end_at" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Tutup Pendaftaran <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="registration_end_at" name="registration_end_at"
                                       value="{{ old('registration_end_at', $event->registration_end_at ? $event->registration_end_at->format('Y-m-d') : ($event->start_date ? $event->start_date->copy()->subDay()->format('Y-m-d') : '')) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('registration_end_at')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Step 1 (Mirip Super Admin) --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 action-buttons form-section pt-6 border-t border-slate-100">
                        <x-button :href="route('admin-pusat.management-course.bimtek-events.show', $event->id)" variant="secondary" size="lg" rounded="md">
                            Batal
                        </x-button>
                        <x-button type="button" size="lg" rounded="md" @click="nextStep()">
                            Lanjutkan
                        </x-button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- LANGKAH 2: PERIODE PELAKSANAAN & LIVE ZOOM MULTI-SESI     --}}
                {{-- ======================================================== --}}
                <div x-show="step === 2" x-cloak class="space-y-6">
                    {{-- Rentang Tanggal Pelaksanaan (Flat) --}}
                    <div class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Rentang Tanggal Pelaksanaan</h3>
                            <span id="exec-min-days-hint" class="text-xs text-slate-500 font-medium">
                                Jadwal harus setelah periode pendaftaran
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Mulai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date"
                                       value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('start_date')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Selesai Pelaksanaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="end_date" name="end_date"
                                       value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('end_date')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Ruang Meeting Zoom Utama (Flat) --}}
                    <div id="card-zoom" class="pt-6 border-t border-slate-100 space-y-6">
                        <div class="space-y-3.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                                    Informasi Ruang Meeting Zoom Utama
                                </h3>
                                <span id="zoom-status-badge" class="text-xs text-slate-500 font-medium">
                                    Tautan Zoom yang digunakan peserta selama pelaksanaan
                                </span>
                            </div>

                            <div>
                                <label for="zoom_url" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Link URL Meeting Zoom (Tautan Utama)
                                </label>
                                <input type="url" id="zoom_url" name="zoom_url" value="{{ old('zoom_url', $event->zoom_url) }}"
                                       placeholder="https://zoom.us/j/1234567890?pwd=..."
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-colors">
                                @error('zoom_url')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="zoom_meeting_id" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                        Meeting ID (Opsional)
                                    </label>
                                    <input type="text" id="zoom_meeting_id" name="zoom_meeting_id" value="{{ old('zoom_meeting_id', $event->zoom_meeting_id) }}"
                                           placeholder="Contoh: 839 2039 1234"
                                           class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                                <div>
                                    <label for="zoom_passcode" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                        Passcode Zoom (Opsional)
                                    </label>
                                    <input type="text" id="zoom_passcode" name="zoom_passcode" value="{{ old('zoom_passcode', $event->zoom_passcode) }}"
                                           placeholder="Contoh: SPBE2026"
                                           class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI MULTI-SESI ZOOM & PRESENSI 1-KE-1 SESUAI AGENDA MASTER KURSUS (Flat) --}}
                        <div class="pt-6 border-t border-slate-100 space-y-3.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                                        Jadwal Sesi Zoom &amp; Presensi Per Agenda
                                    </h3>
                                    <span id="sessions-count-badge" class="px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">
                                        0 Sesi
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <x-button type="button" id="btn-auto-generate-sessions"
                                              title="Buat jadwal sesi Zoom & presensi otomatis 1-ke-1 mengikuti daftar agenda pada Master Kursus"
                                              variant="warning" size="sm" rounded="md">
                                        Generate Sesi Sesuai Agenda
                                    </x-button>
                                </div>
                            </div>

                            {{-- Container Daftar Sesi Dinamis --}}
                            <div id="sessions-container" class="space-y-3">
                                {{-- Empty State jika belum ada sesi --}}
                                <div id="sessions-empty-state" class="p-5 rounded-md border border-dashed border-slate-300 text-center space-y-1 bg-slate-50/70">
                                    <p class="text-xs sm:text-sm text-slate-700 font-semibold">
                                        Belum ada jadwal sesi Zoom yang dibuat.
                                    </p>
                                    <p class="text-xs text-slate-500 max-w-xl mx-auto">
                                        Klik <strong>"Generate Sesi Sesuai Agenda"</strong> untuk menyiapkan sesi Zoom otomatis 1-ke-1 sesuai jumlah agenda Master Kursus. Anda bebas menggeser tanggal tiap sesi di dalam rentang pelaksanaan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Step 2 (Mirip Super Admin) --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 action-buttons form-section pt-6 border-t border-slate-100">
                        <x-button type="button" variant="secondary" size="lg" rounded="md" @click="prevStep()">
                            Kembali
                        </x-button>
                        <x-button type="button" size="lg" rounded="md" @click="nextStep()">
                            Lanjutkan
                        </x-button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- LANGKAH 3: EVALUASI, SERTIFIKAT & KONFIRMASI AKHIR       --}}
                {{-- ======================================================== --}}
                <div x-show="step === 3" x-cloak class="space-y-6">
                    {{-- Periode Evaluasi & Sertifikat (Flat) --}}
                    <div class="space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Periode Evaluasi &amp; Batas Kelulusan</h3>
                            <span class="text-xs text-slate-500 font-medium">
                                Jadwal pengerjaan ujian dan penerbitan sertifikat
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="assessment_start_at" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Mulai Evaluasi <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="assessment_start_at" name="assessment_start_at"
                                       value="{{ old('assessment_start_at', $event->assessment_start_at ? $event->assessment_start_at->format('Y-m-d') : ($event->end_date ? $event->end_date->format('Y-m-d') : '')) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('assessment_start_at')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="assessment_end_at" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                                    Tanggal Batas Akhir Evaluasi / Sertifikat <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="assessment_end_at" name="assessment_end_at"
                                       value="{{ old('assessment_end_at', $event->assessment_end_at ? $event->assessment_end_at->format('Y-m-d') : ($event->end_date ? $event->end_date->copy()->addDays(3)->format('Y-m-d') : '')) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm sm:text-[15px] focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white transition-colors">
                                @error('assessment_end_at')
                                    <p class="text-xs sm:text-sm text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Ringkasan Linimasa & Estimasi Status Otomatis (Ocean Blue Solid bg-sky-700) --}}
                    <div class="p-5 sm:p-6 rounded-md bg-sky-700 text-white shadow-sm space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/20 pb-3">
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white/85 block">Rentang Waktu Keseluruhan Program:</span>
                                <p id="summary-timeline-text" class="text-base sm:text-xl font-extrabold text-white mt-0.5">
                                    {{ $event->overall_timeline_formatted }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="summary-duration-tag" class="hidden px-2.5 py-1 rounded-md text-xs sm:text-sm font-semibold bg-white/15 border border-white/25 text-white">
                                    <span id="summary-duration-text">-</span>
                                </span>
                                <span id="summary-status-tag" class="px-2.5 py-1 rounded-md text-xs sm:text-sm font-semibold bg-white/15 border border-white/25 text-white">
                                    Status Otomatis: <strong id="summary-status-text" class="text-white">{{ $event->status_badge['label'] }}</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Step Flow Review --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs sm:text-sm">
                            <div class="bg-white/15 rounded-md p-3 border border-white/20 text-white">
                                <span class="text-xs font-semibold text-white/85 block">1. Periode Pendaftaran</span>
                                <span id="flow-reg-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->registration_start_at ? $event->registration_start_at->translatedFormat('d M') . ' - ' . ($event->registration_end_at ? $event->registration_end_at->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                            <div class="bg-white/15 rounded-md p-3 border border-white/20 text-white">
                                <span class="text-xs font-semibold text-white/85 block">2. Periode Pelaksanaan</span>
                                <span id="flow-exec-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->start_date ? $event->start_date->translatedFormat('d M') . ' - ' . ($event->end_date ? $event->end_date->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                            <div class="bg-white/15 rounded-md p-3 border border-white/20 text-white">
                                <span class="text-xs font-semibold text-white/85 block">3. Periode Evaluasi</span>
                                <span id="flow-eval-text" class="font-bold text-white mt-0.5 block">
                                    {{ $event->assessment_start_at ? $event->assessment_start_at->translatedFormat('d M') . ' - ' . ($event->assessment_end_at ? $event->assessment_end_at->translatedFormat('d M Y') : '-') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Step 3 (Mirip Super Admin) --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 action-buttons form-section pt-6 border-t border-slate-100">
                        <x-button type="button" variant="secondary" size="lg" rounded="md" @click="prevStep()">
                            Kembali
                        </x-button>
                        <x-button type="submit" id="btn-submit" size="lg" rounded="md">
                            Perbarui Gelombang
                        </x-button>
                    </div>
                </div>

                </form>
            </div>
        </div>

    </div>

    {{-- Modal Alert Validasi (Menggantikan Alert Bawaan Browser) --}}
    <x-modal name="bimtek-alert-modal" title="Perhatian" maxWidth="sm:max-w-md">
        <div x-data="{ message: '' }"
             x-on:set-bimtek-alert.window="message = $event.detail.message"
             class="space-y-4">
            <div class="flex items-start gap-3.5 py-1">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 pt-0.5">
                    <p id="bimtek-alert-message" class="text-sm font-medium text-slate-700 leading-relaxed" x-text="message"></p>
                </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <x-button type="button" variant="primary" size="md" rounded="md" onclick="onBimtekAlertClose()">
                    Mengerti
                </x-button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            let currentFocusTarget = null;
            function showBimtekAlert(message, title = 'Perhatian', focusTarget = null) {
                currentFocusTarget = focusTarget;
                const msgEl = document.getElementById('bimtek-alert-message');
                if (msgEl) {
                    msgEl.textContent = message;
                }
                window.dispatchEvent(new CustomEvent('set-bimtek-alert', {
                    detail: { message, title }
                }));
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'bimtek-alert-modal'
                }));
            }

            function onBimtekAlertClose() {
                window.dispatchEvent(new CustomEvent('close-modal', {
                    detail: 'bimtek-alert-modal'
                }));
                if (currentFocusTarget) {
                    const target = currentFocusTarget;
                    currentFocusTarget = null;
                    setTimeout(() => {
                        try { target.focus(); } catch (err) {}
                    }, 150);
                }
            }

            window.addEventListener('close-modal', function(e) {
                if (e.detail === 'bimtek-alert-modal' && currentFocusTarget) {
                    const target = currentFocusTarget;
                    currentFocusTarget = null;
                    setTimeout(() => {
                        try { target.focus(); } catch (err) {}
                    }, 150);
                }
            });

            function bimtekFormWizard(config = {}) {
                return {
                    step: config.initialStep || 1,
                    maxStepReached: config.isEdit ? 3 : (config.initialStep || 1),
                    isUnlimited: config.isUnlimitedQuota || false,
                    waveNumber: config.waveNumber || 1,
                    waveRegion: config.waveRegion || 'Nasional',
                    waveRegionCustom: config.waveRegionCustom || '',
                    waveYear: config.waveYear || String(new Date().getFullYear()),

                    get effectiveWaveRegion() {
                        return this.waveRegion === '__custom__'
                            ? (this.waveRegionCustom || '').trim()
                            : (this.waveRegion || '').trim();
                    },

                    get computedBatchName() {
                        const num = parseInt(this.waveNumber, 10);
                        const reg = this.effectiveWaveRegion;
                        const yr = String(this.waveYear || '').trim();
                        if (!num || num < 1 || !reg || !/^20\d{2}$/.test(yr)) {
                            return '';
                        }
                        return `Gelombang ${num} ${reg} ${yr}`;
                    },

                    init() {
                        window._bimtekWizard = this;
                    },

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
                                showBimtekAlert('Silakan pilih Master Kursus / Kurikulum terlebih dahulu.', 'Peringatan', courseId);
                                return false;
                            }
                            const waveNumberEl = document.getElementById('wave_number');
                            const waveRegionEl = document.getElementById('wave_region');
                            const waveRegionCustomEl = document.getElementById('wave_region_custom');
                            const waveYearEl = document.getElementById('wave_year');

                            if (!this.waveNumber || parseInt(this.waveNumber, 10) < 1) {
                                showBimtekAlert('Silakan isi Nomor Gelombang minimal 1.', 'Peringatan', waveNumberEl);
                                return false;
                            }
                            if (!this.effectiveWaveRegion) {
                                showBimtekAlert('Silakan pilih atau ketik Wilayah gelombang.', 'Peringatan', this.waveRegion === '__custom__' ? waveRegionCustomEl : waveRegionEl);
                                return false;
                            }
                            if (!/^20\d{2}$/.test(String(this.waveYear || '').trim())) {
                                showBimtekAlert('Silakan isi Tahun gelombang dengan format 4 digit yang valid (contoh: 2026).', 'Peringatan', waveYearEl);
                                return false;
                            }
                            if (batchName) {
                                batchName.value = this.computedBatchName;
                            }
                            if (!this.isUnlimited && quota && (!quota.value || parseInt(quota.value) < 1)) {
                                showBimtekAlert('Silakan tentukan kuota peserta (minimal 1) atau centang Tanpa Batas.', 'Peringatan', quota);
                                return false;
                            }
                            if (!regStart?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Buka Pendaftaran.', 'Peringatan', regStart);
                                return false;
                            }
                            if (!regEnd?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Tutup Pendaftaran.', 'Peringatan', regEnd);
                                return false;
                            }
                            if (regEnd.value < regStart.value) {
                                showBimtekAlert('Tanggal tutup pendaftaran tidak boleh sebelum tanggal buka pendaftaran.', 'Peringatan', regEnd);
                                return false;
                            }
                            return true;
                        }

                        if (currentStepNum === 2) {
                            const execStart = document.getElementById('start_date');
                            const execEnd = document.getElementById('end_date');
                            const regEnd = document.getElementById('registration_end_at');

                            if (!execStart?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Mulai Pelaksanaan.', 'Peringatan', execStart);
                                return false;
                            }
                            if (!execEnd?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Selesai Pelaksanaan.', 'Peringatan', execEnd);
                                return false;
                            }
                            if (execEnd.value < execStart.value) {
                                showBimtekAlert('Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.', 'Peringatan', execEnd);
                                return false;
                            }
                            if (regEnd?.value && execStart.value < regEnd.value) {
                                showBimtekAlert('Tanggal mulai pelaksanaan harus setelah atau sama dengan tanggal tutup pendaftaran (' + regEnd.value + ').', 'Peringatan', execStart);
                                return false;
                            }

                            const selectedAgendas = window._getSelectedCourseAgendas ? window._getSelectedCourseAgendas() : [];
                            if (selectedAgendas.length > 0) {
                                const dS = new Date(execStart.value + 'T00:00:00');
                                const dE = new Date(execEnd.value + 'T00:00:00');
                                const execDays = Math.floor((dE - dS) / (1000 * 60 * 60 * 24)) + 1;
                                if (execDays < selectedAgendas.length) {
                                    showBimtekAlert(`Durasi pelaksanaan minimal ${selectedAgendas.length} hari sesuai jumlah agenda pada Master Kursus yang dipilih.`, 'Peringatan', execEnd);
                                    return false;
                                }
                            }

                            const sessionCards = document.querySelectorAll('#sessions-container .session-card');
                            if (selectedAgendas.length > 0 && sessionCards.length > 0 && sessionCards.length !== selectedAgendas.length) {
                                showBimtekAlert(`Jumlah sesi Zoom (${sessionCards.length}) harus sama dengan jumlah agenda Master Kursus (${selectedAgendas.length} agenda). Klik "Generate Sesi Sesuai Agenda" untuk menyelaraskan.`, 'Peringatan');
                                return false;
                            }

                            for (let card of sessionCards) {
                                const titleInput = card.querySelector('.session-title-input');
                                const dateInput = card.querySelector('.session-date-input');
                                const startInput = card.querySelector('.session-start-input');

                                if (!titleInput?.value.trim()) {
                                    showBimtekAlert('Judul sesi tidak boleh kosong.', 'Peringatan', titleInput);
                                    return false;
                                }
                                if (!dateInput?.value) {
                                    showBimtekAlert('Tanggal sesi tidak boleh kosong.', 'Peringatan', dateInput);
                                    return false;
                                }
                                if (dateInput.value < execStart.value || dateInput.value > execEnd.value) {
                                    showBimtekAlert(`Tanggal ${titleInput.value} harus berada di dalam rentang pelaksanaan (${execStart.value} s/d ${execEnd.value}).`, 'Peringatan', dateInput);
                                    return false;
                                }
                                if (!startInput?.value) {
                                    showBimtekAlert('Jam mulai Zoom sesi tidak boleh kosong.', 'Peringatan', startInput);
                                    return false;
                                }
                            }

                            return true;
                        }

                        if (currentStepNum === 3) {
                            const evalStart = document.getElementById('assessment_start_at');
                            const evalEnd = document.getElementById('assessment_end_at');
                            const execEnd = document.getElementById('end_date');

                            if (!evalStart?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Mulai Evaluasi.', 'Peringatan', evalStart);
                                return false;
                            }
                            if (execEnd?.value && evalStart.value < execEnd.value) {
                                showBimtekAlert('Tanggal mulai evaluasi harus setelah atau sama dengan tanggal selesai pelaksanaan (' + execEnd.value + ').', 'Peringatan', evalStart);
                                return false;
                            }
                            if (!evalEnd?.value) {
                                showBimtekAlert('Silakan tentukan Tanggal Batas Akhir Evaluasi / Sertifikat.', 'Peringatan', evalEnd);
                                return false;
                            }
                            if (evalEnd.value < evalStart.value) {
                                showBimtekAlert('Tanggal batas akhir evaluasi harus sama atau setelah tanggal mulai evaluasi.', 'Peringatan', evalEnd);
                                return false;
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
                // MULTI-SESI ZOOM & PRESENSI 1-KE-1 SESUAI AGENDA MASTER KURSUS
                // ==========================================
                const courseSelect = document.getElementById('course_id');
                const courseAgendaInfo = document.getElementById('course-agenda-info');
                const courseAgendaSummary = document.getElementById('course-agenda-summary');
                const courseAgendaList = document.getElementById('course-agenda-list');
                const execMinDaysHint = document.getElementById('exec-min-days-hint');
                const courseAgendasMap = @json($courseAgendasMap ?? []);

                const sessionsContainer = document.getElementById('sessions-container');
                const sessionsEmptyState = document.getElementById('sessions-empty-state');
                const sessionsCountBadge = document.getElementById('sessions-count-badge');
                const btnAutoGenerate = document.getElementById('btn-auto-generate-sessions');

                @php
                    $initialSessionsData = old('sessions');
                    if ($initialSessionsData === null) {
                        if ($event->sessions && $event->sessions->isNotEmpty()) {
                            $initialSessionsData = $event->sessions->map(function ($s) {
                                return [
                                    'id' => $s->id,
                                    'title' => $s->title,
                                    'session_date' => $s->session_date ? $s->session_date->format('Y-m-d') : '',
                                    'start_time' => $s->start_time ? $s->start_time->format('Y-m-d\TH:i') : '',
                                    'end_time' => $s->end_time ? $s->end_time->format('Y-m-d\TH:i') : '',
                                    'attendance_start_at' => $s->attendance_start_at ? $s->attendance_start_at->format('Y-m-d\TH:i') : '',
                                    'attendance_end_at' => $s->attendance_end_at ? $s->attendance_end_at->format('Y-m-d\TH:i') : '',
                                ];
                            })->values()->toArray();
                        } elseif ($event->zoom_scheduled_at) {
                            $initialSessionsData = [[
                                'id' => '',
                                'title' => 'Sesi Hari 1',
                                'session_date' => $event->zoom_scheduled_at->format('Y-m-d'),
                                'start_time' => $event->zoom_scheduled_at->format('Y-m-d\TH:i'),
                                'end_time' => $event->zoom_end_at ? $event->zoom_end_at->format('Y-m-d\TH:i') : '',
                                'attendance_start_at' => $event->zoom_attendance_start_at ? $event->zoom_attendance_start_at->format('Y-m-d\TH:i') : '',
                                'attendance_end_at' => $event->zoom_attendance_end_at ? $event->zoom_attendance_end_at->format('Y-m-d\TH:i') : '',
                            ]];
                        } else {
                            $initialSessionsData = [];
                        }
                    }
                @endphp
                const initialSessions = @json($initialSessionsData);

                function getSelectedCourseAgendas() {
                    const cid = courseSelect?.value;
                    if (!cid || !Array.isArray(courseAgendasMap[cid])) return [];
                    return courseAgendasMap[cid];
                }
                window._getSelectedCourseAgendas = getSelectedCourseAgendas;

                function escapeHtml(str) {
                    return String(str ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }

                function updateCourseAgendaPreview(clearSessionsOnChange = false) {
                    const agendas = getSelectedCourseAgendas();
                    if (agendas.length > 0) {
                        if (courseAgendaInfo) courseAgendaInfo.classList.remove('hidden');
                        if (courseAgendaSummary) {
                            courseAgendaSummary.textContent = `Master kursus ini memiliki ${agendas.length} Agenda/Hari (1 Agenda = 1 Sesi Zoom):`;
                        }
                        if (courseAgendaList) {
                            courseAgendaList.innerHTML = agendas
                                .map(a => `<li><span class="font-medium text-slate-700">${escapeHtml(a.name)}</span></li>`)
                                .join('');
                        }
                        if (execMinDaysHint) {
                            execMinDaysHint.textContent = `Minimal ${agendas.length} hari sesuai jumlah agenda kursus (boleh lebih panjang jika ada jeda belajar mandiri)`;
                        }
                    } else {
                        if (courseAgendaInfo) courseAgendaInfo.classList.add('hidden');
                        if (execMinDaysHint) {
                            execMinDaysHint.textContent = 'Jadwal harus setelah periode pendaftaran';
                        }
                    }

                    if (clearSessionsOnChange) {
                        const existingCards = sessionsContainer.querySelectorAll('.session-card');
                        if (existingCards.length > 0 && existingCards.length !== agendas.length) {
                            existingCards.forEach(el => el.remove());
                        }
                    }
                    updateSessionNumbersAndBadges();
                }

                courseSelect?.addEventListener('change', () => updateCourseAgendaPreview(true));

                function updateSessionNumbersAndBadges() {
                    const cards = sessionsContainer.querySelectorAll('.session-card');
                    const agendas = getSelectedCourseAgendas();

                    if (sessionsCountBadge) {
                        if (agendas.length > 0) {
                            sessionsCountBadge.textContent = `${cards.length} / ${agendas.length} Agenda`;
                            sessionsCountBadge.className = cards.length === agendas.length
                                ? 'px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-600 text-white'
                                : 'px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700';
                        } else {
                            sessionsCountBadge.textContent = `${cards.length} Sesi`;
                            sessionsCountBadge.className = 'px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700';
                        }
                    }

                    if (cards.length === 0) {
                        if (sessionsEmptyState) sessionsEmptyState.style.display = 'block';
                    } else {
                        if (sessionsEmptyState) sessionsEmptyState.style.display = 'none';
                    }

                    cards.forEach((card, index) => {
                        card.dataset.index = index;
                        const badge = card.querySelector('.session-num-badge');
                        if (badge) badge.textContent = `Agenda Hari ${index + 1}`;

                        card.querySelectorAll('input').forEach(input => {
                            if (input.name) {
                                input.name = input.name.replace(/sessions\[\d*\]/, `sessions[${index}]`);
                            }
                        });
                    });
                }

                function replaceDatetimeDatePart(dtVal, newDate, fallbackTime) {
                    if (!newDate) return dtVal || '';
                    const timePart = (dtVal && dtVal.includes('T')) ? dtVal.split('T')[1] : fallbackTime;
                    return `${newDate}T${timePart}`;
                }

                function createSessionCard(data = {}, index = 0) {
                    const card = document.createElement('div');
                    card.className = 'session-card p-4 rounded-md bg-slate-50/70 border border-slate-200 space-y-3.5 transition-all';
                    card.dataset.index = index;

                    const agendas = getSelectedCourseAgendas();
                    const matchedAgenda = agendas[index] || null;
                    const defaultTitle = matchedAgenda ? `Hari ${index + 1}: ${matchedAgenda.name}` : `Sesi Hari ${index + 1}`;
                    const titleVal = data.title || defaultTitle;
                    const dateVal = data.session_date || (execStart?.value || '');
                    const startVal = data.start_time || (dateVal ? `${dateVal}T09:00` : '');
                    const endVal = data.end_time || (dateVal ? `${dateVal}T12:00` : '');
                    const attStartVal = data.attendance_start_at || (dateVal ? `${dateVal}T09:15` : '');
                    const attEndVal = data.attendance_end_at || (dateVal ? `${dateVal}T10:00` : '');
                    const sessionId = data.id || '';

                    card.innerHTML = `
                        ${sessionId ? `<input type="hidden" name="sessions[${index}][id]" value="${escapeHtml(sessionId)}">` : ''}
                        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-200/80 flex-wrap">
                            <div class="flex items-center gap-2 flex-1 min-w-0 flex-wrap">
                                <span class="session-num-badge px-2.5 py-1 rounded-md text-xs font-bold bg-[#13416B] text-white shrink-0">
                                    Agenda Hari ${index + 1}
                                </span>
                                <input type="text" name="sessions[${index}][title]" value="${escapeHtml(titleVal)}" placeholder="Judul Sesi" required
                                       class="session-title-input flex-1 min-w-[200px] px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Sesi <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="sessions[${index}][session_date]" value="${escapeHtml(dateVal)}" required
                                       class="session-date-input w-full px-3 py-2 text-xs sm:text-sm rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Mulai Zoom <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" name="sessions[${index}][start_time]" value="${escapeHtml(startVal)}" required
                                       class="session-start-input w-full px-3 py-2 text-xs sm:text-sm rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Selesai Zoom (Opsional)
                                </label>
                                <input type="datetime-local" name="sessions[${index}][end_time]" value="${escapeHtml(endVal)}"
                                       class="session-end-input w-full px-3 py-2 text-xs sm:text-sm rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                            </div>
                        </div>

                        {{-- Rentang Jam Pembukaan Presensi Sesi Ini (Flat dengan Garis Tipis) --}}
                        <div class="pt-3 border-t border-slate-200/80 space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                <span class="font-bold text-slate-700">
                                    Jadwal Presensi Sesi Ini
                                </span>
                                <span class="text-slate-500 text-[11px]">Berada di dalam jam Zoom sesi ini</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Buka Presensi</label>
                                    <input type="datetime-local" name="sessions[${index}][attendance_start_at]" value="${escapeHtml(attStartVal)}"
                                           class="session-att-start w-full px-3 py-2 text-xs sm:text-sm rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Tutup Presensi</label>
                                    <input type="datetime-local" name="sessions[${index}][attendance_end_at]" value="${escapeHtml(attEndVal)}"
                                           class="session-att-end w-full px-3 py-2 text-xs sm:text-sm rounded-md bg-white text-slate-900 border border-slate-300 focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                                </div>
                            </div>
                        </div>
                    `;

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

                    // Saat admin mengubah Tanggal Sesi, otomatis sinkronkan tanggal pada jam Zoom & Presensi
                    dateInput?.addEventListener('change', function () {
                        const newDate = dateInput.value;
                        if (newDate) {
                            if (startInput) startInput.value = replaceDatetimeDatePart(startInput.value, newDate, '09:00');
                            if (endInput) endInput.value = replaceDatetimeDatePart(endInput.value, newDate, '12:00');
                            if (attStartInput) attStartInput.value = replaceDatetimeDatePart(attStartInput.value, newDate, '09:15');
                            if (attEndInput) attEndInput.value = replaceDatetimeDatePart(attEndInput.value, newDate, '10:00');
                        }
                        syncCardBounds();
                    });

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

                btnAutoGenerate?.addEventListener('click', function () {
                    const agendas = getSelectedCourseAgendas();
                    if (!courseSelect?.value) {
                        showBimtekAlert('Silakan pilih Master Kursus di Langkah 1 terlebih dahulu.', 'Peringatan', courseSelect);
                        return;
                    }
                    if (!execStart?.value || !execEnd?.value) {
                        showBimtekAlert('Silakan tentukan tanggal mulai dan tanggal selesai pelaksanaan terlebih dahulu sebelum membuat sesi otomatis.', 'Peringatan', execStart);
                        return;
                    }

                    const dStartParts = execStart.value.split('-');
                    const dEndParts = execEnd.value.split('-');
                    const dStart = new Date(dStartParts[0], dStartParts[1] - 1, dStartParts[2]);
                    const dEnd = new Date(dEndParts[0], dEndParts[1] - 1, dEndParts[2]);

                    if (isNaN(dStart.getTime()) || isNaN(dEnd.getTime()) || dStart > dEnd) {
                        showBimtekAlert('Format rentang tanggal pelaksanaan tidak valid.', 'Peringatan');
                        return;
                    }

                    const totalCalendarDays = Math.floor((dEnd - dStart) / (1000 * 60 * 60 * 24)) + 1;
                    const targetCount = agendas.length > 0 ? agendas.length : totalCalendarDays;

                    if (agendas.length > 0 && totalCalendarDays < agendas.length) {
                        showBimtekAlert(`Durasi pelaksanaan (${totalCalendarDays} hari) lebih pendek dari jumlah agenda Master Kursus (${agendas.length} agenda). Perpanjang tanggal selesai pelaksanaan minimal ${agendas.length} hari.`, 'Peringatan', execEnd);
                        return;
                    }

                    sessionsContainer.querySelectorAll('.session-card').forEach(el => el.remove());

                    // Sebar tanggal sesi secara merata dalam rentang pelaksanaan sesuai jumlah agenda
                    for (let i = 0; i < targetCount; i++) {
                        let dayOffset = 0;
                        if (targetCount > 1) {
                            dayOffset = Math.round((i * (totalCalendarDays - 1)) / (targetCount - 1));
                        }
                        const cur = new Date(dStart.getTime());
                        cur.setDate(cur.getDate() + dayOffset);

                        const y = cur.getFullYear();
                        const m = String(cur.getMonth() + 1).padStart(2, '0');
                        const d = String(cur.getDate()).padStart(2, '0');
                        const dateStr = `${y}-${m}-${d}`;

                        const isLast = (i === targetCount - 1);
                        const agendaItem = agendas[i] || null;
                        const title = agendaItem ? `Hari ${i + 1}: ${agendaItem.name}` : `Sesi Hari ${i + 1}`;
                        const start = `${dateStr}T09:00`;
                        const end = (isLast && targetCount > 1) ? `${dateStr}T11:30` : `${dateStr}T12:00`;
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
                    }
                });

                updateCourseAgendaPreview(false);

                if (Array.isArray(initialSessions) && initialSessions.length > 0) {
                    initialSessions.forEach((s) => {
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

                document.getElementById('bimtekForm')?.addEventListener('submit', function (e) {
                    if (window._bimtekWizard) {
                        if (!window._bimtekWizard.validateStep(1)) {
                            e.preventDefault();
                            window._bimtekWizard.step = 1;
                            window._bimtekWizard.scrollToTop();
                            return false;
                        }
                        if (!window._bimtekWizard.validateStep(2)) {
                            e.preventDefault();
                            window._bimtekWizard.step = 2;
                            window._bimtekWizard.scrollToTop();
                            return false;
                        }
                        if (!window._bimtekWizard.validateStep(3)) {
                            e.preventDefault();
                            window._bimtekWizard.step = 3;
                            window._bimtekWizard.scrollToTop();
                            return false;
                        }
                    }
                });

                updateTimelineSummary();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
