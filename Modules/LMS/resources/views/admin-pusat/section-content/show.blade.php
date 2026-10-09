<x-dashboard::layouts.dashboard title="Preview Materi: {{ $content->name }}">
    <div class="p-2 sm:p-6 max-w-full mx-auto">
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-3 flex-wrap">
                <li>
                    <div class="flex items-center">
                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                            class="ml-1 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors md:ml-2">
                            <i class="fas fa-home mr-2"></i> {{ $course->name }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-300 text-xs mx-1"></i>
                        <span class="ml-1 text-sm font-bold text-slate-500 md:ml-2">{{ $content->section->name }}</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-300 text-xs mx-1"></i>
                        <span class="ml-1 text-sm font-bold text-slate-800 md:ml-2">{{ $content->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Header Materi -->
            <div
                class="px-6 py-6 sm:px-10 border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span
                        class="inline-block px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-[#13416B] bg-[#13416B]/10 rounded-lg border border-[#13416B]/20 mb-3">
                        Preview Materi
                    </span>
                    <h2 class="text-2xl font-extrabold text-slate-800">{{ $content->name }}</h2>
                    <p class="text-xs font-medium text-slate-500 mt-1">Bagian: <span
                            class="text-[#13416B]">{{ $content->section->name }}</span></p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin-pusat.management-course.course-sections-contents.edit', [$content->id, 'course_slug' => $course->slug]) }}"
                        class="flex-1 sm:flex-none text-center px-6 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Edit Materi
                    </a>
                    <!-- Tombol kembali dihapus sesuai permintaan -->
                </div>
            </div>

            <div class="p-6 sm:p-10 space-y-8">

                <!-- Bagian Konten Teks & Media (Rich Text) -->
                @if (!empty($content->content_text))
                    <div class="w-full">
                        <div
                            class="prose prose-slate prose-sm sm:prose-base max-w-none text-slate-700 quill-content-render">
                            {!! $content->content_text !!}
                        </div>
                    </div>
                @endif

                <!-- Bagian Dokumen -->
                @if ($content->document_url)
                    <div class="w-full {{ !empty($content->content_text) ? 'pt-8 border-t border-slate-100' : '' }}">
                        <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-paperclip text-[#13416B]"></i> Lampiran Unduhan
                        </h3>
                        <div
                            class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center shrink-0">
                                    <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Dokumen Pendukung Materi</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Berisi berkas berformat PDF atau DOCX</p>
                                </div>
                            </div>
                            <a href="{{ $content->document_url }}" target="_blank"
                                class="w-full sm:w-auto text-center inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 hover:text-[#13416B] transition-colors shadow-sm">
                                <i class="fas fa-external-link-alt"></i> Buka Dokumen
                            </a>
                        </div>
                    </div>
                @endif

                <!-- State Kosong -->
                @if (empty($content->content_text) && !$content->document_url)
                    <div class="text-center py-16 px-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                        <div
                            class="w-20 h-20 bg-white shadow-sm border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-5">
                            <i class="fas fa-box-open text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Konten Kosong</h3>
                        <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">Materi ini belum memiliki teks, video,
                            gambar, maupun dokumen lampiran. Silakan edit untuk melengkapinya.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* CSS Quill Render agar Gambar & Video bisa Resize dan Rata Sesuai Pengaturan Admin */

            .quill-content-render img,
            .quill-content-render video,
            .quill-content-render iframe {
                display: inline-block;
                max-width: 100% !important;
                height: auto;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .quill-content-render img[style*="margin: auto"],
            .quill-content-render img[style*="display: block"],
            .quill-content-render video[style*="margin: auto"],
            .quill-content-render video[style*="display: block"],
            .quill-content-render iframe[style*="margin: auto"] {
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .quill-content-render img[style*="float: left"],
            .quill-content-render video[style*="float: left"],
            .quill-content-render iframe[style*="float: left"] {
                float: left !important;
                margin-right: 1.5rem !important;
                margin-bottom: 1rem !important;
                margin-top: 0.5rem !important;
            }

            .quill-content-render img[style*="float: right"],
            .quill-content-render video[style*="float: right"],
            .quill-content-render iframe[style*="float: right"] {
                float: right !important;
                margin-left: 1.5rem !important;
                margin-bottom: 1rem !important;
                margin-top: 0.5rem !important;
            }

            .quill-content-render .ql-align-center {
                text-align: center !important;
            }

            .quill-content-render .ql-align-right {
                text-align: right !important;
            }

            .quill-content-render .ql-align-justify {
                text-align: justify !important;
            }

            .quill-content-render .ql-indent-1 {
                padding-left: 3em !important;
            }

            .quill-content-render .ql-indent-2 {
                padding-left: 6em !important;
            }

            .quill-content-render .ql-indent-3 {
                padding-left: 9em !important;
            }

            .quill-content-render .ql-indent-4 {
                padding-left: 12em !important;
            }

            .quill-content-render pre.ql-syntax {
                background-color: #0f172a !important;
                color: #f8fafc !important;
                padding: 1.25rem !important;
                border-radius: 0.75rem !important;
                overflow-x: auto !important;
                font-size: 0.875rem !important;
                line-height: 1.6 !important;
                border: 1px solid #1e293b;
            }
        </style>
    @endpush
</x-dashboard::layouts.dashboard>
