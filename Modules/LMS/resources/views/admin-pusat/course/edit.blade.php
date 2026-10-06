<x-dashboard::layouts.dashboard title="Edit Course">
    <div class="p-2 sm:p-6">
        <!-- Breadcrumb Navigation -->
        <x-breadcrumb :home="route('admin-pusat.dashboard')" :items="[
            ['label' => 'Daftar Course', 'url' => route('admin-pusat.management-course.courses.index')],
            ['label' => 'Edit Course'],
        ]" />

        <x-validation-errors />

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-800">Informasi Course</h2>
                <p class="text-sm text-slate-500">Lengkapi data di bawah ini untuk mengedit course.</p>
            </div>

            <form action="{{ route('admin-pusat.management-course.courses.update', $course->slug) }}" method="POST"
                enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @include('lms::admin-pusat.course.partials.category-picker', [
                        'categories' => $categories,
                        'selectedCategoryId' => old('category_id', $course->category->id ?? ''),
                    ])

                    <!-- Nama Course -->
                    <div class="col-span-1 md:col-span-2">
                        <x-form.input name="name" label="Nama Course" :value="$course->name"
                            placeholder="Contoh: Perencanaan Tenaga Kerja" required />
                    </div>

                    <!-- Thumbnail Section -->
                    <div class="col-span-1 md:col-span-2 space-y-4" x-data="{ mode: '{{ str_starts_with($course->thumbnail ?? '', 'http') ? 'auto' : 'upload' }}' }">
                        <label class="block text-sm font-semibold text-slate-800">Thumbnail Course</label>

                        <!-- Pilihan Mode Thumbnail -->
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="radio" name="thumb_mode" value="auto" x-model="mode"
                                    class="text-[#13416B] focus:ring-[#13416B]">
                                <span>Gunakan Otomatis (Inisial & Warna)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="radio" name="thumb_mode" value="upload" x-model="mode"
                                    class="text-[#13416B] focus:ring-[#13416B]">
                                <span>Upload Gambar Baru</span>
                            </label>
                        </div>

                        @php
                            // Ambil kode warna dari URL ui-avatars jika thumbnail menggunakan mode otomatis
                            $currentBgColor = '13416B'; // Default Navy
                            if (!empty($course->thumbnail) && str_starts_with($course->thumbnail, 'http')) {
                                parse_str(parse_url($course->thumbnail, PHP_URL_QUERY), $queryParams);
                                if (!empty($queryParams['background'])) {
                                    $currentBgColor = $queryParams['background'];
                                }
                            }
                        @endphp

                        <!-- Mode 1: Pilihan Warna Kotak Tipis & Checkbox -->
                        <div x-show="mode === 'auto'"
                            class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                            <span class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih Warna
                                Background Thumbnail:</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <!-- Navy -->
                                <label
                                    class="relative flex items-center justify-between p-3 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 shadow-2xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-sm bg-[#13416B] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Navy</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="13416B"
                                        class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] w-4 h-4"
                                        @checked($currentBgColor === '13416B')
                                        onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Slate Blue -->
                                <label
                                    class="relative flex items-center justify-between p-3 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 shadow-2xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-sm bg-[#547996] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Slate Blue</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="547996"
                                        class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] w-4 h-4"
                                        @checked($currentBgColor === '547996')
                                        onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Light Blue -->
                                <label
                                    class="relative flex items-center justify-between p-3 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 shadow-2xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-sm bg-[#8BB1CC] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Light Blue</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="8BB1CC"
                                        class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] w-4 h-4"
                                        @checked($currentBgColor === '8BB1CC')
                                        onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Muted Green -->
                                <label
                                    class="relative flex items-center justify-between p-3 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30 shadow-2xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-sm bg-[#79A736] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Muted Green</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="79A736"
                                        class="rounded border-slate-300 text-[#13416B] focus:ring-[#13416B] w-4 h-4"
                                        @checked($currentBgColor === '79A736')
                                        onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">Jika opsi ini dipilih, thumbnail akan
                                diperbarui secara otomatis menggunakan inisial kursus.</p>
                        </div>

                        <!-- Mode 2: Upload File -->
                        <div x-show="mode === 'upload'" class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <x-form.input type="file" name="thumbnail" accept="image/*"
                                class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354]" />
                            <p class="text-[11px] text-slate-500 mt-2 font-medium">Biarkan kosong jika tidak ingin
                                mengubah gambar thumbnail saat ini.</p>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-span-1 md:col-span-2">
                        <x-form.textarea name="description" label="Deskripsi Course" rows="5" :value="$course->description"
                            placeholder="Tuliskan deskripsi lengkap mengenai course ini..." required />
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-8 flex justify-end space-x-3 border-t border-slate-200 pt-5">
                    <x-button :href="route('admin-pusat.management-course.courses.index')" variant="white">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Simpan Course
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Autocomplete Slug dari Nama -->
    @push('scripts')
    @endpush
</x-dashboard::layouts.dashboard>
