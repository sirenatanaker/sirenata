<x-dashboard::layouts.dashboard title="{{ data_get($content, 'name', 'Detail Topik') }} | SIRENATA">
    {{-- PERBAIKAN RESPONSIVE: Hilangkan padding luar di HP (p-0) agar mepet pojok, munculkan di layar sm ke atas --}}
    <div class="p-0 sm:p-4 md:p-6 max-w-full mx-auto min-h-screen"
        x-data="{
            reachedEnd: {{ data_get($content, 'is_completed') ? 'true' : 'false' }},
            submitting: false,
            init() {
                if (this.reachedEnd) return;
                const sentinel = this.$refs.endSentinel;
                if (!sentinel || !('IntersectionObserver' in window)) { this.reachedEnd = true; return; }
                const io = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        this.reachedEnd = true;
                        io.disconnect();
                    }
                }, { threshold: 0 });
                io.observe(sentinel);
            },
            scrollToEnd() {
                this.$refs.endSentinel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }">
        
        {{-- Main Content Container --}}
        {{-- Di HP tidak ada border-radius, di Desktop membulat (sm:rounded-2xl) --}}
        <div class="bg-white sm:rounded-md sm:shadow-sm border-b sm:border border-slate-200 overflow-hidden min-h-screen sm:min-h-0">

            {{-- Body Content --}}
            <div class="p-5 sm:p-10 lg:p-12">

                {{-- PERBAIKAN TAUTAN KEMBALI: Tahan ikon panah agar tidak menyusut, dan rapikan teks yang panjang --}}
                <div class="mb-8">
                    <a href="{{ route('user.course.my-course.detail', $slug) }}"
                        class="group flex items-center gap-3 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                        
                        {{-- Lingkaran Ikon Panah --}}
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-[#13416B]/10 transition-colors">
                            <i class="fas fa-arrow-left text-xs text-slate-600 group-hover:text-[#13416B]"></i>
                        </div>
                        
                        {{-- Teks --}}
                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center sm:gap-1.5 leading-tight">
                            <span class="text-[11px] sm:text-sm text-slate-400 mb-0.5 sm:mb-0">Kembali ke</span>
                            <span class="font-bold text-slate-700 truncate group-hover:text-[#13416B] transition-colors">
                                {{ data_get($course, 'course_name', 'Kursus') }}
                            </span>
                        </div>
                    </a>
                </div>

                {{-- Header & Title Materi --}}
                <div class="border-b border-slate-100 pb-6 sm:pb-8 mb-6 sm:mb-8">
                    <div class="flex items-center gap-2 mb-3 sm:mb-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/10 rounded-lg border border-[#13416B]/20">
                            Topik Pembelajaran
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight sm:leading-tight">
                        {{ data_get($content, 'name') }}
                    </h1>
                </div>

                {{-- 1. Text & Media Content --}}
                @php
                    $contentText = is_object($content)
                        ? $content->content_text
                        : (is_array($content)
                            ? $content['content_text'] ?? null
                            : null);
                @endphp

                @if (!empty($contentText))
                    <div class="prose prose-slate prose-sm sm:prose-base lg:prose-lg max-w-none mb-10 text-slate-700 quill-content-render">
                        {!! $contentText !!}
                    </div>
                @endif

                {{-- 2. Document Attachment --}}
                @php
                    $docUrl = data_get($content, 'document_url') ?? data_get($content, 'document');
                @endphp

                @if ($docUrl)
                    <div class="{{ !empty($contentText) ? 'pt-8 border-t border-slate-100' : '' }}">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-4 flex items-center gap-2 uppercase tracking-wider">
                            <i class="fas fa-paperclip text-slate-400"></i> Lampiran Pendukung
                        </h3>
                        <a href="{{ $docUrl }}" target="_blank"
                            class="group flex flex-col sm:flex-row sm:items-center p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-[#13416B]/5 hover:border-[#13416B]/30 transition-all duration-300 gap-4">
                            
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 group-hover:border-[#13416B]/40 group-hover:shadow-sm transition-all">
                                    <i class="fas fa-file-pdf text-xl sm:text-2xl text-red-500"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-[#13416B] transition-colors leading-tight">
                                        Unduh Dokumen Materi
                                    </p>
                                    <p class="text-[11px] sm:text-xs text-slate-500 mt-1">Format PDF/DOCX tersedia untuk dipelajari luring.</p>
                                </div>
                            </div>
                            
                            <div class="w-full sm:w-10 h-10 rounded-xl sm:rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs sm:text-sm group-hover:text-white group-hover:bg-[#13416B] group-hover:border-[#13416B] transition-all gap-2">
                                <i class="fas fa-download"></i> <span class="sm:hidden">Unduh Sekarang</span>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- Empty State --}}
                @if (empty($contentText) && !$docUrl)
                    <div class="text-center py-12 sm:py-16 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow-sm">
                            <i class="fas fa-tools text-2xl sm:text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">Materi Sedang Disiapkan</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-sm mx-auto">Tutor belum mengunggah konten bacaan, video, maupun dokumen lampiran untuk modul ini.</p>
                    </div>
                @endif

                {{-- Penanda akhir konten: saat terlihat di layar, tombol "Tandai Selesai" dimunculkan --}}
                <div x-ref="endSentinel" class="h-px w-full" aria-hidden="true"></div>
            </div>

            {{-- Action Footer --}}
            <div class="px-5 py-5 sm:px-10 sm:py-6 border-t border-slate-100 bg-slate-50 flex flex-col-reverse sm:flex-row justify-between items-center gap-3 sm:gap-4">
                <a href="{{ route('user.course.my-course.detail', $slug) }}"
                    class="w-full sm:w-auto text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors text-center py-2.5 sm:py-2 border border-slate-200 sm:border-transparent rounded-xl sm:rounded-none bg-white sm:bg-transparent">
                    <i class="fas fa-list mr-1.5"></i> Daftar Modul
                </a>

                @if (!data_get($content, 'is_completed'))
                    {{-- Petunjuk: tampil sebelum user sampai di akhir konten --}}
                    <button type="button" x-show="!reachedEnd" @click="scrollToEnd()"
                        class="w-full sm:w-auto px-6 py-3 sm:py-3.5 text-sm font-bold text-slate-500 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center gap-2 hover:bg-slate-200 transition-colors">
                        <i class="fas fa-arrow-down text-xs animate-bounce"></i> Baca sampai akhir untuk menandai selesai
                    </button>

                    {{-- Tombol tandai selesai: muncul setelah konten dibaca sampai bawah --}}
                    <form action="{{ route('user.course.content.complete', ['content' => data_get($content, 'id')]) }}"
                        method="POST" class="w-full sm:w-auto m-0" style="display: none;"
                        x-show="reachedEnd"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        @submit="submitting = true">
                        @csrf
                        <button type="submit" :disabled="submitting"
                            class="w-full sm:w-auto px-6 py-3 sm:py-3.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <i class="fas fa-check-circle text-base sm:text-lg" x-show="!submitting"></i>
                            <i class="fas fa-spinner fa-spin text-base sm:text-lg" x-show="submitting" style="display: none;"></i>
                            <span x-text="submitting ? 'Menyimpan...' : 'Tandai Selesai & Lanjut'"></span>
                        </button>
                    </form>
                @else
                    <span class="completed-pop w-full sm:w-auto px-6 py-3 sm:py-3.5 text-sm font-bold text-white bg-emerald-600 border border-emerald-600 rounded-xl text-center flex items-center justify-center gap-2 shadow-sm cursor-default select-none">
                        <i class="fas fa-check-double text-base sm:text-lg"></i> Selesai Dipelajari
                    </span>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @keyframes completedPop {
            0%   { transform: scale(0.94); opacity: 0.6; }
            60%  { transform: scale(1.03); opacity: 1; }
            100% { transform: scale(1); }
        }
        .completed-pop { animation: completedPop 0.45s ease-out; }

        .quill-content-render .ql-align-center { text-align: center !important; }
        .quill-content-render .ql-align-right { text-align: right !important; }
        .quill-content-render .ql-align-justify { text-align: justify !important; }

        .quill-content-render img,
        .quill-content-render iframe.ql-video {
            display: inline-block !important; 
            max-width: 100%;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem; 
        }

        .quill-content-render img[style*="margin: auto"],
        .quill-content-render img[style*="display: block"],
        .quill-content-render iframe[style*="margin: auto"],
        .quill-content-render iframe[style*="display: block"] {
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .quill-content-render img[style*="float: left"],
        .quill-content-render iframe[style*="float: left"] {
            float: left !important;
            margin-right: 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .quill-content-render img[style*="float: right"],
        .quill-content-render iframe[style*="float: right"] {
            float: right !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
        }

        .quill-content-render .ql-indent-1 { padding-left: 3em !important; }
        .quill-content-render .ql-indent-2 { padding-left: 6em !important; }
        .quill-content-render .ql-indent-3 { padding-left: 9em !important; }
        .quill-content-render .ql-indent-4 { padding-left: 12em !important; }

        .quill-content-render pre.ql-syntax {
            background-color: #0f172a !important; 
            color: #e2e8f0 !important;
            padding: 1rem sm:1.25rem !important;
            border-radius: 0.5rem sm:0.75rem !important;
            overflow-x: auto !important;
            text-align: left !important;
            font-size: 0.75rem sm:0.875rem !important;
            line-height: 1.5 !important;
        }
    </style>
    @endpush
</x-dashboard::layouts.dashboard>