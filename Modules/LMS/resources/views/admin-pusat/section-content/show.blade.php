<x-dashboard::layouts.dashboard title="Preview Materi: {{ $content->name }}">
    <div class="p-2 sm:p-5 max-w-full mx-auto">
        
        <!-- Breadcrumb Navigation (Maksimal 2 Teks) -->
        <nav class="flex mb-3.5 sm:mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-3 flex-wrap">
                <li>
                    <div class="flex items-center">
                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                            class="ml-1 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors md:ml-2">
                            {{ Str::limit($course->name, 40) }}
                        </a>
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
            <div class="px-5 py-4 sm:px-8 sm:py-5 border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-widest text-[#13416B] bg-[#13416B]/10 rounded-lg border border-[#13416B]/20 mb-1.5">
                        Preview Materi
                    </span>
                    <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-snug">{{ $content->name }}</h2>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Bagian: <span class="text-[#13416B]">{{ $content->section->name }}</span></p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin-pusat.management-course.course-sections-contents.edit', [$content->id, 'course_slug' => $course->slug]) }}"
                        class="flex-1 sm:flex-none text-center px-5 py-2 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Edit Materi
                    </a>
                </div>
            </div>

            <div class="p-5 sm:p-8 lg:p-10 xl:p-12 space-y-5 sm:space-y-6">

                <!-- Bagian Konten Teks & Media (Rich Text) -->
                @if (!empty($content->content_text))
                    <div class="w-full">
                        <div class="prose prose-slate prose-sm sm:prose-base max-w-none text-slate-700 quill-content-render">
                            {!! $content->content_text !!}
                        </div>
                    </div>
                @endif

                <!-- Bagian Referensi & Sumber Belajar Lainnya (Dokumen & Koleksi Perpustakaan) -->
                @php
                    $allDocs = $content->all_documents;
                    $allLibs = $content->all_libraries;
                    $hasDoc = $allDocs->isNotEmpty();
                    $hasLibrary = $allLibs->isNotEmpty();
                @endphp

                @if ($hasDoc || $hasLibrary)
                    <div class="w-full {{ !empty($content->content_text) ? 'pt-4 sm:pt-5 border-t border-slate-100' : '' }}">
                        <div class="mb-3.5">
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center gap-2 uppercase tracking-wider">
                                <i class="fas fa-bookmark text-[#13416B]"></i>
                                Referensi & Sumber Belajar Lainnya
                            </h3>
                            <p class="text-xs sm:text-[13px] text-slate-500 mt-1">
                                Dokumen materi pelengkap dan literatur referensi perpustakaan terkait topik ini.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <!-- Dokumen Lampiran (Bisa Banyak) -->
                            @if ($hasDoc)
                                @foreach ($allDocs as $doc)
                                    @php
                                        $docUrl = $doc->file_url ?? (filter_var($doc->file_path, FILTER_VALIDATE_URL) ? $doc->file_path : asset('storage/' . $doc->file_path));
                                        $docName = $doc->file_name ?? basename($doc->file_path);
                                        $docExt = strtolower(pathinfo($docName, PATHINFO_EXTENSION));
                                        $iconClass = 'fa-file-alt text-slate-500';
                                        if (in_array($docExt, ['pdf'])) {
                                            $iconClass = 'fa-file-pdf text-red-500';
                                        } elseif (in_array($docExt, ['doc', 'docx'])) {
                                            $iconClass = 'fa-file-word text-blue-600';
                                        } elseif (in_array($docExt, ['xls', 'xlsx', 'csv'])) {
                                            $iconClass = 'fa-file-excel text-emerald-600';
                                        } elseif (in_array($docExt, ['ppt', 'pptx'])) {
                                            $iconClass = 'fa-file-powerpoint text-amber-600';
                                        } elseif (in_array($docExt, ['zip', 'rar'])) {
                                            $iconClass = 'fa-file-archive text-yellow-600';
                                        }
                                    @endphp
                                    <div class="group flex items-center justify-between gap-3 sm:gap-4 py-3 sm:py-3.5 px-2 sm:px-3 rounded-xl hover:bg-slate-50/80 transition-colors">
                                        <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                                <i class="fas {{ $iconClass }} text-lg sm:text-xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs sm:text-[15px] font-bold text-slate-800 leading-snug group-hover:text-[#13416B] transition-colors line-clamp-2 sm:line-clamp-1 break-words" title="{{ $docName }}">
                                                    {{ $docName }}
                                                </h4>
                                                @if (!empty($doc->formatted_file_size))
                                                    <span class="text-[11px] sm:text-xs text-slate-400 font-medium inline-block mt-0.5">{{ $doc->formatted_file_size }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ $docUrl }}" target="_blank" download
                                            class="inline-flex shrink-0 items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-2xs hover:shadow-xs"
                                            title="Buka / Unduh Dokumen">
                                            <i class="fas fa-download text-[11px] sm:text-xs"></i>
                                            <span class="hidden sm:inline">Unduh</span>
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Koleksi Perpustakaan Terkait (Bisa Banyak & Thumbnail Saja) -->
                            @if ($hasLibrary)
                                @foreach ($allLibs as $lib)
                                    @php
                                        $libCover = $lib->cover_image_url;
                                        $libUrl = route('user.library.index', ['open' => $lib->id]);
                                    @endphp
                                    <div class="group flex items-center justify-between gap-3 sm:gap-4 py-3 sm:py-3.5 px-2 sm:px-3 rounded-xl hover:bg-slate-50/80 transition-colors">
                                        <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                                            {{-- THUMBNAIL BUKU --}}
                                            <div class="w-10 h-14 sm:w-11 sm:h-15 rounded-md bg-slate-100 overflow-hidden shrink-0 group-hover:scale-105 transition-transform relative shadow-2xs">
                                                @if ($libCover)
                                                    <img src="{{ $libCover }}" alt="{{ $lib->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-400">
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase">BUKU</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs sm:text-[15px] font-bold text-slate-800 leading-snug group-hover:text-[#13416B] transition-colors line-clamp-2 sm:line-clamp-1 break-words" title="{{ $lib->title }}">
                                                    {{ $lib->title }}
                                                </h4>
                                                @if (!empty($lib->description))
                                                    <p class="hidden sm:block text-[11px] sm:text-xs text-slate-500 line-clamp-1 leading-tight mt-0.5">
                                                        {{ strip_tags($lib->description) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ $libUrl }}" target="_blank"
                                            class="inline-flex shrink-0 items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B] hover:text-white transition-all shadow-2xs"
                                            title="Buka Koleksi di Perpustakaan">
                                            <span>Buka</span>
                                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                <!-- State Kosong -->
                @if (empty($content->content_text) && !$hasDoc && !$hasLibrary)
                    <div class="text-center py-16 px-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-white shadow-sm border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-5">
                            <i class="fas fa-box-open text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Konten Kosong</h3>
                        <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">Materi ini belum memiliki teks, video, gambar, maupun referensi berkas/pustaka. Silakan edit untuk melengkapinya.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
    <style>
        @include('lms::partials.quill-content-style')
    </style>
    @endpush

    @push('scripts')
        @include('lms::partials.custom-video-player')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.hljs) {
                    try { hljs.highlightAll(); } catch (e) {}
                }
                if (window.katex) {
                    document.querySelectorAll('.quill-content-render .ql-formula[data-value]').forEach(function (el) {
                        try { katex.render(el.getAttribute('data-value'), el, { throwOnError: false }); } catch (e) {}
                    });
                }
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
