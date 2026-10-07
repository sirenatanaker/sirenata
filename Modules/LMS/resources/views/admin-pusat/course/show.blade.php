<x-dashboard::layouts.dashboard title="Detail Course: {{ $course->name }}">
    <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-full mx-auto space-y-6">
        
        <!-- Custom Breadcrumb -->
        <nav class="hidden md:flex mb-2 py-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-y-1.5 gap-x-2">
                <li>
                    <a href="{{ route('admin-pusat.management-course.courses.index') }}"
                        class="text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors whitespace-nowrap flex items-center gap-1.5">
                        <i class="fas fa-home text-xs"></i> Daftar Course
                    </a>
                </li>
                <li>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[10px]"></i>
                        <span class="text-sm font-bold text-slate-800 leading-snug">
                            {{ $course->name }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header Card Utama (Adaptasi UI User) -->
        <div class="bg-white rounded-md shadow-sm border border-slate-200 p-5 sm:p-6 lg:p-8 flex flex-col md:flex-row gap-6 lg:gap-8 items-start transition-all">
            <!-- Bagian Kiri: Thumbnail -->
            <div class="w-full md:w-1/3 lg:w-1/4 shrink-0 rounded-md overflow-hidden bg-slate-100 aspect-video md:aspect-[4/3] relative border border-slate-200 flex items-center justify-center">
                @if (!empty($course->thumbnail))
                    <img src="{{ $course->thumbnail }}" alt="{{ $course->name }}" class="w-full h-full object-cover" />
                @else
                    <div class="flex flex-col items-center justify-center text-slate-400 gap-2">
                        <i class="fas fa-image text-4xl"></i>
                        <span class="text-xs font-medium">Tanpa Thumbnail</span>
                    </div>
                @endif
            </div>

            <!-- Bagian Kanan: Informasi Kursus -->
            <div class="flex-1 flex flex-col w-full h-full">
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-3 py-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/10 border border-[#13416B]/20 rounded-md">
                        {{ $course->category->name ?? 'Tanpa Kategori' }}
                    </span>
                </div>

                <!-- Judul -->
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-3 tracking-tight leading-tight">
                    {{ $course->name }}
                </h1>

                <!-- Deskripsi -->
                <div class="prose prose-sm text-slate-600 mb-4 max-w-none">
                    <p class="leading-relaxed">
                        {{ $course->description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Grid Layout Bawah -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
            
            <!-- KOLOM KIRI (Kurikulum) -->
            <div class="order-2 lg:order-1 lg:col-span-2 space-y-6">
                <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
                    <!-- Header Kurikulum -->
                    <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-white text-[#13416B] rounded-xl shrink-0 border border-slate-200 shadow-sm">
                                <i class="fas fa-layer-group text-lg"></i>
                            </div>
                            <h2 class="text-lg font-bold text-slate-800 tracking-wide">Kurikulum / Modul Belajar</h2>
                        </div>
                        <button type="button" x-data @click="$dispatch('open-modal', 'course_sections-{{ $course->slug }}')"
                            class="px-4 py-2 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-colors shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> Tambah Bagian
                        </button>
                    </div>

                    <!-- Modal Tambah Bagian (Section) -->
                    <x-modal name="course_sections-{{ $course->slug }}" title="Tambah Bagian Baru" maxWidth="sm:max-w-xl">
                        <x-validation-errors class="mb-4" />
                        <form action="{{ route('admin-pusat.management-course.course-sections.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="course_slug" value="{{ $course->slug }}" />
                            <div class="mb-5">
                                <h3 class="text-lg font-bold text-slate-800">Tambah Bagian Baru</h3>
                                <p class="text-sm text-slate-500 mt-1">Buat struktur bagian (section) untuk materi course Anda.</p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Nama Bagian <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" required placeholder="Contoh: Bab 1: Pengenalan Dasar"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea name="description" rows="3" placeholder="Tuliskan gambaran singkat mengenai bagian ini..."
                                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-sm"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                                <button type="button" x-data @click="$dispatch('close-modal', 'course_sections-{{ $course->slug }}')"
                                    class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                                <button type="submit"
                                    class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-colors shadow-sm">Simpan Bagian</button>
                            </div>
                        </form>
                    </x-modal>

                    <div class="p-5 sm:p-6">
                        @if (count($course->course_sections) > 0)
                            <div class="space-y-4 sm:space-y-5">
                                <!-- Looping Bagian Materi -->
                                @foreach ($course->course_sections as $index => $section)
                                    <div x-data="{ expanded: true }" class="border border-slate-200 rounded-md overflow-hidden shadow-sm transition-all duration-300" :class="{ 'ring-1 ring-[#13416B]/20': expanded }">
                                        <!-- Header Section -->
                                        <div class="bg-slate-50/80 px-4 sm:px-5 py-3 sm:py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-3 transition-colors cursor-pointer" @click="expanded = !expanded">
                                            <div class="flex items-center gap-3.5">
                                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-[#13416B] text-xs font-bold shrink-0 border border-slate-200 shadow-sm">
                                                    {{ $section->position ?? ($index + 1) }}
                                                </span>
                                                <div>
                                                    <h3 class="font-bold text-slate-800 text-sm sm:text-base leading-snug">
                                                        {{ $section->name }}
                                                    </h3>
                                                    <span class="text-[11px] font-medium text-slate-500 mt-0.5 block">
                                                        {{ count($section->contents) }} Topik Materi
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2 self-end sm:self-auto" @click.stop>
                                                <!-- Tombol Hapus Section -->
                                                <button type="button" x-data @click="$dispatch('open-modal', 'delete-section-{{ $section->id }}')"
                                                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors border border-transparent hover:border-red-100" title="Hapus Bagian">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                                
                                                <button type="button" class="w-8 h-8 flex items-center justify-center text-slate-400 transition-transform duration-200 pointer-events-none" :class="{ 'rotate-180': expanded }">
                                                    <i class="fas fa-chevron-down text-sm"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Modal Hapus Section -->
                                        <x-modal name="delete-section-{{ $section->id }}" title="Konfirmasi Hapus" maxWidth="sm:max-w-xl">
                                            <form action="{{ route('admin-pusat.management-course.course-sections.destroy', $section->id) }}" method="POST" class="p-6">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="course_slug" value="{{ $course->slug }}" />
                                                <div class="text-center">
                                                    <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4 border border-red-100">
                                                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                                                    </div>
                                                    <h3 class="text-lg font-bold text-slate-800">Hapus Bagian</h3>
                                                    <p class="text-sm text-slate-500 mt-2">Yakin ingin menghapus bagian <strong>"{{ $section->name }}"</strong>?</p>
                                                    <p class="text-xs text-red-500 font-semibold bg-red-50 py-2 px-3 rounded-lg mt-3 inline-block">
                                                        Peringatan: Semua materi di dalamnya akan ikut terhapus!
                                                    </p>
                                                </div>
                                                <div class="flex justify-center gap-3 mt-6">
                                                    <button type="button" x-data @click="$dispatch('close-modal', 'delete-section-{{ $section->id }}')"
                                                        class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 shadow-sm">Batal</button>
                                                    <button type="submit"
                                                        class="px-5 py-2.5 text-sm font-bold text-white bg-red-600 border border-transparent rounded-xl hover:bg-red-700 shadow-sm">Ya, Hapus</button>
                                                </div>
                                            </form>
                                        </x-modal>

                                        <!-- Expandable Content -->
                                        <div x-show="expanded" x-collapse x-cloak>
                                            <div class="p-3 sm:p-5 border-t border-slate-100 bg-slate-50/30 space-y-3">
                                                <!-- List Content -->
                                                @forelse ($section->contents as $content)
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-md border border-slate-200 bg-white hover:border-[#13416B]/40 hover:shadow-sm transition-all duration-200 gap-3">
                                                        
                                                        <div class="flex items-start sm:items-center gap-3.5 flex-1 min-w-0">
                                                            <span class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 text-[#13416B] shrink-0 border border-blue-100 mt-0.5 sm:mt-0">
                                                                @if ($content->video)
                                                                    <i class="fas fa-play text-sm"></i>
                                                                @else
                                                                    <i class="fas fa-file-alt text-sm"></i>
                                                                @endif
                                                            </span>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="font-bold text-slate-800 text-sm leading-tight break-words">
                                                                    {{ $content->name }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0 w-full sm:w-auto justify-end">
                                                            <a href="{{ route('admin-pusat.management-course.course-sections-contents.show', $content->id) }}?course_slug={{ $course->slug }}"
                                                                class="px-3 py-1.5 text-xs font-bold text-[#13416B] bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-1.5" title="Lihat">
                                                                <i class="fas fa-eye"></i> <span class="sm:hidden lg:inline">Lihat</span>
                                                            </a>
                                                            <a href="{{ route('admin-pusat.management-course.course-sections-contents.edit', $content->id) }}?course_slug={{ $course->slug }}"
                                                                class="px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors shadow-sm flex items-center gap-1.5" title="Edit">
                                                                <i class="fas fa-edit"></i> <span class="sm:hidden lg:inline">Edit</span>
                                                            </a>
                                                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-content-{{ $content->id }}')"
                                                                class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-lg hover:bg-red-100 transition-colors shadow-sm flex items-center gap-1.5" title="Hapus">
                                                                <i class="fas fa-trash-alt"></i> <span class="sm:hidden lg:inline">Hapus</span>
                                                            </button>
                                                        </div>

                                                        <!-- Modal Hapus Content Materi -->
                                                        <x-modal name="delete-content-{{ $content->id }}" title="Konfirmasi Hapus" maxWidth="sm:max-w-xl">
                                                            <form action="{{ route('admin-pusat.management-course.course-sections-contents.destroy', $content->id) }}" method="POST" class="p-6">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="course_slug" value="{{ $course->slug }}" />
                                                                <div class="text-center">
                                                                    <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4 border border-red-100">
                                                                        <i class="fas fa-trash-alt text-2xl"></i>
                                                                    </div>
                                                                    <h3 class="text-lg font-bold text-slate-800">Hapus Materi</h3>
                                                                    <p class="text-sm text-slate-500 mt-2">Yakin hapus materi <strong>"{{ $content->name }}"</strong>?</p>
                                                                </div>
                                                                <div class="flex justify-center gap-3 mt-6">
                                                                    <button type="button" x-data @click="$dispatch('close-modal', 'delete-content-{{ $content->id }}')"
                                                                        class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded-xl shadow-sm">Batal</button>
                                                                    <button type="submit"
                                                                        class="px-5 py-2.5 text-sm font-bold text-white bg-red-600 rounded-xl shadow-sm hover:bg-red-700">Ya, Hapus</button>
                                                                </div>
                                                            </form>
                                                        </x-modal>
                                                    </div>
                                                @empty
                                                    <div class="p-4 text-center text-slate-500 text-sm bg-white rounded-xl border border-dashed border-slate-200">
                                                        <i class="fas fa-folder-open mb-2 text-slate-300 text-xl block"></i>
                                                        Belum ada materi di bagian ini.
                                                    </div>
                                                @endforelse

                                                <!-- Footer Section: Tambah Materi & Post Test -->
                                                @php
                                                    $existingPostTest = \Modules\LMS\Models\PostTest::where('course_section_id', $section->id)->first();
                                                @endphp
                                                <div class="mt-4 pt-4 border-t border-slate-200/80 flex flex-col sm:flex-row gap-3">
                                                    
                                                    <a href="{{ route('admin-pusat.management-course.course-sections-contents.create', ['course_slug' => $course->slug, 'section_id' => $section->id]) }}"
                                                        class="flex-1 px-4 py-3 border-2 border-dashed border-[#13416B]/30 rounded-xl text-sm font-bold text-[#13416B] bg-white hover:bg-[#13416B]/5 transition-colors flex items-center justify-center gap-2">
                                                        <i class="fas fa-plus-circle"></i> Tambah Materi
                                                    </a>
                                                    
                                                    @if ($existingPostTest)
                                                        <a href="{{ route('admin-pusat.management-course.post-tests.edit', $existingPostTest->id) }}?course_slug={{ $course->slug }}"
                                                            class="flex-1 px-4 py-3 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                                            <i class="fas fa-edit"></i> Edit Post Test
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin-pusat.management-course.post-tests.create', ['course_slug' => $course->slug, 'section_id' => $section->id]) }}"
                                                            class="flex-1 px-4 py-3 border border-emerald-200 rounded-xl text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                                            <i class="fas fa-clipboard-check"></i> Buat Post Test
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- ============================================== -->
                                <!-- FINAL POST TEST (EVALUASI AKHIR MODUL) CARD    -->
                                <!-- ============================================== -->
                                @php
                                    $existingFinalTest = \Modules\LMS\Models\PostTest::where('course_id', $course->id)
                                        ->whereNull('course_section_id')
                                        ->first();
                                @endphp
                                <div class="bg-gradient-to-r from-[#13416B] to-[#0f3354] rounded-md overflow-hidden shadow-md mt-8 relative">
                                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                                        <div class="flex items-start sm:items-center gap-4 flex-1">
                                            <div class="w-12 h-12 rounded-full bg-white/20 text-white flex items-center justify-center shrink-0 border border-white/30 mt-1 sm:mt-0">
                                                <i class="fas fa-graduation-cap text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-blue-200 mb-1">Tahap Akhir</p>
                                                <h3 class="text-lg font-extrabold text-white tracking-wide leading-tight">Evaluasi Akhir Course</h3>
                                            </div>
                                        </div>

                                        @if ($existingFinalTest)
                                            <a href="{{ route('admin-pusat.management-course.post-tests.edit', $existingFinalTest->id) }}?course_slug={{ $course->slug }}"
                                                class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-600 transition-all shadow-sm flex items-center justify-center gap-2 shrink-0">
                                                <i class="fas fa-edit"></i> Edit Evaluasi
                                            </a>
                                        @else
                                            <a href="{{ route('admin-pusat.management-course.post-tests.create', ['course_slug' => $course->slug, 'course_id' => $course->id]) }}"
                                                class="w-full sm:w-auto px-5 py-2.5 bg-white text-[#13416B] text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2 shrink-0">
                                                <i class="fas fa-plus"></i> Buat Evaluasi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Empty State Kurikulum -->
                            <div class="text-center py-12 px-4 border border-dashed border-slate-200 rounded-md bg-slate-50">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white text-[#13416B] mb-4 border border-slate-200 shadow-sm">
                                    <i class="fas fa-folder-open text-2xl"></i>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 mb-1">Kurikulum Kosong</h3>
                                <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">Mulai bangun struktur materi course Anda dengan menambahkan bagian pertama.</p>
                                <button type="button" x-data @click="$dispatch('open-modal', 'course_sections-{{ $course->slug }}')"
                                    class="px-5 py-2.5 text-sm font-bold text-[#13416B] bg-white border border-[#13416B]/30 rounded-xl hover:bg-[#13416B]/10 transition-colors shadow-sm inline-flex items-center gap-2">
                                    <i class="fas fa-plus"></i> Buat Bagian Pertama
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (Sidebar: Aksi, Informasi, Catatan) -->
            <div class="order-1 lg:order-2 flex flex-col gap-3 sm:gap-6 lg:sticky lg:top-24 lg:self-start">
                
                <!-- Card Aksi -->
                <div class="order-1 bg-white rounded-md shadow-sm border border-slate-200 p-3 sm:p-6">
                    <h3 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 sm:mb-4">Aksi Course</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin-pusat.management-course.courses.edit', $course->slug) }}"
                            class="flex items-center justify-center w-full px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-white bg-amber-500 rounded-md sm:rounded-xl hover:bg-amber-600 transition-colors shadow-sm gap-2">
                            <i class="fas fa-edit"></i> Edit Informasi Course
                        </a>
                        <a href="{{ route('admin-pusat.management-course.courses.index') }}"
                            class="flex items-center justify-center w-full px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded-md sm:rounded-xl hover:bg-slate-50 transition-colors shadow-sm gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <!-- Card Pengaturan Sertifikat (WIDGET BARU) -->
                <div class="order-4 bg-white rounded-md shadow-sm border border-slate-200 p-3 sm:p-6">
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl shrink-0 border border-emerald-100">
                            <i class="fas fa-certificate text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 tracking-wide">Sertifikat Kelulusan</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Atur dokumen penghargaan</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 mb-5 leading-relaxed">
                        Pastikan Anda telah mengatur template dan tanda tangan digital untuk sertifikat yang akan diterbitkan ke peserta kursus.
                    </p>
                    <a href="{{ route('admin-pusat.certificates.index') }}"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm gap-2">
                        <i class="fas fa-cog"></i> Atur Sertifikat
                    </a>
                </div>

                <!-- Card Informasi Tambahan -->
                <div class="order-2 bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Tambahan</h3>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-slate-100">
                            <li class="flex justify-between items-center gap-2 px-3 sm:px-6 py-2.5 sm:py-3.5 hover:bg-slate-50 transition-colors">
                                <span class="text-xs sm:text-sm font-medium text-slate-500">Kategori</span>
                                <span class="text-xs sm:text-sm font-bold text-[#13416B] text-right">{{ $course->category->name ?? '-' }}</span>
                            </li>
                            <li class="flex justify-between items-center gap-2 px-3 sm:px-6 py-2.5 sm:py-3.5 hover:bg-slate-50 transition-colors">
                                <span class="text-xs sm:text-sm font-medium text-slate-500">Dibuat Pada</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-800 text-right">
                                    {{ \Carbon\Carbon::parse($course->created_at)->translatedFormat('d M Y') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card Peringatan / Wajib Post Test -->
                <div class="order-3 bg-amber-50 rounded-md border border-amber-200 p-3 sm:p-5 flex items-start gap-2 sm:gap-3 shadow-sm">
                    <i class="fas fa-info-circle text-amber-500 mt-0.5 text-base sm:text-lg shrink-0"></i>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-amber-800 mb-1">Panduan Evaluasi</h4>
                        <p class="text-[10px] sm:text-xs text-amber-700 leading-relaxed font-medium">
                            Anda <strong>wajib menambahkan Post Test</strong> pada setiap akhir bagian (section) materi sebagai syarat kelulusan peserta untuk lanjut ke bagian berikutnya.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-dashboard::layouts.dashboard>