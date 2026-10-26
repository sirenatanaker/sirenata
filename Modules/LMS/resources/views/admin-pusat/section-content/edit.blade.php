<x-dashboard::layouts.dashboard title="Edit Materi: {{ $content->name }}">
    <div class="p-2 sm:p-6 max-w-full mx-auto">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-3 flex-wrap">
                <li>
                    <div class="flex items-center">
                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                            class="text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                            {{ Str::limit($course->name, 40) }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-300 text-xs mx-1"></i>
                        <span class="ml-1 text-sm font-bold text-slate-700 md:ml-2">{{ $content->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Form Card Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Edit Materi</h2>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Bagian: <span class="text-[#13416B]">{{ $content->section->name }}</span></p>
                </div>
            </div>

            <x-validation-errors class="p-6 pb-0" />

            <form action="{{ route('admin-pusat.management-course.course-sections-contents.update', $content->id) }}"
                method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="course_slug" value="{{ $course->slug }}" />

                <!-- Nama / Judul Materi -->
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                        Judul Materi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        value="{{ old('name', $content->name) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] transition-all" />
                </div>

                <!-- Rich Text Editor (Full Width) -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <label class="block text-sm font-bold text-slate-700">
                            Isi Materi Pembelajaran
                        </label>
                        <button type="button" onclick="openEditorInfoModal()" class="text-[#13416B] hover:text-[#0f3354] transition-colors" title="Panduan Teks Editor">
                            <i class="fas fa-info-circle text-lg"></i>
                        </button>
                    </div>
                    <div class="prose max-w-none bg-white relative">
                        <input type="hidden" name="content_text" id="content_text" value="{{ old('content_text', $content->content_text) }}">
                        <!-- Container Editor -->
                        <div id="editor-container">{!! old('content_text', $content->content_text) !!}</div>
                    </div>
                </div>

                <!-- Dokumen Lampiran & Referensi Sumber Belajar -->
                @php
                    $existingDocuments = $content->all_documents;
                    $selectedLibIds = old('library_ids', $content->all_libraries->pluck('id')->all());
                    if (!is_array($selectedLibIds)) {
                        $selectedLibIds = old('library_id') ? [old('library_id')] : [];
                    }
                    $selectedLibraries = $libraries->whereIn('id', $selectedLibIds);
                @endphp
                <div class="space-y-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">
                            Dokumen Lampiran & Referensi Belajar <span class="text-xs font-normal text-slate-400">(Opsional - Bisa Banyak)</span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <!-- 1. Dropzone Unggah Berkas Dokumen (Bisa Banyak) -->
                        <div class="flex flex-col justify-between p-4 rounded-xl bg-white border border-slate-200 shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <label class="block text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700">
                                        Dokumen Berkas Lampiran
                                    </label>
                                    <span id="file-count-badge" class="{{ $existingDocuments->count() ? '' : 'hidden' }} text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#13416B]/10 text-[#13416B]">
                                        {{ $existingDocuments->count() }} Berkas
                                    </span>
                                </div>
                                
                                <input type="file" name="documents[]" id="document_file" class="hidden" multiple
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.csv" />

                                <!-- Container Hidden Inputs untuk berkas yang ditandai dihapus -->
                                <div id="delete-documents-container"></div>

                                <!-- List Berkas Yang Sudah Tersimpan Sebelumnya -->
                                <div id="existing-documents-list" class="{{ $existingDocuments->count() ? '' : 'hidden' }} space-y-2 mb-3">
                                    @foreach ($existingDocuments as $doc)
                                        @php
                                            $ext = strtolower($doc->file_extension);
                                            $iconClass = match($ext) {
                                                'pdf' => 'fas fa-file-pdf text-red-500',
                                                'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                                'xls', 'xlsx', 'csv' => 'fas fa-file-excel text-emerald-600',
                                                'ppt', 'pptx' => 'fas fa-file-powerpoint text-amber-600',
                                                'zip', 'rar', '7z' => 'fas fa-file-archive text-yellow-600',
                                                default => 'fas fa-file-alt text-[#13416B]',
                                            };
                                        @endphp
                                        <div class="existing-doc-card p-3 sm:p-3.5 rounded-xl border border-blue-200 bg-blue-50/40 flex items-center justify-between gap-3 sm:gap-4 transition shadow-2xs" data-id="{{ $doc->id }}">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-md bg-white border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                                    <i class="{{ $iconClass }} text-base sm:text-lg"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug break-words">{{ $doc->file_name }}</p>
                                                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5">{{ $doc->formatted_file_size }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <a href="{{ $doc->file_url }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs font-bold text-[#13416B] hover:underline px-2 py-1">
                                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka
                                                </a>
                                                <button type="button" class="btn-delete-existing-doc w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer" data-id="{{ $doc->id }}" title="Hapus Berkas Ini">
                                                    <i class="fas fa-trash-alt text-xs sm:text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- List Berkas Baru Terpilih -->
                                <div id="new-documents-list" class="hidden space-y-2 mb-3"></div>

                                <!-- Dropzone Area -->
                                <div id="document-dropzone"
                                    class="border-2 border-dashed border-slate-300 hover:border-[#13416B] rounded-xl p-5 text-center cursor-pointer transition-all bg-slate-50/50 hover:bg-slate-50 flex flex-col items-center justify-center min-h-[120px]">
                                    <div class="w-10 h-10 rounded-full bg-[#13416B]/10 text-[#13416B] flex items-center justify-center mb-2">
                                        <i class="fas fa-cloud-arrow-up text-lg"></i>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">
                                        Tarik & letakkan beberapa berkas di sini atau <span class="text-[#13416B] underline">Pilih Berkas</span>
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        PDF, DOCX, XLSX, PPTX, ZIP, CSV (Maks. 20 MB/file)
                                    </p>
                                </div>

                                <!-- Tombol Tambah Berkas Lainnya -->
                                <div id="btn-add-more-docs-wrap" class="hidden mt-2.5">
                                    <button type="button" id="btn-add-more-docs"
                                        class="w-full py-2 px-3 rounded-lg border border-dashed border-slate-300 hover:border-[#13416B] text-slate-600 hover:text-[#13416B] bg-slate-50 hover:bg-[#13416B]/5 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fas fa-plus text-[10px]"></i> Tambah Berkas Lainnya
                                    </button>
                                </div>
                            </div>

                         
                        </div>

                        <!-- 2. Tautkan dari Perpustakaan (Bisa Banyak & Thumbnail Saja Tanpa Icon) -->
                        <div class="flex flex-col justify-between p-4 rounded-xl bg-white border border-slate-200 shadow-2xs">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <label class="block text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700">
                                        Koleksi Perpustakaan Terkait
                                    </label>
                                    <span id="library-count-badge" class="{{ $selectedLibraries->count() ? '' : 'hidden' }} text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $selectedLibraries->count() }} Koleksi
                                    </span>
                                </div>

                                <div id="library-inputs-container">
                                    @foreach ($selectedLibraries as $selLib)
                                        <input type="hidden" name="library_ids[]" value="{{ $selLib->id }}" class="lib-input-hidden" data-id="{{ $selLib->id }}">
                                    @endforeach
                                </div>

                                <!-- State: Belum Ada Koleksi Terpilih -->
                                <div id="library-empty-state" class="{{ $selectedLibraries->count() ? 'hidden' : '' }} border-2 border-dashed border-slate-300 rounded-xl p-5 text-center bg-slate-50/50 flex flex-col items-center justify-center min-h-[130px]">
                                    <p class="text-xs font-medium text-slate-600 mb-2.5">
                                        Tautkan satu atau beberapa buku, jurnal, atau modul dari perpustakaan digital.
                                    </p>
                                    <button type="button" id="btn-open-library-modal"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs transition-all cursor-pointer">
                                        <i class="fas fa-plus text-xs"></i> Pilih dari Perpustakaan
                                    </button>
                                </div>

                                <!-- State: List Koleksi Terpilih -->
                                <div id="selected-libraries-list" class="{{ $selectedLibraries->count() ? '' : 'hidden' }} space-y-2 mt-1">
                                    @foreach ($selectedLibraries as $selLib)
                                        @php
                                            $coverUrl = $selLib->cover_image_url;
                                        @endphp
                                        <div class="selected-lib-card border border-indigo-200 bg-indigo-50/30 rounded-xl p-3 sm:p-3.5 flex items-center justify-between gap-3 sm:gap-4 transition shadow-2xs" data-id="{{ $selLib->id }}">
                                            <div class="flex items-start gap-3 min-w-0">
                                                {{-- THUMBNAIL LEBIH BESAR --}}
                                                <div class="w-12 h-16 sm:w-14 sm:h-20 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 shadow-2xs">
                                                    @if ($coverUrl)
                                                        <img src="{{ $coverUrl }}" alt="{{ $selLib->title }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300"></div>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug">
                                                        {{ $selLib->title }}
                                                    </h4>
                                                    <p class="text-[11px] sm:text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                                        {{ Str::limit(strip_tags($selLib->description ?? ''), 140) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-remove-selected-lib w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0 cursor-pointer" data-id="{{ $selLib->id }}" title="Hapus Tautan">
                                                <i class="fas fa-trash-alt text-xs sm:text-sm"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Tombol Tambah Koleksi Lainnya -->
                                <div id="btn-add-more-libs-wrap" class="{{ $selectedLibraries->count() ? '' : 'hidden' }} mt-2.5">
                                    <button type="button" id="btn-add-more-libs"
                                        class="w-full py-2 px-3 rounded-lg border border-dashed border-indigo-200 hover:border-[#13416B] text-[#13416B] bg-indigo-50/50 hover:bg-indigo-50 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fas fa-plus text-[10px]"></i> Tambah Koleksi Lainnya
                                    </button>
                                </div>
                            </div>

                          
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                        class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Perbarui Materi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL INFO EDITOR -->
    <div id="editor-info-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-0 border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modal -->
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h3 class="text-base font-extrabold text-slate-700 flex items-center gap-2"><i class="fas fa-info-circle text-[#13416B]"></i> Panduan Alat Teks Editor</h3>
                <button type="button" onclick="closeEditorInfoModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            
            <!-- Body Modal (Scrollable) -->
            <div class="p-6 overflow-y-auto grow space-y-6 text-sm text-slate-600 custom-scrollbar">
                
                <!-- Grup Format Teks -->
                <div>
                    <h4 class="font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Format Teks & Paragraf</h4>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-4 text-xs">
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 text-[11px] gap-1.5"><span class="text-slate-500 font-semibold">Ukuran Huruf</span> <b>14</b> <i class="fas fa-caret-down"></i></span>
                            <span>Menentukan ukuran huruf teks (10, 12, 14, 16, 18, 24, 32).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 text-[11px] gap-1.5"><span class="text-slate-500 font-semibold">Jarak Baris</span> <b>1.5</b> <i class="fas fa-caret-down"></i></span>
                            <span>Mengatur kerapatan baris (1.0, 1.2, 1.5, 2.0, 2.5).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 text-[11px] gap-1.5"><span class="text-slate-500 font-semibold">Jarak Huruf</span> <b>0</b> <i class="fas fa-caret-down"></i></span>
                            <span>Mengatur spasi / jarak antar huruf (0 (Default), 1, 2, 3, 4).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white font-bold shrink-0">B</span>
                            <span>Menebalkan tulisan (Bold).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white italic shrink-0 font-serif">I</span>
                            <span>Memiringkan tulisan (Italic).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white underline shrink-0">U</span>
                            <span>Memberi garis bawah (Underline).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white line-through shrink-0">S</span>
                            <span>Mencoret teks (Strikethrough).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0">X<sub class="text-[9px]">2</sub></span>
                            <span>Format angka/pangkat di bawah (Subscript).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0">X<sup class="text-[9px]">2</sup></span>
                            <span>Format angka/pangkat di atas (Superscript).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-10 h-7 bg-white shrink-0"><u class="text-blue-600 font-bold decoration-blue-600 decoration-2">A</u><i class="fas fa-caret-down text-[10px] ml-1"></i></span>
                            <span>Mengubah warna teks (Text Color).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-10 h-7 bg-white shrink-0"><span class="bg-yellow-300 px-0.5 font-bold">A</span><i class="fas fa-caret-down text-[10px] ml-1"></i></span>
                            <span>Warna latar belakang teks (Highlight).</span>
                        </li>
                    </ul>
                </div>

                <!-- Grup Sisipan & Media -->
                <div>
                    <h4 class="font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Media & Lainnya</h4>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-4 text-xs">
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-link text-slate-500 text-[11px]"></i></span>
                            <span>Menyisipkan tautan/URL (Link) ke teks.</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-image text-slate-500 text-[11px]"></i></span>
                            <span>Mengunggah dan menyisipkan Gambar.</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-video text-slate-500 text-[11px]"></i></span>
                            <span>Menyisipkan Video (.mp4 / link YouTube).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-quote-right text-slate-500 text-[11px]"></i></span>
                            <span>Membuat format kutipan (Blockquote).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-code text-slate-500 text-[11px]"></i></span>
                            <span>Menyisipkan blok kode pemrograman.</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-square-root-variable text-slate-500 text-[11px]"></i></span>
                            <span>Menyisipkan Rumus Matematika (Formula).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-center border border-slate-300 rounded w-7 h-7 bg-white shrink-0"><i class="fas fa-eraser text-slate-500 text-[11px]"></i></span>
                            <span>Menghapus semua format (warna/tebal/miring) pada teks.</span>
                        </li>
                    </ul>
                </div>

                <!-- Tips Tambahan -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-indigo-900 mb-2 flex items-center gap-2"><i class="fas fa-lightbulb text-indigo-500"></i> Tips Tambahan Editor:</h4>
                    <ul class="list-disc pl-5 space-y-1.5 text-xs text-indigo-800">
                        <li><b>Mengubah Ukuran (Resize) Gambar & Video:</b> Setelah menyisipkan gambar atau video, cukup <b>klik pada media tersebut</b>. Akan muncul kotak dengan titik di sudutnya. Tarik titik (drag) tersebut untuk mengecilkan atau membesarkan ukuran secara bebas.</li>
                        <li><b>Tampilan Halaman:</b> Teks editor ini sengaja tidak dibuat menyerupai kertas batas tengah, melainkan <b>melebar penuh (Full Width)</b>. Tujuannya agar Anda bisa memiliki gambaran visual yang nyata mengenai proporsi peletakan teks dan media di halaman materi siswa nanti.</li>
                        <li><b>Shortcut Keyboard:</b> Anda tetap bisa menggunakan pintasan dasar seperti <kbd class="bg-white border px-1 rounded shadow-sm text-[10px]">Ctrl+B</kbd> untuk Bold, <kbd class="bg-white border px-1 rounded shadow-sm text-[10px]">Ctrl+I</kbd> untuk Italic, dan <kbd class="bg-white border px-1 rounded shadow-sm text-[10px]">Ctrl+Z</kbd> untuk Undo/Mengembalikan perintah.</li>
                    </ul>
                </div>

            </div>
            
            <!-- Footer Modal -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0">
                <button type="button" onclick="closeEditorInfoModal()" class="px-5 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors">Tutup Panduan</button>
            </div>
        </div>
    </div>

    <!-- MODAL LINK -->
    <div id="custom-link-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-slate-700 flex items-center gap-2"><i class="fas fa-link text-[#13416B]"></i> Tambahkan Tautan</h3>
                <button type="button" id="btn-close-link" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Teks yang ditampilkan</label>
                    <input type="text" id="link-text-input" placeholder="Contoh: Klik disini" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">URL / Tautan <span class="text-red-500">*</span></label>
                    <input type="url" id="link-url-input" placeholder="https://..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" id="btn-cancel-link" class="px-5 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="button" id="btn-save-link" class="px-5 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors"><i class="fas fa-check mr-1.5"></i> Simpan</button>
            </div>
        </div>
    </div>

    <!-- MODAL VIDEO -->
    <div id="custom-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-5 sm:p-6 border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Top Controls (Header "Sisipkan Video" Dihapus sesuai permintaan, hanya tombol close di kanan) -->
            <div class="flex justify-end items-center mb-2">
                <button type="button" id="btn-close-video" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto space-y-4 pr-0.5 custom-scrollbar">
                <!-- Segmented Tab Source Switcher -->
                <div class="p-1 bg-slate-100 rounded-xl flex gap-1 border border-slate-200/80">
                    <button type="button" id="tab-video-upload" class="flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 bg-white text-[#13416B] shadow-xs cursor-pointer">
                        <i class="fas fa-cloud-arrow-up text-sm"></i> Unggah Video (File)
                    </button>
                    <button type="button" id="tab-video-link" class="flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800 cursor-pointer">
                        <i class="fab fa-youtube text-red-600 text-sm"></i> Tautan YouTube
                    </button>
                </div>

                <!-- Panel 1: Upload Video File (Advance Dropzone & Preview) -->
                <div id="panel-upload" class="block transition-all space-y-3">
                    <input type="file" id="video-file-input" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-matroska" class="hidden">

                    <!-- Interactive Drag & Drop Area -->
                    <div id="video-dropzone" class="border-2 border-dashed border-slate-300 hover:border-[#13416B] bg-slate-50/70 hover:bg-blue-50/30 rounded-2xl p-6 sm:p-8 text-center cursor-pointer transition-all duration-200 group">
                        <div class="w-16 h-16 rounded-2xl bg-white shadow-xs border border-slate-200 text-[#13416B] flex items-center justify-center mx-auto mb-3.5 group-hover:scale-105 group-hover:border-[#13416B]/40 transition-all">
                            <i class="fas fa-film text-2xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-800 mb-1">
                            Tarik & lepas file video di sini, atau <span class="text-[#13416B] underline font-extrabold">Telusuri Berkas</span>
                        </p>
                        <p class="text-xs text-slate-400 mb-3">Format didukung: MP4, WebM, OGG, MKV, MOV</p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200/60 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-info-circle text-[10px] text-slate-400"></i> Maksimal ukuran: 100 MB
                        </div>
                    </div>

                    <!-- Selected File Info & Live Video Player Preview -->
                    <div id="video-file-card" class="hidden bg-slate-50 border border-slate-200 rounded-2xl p-4 transition-all space-y-3">
                        <div class="relative rounded-xl overflow-hidden bg-black shadow-inner aspect-video flex items-center justify-center">
                            <video id="video-preview-player" controls class="w-full h-full object-contain"></video>
                        </div>

                        <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center shrink-0">
                                    <i class="fas fa-file-video text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p id="video-file-name" class="text-xs sm:text-sm font-bold text-slate-800 truncate">video.mp4</p>
                                    <p id="video-file-size" class="text-[11px] text-slate-400 font-medium">0 MB</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" id="btn-change-video" class="px-2.5 py-1.5 text-xs font-bold text-[#13416B] hover:bg-[#13416B]/10 rounded-lg transition-colors flex items-center gap-1 cursor-pointer" title="Ganti Berkas">
                                    <i class="fas fa-sync-alt text-[10px]"></i> Ganti
                                </button>
                                <button type="button" id="btn-remove-video" class="px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1 cursor-pointer" title="Hapus Berkas">
                                    <i class="fas fa-trash-alt text-[10px]"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress Realtime Bar -->
                    <div id="upload-progress-container" class="hidden p-4 rounded-xl bg-blue-50/60 border border-blue-100 transition-all">
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-2">
                            <span class="flex items-center gap-2 text-[#13416B]">
                                <i class="fas fa-spinner fa-spin"></i> Sedang mengunggah video ke server...
                            </span>
                            <span id="upload-progress-percent" class="text-[#13416B] bg-white px-2 py-0.5 rounded-full border border-blue-200 text-[11px] font-extrabold shadow-2xs">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div id="upload-progress-bar" class="bg-gradient-to-r from-[#13416B] to-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="upload-progress-text" class="text-[11px] font-medium text-slate-500 mt-2 text-right">0 MB / 0 MB</p>
                    </div>
                </div>

                <!-- Panel 2: Link Video YouTube -->
                <div id="panel-link" class="hidden space-y-3.5 transition-all">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tautan URL Video YouTube</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-600">
                                <i class="fab fa-youtube text-lg"></i>
                            </span>
                            <input type="url" id="video-url-input" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border border-slate-300 pl-11 pr-3 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-xs">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Salin dan tempel link video YouTube (watch, embed, atau share link).</p>
                    </div>

                    <!-- Live YouTube Video Preview Frame -->
                    <div id="youtube-preview-container" class="hidden rounded-xl overflow-hidden border border-slate-200 shadow-xs bg-black aspect-video relative">
                        <iframe id="youtube-preview-frame" class="w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" id="btn-cancel-video" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="button" id="btn-save-video" class="px-6 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check text-xs"></i> Sisipkan Video
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL FORMULA MATEMATIKA (VISUAL EQUATION BUILDER SEPERTI DI WORD) -->
    <div id="custom-formula-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-5 sm:p-6 border border-slate-200 flex flex-col max-h-[92vh] overflow-hidden">
            <!-- Tombol Tutup Modal di Kanan Atas -->
            <div class="flex justify-end mb-1 shrink-0">
                <button type="button" id="btn-close-formula" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Body with scroll -->
            <div class="space-y-5 overflow-y-auto pr-1 flex-1">
                <!-- 1. Struktur Rumus Dasar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Struktur Rumus
                    </label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                        <button type="button" data-cmd="\frac{#?}{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Pecahan / Pembagian (a/b)">
                            <span class="text-base font-serif font-bold text-slate-800 group-hover:text-[#13416B]">a/b</span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Pecahan</span>
                        </button>
                        <button type="button" data-cmd="{#?}^{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Pangkat Atas (x²)">
                            <span class="text-base font-serif font-bold text-slate-800 group-hover:text-[#13416B]">x<sup>2</sup></span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Pangkat</span>
                        </button>
                        <button type="button" data-cmd="{#?}_{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Indeks Bawah (Pᵤ)">
                            <span class="text-base font-serif font-bold text-slate-800 group-hover:text-[#13416B]">P<sub>u</sub></span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Indeks Bawah</span>
                        </button>
                        <button type="button" data-cmd="{#?}_{#?}^{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Pangkat & Indeks Gabungan (Xᵢ²)">
                            <span class="text-base font-serif font-bold text-slate-800 group-hover:text-[#13416B]">X<sub>i</sub><sup>2</sup></span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Kombinasi</span>
                        </button>
                        <button type="button" data-cmd="\sqrt{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Akar Kuadrat (√x)">
                            <span class="text-base font-serif font-bold text-slate-800 group-hover:text-[#13416B]">√x</span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Akar</span>
                        </button>
                        <button type="button" data-cmd="\text{#?}" class="btn-formula-cmd flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-[#13416B]/10 border border-slate-200 hover:border-[#13416B] rounded-xl transition group cursor-pointer" title="Teks Tegak dalam Rumus">
                            <span class="text-base font-sans font-bold text-slate-800 group-hover:text-[#13416B]">Teks</span>
                            <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Teks Kata</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Simbol Operasi & Relasi Cepat -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Simbol Cepat
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-cmd=" \times " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Kali Silang (×)">×</button>
                        <button type="button" data-cmd=" \div " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Bagi (÷)">÷</button>
                        <button type="button" data-cmd=" \pm " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Plus Minus (±)">±</button>
                        <button type="button" data-cmd=" \ge " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Lebih Dari Sama Dengan (≥)">≥</button>
                        <button type="button" data-cmd=" \le " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Kurang Dari Sama Dengan (≤)">≤</button>
                        <button type="button" data-cmd=" \ne " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Tidak Sama Dengan (≠)">≠</button>
                        <button type="button" data-cmd=" \approx " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Mendekati / Kira-kira (≈)">≈</button>
                        <button type="button" data-cmd="\%" class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Persen (%)">%</button>
                        <button type="button" data-cmd=" \sum " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Sigma / Penjumlahan (∑)">∑</button>
                        <button type="button" data-cmd="\bar{#?}" class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Rata-rata (X̄)">X̄</button>
                        <button type="button" data-cmd=" \rightarrow " class="btn-formula-cmd px-3 py-1.5 bg-slate-100 hover:bg-[#13416B] hover:text-white rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer" title="Panah Kanan (→)">→</button>
                    </div>
                </div>

                <!-- 3. Kanvas Interaktif Langsung Diedit -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-700">
                            Kanvas Rumus
                        </label>
                        <button type="button" id="btn-clear-formula" class="text-[11px] font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 cursor-pointer">
                            <i class="fas fa-trash-can text-[10px]"></i> Bersihkan
                        </button>
                    </div>

                    <div class="relative bg-slate-50 border-2 border-slate-300 focus-within:border-[#13416B] focus-within:ring-2 focus-within:ring-[#13416B]/20 rounded-2xl p-4 sm:p-5 transition shadow-inner min-h-[96px] flex items-center justify-center">
                        <math-field id="formula-math-field" class="w-full text-xl sm:text-2xl bg-transparent outline-none text-slate-800 text-center" default-mode="math"></math-field>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 mt-4 shrink-0">
                <button type="button" id="btn-cancel-formula" class="px-5 py-2 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">Batal</button>
                <button type="button" id="btn-save-formula" class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition shadow-xs hover:shadow-sm cursor-pointer flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Sisipkan ke Dokumen
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PILIH KOLEKSI PERPUSTAKAAN -->
    <div id="library-picker-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center">
                        <i class="fas fa-book text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Pilih Koleksi Perpustakaan</h3>
                        <p class="text-xs text-slate-500">Tautkan literatur pustaka sebagai bahan bacaan referensi topik ini</p>
                    </div>
                </div>
                <button type="button" id="btn-close-library-modal" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Search Bar & Filters -->
            <div class="p-4 sm:p-5 border-b border-slate-100 space-y-3 shrink-0">
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="library-search-input" placeholder="Cari judul koleksi atau kategori pustaka..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] transition-all">
                </div>

                @php
                    $libraryCategories = $libraries->pluck('libraryCategory.name')->filter()->unique()->values();
                @endphp
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
                    <button type="button" class="lib-cat-btn active px-3 py-1 rounded-full font-bold bg-[#13416B] text-white transition-all cursor-pointer" data-category="ALL">
                        Semua ({{ $libraries->count() }})
                    </button>
                    @foreach ($libraryCategories as $catName)
                        <button type="button" class="lib-cat-btn px-3 py-1 rounded-full font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer" data-category="{{ Str::lower($catName) }}">
                            {{ $catName }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- List / Grid of Libraries -->
            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1">
                <div id="library-grid-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @forelse ($libraries as $lib)
                        @php
                            $catName = $lib->libraryCategory?->name ?? 'Pustaka';
                            $coverUrl = $lib->cover_image_url;
                            $descText = Str::limit(strip_tags($lib->description ?? ''), 80);
                        @endphp
                        <div class="lib-item group p-3.5 rounded-xl border border-slate-200 hover:border-[#13416B]/50 hover:shadow-md bg-white transition-all flex items-start gap-3 cursor-pointer"
                            data-id="{{ $lib->id }}"
                            data-title="{{ $lib->title }}"
                            data-category="{{ $catName }}"
                            data-category-lower="{{ Str::lower($catName) }}"
                            data-cover="{{ $coverUrl ?? '' }}"
                            data-desc="{{ $descText }}">
                            
                            <div class="w-12 h-16 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 relative shadow-2xs">
                                @if ($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $lib->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300"></div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 flex flex-col justify-between h-full">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-[#13416B] line-clamp-2 leading-snug">
                                        {{ $lib->title }}
                                    </h4>
                                    @if ($descText)
                                        <p class="text-[10px] text-slate-400 line-clamp-2 mt-0.5">{{ $descText }}</p>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="lib-status-text text-[10px] font-semibold text-slate-400">
                                        <i class="far fa-circle mr-0.5"></i> Belum Dipilih
                                    </span>
                                    <span class="lib-action-badge text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 transition-all font-bold">
                                        Pilih
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 py-8 text-center text-slate-400">
                            <i class="fas fa-book-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs">Belum ada koleksi perpustakaan yang terdaftar.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Empty Search State -->
                <div id="library-search-empty" class="hidden py-10 text-center">
                    <i class="fas fa-search text-3xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600">Tidak ada koleksi perpustakaan yang cocok</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian atau kategori lain</p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
                <span id="modal-selected-count" class="text-xs font-bold text-slate-700">0 Koleksi Dipilih</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-cancel-library-modal" class="px-4 py-1.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btn-apply-library-modal" class="px-4 py-1.5 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-lg transition-colors cursor-pointer">
                        Terapkan Pilihan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/mathlive"></script>
        <style>
            math-field {
                border: none;
                outline: none;
                background: transparent;
                font-size: 1.5rem;
                width: 100%;
                min-height: 55px;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            math-field:focus-within {
                outline: none;
            }
            math-field::part(virtual-keyboard-toggle) {
                display: none !important;
            }
            math-field::part(menu-toggle) {
                display: none !important;
            }
        </style>
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill-blot-formatter@1.0.5/dist/quill-blot-formatter.min.js"></script>

        <script>
            // LOGIKA MODAL INFO EDITOR
            function openEditorInfoModal() { document.getElementById('editor-info-modal').classList.remove('hidden'); document.getElementById('editor-info-modal').classList.add('flex'); }
            function closeEditorInfoModal() { document.getElementById('editor-info-modal').classList.add('hidden'); document.getElementById('editor-info-modal').classList.remove('flex'); }

            Quill.register('modules/blotFormatter', QuillBlotFormatter.default);

            const Parchment = Quill.import('parchment');
            const Font = Quill.import('formats/font'); Font.whitelist = ['arial', 'times-new-roman', 'calibri', 'verdana', 'courier']; Quill.register(Font, true);
            const Size = Quill.import('attributors/style/size'); Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '24px', '32px']; Quill.register(Size, true);
            const LineHeightStyle = new Parchment.Attributor.Style('lineHeight', 'line-height', { scope: Parchment.Scope.BLOCK, whitelist: ['1', '1.0', '1.2', '1.5', '2.0', '2.5'] }); Quill.register(LineHeightStyle, true);
            const LetterSpacingStyle = new Parchment.Attributor.Style('letterSpacing', 'letter-spacing', { scope: Parchment.Scope.INLINE, whitelist: ['0', '0px', '1px', '2px', '3px', '4px'] }); Quill.register(LetterSpacingStyle, true);

            class VideoElementSpec extends QuillBlotFormatter.BlotSpec {
                constructor(formatter) { super(formatter); this.targetElement = null; }
                init() { this.formatter.quill.root.addEventListener('click', (event) => { const el = event.target; if (el.tagName === 'VIDEO') { this.targetElement = el; this.formatter.show(this); } }); this.formatter.quill.on('text-change', () => { if (this.targetElement && !this.formatter.quill.root.contains(this.targetElement)) this.formatter.hide(); }); }
                getTargetElement() { return this.targetElement; }
            }
            const BaseImage = Quill.import('formats/image');
            class CustomImage extends BaseImage { static formats(domNode) { return ['style', 'width', 'height'].reduce(function(formats, attribute) { if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute); return formats; }, {}); } format(name, value) { if (['style', 'width', 'height'].includes(name)) { if (value) this.domNode.setAttribute(name, value); else this.domNode.removeAttribute(name); } else super.format(name, value); } } Quill.register(CustomImage, true);
            const BlockEmbed = Quill.import('blots/block/embed');
            class CustomVideo extends BlockEmbed {
                static create(value) { let isMp4 = value.match(/\.(mp4|webm|ogg)$/i); let node = document.createElement(isMp4 ? 'video' : 'iframe'); if (isMp4) { node.setAttribute('controls', ''); node.setAttribute('controlsList', 'nodownload'); node.setAttribute('src', value); node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;'); } else { node.setAttribute('frameborder', '0'); node.setAttribute('allowfullscreen', true); node.setAttribute('src', value); node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;'); node.setAttribute('width', '560'); node.setAttribute('height', '315'); } return node; }
                static value(node) { return node.getAttribute('src'); } static formats(domNode) { return ['style', 'width', 'height'].reduce(function(formats, attribute) { if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute); return formats; }, {}); } format(name, value) { if (['style', 'width', 'height'].includes(name)) { if (value) this.domNode.setAttribute(name, value); else this.domNode.removeAttribute(name); } else super.format(name, value); }
            } CustomVideo.blotName = 'video'; CustomVideo.tagName = ['IFRAME', 'VIDEO']; Quill.register(CustomVideo, true);

            hljs.configure({ languages: ['javascript', 'php', 'html', 'css', 'python', 'java', 'sql', 'bash'] });

            // OVERRIDE ICON QUILL DEFAULT DENGAN CUSTOM HTML
            var icons = Quill.import('ui/icons');
            icons['bold'] = '<strong class="text-[15px] font-sans">B</strong>';
            icons['italic'] = '<em class="text-[15px] font-serif">I</em>';
            icons['underline'] = '<u class="text-[15px] font-sans">U</u>';
            icons['strike'] = '<s class="text-[15px] font-sans">S</s>';
            icons['script']['sub'] = '<span class="text-[14px] font-sans">X<sub class="text-[10px]">2</sub></span>';
            icons['script']['super'] = '<span class="text-[14px] font-sans">X<sup class="text-[10px]">2</sup></span>';
            icons['link'] = '<i class="fas fa-link text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['image'] = '<i class="fas fa-image text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['video'] = '<i class="fas fa-video text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['blockquote'] = '<i class="fas fa-quote-right text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['code-block'] = '<i class="fas fa-code text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['formula'] = '<i class="fas fa-square-root-variable text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['clean'] = '<i class="fas fa-eraser text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['list']['ordered'] = '<i class="fas fa-list-ol text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['list']['bullet'] = '<i class="fas fa-list-ul text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['indent']['-1'] = '<i class="fas fa-outdent text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['indent']['+1'] = '<i class="fas fa-indent text-[13.5px] text-slate-500 ql-icon-custom"></i>';
            icons['header']['1'] = '<b class="text-[13.5px] font-sans">H1</b>';
            icons['header']['2'] = '<b class="text-[13.5px] font-sans">H2</b>';
            icons['align'][''] = '<i class="fas fa-align-left text-[14px] text-slate-600 ql-icon-custom"></i>';
            icons['align']['center'] = '<i class="fas fa-align-center text-[14px] text-slate-600 ql-icon-custom"></i>';
            icons['align']['right'] = '<i class="fas fa-align-right text-[14px] text-slate-600 ql-icon-custom"></i>';
            icons['align']['justify'] = '<i class="fas fa-align-justify text-[14px] text-slate-600 ql-icon-custom"></i>';

            var quill = new Quill('#editor-container', {
                bounds: '#editor-container', // <--- PENTING: Mencegah modal/tooltip rumus terpotong ke luar editor
                modules: {
                    syntax: true,
                    blotFormatter: { specs: [ QuillBlotFormatter.ImageSpec, QuillBlotFormatter.IframeVideoSpec, VideoElementSpec ] },
                    toolbar: {
                        container: [
                            [{ 'size': ['10px', '12px', '14px', '16px', '18px', '24px', '32px'] }],
                            [{ 'lineHeight': ['1.0', '1.2', '1.5', '2.0', '2.5'] }],
                            [{ 'letterSpacing': [false, '1px', '2px', '3px', '4px'] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'align': [] }, { 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                            ['link', 'image', 'video'],
                            ['blockquote', 'code-block', 'formula', 'clean']
                        ],
                        handlers: {
                            'link': function() { var range = quill.getSelection(true); var text = range && range.length > 0 ? quill.getText(range.index, range.length) : ''; document.getElementById('link-text-input').value = text; document.getElementById('link-url-input').value = ''; const modal = document.getElementById('custom-link-modal'); modal.classList.remove('hidden'); modal.classList.add('flex'); setTimeout(() => document.getElementById('link-url-input').focus(), 100); window.quillLinkRange = range; },
                            'video': function() {
                                var range = quill.getSelection(true);
                                window.quillVideoRange = range;
                                switchVideoTab('upload');
                                resetVideoFile();
                                videoUrlInput.value = '';
                                youtubePreviewFrame.src = '';
                                youtubePreviewContainer.classList.add('hidden');
                                uploadProgressContainer.classList.add('hidden');
                                uploadProgressBar.style.width = '0%';
                                const modal = document.getElementById('custom-video-modal');
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                            },
                            'formula': function() {
                                var range = quill.getSelection(true);
                                window.quillFormulaRange = range;
                                openFormulaModal();
                            }
                        }
                    }
                },
                placeholder: 'Ketik dokumen / materi pembelajaran di sini...',
                theme: 'snow'
            });

            // Cegah scroll jump ke atas ketika rumus matematika (formula) disubmit / disimpan
            if (quill.root) {
                const originalRootFocus = quill.root.focus.bind(quill.root);
                quill.root.focus = function(options) {
                    originalRootFocus(Object.assign({ preventScroll: true }, options));
                };
            }

            if (quill.theme && quill.theme.tooltip) {
                const originalTooltipSave = quill.theme.tooltip.save.bind(quill.theme.tooltip);
                quill.theme.tooltip.save = function() {
                    const savedScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
                    originalTooltipSave();
                    window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                    requestAnimationFrame(() => {
                        window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                    });
                    setTimeout(() => {
                        window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                    }, 20);
                    setTimeout(() => {
                        window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                    }, 80);
                };

                const tooltipRoot = quill.theme.tooltip.root;
                if (tooltipRoot) {
                    const tooltipInput = tooltipRoot.querySelector('input');
                    if (tooltipInput) {
                        tooltipInput.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.stopPropagation();
                                const savedScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
                                setTimeout(() => {
                                    window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                                }, 0);
                            }
                        }, true);
                    }
                    const actionBtn = tooltipRoot.querySelector('a.ql-action');
                    if (actionBtn) {
                        actionBtn.addEventListener('click', function(e) {
                            const savedScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
                            setTimeout(() => {
                                window.scrollTo({ top: savedScrollY, behavior: 'instant' });
                            }, 0);
                        }, true);
                    }
                }
            }

            // Sisipkan teks label di luar menu dropdown (Ukuran Huruf, Jarak Baris, Jarak Huruf)
            function setupLabeledPickers() {
                const items = [
                    { selector: '.ql-size', label: 'Ukuran Huruf' },
                    { selector: '.ql-lineHeight', label: 'Jarak Baris' },
                    { selector: '.ql-letterSpacing', label: 'Jarak Huruf' }
                ];
                items.forEach(item => {
                    const picker = document.querySelector(item.selector);
                    if (!picker || picker.dataset.labeled) return;
                    picker.dataset.labeled = 'true';

                    const group = document.createElement('span');
                    group.className = 'ql-labeled-group';

                    const labelEl = document.createElement('span');
                    labelEl.className = 'ql-custom-label';
                    labelEl.textContent = item.label;

                    picker.parentNode.insertBefore(group, picker);
                    group.appendChild(labelEl);
                    group.appendChild(picker);
                });
            }
            setupLabeledPickers();

            setTimeout(() => {
                document.querySelectorAll('.ql-toolbar button, .ql-toolbar .ql-picker').forEach(el => {
                    let title = '';
                    if(el.classList.contains('ql-bold')) title = 'Tebal (Bold)';
                    else if(el.classList.contains('ql-italic')) title = 'Miring (Italic)';
                    else if(el.classList.contains('ql-underline')) title = 'Garis Bawah (Underline)';
                    else if(el.classList.contains('ql-strike')) title = 'Coret Teks (Strikethrough)';
                    else if(el.classList.contains('ql-script') && el.value==='sub') title = 'Subscript (X₂)';
                    else if(el.classList.contains('ql-script') && el.value==='super') title = 'Superscript (X²)';
                    else if(el.classList.contains('ql-link')) title = 'Sisipkan Tautan (Link)';
                    else if(el.classList.contains('ql-image')) title = 'Sisipkan Gambar';
                    else if(el.classList.contains('ql-video')) title = 'Sisipkan Video';
                    else if(el.classList.contains('ql-clean')) title = 'Hapus Format Teks';
                    else if(el.classList.contains('ql-blockquote')) title = 'Kutipan (Quote)';
                    else if(el.classList.contains('ql-code-block')) title = 'Blok Kode';
                    else if(el.classList.contains('ql-formula')) title = 'Rumus Matematika';
                    else if(el.classList.contains('ql-list')) title = el.value === 'ordered' ? 'Daftar Angka' : 'Daftar Titik';
                    else if(el.classList.contains('ql-indent')) title = el.value === '-1' ? 'Geser Kiri (Decrease Indent)' : 'Geser Kanan (Increase Indent)';
                    else if(el.classList.contains('ql-size')) title = 'Ukuran Huruf (Font Size)';
                    else if(el.classList.contains('ql-font')) title = 'Jenis Huruf (Font Family)';
                    else if(el.classList.contains('ql-color')) title = 'Warna Teks';
                    else if(el.classList.contains('ql-background')) title = 'Warna Sorotan (Highlight)';
                    else if(el.classList.contains('ql-align')) title = 'Rata Teks (Alignment)';
                    else if(el.classList.contains('ql-lineHeight')) title = 'Jarak Baris';
                    else if(el.classList.contains('ql-letterSpacing')) title = 'Jarak Huruf';
                    if(title) { el.setAttribute('data-tooltip', title); el.classList.add('quill-tooltip'); }
                });
            }, 500);

            function closeLinkModal() { document.getElementById('custom-link-modal').classList.add('hidden'); document.getElementById('custom-link-modal').classList.remove('flex'); }
            document.getElementById('btn-close-link').addEventListener('click', closeLinkModal); document.getElementById('btn-cancel-link').addEventListener('click', closeLinkModal);
            document.getElementById('btn-save-link').addEventListener('click', function() { var text = document.getElementById('link-text-input').value.trim(); var url = document.getElementById('link-url-input').value.trim(); var range = window.quillLinkRange; if (!url) return alert('URL wajib diisi!'); if (!/^https?:\/\//i.test(url)) url = 'https://' + url; if (!text) text = url; quill.focus(); if (range && range.length > 0) { quill.deleteText(range.index, range.length); quill.insertText(range.index, text, 'link', url); quill.setSelection(range.index + text.length); } else { var cursorPosition = range ? range.index : quill.getLength(); quill.insertText(cursorPosition, text, 'link', url); quill.setSelection(cursorPosition + text.length); } closeLinkModal(); });

            // STATE & HANDLER MODAL VIDEO ADVANCED
            let currentVideoSource = 'upload';
            let selectedVideoFile = null;
            let previewObjectUrl = null;

            const tabUpload = document.getElementById('tab-video-upload');
            const tabLink = document.getElementById('tab-video-link');
            const panelUpload = document.getElementById('panel-upload');
            const panelLink = document.getElementById('panel-link');
            const videoDropzone = document.getElementById('video-dropzone');
            const videoFileInput = document.getElementById('video-file-input');
            const videoFileCard = document.getElementById('video-file-card');
            const videoPreviewPlayer = document.getElementById('video-preview-player');
            const videoFileName = document.getElementById('video-file-name');
            const videoFileSize = document.getElementById('video-file-size');
            const btnRemoveVideo = document.getElementById('btn-remove-video');
            const btnChangeVideo = document.getElementById('btn-change-video');
            const videoUrlInput = document.getElementById('video-url-input');
            const youtubePreviewContainer = document.getElementById('youtube-preview-container');
            const youtubePreviewFrame = document.getElementById('youtube-preview-frame');
            const uploadProgressContainer = document.getElementById('upload-progress-container');
            const uploadProgressBar = document.getElementById('upload-progress-bar');
            const uploadProgressText = document.getElementById('upload-progress-text');
            const uploadProgressPercent = document.getElementById('upload-progress-percent');

            function switchVideoTab(source) {
                currentVideoSource = source;
                if (source === 'upload') {
                    tabUpload.className = 'flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 bg-white text-[#13416B] shadow-xs cursor-pointer';
                    tabLink.className = 'flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800 cursor-pointer';
                    panelUpload.classList.remove('hidden');
                    panelUpload.classList.add('block');
                    panelLink.classList.remove('block');
                    panelLink.classList.add('hidden');
                } else {
                    tabLink.className = 'flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 bg-white text-[#13416B] shadow-xs cursor-pointer';
                    tabUpload.className = 'flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800 cursor-pointer';
                    panelLink.classList.remove('hidden');
                    panelLink.classList.add('block');
                    panelUpload.classList.remove('block');
                    panelUpload.classList.add('hidden');
                    setTimeout(() => videoUrlInput.focus(), 100);
                }
            }
            tabUpload.addEventListener('click', () => switchVideoTab('upload'));
            tabLink.addEventListener('click', () => switchVideoTab('link'));

            function handleFileSelect(file) {
                if (!file) return;
                const maxSize = 100 * 1024 * 1024; // 100MB
                if (file.size > maxSize) {
                    alert('Ukuran file video melebihi batas maksimal 100MB!');
                    return;
                }
                selectedVideoFile = file;
                videoFileName.textContent = file.name;
                videoFileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                if (previewObjectUrl) {
                    URL.revokeObjectURL(previewObjectUrl);
                }
                previewObjectUrl = URL.createObjectURL(file);
                videoPreviewPlayer.src = previewObjectUrl;

                videoDropzone.classList.add('hidden');
                videoFileCard.classList.remove('hidden');
            }

            function resetVideoFile() {
                selectedVideoFile = null;
                videoFileInput.value = '';
                if (previewObjectUrl) {
                    URL.revokeObjectURL(previewObjectUrl);
                    previewObjectUrl = null;
                }
                videoPreviewPlayer.pause();
                videoPreviewPlayer.src = '';
                videoFileCard.classList.add('hidden');
                videoDropzone.classList.remove('hidden');
            }

            videoDropzone.addEventListener('click', () => videoFileInput.click());
            btnChangeVideo.addEventListener('click', () => videoFileInput.click());
            btnRemoveVideo.addEventListener('click', resetVideoFile);

            videoFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFileSelect(this.files[0]);
                }
            });

            // Drag and Drop Effects
            ['dragenter', 'dragover'].forEach(eventName => {
                videoDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    videoDropzone.classList.add('border-[#13416B]', 'bg-blue-50/60', 'scale-[1.01]');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                videoDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    videoDropzone.classList.remove('border-[#13416B]', 'bg-blue-50/60', 'scale-[1.01]');
                });
            });
            videoDropzone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files && files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });

            // YouTube Live Preview
            function parseYouTubeEmbedUrl(url) {
                if (!url) return null;
                const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|shorts\/|watch\?.+&v=))([\w-]{11})/i);
                if (match && match[1]) {
                    return `https://www.youtube.com/embed/${match[1]}`;
                }
                return null;
            }

            videoUrlInput.addEventListener('input', function() {
                const embedUrl = parseYouTubeEmbedUrl(this.value.trim());
                if (embedUrl) {
                    youtubePreviewFrame.src = embedUrl;
                    youtubePreviewContainer.classList.remove('hidden');
                } else {
                    youtubePreviewFrame.src = '';
                    youtubePreviewContainer.classList.add('hidden');
                }
            });

            function closeVideoModal() {
                const modal = document.getElementById('custom-video-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (videoPreviewPlayer) {
                    videoPreviewPlayer.pause();
                }
            }
            document.getElementById('btn-close-video').addEventListener('click', closeVideoModal);
            document.getElementById('btn-cancel-video').addEventListener('click', closeVideoModal);

            document.getElementById('btn-save-video').addEventListener('click', function() {
                var range = window.quillVideoRange;
                var btn = this;
                if (currentVideoSource === 'upload') {
                    if (!selectedVideoFile) return alert('Silakan pilih atau tarik file video terlebih dahulu!');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengunggah...';
                    btn.disabled = true;
                    uploadProgressContainer.classList.remove('hidden');

                    const formData = new FormData();
                    formData.append('video', selectedVideoFile);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "{{ route('admin-pusat.management-course.upload-video-editor') }}", true);
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                            const totalMB = (e.total / (1024 * 1024)).toFixed(2);
                            uploadProgressText.innerText = `${loadedMB} MB / ${totalMB} MB`;
                            uploadProgressPercent.innerText = `${Math.round(percentComplete)}%`;
                            uploadProgressBar.style.width = percentComplete + '%';
                        }
                    };

                    xhr.onload = function() {
                        if (xhr.status === 200 || xhr.status === 201) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    quill.insertEmbed(range ? range.index : quill.getLength(), 'video', data.url);
                                    quill.setSelection((range ? range.index : quill.getLength()) + 1);
                                    closeVideoModal();
                                } else {
                                    alert('Gagal mengunggah video: ' + (data.message || 'Error'));
                                }
                            } catch (error) {
                                alert('Respon server tidak valid.');
                            }
                        } else if (xhr.status === 422) {
                            let errorMsg = 'Validasi video gagal.';
                            try {
                                const res = JSON.parse(xhr.responseText);
                                if (res.errors && res.errors.video) {
                                    errorMsg = res.errors.video.join('\n');
                                } else if (res.message) {
                                    errorMsg = res.message;
                                }
                            } catch(e) {}
                            alert(errorMsg);
                        } else if (xhr.status === 413) {
                            alert('Ukuran video terlalu besar melebihi batas upload server.');
                        } else {
                            alert('Kesalahan server (Error ' + xhr.status + ').');
                        }
                        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Sisipkan Video';
                        btn.disabled = false;
                        uploadProgressContainer.classList.add('hidden');
                    };
                    xhr.onerror = function() {
                        alert('Gagal mengunggah video.');
                        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Sisipkan Video';
                        btn.disabled = false;
                        uploadProgressContainer.classList.add('hidden');
                    };
                    xhr.send(formData);
                } else {
                    var rawUrl = videoUrlInput.value.trim();
                    if (!rawUrl) return alert('Silakan masukkan tautan video YouTube!');
                    var embedUrl = parseYouTubeEmbedUrl(rawUrl) || rawUrl;
                    quill.insertEmbed(range ? range.index : quill.getLength(), 'video', embedUrl);
                    quill.setSelection((range ? range.index : quill.getLength()) + 1);
                    closeVideoModal();
                }
            });

            // ============================================================
            // LOGIKA VISUAL EQUATION BUILDER (WORD-STYLE WYSIWYG MATHLIVE)
            // ============================================================
            const formulaModal = document.getElementById('custom-formula-modal');
            const mathField = document.getElementById('formula-math-field');

            if (mathField) {
                // Nonaktifkan virtual keyboard otomatis di layar agar tidak menghalangi popup
                mathField.mathVirtualKeyboardPolicy = "manual";
            }

            function openFormulaModal() {
                if (!formulaModal) return;
                formulaModal.classList.remove('hidden');
                formulaModal.classList.add('flex');
                setTimeout(() => {
                    if (mathField && typeof mathField.focus === 'function') {
                        mathField.focus();
                    }
                }, 150);
            }

            function closeFormulaModal() {
                if (!formulaModal) return;
                formulaModal.classList.add('hidden');
                formulaModal.classList.remove('flex');
            }

            document.querySelectorAll('.close-formula-modal-btn, #btn-close-formula, #btn-cancel-formula').forEach(btn => {
                btn.addEventListener('click', closeFormulaModal);
            });

            if (formulaModal) {
                formulaModal.addEventListener('click', function(e) {
                    if (e.target === formulaModal) closeFormulaModal();
                });
            }

            document.getElementById('btn-clear-formula')?.addEventListener('click', function() {
                if (!mathField) return;
                mathField.value = '';
                if (typeof mathField.focus === 'function') mathField.focus();
            });

            document.querySelectorAll('.btn-formula-cmd').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!mathField) return;
                    const cmd = this.getAttribute('data-cmd') || '';
                    if (!cmd) return;

                    if (typeof mathField.executeCommand === 'function') {
                        mathField.executeCommand(['insert', cmd]);
                        mathField.focus();
                    } else {
                        mathField.value = (mathField.value || '') + cmd;
                    }
                });
            });

            document.getElementById('btn-save-formula')?.addEventListener('click', function() {
                if (!mathField) return;
                let latex = '';
                if (typeof mathField.getValue === 'function') {
                    latex = mathField.getValue('latex-expanded');
                }
                if (!latex) {
                    latex = mathField.value || '';
                }

                // Bersihkan placeholder sisa jika pengguna membiarkan kotak kosong
                latex = latex.replace(/\\placeholder\{\}/g, '').replace(/#\?/g, '').trim();

                if (!latex) {
                    alert('Silakan pilih struktur rumus atau buat rumus terlebih dahulu!');
                    return;
                }

                const range = window.quillFormulaRange;
                const index = range ? range.index : quill.getLength();

                quill.insertEmbed(index, 'formula', latex, 'user');
                quill.setSelection(index + 1, 'silent');
                closeFormulaModal();

                // Langsung scroll ke posisi rumus yang baru ditambahkan
                setTimeout(() => {
                    quill.focus();
                    quill.setSelection(index + 1, 'silent');

                    let targetEl = null;
                    try {
                        const [leaf] = quill.getLeaf(index);
                        if (leaf && leaf.domNode) {
                            targetEl = leaf.domNode.nodeType === Node.ELEMENT_NODE 
                                ? leaf.domNode 
                                : leaf.domNode.parentElement;
                        }
                    } catch (e) {}

                    if (!targetEl || !targetEl.classList.contains('ql-formula')) {
                        const formulas = quill.root.querySelectorAll('.ql-formula');
                        if (formulas.length > 0) {
                            targetEl = formulas[formulas.length - 1];
                        }
                    }

                    if (targetEl && typeof targetEl.scrollIntoView === 'function') {
                        targetEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });
                        targetEl.classList.add('ring-2', 'ring-[#13416B]', 'ring-offset-2', 'rounded');
                        setTimeout(() => {
                            targetEl.classList.remove('ring-2', 'ring-[#13416B]', 'ring-offset-2', 'rounded');
                        }, 2000);
                    } else {
                        const bounds = quill.getBounds(index);
                        if (bounds) {
                            const editorRect = quill.container.getBoundingClientRect();
                            const targetY = window.pageYOffset + editorRect.top + bounds.top - (window.innerHeight / 2);
                            window.scrollTo({
                                top: Math.max(0, targetY),
                                behavior: 'smooth'
                            });
                        }
                    }
                }, 100);
            });

            quill.on('text-change', function() {
                var contentText = document.getElementById('content_text');
                if (quill.getText().trim().length === 0 && !quill.root.innerHTML.includes('<img') && !quill.root.innerHTML.includes('<iframe') && !quill.root.innerHTML.includes('<video')) { contentText.value = ''; } else { contentText.value = quill.root.innerHTML; }
            });

            // ==========================================
            // LOGIKA DOKUMEN LAMPIRAN BANYAK (EDIT)
            // ==========================================
            const docInput = document.getElementById('document_file');
            const docDropzone = document.getElementById('document-dropzone');
            const newDocsList = document.getElementById('new-documents-list');
            const btnAddMoreDocs = document.getElementById('btn-add-more-docs');
            const btnAddMoreDocsWrap = document.getElementById('btn-add-more-docs-wrap');
            const fileCountBadge = document.getElementById('file-count-badge');
            const deleteDocsContainer = document.getElementById('delete-documents-container');
            const existingDocsList = document.getElementById('existing-documents-list');

            // Queue file baru menggunakan DataTransfer
            const fileDt = new DataTransfer();

            function formatBytes(bytes, decimals = 1) {
                if (!bytes || bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            function getIconByExt(filename) {
                const ext = filename.split('.').pop().toLowerCase();
                if (['pdf'].includes(ext)) return '<i class="fas fa-file-pdf text-red-500 text-sm"></i>';
                if (['doc', 'docx'].includes(ext)) return '<i class="fas fa-file-word text-blue-600 text-sm"></i>';
                if (['xls', 'xlsx', 'csv'].includes(ext)) return '<i class="fas fa-file-excel text-emerald-600 text-sm"></i>';
                if (['ppt', 'pptx'].includes(ext)) return '<i class="fas fa-file-powerpoint text-amber-600 text-sm"></i>';
                if (['zip', 'rar', '7z'].includes(ext)) return '<i class="fas fa-file-archive text-yellow-600 text-sm"></i>';
                return '<i class="fas fa-file-alt text-[#13416B] text-sm"></i>';
            }

            function updateOverallFileBadge() {
                const existingVisible = document.querySelectorAll('.existing-doc-card:not(.hidden)').length;
                const newCount = fileDt.files.length;
                const total = existingVisible + newCount;
                if (total > 0) {
                    fileCountBadge.classList.remove('hidden');
                    fileCountBadge.textContent = `${total} Berkas`;
                } else {
                    fileCountBadge.classList.add('hidden');
                }
            }

            function renderFileList() {
                newDocsList.innerHTML = '';
                const count = fileDt.files.length;

                if (count > 0) {
                    newDocsList.classList.remove('hidden');
                    btnAddMoreDocsWrap.classList.remove('hidden');

                    for (let i = 0; i < count; i++) {
                        const file = fileDt.files[i];
                        const card = document.createElement('div');
                        card.className = 'p-3 sm:p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 flex items-center justify-between gap-3 sm:gap-4 transition shadow-2xs';
                        card.innerHTML = `
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-md bg-white border border-emerald-200 flex items-center justify-center shrink-0 shadow-2xs">
                                    ${getIconByExt(file.name)}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug break-words">${file.name}</p>
                                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5">${formatBytes(file.size)}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-new-doc w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0 cursor-pointer" data-index="${i}" title="Batalkan Berkas">
                                <i class="fas fa-times text-xs sm:text-sm"></i>
                            </button>
                        `;
                        newDocsList.appendChild(card);
                    }

                    // Event listener hapus item berkas baru
                    newDocsList.querySelectorAll('.btn-remove-new-doc').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const idx = parseInt(this.dataset.index);
                            removeFileFromQueue(idx);
                        });
                    });
                } else {
                    newDocsList.classList.add('hidden');
                    btnAddMoreDocsWrap.classList.add('hidden');
                }
                updateOverallFileBadge();
            }

            function addFilesToQueue(files) {
                for (let i = 0; i < files.length; i++) {
                    const f = files[i];
                    if (f.size > 20 * 1024 * 1024) {
                        alert(`Berkas "${f.name}" melebihi batas 20 MB!`);
                        continue;
                    }
                    fileDt.items.add(f);
                }
                docInput.files = fileDt.files;
                renderFileList();
            }

            function removeFileFromQueue(index) {
                fileDt.items.remove(index);
                docInput.files = fileDt.files;
                renderFileList();
            }

            if (docDropzone) {
                docDropzone.addEventListener('click', () => docInput.click());
                docDropzone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    docDropzone.classList.add('border-[#13416B]', 'bg-[#13416B]/5');
                });
                docDropzone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    docDropzone.classList.remove('border-[#13416B]', 'bg-[#13416B]/5');
                });
                docDropzone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    docDropzone.classList.remove('border-[#13416B]', 'bg-[#13416B]/5');
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        addFilesToQueue(e.dataTransfer.files);
                    }
                });
            }

            if (docInput) {
                docInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        addFilesToQueue(this.files);
                    }
                });
            }

            if (btnAddMoreDocs) {
                btnAddMoreDocs.addEventListener('click', () => docInput.click());
            }

            // Hapus berkas lama (existing)
            document.querySelectorAll('.btn-delete-existing-doc').forEach(btn => {
                btn.addEventListener('click', function() {
                    const docId = this.dataset.id;
                    if (confirm('Apakah Anda yakin ingin menghapus berkas lampiran ini?')) {
                        const card = this.closest('.existing-doc-card');
                        if (card) {
                            card.classList.add('hidden');
                        }
                        // Tambahkan input hidden delete_document_ids[]
                        const inputHidden = document.createElement('input');
                        inputHidden.type = 'hidden';
                        inputHidden.name = 'delete_document_ids[]';
                        inputHidden.value = docId;
                        deleteDocsContainer.appendChild(inputHidden);

                        // Cek apakah ada existing doc yang masih terlihat
                        const remaining = document.querySelectorAll('.existing-doc-card:not(.hidden)').length;
                        if (remaining === 0 && existingDocsList) {
                            existingDocsList.classList.add('hidden');
                        }
                        updateOverallFileBadge();
                    }
                });
            });

            // ==========================================
            // LOGIKA KOLEKSI PERPUSTAKAAN BANYAK (MULTIPLE)
            // ==========================================
            const libModal = document.getElementById('library-picker-modal');
            const btnOpenLibModal = document.getElementById('btn-open-library-modal');
            const btnAddMoreLibs = document.getElementById('btn-add-more-libs');
            const btnAddMoreLibsWrap = document.getElementById('btn-add-more-libs-wrap');
            const btnCloseLibModal = document.getElementById('btn-close-library-modal');
            const btnCancelLibModal = document.getElementById('btn-cancel-library-modal');
            const btnApplyLibModal = document.getElementById('btn-apply-library-modal');
            const libEmptyState = document.getElementById('library-empty-state');
            const selectedLibsList = document.getElementById('selected-libraries-list');
            const libInputsContainer = document.getElementById('library-inputs-container');
            const libraryCountBadge = document.getElementById('library-count-badge');
            const modalSelectedCount = document.getElementById('modal-selected-count');
            const libSearchInput = document.getElementById('library-search-input');
            const libCatBtns = document.querySelectorAll('.lib-cat-btn');
            const libItems = document.querySelectorAll('.lib-item');
            const libSearchEmpty = document.getElementById('library-search-empty');

            // Set ID koleksi terpilih
            let selectedLibraryIds = new Set();
            document.querySelectorAll('.lib-input-hidden').forEach(input => {
                if (input.value) selectedLibraryIds.add(input.value);
            });

            function updateModalItemsState() {
                libItems.forEach(item => {
                    const id = item.dataset.id;
                    const isSelected = selectedLibraryIds.has(id);
                    const badge = item.querySelector('.lib-action-badge');
                    const statusText = item.querySelector('.lib-status-text');

                    if (isSelected) {
                        item.classList.add('border-[#13416B]', 'bg-[#13416B]/5');
                        if (badge) {
                            badge.className = 'lib-action-badge text-[10px] px-2 py-0.5 rounded bg-[#13416B] text-white transition-all font-bold';
                            badge.textContent = 'Terpilih';
                        }
                        if (statusText) {
                            statusText.className = 'lib-status-text text-[10px] font-semibold text-[#13416B]';
                            statusText.innerHTML = '<i class="fas fa-check-circle mr-0.5"></i> Ditautkan';
                        }
                    } else {
                        item.classList.remove('border-[#13416B]', 'bg-[#13416B]/5');
                        if (badge) {
                            badge.className = 'lib-action-badge text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 transition-all font-bold';
                            badge.textContent = 'Pilih';
                        }
                        if (statusText) {
                            statusText.className = 'lib-status-text text-[10px] font-semibold text-slate-400';
                            statusText.innerHTML = '<i class="far fa-circle mr-0.5"></i> Belum Dipilih';
                        }
                    }
                });

                if (modalSelectedCount) {
                    modalSelectedCount.textContent = `${selectedLibraryIds.size} Koleksi Dipilih`;
                }
            }

            function openLibModal() {
                if (!libModal) return;
                updateModalItemsState();
                libModal.classList.remove('hidden');
                libModal.classList.add('flex');
                if (libSearchInput) {
                    libSearchInput.value = '';
                    filterLibraries();
                    setTimeout(() => libSearchInput.focus(), 100);
                }
            }

            function closeLibModal() {
                if (!libModal) return;
                libModal.classList.add('hidden');
                libModal.classList.remove('flex');
            }

            if (btnOpenLibModal) btnOpenLibModal.addEventListener('click', openLibModal);
            if (btnAddMoreLibs) btnAddMoreLibs.addEventListener('click', openLibModal);
            if (btnCloseLibModal) btnCloseLibModal.addEventListener('click', closeLibModal);
            if (btnCancelLibModal) btnCancelLibModal.addEventListener('click', closeLibModal);

            // Toggle item saat diklik di modal
            libItems.forEach(item => {
                item.addEventListener('click', function() {
                    const id = this.dataset.id;
                    if (selectedLibraryIds.has(id)) {
                        selectedLibraryIds.delete(id);
                    } else {
                        selectedLibraryIds.add(id);
                    }
                    updateModalItemsState();
                });
            });

            // Terapkan Pilihan dari modal ke form
            if (btnApplyLibModal) {
                btnApplyLibModal.addEventListener('click', function() {
                    renderSelectedLibrariesForm();
                    closeLibModal();
                });
            }

            function renderSelectedLibrariesForm() {
                selectedLibsList.innerHTML = '';
                libInputsContainer.innerHTML = '';

                const count = selectedLibraryIds.size;
                if (count > 0) {
                    libEmptyState.classList.add('hidden');
                    selectedLibsList.classList.remove('hidden');
                    if (btnAddMoreLibsWrap) btnAddMoreLibsWrap.classList.remove('hidden');
                    if (libraryCountBadge) {
                        libraryCountBadge.classList.remove('hidden');
                        libraryCountBadge.textContent = `${count} Koleksi`;
                    }

                    selectedLibraryIds.forEach(id => {
                        const itemEl = document.querySelector(`.lib-item[data-id="${id}"]`);
                        if (!itemEl) return;

                        const title = itemEl.dataset.title;
                        const category = itemEl.dataset.category;
                        const cover = itemEl.dataset.cover;
                        const desc = itemEl.dataset.desc;

                        // Tambah input hidden
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'library_ids[]';
                        hiddenInput.value = id;
                        hiddenInput.className = 'lib-input-hidden';
                        hiddenInput.dataset.id = id;
                        libInputsContainer.appendChild(hiddenInput);

                        // Card di form (THUMBNAIL LEBIH BESAR)
                        const card = document.createElement('div');
                        card.className = 'selected-lib-card border border-indigo-200 bg-indigo-50/30 rounded-xl p-3 sm:p-3.5 flex items-center justify-between gap-3 sm:gap-4 transition shadow-2xs';
                        card.dataset.id = id;

                        const thumbHtml = cover 
                            ? `<img src="${cover}" alt="${title}" class="w-full h-full object-cover">` 
                            : `<div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300"></div>`;

                        card.innerHTML = `
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-12 h-16 sm:w-14 sm:h-20 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 shadow-2xs">
                                    ${thumbHtml}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug">${title}</h4>
                                    <p class="text-[11px] sm:text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">${desc || ''}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-selected-lib w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0 cursor-pointer" data-id="${id}" title="Hapus Tautan">
                                <i class="fas fa-trash-alt text-xs sm:text-sm"></i>
                            </button>
                        `;
                        selectedLibsList.appendChild(card);
                    });

                    // Event remove pada masing-masing card di form
                    selectedLibsList.querySelectorAll('.btn-remove-selected-lib').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.dataset.id;
                            selectedLibraryIds.delete(id);
                            renderSelectedLibrariesForm();
                        });
                    });
                } else {
                    libEmptyState.classList.remove('hidden');
                    selectedLibsList.classList.add('hidden');
                    if (btnAddMoreLibsWrap) btnAddMoreLibsWrap.classList.add('hidden');
                    if (libraryCountBadge) libraryCountBadge.classList.add('hidden');
                }
            }

            // Remove listener pada card yang di-render server-side
            document.querySelectorAll('.btn-remove-selected-lib').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    selectedLibraryIds.delete(id);
                    renderSelectedLibrariesForm();
                });
            });

            // Filter pencarian dan kategori di modal
            let currentLibCatFilter = 'ALL';
            function filterLibraries() {
                const query = (libSearchInput?.value || '').toLowerCase().trim();
                let visibleCount = 0;

                libItems.forEach(item => {
                    const title = (item.dataset.title || '').toLowerCase();
                    const desc = (item.dataset.desc || '').toLowerCase();
                    const cat = (item.dataset.categoryLower || '').toLowerCase();

                    const matchCat = (currentLibCatFilter === 'ALL' || cat === currentLibCatFilter);
                    const matchQuery = (!query || title.includes(query) || desc.includes(query) || cat.includes(query));

                    if (matchCat && matchQuery) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (libSearchEmpty) {
                    if (visibleCount === 0) {
                        libSearchEmpty.classList.remove('hidden');
                    } else {
                        libSearchEmpty.classList.add('hidden');
                    }
                }
            }

            if (libSearchInput) {
                libSearchInput.addEventListener('input', filterLibraries);
            }

            libCatBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    libCatBtns.forEach(b => {
                        b.classList.remove('active', 'bg-[#13416B]', 'text-white');
                        b.classList.add('bg-slate-100', 'text-slate-600');
                    });
                    this.classList.add('active', 'bg-[#13416B]', 'text-white');
                    this.classList.remove('bg-slate-100', 'text-slate-600');
                    currentLibCatFilter = this.dataset.category;
                    filterLibraries();
                });
            });
        </script>
        
        <style>
            /* Custom Scrollbar untuk Modal Panduan */
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

            /* TOOLTIP UI - Ditampilkan di ATAS tombol agar tidak menutupi icon maupun dropdown menu */
            .quill-tooltip { position: relative; }
            .quill-tooltip:hover::after {
                content: attr(data-tooltip);
                position: absolute;
                bottom: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                background-color: #1e293b;
                color: #ffffff;
                padding: 5px 10px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
                opacity: 0;
                animation: fadeInTooltip 0.2s forwards;
                animation-delay: 0.25s;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            }
            .quill-tooltip:hover::before {
                content: '';
                position: absolute;
                bottom: calc(100% + 2px);
                left: 50%;
                transform: translateX(-50%);
                border-width: 6px 5px 0 5px;
                border-style: solid;
                border-color: #1e293b transparent transparent transparent;
                opacity: 0;
                animation: fadeInTooltip 0.2s forwards;
                animation-delay: 0.25s;
                pointer-events: none;
                z-index: 1000;
            }
            @keyframes fadeInTooltip { to { opacity: 1; } }

            /* Saat dropdown picker sedang terbuka/aktif, SEMBUNYIKAN tooltip agar tidak mengganggu pilihan user */
            .ql-picker.ql-expanded::after,
            .ql-picker.ql-expanded::before,
            .ql-picker.ql-expanded:hover::after,
            .ql-picker.ql-expanded:hover::before {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }

            /* ========================================================
               DESAIN UI TOOLBAR (Sesuai Panduan Border Kotak Rounded)
               ======================================================== */
            .ql-toolbar.ql-snow {
                border-radius: 0.75rem 0.75rem 0 0; border-color: #cbd5e1; background-color: #ffffff;
                padding: 12px 18px; display: flex; flex-wrap: wrap; gap: 8px 12px; border-bottom: 1px solid #cbd5e1;
            }
            .ql-toolbar.ql-snow .ql-formats { display: flex; align-items: center; gap: 6px; margin-right: 0 !important; }
            .ql-toolbar.ql-snow .ql-formats::after { content: ''; display: inline-block; width: 1px; height: 24px; background-color: #cbd5e1; margin-left: 8px; }
            .ql-toolbar.ql-snow .ql-formats:last-child::after { display: none; }
            .ql-toolbar.ql-snow button {
                border: 1px solid #cbd5e1 !important; border-radius: 8px !important; background-color: #ffffff !important;
                width: 34px !important; height: 34px !important; display: inline-flex !important; align-items: center !important;
                justify-content: center !important; color: #475569 !important; transition: all 0.2s ease; padding: 0 !important;
            }
            .ql-toolbar.ql-snow button svg { display: none !important; }
            .ql-toolbar.ql-snow button:hover { background-color: #f8fafc !important; border-color: #94a3b8 !important; color: #1e293b !important; }
            .ql-toolbar.ql-snow button:hover .ql-icon-custom { color: #1e293b !important; }
            .ql-toolbar.ql-snow button.ql-active { background-color: #eff6ff !important; border-color: #93c5fd !important; color: #1e3a8a !important; }
            .ql-toolbar.ql-snow button.ql-active .ql-icon-custom { color: #1e3a8a !important; }

            .ql-toolbar.ql-snow .ql-picker {
                border: 1px solid #cbd5e1 !important; border-radius: 8px !important; background-color: #ffffff !important;
                height: 34px !important; color: #475569 !important;
            }
            .ql-toolbar.ql-snow .ql-picker-label { padding: 0 8px !important; border: none !important; line-height: 32px !important; display: flex; align-items: center; justify-content: space-between; }
            .ql-toolbar.ql-snow .ql-picker-label::before { line-height: 32px !important; }
            .ql-toolbar.ql-snow .ql-picker:hover { border-color: #94a3b8 !important; }

            .ql-toolbar.ql-snow .ql-picker-options {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
                padding: 4px !important;
                background-color: #ffffff !important;
            }
            .ql-toolbar.ql-snow .ql-picker-item {
                padding: 5px 8px !important;
                font-size: 12.5px !important;
                border-radius: 4px !important;
                cursor: pointer;
            }
            .ql-toolbar.ql-snow .ql-picker-item:hover,
            .ql-toolbar.ql-snow .ql-picker-item.ql-selected {
                background-color: #f1f5f9 !important;
                color: #1e3a8a !important;
                font-weight: 600 !important;
            }
            
            .ql-toolbar.ql-snow .ql-color, .ql-toolbar.ql-snow .ql-background { width: 40px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label, .ql-toolbar.ql-snow .ql-background .ql-picker-label { padding: 0 3px 0 7px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label svg, .ql-toolbar.ql-snow .ql-background .ql-picker-label svg { width: 18px !important; height: 18px !important; display: block !important; margin-right: 2px; }

            /* DESAIN DROPDOWN RATA TEKS (ALIGNMENT) */
            .ql-toolbar.ql-snow .ql-picker.ql-align {
                width: 36px !important;
                height: 34px !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-label {
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 100% !important;
                width: 100% !important;
                border: none !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-label .ql-icon-custom {
                font-size: 15px !important;
                color: #475569 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align:hover .ql-picker-label .ql-icon-custom {
                color: #1e293b !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align.ql-expanded .ql-picker-label .ql-icon-custom {
                color: #1e3a8a !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-options {
                width: 36px !important;
                min-width: 36px !important;
                padding: 4px !important;
                display: none;
                top: calc(100% + 4px) !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align.ql-expanded .ql-picker-options {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item {
                width: 28px !important;
                height: 28px !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                margin: 2px 0 !important;
                border-radius: 4px !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item .ql-icon-custom {
                font-size: 14px !important;
                color: #475569 !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item:hover,
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item.ql-selected {
                background-color: #f1f5f9 !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item:hover .ql-icon-custom,
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item.ql-selected .ql-icon-custom {
                color: #1e3a8a !important;
            }
            .ql-toolbar.ql-snow .ql-picker.ql-align svg {
                width: 18px !important;
                height: 18px !important;
            }

            /* WRAPPER KELOMPOK DENGAN LABEL TEKS DI LUAR DROPDOWN */
            .ql-toolbar.ql-snow .ql-labeled-group {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
                background-color: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 2px 6px 2px 10px;
                height: 34px;
                vertical-align: middle;
            }
            .ql-toolbar.ql-snow .ql-custom-label {
                font-size: 12px !important;
                font-weight: 700 !important;
                color: #334155 !important;
                white-space: nowrap !important;
                user-select: none !important;
                line-height: 1 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker {
                border: none !important;
                height: 28px !important;
                background: transparent !important;
                margin: 0 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker .ql-picker-label {
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                background-color: #ffffff !important;
                padding-left: 8px !important;
                padding-right: 20px !important;
                height: 28px !important;
                line-height: 26px !important;
                font-size: 12.5px !important;
                font-weight: 600 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker:hover .ql-picker-label {
                border-color: #94a3b8 !important;
            }

            /* UKURAN DROPDOWN DI DALAM GROUP */
            .ql-picker.ql-size { width: 54px !important; }
            .ql-picker.ql-size .ql-picker-item[data-value="10px"]::before { content: '10'; font-size: 10px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12'; font-size: 12px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14'; font-size: 14px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16'; font-size: 16px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18'; font-size: 18px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before { content: '24'; font-size: 24px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="32px"]::before { content: '32'; font-size: 32px !important;}
            .ql-picker.ql-size .ql-picker-item:not([data-value])::before { content: '14'; }
            .ql-picker.ql-size .ql-picker-label[data-value="10px"]::before { content: '10'; }
            .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before { content: '12'; }
            .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before { content: '14'; }
            .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before { content: '16'; }
            .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before { content: '18'; }
            .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before { content: '24'; }
            .ql-picker.ql-size .ql-picker-label[data-value="32px"]::before { content: '32'; }
            .ql-picker.ql-size .ql-picker-label:not([data-value])::before { content: '14'; }

            /* LABEL DINAMIS: JARAK BARIS & HURUF */
            .ql-picker.ql-lineHeight { width: 62px !important; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1"]::before,
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.0"]::before { content: '1.0'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.2"]::before { content: '1.2'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.5"]::before { content: '1.5'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.0"]::before,
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2"]::before { content: '2.0'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.5"]::before { content: '2.5'; }
            .ql-picker.ql-lineHeight .ql-picker-item:not([data-value])::before { content: '1.5'; }
            
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1"]::before,
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.0"]::before { content: '1.0'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.2"]::before { content: '1.2'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.5"]::before { content: '1.5'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.0"]::before,
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2"]::before { content: '2.0'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.5"]::before { content: '2.5'; }
            .ql-picker.ql-lineHeight .ql-picker-label:not([data-value])::before { content: '1.5'; }

            .ql-picker.ql-letterSpacing { width: 52px !important; }
            .ql-picker.ql-letterSpacing .ql-picker-options { min-width: 104px !important; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="1px"]::before { content: '1'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="2px"]::before { content: '2'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="3px"]::before { content: '3'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="4px"]::before { content: '4'; }
            .ql-picker.ql-letterSpacing .ql-picker-item:not([data-value])::before,
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value=""]::before,
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="false"]::before { content: '0 (Default)'; font-weight: 600; }

            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="1px"]::before { content: '1'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="2px"]::before { content: '2'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="3px"]::before { content: '3'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="4px"]::before { content: '4'; }
            .ql-picker.ql-letterSpacing .ql-picker-label:not([data-value])::before,
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value=""]::before,
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="false"]::before { content: '0'; }

            /* STYLE EDITOR CONTAINER BAWAAN */
            .ql-container.ql-snow { border-radius: 0 0 0.75rem 0.75rem; border-color: #cbd5e1; min-height: 520px; font-family: inherit; font-size: 0.98rem; line-height: 1.7; }
            .ql-editor { min-height: 520px; padding: 24px; }
            .ql-editor pre.ql-syntax { background-color: #0f172a; color: #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; }

            /* ========================================================
               DESAIN KHUSUS BLOCKQUOTE AGAR TERLIHAT SEPERTI QUOTE
               ======================================================== */
            .ql-editor blockquote {
                border-left: 4px solid #13416B !important;
                background-color: #f8fafc !important;
                padding: 16px 20px !important;
                margin: 12px 0 !important;
                font-style: italic !important;
                color: #334155 !important;
                border-radius: 0 10px 10px 0 !important;
                position: relative;
                overflow: hidden;
            }
            .ql-editor blockquote::after {
                content: "\f10d";
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                position: absolute;
                top: -4px;
                right: 12px;
                font-size: 40px;
                color: #e2e8f0;
                opacity: 0.5;
                z-index: 0;
            }
            .ql-editor blockquote * {
                position: relative;
                z-index: 1;
            }

            /* FONT PILIHAN TOOLBAR: tampilkan sesuai pilihan di dalam editor */
            .ql-editor .ql-font-arial, .ql-editor .ql-font-arial * { font-family: Arial, sans-serif; }
            .ql-editor .ql-font-times-new-roman, .ql-editor .ql-font-times-new-roman * { font-family: 'Times New Roman', serif; }
            .ql-editor .ql-font-calibri, .ql-editor .ql-font-calibri * { font-family: Calibri, sans-serif; }
            .ql-editor .ql-font-verdana, .ql-editor .ql-font-verdana * { font-family: Verdana, sans-serif; }
            .ql-editor .ql-font-courier, .ql-editor .ql-font-courier * { font-family: 'Courier New', monospace; }
        </style>
    @endpush
</x-dashboard::layouts.dashboard>