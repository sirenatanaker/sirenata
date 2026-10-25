<x-dashboard::layouts.dashboard title="Preview Materi: {{ $content->name }}">
    <div class="p-2 sm:p-6 max-w-full mx-auto">
        
        <!-- Breadcrumb Navigation (Maksimal 2 Teks) -->
        <nav class="flex mb-6 sm:mb-8" aria-label="Breadcrumb">
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
            <div class="px-6 py-6 sm:px-10 border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-block px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-[#13416B] bg-[#13416B]/10 rounded-lg border border-[#13416B]/20 mb-3">
                        Preview Materi
                    </span>
                    <h2 class="text-2xl font-extrabold text-slate-800">{{ $content->name }}</h2>
                    <p class="text-xs font-medium text-slate-500 mt-1">Bagian: <span class="text-[#13416B]">{{ $content->section->name }}</span></p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin-pusat.management-course.course-sections-contents.edit', [$content->id, 'course_slug' => $course->slug]) }}"
                        class="flex-1 sm:flex-none text-center px-6 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Edit Materi
                    </a>
                </div>
            </div>

            <div class="p-6 sm:p-10 space-y-8">

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
                    <div class="w-full {{ !empty($content->content_text) ? 'pt-8 border-t border-slate-100' : '' }}">
                        <div class="mb-4">
                            <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 uppercase tracking-wider">
                                <i class="fas fa-bookmark text-[#13416B]"></i>
                                Referensi & Sumber Belajar Lainnya
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Dokumen materi pelengkap dan literatur referensi perpustakaan terkait topik ini.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <!-- Dokumen Lampiran (Bisa Banyak) -->
                            @if ($hasDoc)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach ($allDocs as $doc)
                                        @php
                                            $docUrl = $doc->file_url ?? (filter_var($doc->file_path, FILTER_VALIDATE_URL) ? $doc->file_path : asset('storage/' . $doc->file_path));
                                            $docName = $doc->file_name ?? basename($doc->file_path);
                                            $docExt = strtolower(pathinfo($docName, PATHINFO_EXTENSION));
                                            $iconClass = 'fa-file-alt text-slate-500';
                                            $badgeText = strtoupper($docExt ?: 'FILE');
                                            $badgeBg = 'bg-slate-100 text-slate-700';
                                            if (in_array($docExt, ['pdf'])) {
                                                $iconClass = 'fa-file-pdf text-red-500';
                                                $badgeBg = 'bg-red-50 text-red-600 border border-red-200';
                                            } elseif (in_array($docExt, ['doc', 'docx'])) {
                                                $iconClass = 'fa-file-word text-blue-600';
                                                $badgeBg = 'bg-blue-50 text-blue-600 border border-blue-200';
                                            } elseif (in_array($docExt, ['xls', 'xlsx', 'csv'])) {
                                                $iconClass = 'fa-file-excel text-emerald-600';
                                                $badgeBg = 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                                            } elseif (in_array($docExt, ['ppt', 'pptx'])) {
                                                $iconClass = 'fa-file-powerpoint text-amber-600';
                                                $badgeBg = 'bg-amber-50 text-amber-600 border border-amber-200';
                                            } elseif (in_array($docExt, ['zip', 'rar'])) {
                                                $iconClass = 'fa-file-archive text-yellow-600';
                                                $badgeBg = 'bg-yellow-50 text-yellow-600 border border-yellow-200';
                                            }
                                        @endphp
                                        <div class="group flex flex-col justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-white hover:border-[#13416B]/30 hover:shadow-sm transition-all duration-300">
                                            <div class="flex items-start gap-3">
                                                <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-xs">
                                                    <i class="fas {{ $iconClass }} text-xl"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 leading-snug group-hover:text-[#13416B] transition-colors line-clamp-2 break-words" title="{{ $docName }}">
                                                        {{ $docName }}
                                                    </h4>
                                                    @if (!empty($doc->formatted_file_size))
                                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $doc->formatted_file_size }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-end">
                                                <a href="{{ $docUrl }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-xs">
                                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka Dokumen
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Koleksi Perpustakaan Terkait (Bisa Banyak & Thumbnail Saja) -->
                            @if ($hasLibrary)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach ($allLibs as $lib)
                                        @php
                                            $libCategory = $lib->libraryCategory?->name ?? 'Pustaka';
                                            $libCover = $lib->cover_image_url;
                                            $libUrl = route('user.library.index', ['open' => $lib->id]);
                                        @endphp
                                        <div class="group flex flex-col justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-white hover:border-[#13416B]/30 hover:shadow-sm transition-all duration-300">
                                            <div class="flex items-start gap-3.5">
                                                {{-- THUMBNAIL SAJA (TANPA ICON) --}}
                                                <div class="w-12 h-16 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 group-hover:scale-105 transition-transform relative shadow-xs">
                                                    @if ($libCover)
                                                        <img src="{{ $libCover }}" alt="{{ $lib->title }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300"></div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 leading-snug group-hover:text-[#13416B] transition-colors line-clamp-2">
                                                        {{ $lib->title }}
                                                    </h4>
                                                    @if (!empty($lib->description))
                                                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                                            {{ strip_tags($lib->description) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-end">
                                                <a href="{{ $libUrl }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B] hover:text-white transition-all shadow-xs">
                                                    <span>Buka di Perpustakaan</span>
                                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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
