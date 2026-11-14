<x-dashboard::layouts.dashboard title="Tambah Pertanyaan Kuesioner - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-4xl mx-auto space-y-4 sm:space-y-6">

        {{-- Navigasi Kembali --}}
        <div>
            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index') }}"
               class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-500 hover:text-[#13416B] transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Daftar Kuesioner</span>
            </a>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 bg-[#13416B] text-white">
                <h2 class="text-base sm:text-lg font-bold">Tambah Instrumen Pertanyaan Kuesioner</h2>
                <p class="text-xs text-slate-200 mt-1">
                    Definisikan pertanyaan kuesioner evaluasi yang akan dijawab oleh peserta bimtek setelah menyelesaikan pelatihan.
                </p>
            </div>

            <form action="{{ route('admin-pusat.management-course.evaluation-questions.store') }}" method="POST"
                  x-data="{
                      questionType: '{{ old('type', 'scale') }}',
                      useCustomSection: false
                  }"
                  class="p-5 sm:p-6 space-y-5">
                @csrf

                {{-- 1. Teks Pertanyaan --}}
                <div>
                    <label for="question" class="block text-xs font-bold text-slate-700 mb-1">
                        Teks Pertanyaan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="question" name="question" rows="2" required
                              placeholder="Misal: Penguasaan materi & kejelasan paparan narasumber..."
                              class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none transition-colors">{{ old('question') }}</textarea>
                    @error('question')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Petunjuk Pengisian (Opsional) --}}
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 mb-1">
                        Petunjuk / Penjelasan Tambahan (Opsional)
                    </label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}"
                           placeholder="Misal: Berikan penilaian terhadap kedalaman dan penguasaan materi narasumber..."
                           class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none transition-colors">
                    @error('description')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Kelompok / Bagian Pertanyaan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="section" class="block text-xs font-bold text-slate-700 mb-1">
                            Kelompok / Kategori Bagian <span class="text-rose-500">*</span>
                        </label>
                        <template x-if="!useCustomSection">
                            <select id="section_select"
                                    @change="if ($el.value === '__custom__') { useCustomSection = true; $nextTick(() => $refs.customSectionInput.focus()); } else { $refs.sectionHidden.value = $el.value; }"
                                    class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-xs sm:text-sm bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                @foreach ($sections as $sec)
                                    <option value="{{ $sec }}" @selected(old('section', 'Materi & Pemateri') === $sec)>{{ $sec }}</option>
                                @endforeach
                                <option value="__custom__">+ Buat Kelompok Baru...</option>
                            </select>
                        </template>

                        <div x-show="useCustomSection" x-cloak class="flex items-center gap-2">
                            <input type="text" x-ref="customSectionInput"
                                   @input="$refs.sectionHidden.value = $el.value"
                                   placeholder="Ketik nama kategori bagian baru..."
                                   class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                            <button type="button" @click="useCustomSection = false; $refs.sectionHidden.value = 'Materi & Pemateri'"
                                    class="px-2.5 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-xs text-slate-600 font-semibold shrink-0">
                                Batal
                            </button>
                        </div>

                        <input type="hidden" name="section" x-ref="sectionHidden" value="{{ old('section', 'Materi & Pemateri') }}">
                        @error('section')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Urutan Tampil --}}
                    <div>
                        <label for="order" class="block text-xs font-bold text-slate-700 mb-1">
                            Nomor Urut Tampil <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="order" name="order" min="1" value="{{ old('order', $nextOrder) }}" required
                               class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none transition-colors">
                        @error('order')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 5. Tipe Pertanyaan --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Tipe Format Jawaban <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3.5 rounded-md border transition-all cursor-pointer flex items-start gap-3"
                               :class="questionType === 'scale' ? 'border-[#13416B] bg-[#13416B]/5 ring-1 ring-[#13416B]' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="type" value="scale" x-model="questionType" class="mt-0.5 text-[#13416B]">
                            <div class="space-y-0.5">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-star text-amber-500 text-xs"></i> Skala Linear (1 - 5)
                                </span>
                                <p class="text-[11px] text-slate-500">
                                    Peserta memberikan rating bintang / nilai angka 1 sampai 5.
                                </p>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-md border transition-all cursor-pointer flex items-start gap-3"
                               :class="questionType === 'text' ? 'border-[#13416B] bg-[#13416B]/5 ring-1 ring-[#13416B]' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="type" value="text" x-model="questionType" class="mt-0.5 text-[#13416B]">
                            <div class="space-y-0.5">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-align-left text-purple-600 text-xs"></i> Uraian Teks Terbuka
                                </span>
                                <p class="text-[11px] text-slate-500">
                                    Peserta menuliskan uraian tanggapan, kritik, atau catatan saran.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Label Skala (Hanya muncul jika tipe 'scale') --}}
                <div x-show="questionType === 'scale'" x-cloak class="p-4 rounded-md bg-slate-50 border border-slate-200 space-y-3">
                    <span class="text-xs font-bold text-slate-700 block">Label Ujung Skala Penilaian:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="min_label" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                Label Nilai 1 (Skor Terendah)
                            </label>
                            <input type="text" id="min_label" name="min_label" value="{{ old('min_label', 'Sangat Kurang') }}"
                                   placeholder="Misal: Sangat Kurang / Tidak Puas"
                                   class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs sm:text-sm bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                        </div>
                        <div>
                            <label for="max_label" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                Label Nilai 5 (Skor Tertinggi)
                            </label>
                            <input type="text" id="max_label" name="max_label" value="{{ old('max_label', 'Sangat Baik') }}"
                                   placeholder="Misal: Sangat Baik / Sangat Puas"
                                   class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs sm:text-sm bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                        </div>
                    </div>
                </div>

                {{-- 6. Pengaturan Wajib & Aktif --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 pt-2 border-t border-slate-100">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', true))
                               class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B]">
                        <span class="text-xs sm:text-sm font-bold text-slate-700">Wajib Diisi oleh Peserta</span>
                    </label>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B]">
                        <span class="text-xs sm:text-sm font-bold text-slate-700">Aktifkan Pertanyaan</span>
                    </label>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.evaluation-questions.index') }}"
                       class="px-5 py-2.5 rounded-md text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs inline-flex items-center gap-2">
                        <i class="fas fa-save text-xs"></i>
                        <span>Simpan Pertanyaan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dashboard::layouts.dashboard>
