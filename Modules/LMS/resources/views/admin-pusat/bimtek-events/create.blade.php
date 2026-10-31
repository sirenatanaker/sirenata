<x-dashboard::layouts.dashboard title="Buka Gelombang Bimtek Baru - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-4xl mx-auto space-y-5">

        {{-- Header & Navigasi Kembali --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-1">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Daftar Bimtek</span>
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">Buka Gelombang Bimtek Baru</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Jadwalkan Bimtek Online (Zoom) yang terhubung ke silabus kursus yang sudah ada.</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 sm:p-6">
            <form action="{{ route('admin-pusat.management-course.bimtek-events.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Pilih Master Kursus --}}
                <div>
                    <label for="course_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Pilih Master Kursus / Kurikulum <span class="text-rose-500">*</span>
                    </label>
                    <select id="course_id" name="course_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nama Gelombang --}}
                    <div>
                        <label for="batch_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Gelombang / Angkatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="batch_name" name="batch_name" value="{{ old('batch_name') }}"
                               placeholder="Contoh: Bimtek SPBE Angkatan I 2026" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                        @error('batch_name')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kode Batch --}}
                    <div>
                        <label for="batch_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kode Registrasi Gelombang (Opsional)
                        </label>
                        <input type="text" id="batch_code" name="batch_code" value="{{ old('batch_code') }}"
                               placeholder="Biarkan kosong untuk otomatis (misal: BIMTEK-SPBE-2026-I)"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Metode Pelaksanaan --}}
                    <div>
                        <label for="execution_type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Metode Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <select id="execution_type" name="execution_type" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                            <option value="online" @selected(old('execution_type') === 'online')>Online (Zoom Meeting)</option>
                            <option value="offline" @selected(old('execution_type') === 'offline')>Offline (Tatap Muka Fisik)</option>
                            <option value="hybrid" @selected(old('execution_type') === 'hybrid')>Hybrid (Zoom + Tatap Muka)</option>
                        </select>
                    </div>

                    {{-- Kuota Peserta --}}
                    <div>
                        <label for="quota" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kapasitas Kuota (Peserta) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="quota" name="quota" value="{{ old('quota', 100) }}" min="1" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                            <option value="draft" @selected(old('status') === 'draft')>Draft (Belum Dibuka)</option>
                            <option value="open_registration" @selected(old('status') === 'open_registration')>Buka Pendaftaran</option>
                            <option value="ongoing" @selected(old('status') === 'ongoing')>Sedang Berlangsung</option>
                            <option value="completed" @selected(old('status') === 'completed')>Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Tanggal Mulai --}}
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal & Waktu Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label for="end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal & Waktu Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">
                    </div>
                </div>

                {{-- Pengaturan Zoom Meeting (Online) --}}
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200/80 space-y-3.5">
                    <div class="flex items-center gap-2 text-blue-900 font-bold text-xs uppercase tracking-wider">
                        <i class="fas fa-video text-blue-600"></i>
                        <span>Informasi Live Session Zoom (Online)</span>
                    </div>

                    <div>
                        <label for="zoom_url" class="block text-xs font-semibold text-slate-700 mb-1">
                            Link Meeting Zoom
                        </label>
                        <input type="url" id="zoom_url" name="zoom_url" value="{{ old('zoom_url') }}"
                               placeholder="https://zoom.us/j/1234567890?pwd=..."
                               class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="zoom_scheduled_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tanggal & Jam Mulai Sesi Zoom
                            </label>
                            <input type="datetime-local" id="zoom_scheduled_at" name="zoom_scheduled_at" value="{{ old('zoom_scheduled_at') }}"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('zoom_scheduled_at')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="zoom_end_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tanggal & Jam Selesai Sesi Zoom (Opsional)
                            </label>
                            <input type="datetime-local" id="zoom_end_at" name="zoom_end_at" value="{{ old('zoom_end_at') }}"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('zoom_end_at')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-[11px] text-blue-800/80 leading-relaxed">
                        <i class="fas fa-info-circle mr-1 text-blue-600"></i>
                        Bebas memilih tanggal dan jam spesifik tatap muka Zoom selama masih berada di dalam rentang waktu pelaksanaan (misal pelaksanaan 04 Nov - 09 Nov, sesi Zoom dijadwalkan pada 08 Nov pukul 09:00 WIB).
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="zoom_meeting_id" class="block text-xs font-semibold text-slate-700 mb-1">
                                Meeting ID (Opsional)
                            </label>
                            <input type="text" id="zoom_meeting_id" name="zoom_meeting_id" value="{{ old('zoom_meeting_id') }}"
                                   placeholder="Contoh: 839 2039 1234"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white">
                        </div>
                        <div>
                            <label for="zoom_passcode" class="block text-xs font-semibold text-slate-700 mb-1">
                                Passcode Zoom (Opsional)
                            </label>
                            <input type="text" id="zoom_passcode" name="zoom_passcode" value="{{ old('zoom_passcode') }}"
                                   placeholder="Contoh: SPBE2026"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white">
                        </div>
                    </div>
                </div>

                {{-- Deskripsi / Catatan Tambahan --}}
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keterangan / Panduan Peserta
                    </label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Tuliskan petunjuk teknis atau ketentuan bagi peserta bimtek..."
                              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                        Simpan & Buka Gelombang
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const startInput = document.getElementById('start_date');
                const endInput = document.getElementById('end_date');
                const zoomStartInput = document.getElementById('zoom_scheduled_at');
                const zoomEndInput = document.getElementById('zoom_end_at');

                function syncZoomRange() {
                    if (!startInput || !endInput || !zoomStartInput || !zoomEndInput) return;
                    const minVal = startInput.value || '';
                    const maxVal = endInput.value || '';

                    zoomStartInput.min = minVal;
                    zoomStartInput.max = maxVal;
                    zoomEndInput.min = zoomStartInput.value || minVal;
                    zoomEndInput.max = maxVal;
                }

                startInput?.addEventListener('change', syncZoomRange);
                endInput?.addEventListener('change', syncZoomRange);
                zoomStartInput?.addEventListener('change', syncZoomRange);
                syncZoomRange();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
