<x-dashboard::layouts.dashboard title="Edit Post Test & Soal">
    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <style>
            /* MathLive Web Component */
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

            /* Custom Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

            /* TOOLTIP UI - Ditampilkan di ATAS tombol */
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

            /* Saat dropdown picker sedang terbuka/aktif, SEMBUNYIKAN tooltip */
            .ql-picker.ql-expanded::after,
            .ql-picker.ql-expanded::before,
            .ql-picker.ql-expanded:hover::after,
            .ql-picker.ql-expanded:hover::before {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }

            /* TOOLBAR DESAIN */
            .ql-toolbar.ql-snow {
                border-radius: 0.75rem 0.75rem 0 0;
                border-color: #cbd5e1;
                background-color: #ffffff;
                padding: 10px 14px;
                display: flex;
                flex-wrap: wrap;
                gap: 6px 10px;
                border-bottom: 1px solid #cbd5e1;
            }
            .ql-toolbar.ql-snow .ql-formats { display: flex; align-items: center; gap: 5px; margin-right: 0 !important; }
            .ql-toolbar.ql-snow .ql-formats::after { content: ''; display: inline-block; width: 1px; height: 22px; background-color: #cbd5e1; margin-left: 6px; }
            .ql-toolbar.ql-snow .ql-formats:last-child::after { display: none; }
            .ql-toolbar.ql-snow button {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                background-color: #ffffff !important;
                width: 32px !important;
                height: 32px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #475569 !important;
                transition: all 0.2s ease;
                padding: 0 !important;
            }
            .ql-toolbar.ql-snow button svg { display: none !important; }
            .ql-toolbar.ql-snow button:hover { background-color: #f8fafc !important; border-color: #94a3b8 !important; color: #1e293b !important; }
            .ql-toolbar.ql-snow button:hover .ql-icon-custom { color: #1e293b !important; }
            .ql-toolbar.ql-snow button.ql-active { background-color: #eff6ff !important; border-color: #93c5fd !important; color: #1e3a8a !important; }
            .ql-toolbar.ql-snow button.ql-active .ql-icon-custom { color: #1e3a8a !important; }

            .ql-toolbar.ql-snow .ql-picker {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                background-color: #ffffff !important;
                height: 32px !important;
                color: #475569 !important;
            }
            .ql-toolbar.ql-snow .ql-picker-label { padding: 0 8px !important; border: none !important; line-height: 30px !important; display: flex; align-items: center; justify-content: space-between; }
            .ql-toolbar.ql-snow .ql-picker-label::before { line-height: 30px !important; }
            .ql-toolbar.ql-snow .ql-picker:hover { border-color: #94a3b8 !important; }

            .ql-toolbar.ql-snow .ql-picker-options {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
                padding: 4px !important;
                background-color: #ffffff !important;
                z-index: 50 !important;
            }
            .ql-toolbar.ql-snow .ql-picker-item {
                padding: 5px 8px !important;
                font-size: 12px !important;
                border-radius: 4px !important;
                cursor: pointer;
            }
            .ql-toolbar.ql-snow .ql-picker-item:hover,
            .ql-toolbar.ql-snow .ql-picker-item.ql-selected {
                background-color: #f1f5f9 !important;
                color: #1e3a8a !important;
                font-weight: 600 !important;
            }

            .ql-toolbar.ql-snow .ql-color, .ql-toolbar.ql-snow .ql-background { width: 38px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label, .ql-toolbar.ql-snow .ql-background .ql-picker-label { padding: 0 3px 0 6px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label svg, .ql-toolbar.ql-snow .ql-background .ql-picker-label svg { width: 17px !important; height: 17px !important; display: block !important; margin-right: 2px; }

            /* Alignment Dropdown */
            .ql-toolbar.ql-snow .ql-picker.ql-align { width: 34px !important; height: 32px !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-label { padding: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; height: 100% !important; width: 100% !important; border: none !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-label .ql-icon-custom { font-size: 14px !important; color: #475569 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align:hover .ql-picker-label .ql-icon-custom { color: #1e293b !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align.ql-expanded .ql-picker-label .ql-icon-custom { color: #1e3a8a !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-options { width: 34px !important; min-width: 34px !important; padding: 4px !important; display: none; top: calc(100% + 4px) !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align.ql-expanded .ql-picker-options { display: flex !important; flex-direction: column !important; align-items: center !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item { width: 26px !important; height: 26px !important; padding: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; margin: 2px 0 !important; border-radius: 4px !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item .ql-icon-custom { font-size: 13px !important; color: #475569 !important; }
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item:hover,
            .ql-toolbar.ql-snow .ql-picker.ql-align .ql-picker-item.ql-selected { background-color: #f1f5f9 !important; }

            /* Labeled Dropdown Pickers */
            .ql-toolbar.ql-snow .ql-labeled-group {
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                background-color: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 2px 5px 2px 8px;
                height: 32px;
                vertical-align: middle;
            }
            .ql-toolbar.ql-snow .ql-custom-label {
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #334155 !important;
                white-space: nowrap !important;
                user-select: none !important;
                line-height: 1 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker {
                border: none !important;
                height: 26px !important;
                background: transparent !important;
                margin: 0 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker .ql-picker-label {
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                background-color: #ffffff !important;
                padding-left: 6px !important;
                padding-right: 18px !important;
                height: 26px !important;
                line-height: 24px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
            }
            .ql-toolbar.ql-snow .ql-labeled-group .ql-picker:hover .ql-picker-label { border-color: #94a3b8 !important; }

            .ql-picker.ql-size { width: 52px !important; }
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

            .ql-picker.ql-lineHeight { width: 58px !important; }
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

            .ql-picker.ql-letterSpacing { width: 50px !important; }
            .ql-picker.ql-letterSpacing .ql-picker-options { min-width: 95px !important; }
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

            /* EDITOR CONTAINERS & EDITORS */
            .quill-question .ql-container.ql-snow {
                border-radius: 0 0 0.75rem 0.75rem;
                border-color: #cbd5e1;
                min-height: 140px;
                font-family: inherit;
                font-size: 0.95rem;
                line-height: 1.6;
            }
            .quill-question .ql-editor {
                min-height: 140px;
                padding: 16px 20px;
            }
            .quill-choice .ql-container.ql-snow {
                border-radius: 0 0 0.75rem 0.75rem;
                border-color: #cbd5e1;
                min-height: 60px;
                font-family: inherit;
                font-size: 0.95rem;
                line-height: 1.5;
            }
            .quill-choice .ql-editor {
                min-height: 60px;
                padding: 10px 14px;
            }

            .ql-editor pre.ql-syntax {
                background-color: #0f172a;
                color: #e2e8f0;
                padding: 1rem;
                border-radius: 0.5rem;
            }

            /* Blockquote */
            .ql-editor blockquote {
                border-left: 4px solid #13416B !important;
                background-color: #f8fafc !important;
                padding: 14px 18px !important;
                margin: 10px 0 !important;
                font-style: italic !important;
                color: #334155 !important;
                border-radius: 0 10px 10px 0 !important;
                position: relative;
                overflow: hidden;
            }
            .ql-editor blockquote::after {
                content: "\f10d";
                font-family: "Font Awesome 6 Free", "Font Awesome 5 Free";
                font-weight: 900;
                position: absolute;
                top: -4px;
                right: 12px;
                font-size: 36px;
                color: #e2e8f0;
                opacity: 0.5;
                z-index: 0;
            }
            .ql-editor blockquote * { position: relative; z-index: 1; }

            /* Media & Keterangan / Caption */
            .ql-editor img, .ql-editor video, .ql-editor iframe {
                max-width: 100% !important;
                border-radius: 0.5rem;
            }
            .ql-editor .ql-media-caption,
            .ql-editor p.ql-align-center:has(em) {
                margin-top: 0.25rem !important;
                margin-bottom: 0.75rem !important;
                font-size: 0.85rem !important;
                color: #64748b !important;
                font-style: italic !important;
                text-align: center !important;
                line-height: 1.4 !important;
            }

            [x-cloak] { display: none !important; }
        </style>
    @endpush

    <div class="p-2 sm:p-6" x-data="postTestEditForm()">

        <!-- Breadcrumb Navigation -->
        <div class="mb-6 sm:mb-8 mt-2 sm:mt-4">
            <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                class="group inline-flex items-center gap-3 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors">
                <div
                    class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-[#13416B]/10 transition-colors border border-slate-200">
                    <i class="fas fa-arrow-left text-xs text-slate-600 group-hover:text-[#13416B]"></i>
                </div>
                <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center sm:gap-1.5 leading-tight">
                    <span class="text-[11px] sm:text-sm text-slate-400 mb-0.5 sm:mb-0">Kembali ke</span>
                    <span
                        class="font-bold text-slate-700 truncate group-hover:text-[#13416B] transition-colors max-w-[200px] sm:max-w-md">
                        {{ $course->name }}
                    </span>
                </div>
            </a>
        </div>

        <form action="{{ route('admin-pusat.management-course.post-tests.update', $postTest->id) }}" method="POST"
            class="max-w-full mx-auto">
            @csrf
            @method('PUT')
            <!-- Hidden Inputs -->
            <input type="hidden" name="course_slug" value="{{ $course->slug }}" />

            <!-- Layout Grid: Kiri (Form) Kanan (Navigasi Sticky) -->
            <div class="flex flex-col lg:flex-row gap-6 items-start">

                <!-- KOLOM KIRI: FORM UTAMA -->
                <div class="flex-1 w-full space-y-6">
                    <!-- CARD 1: INFORMASI UTAMA POST TEST -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden scroll-mt-24"
                        id="section-pengaturan">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h2 class="text-base font-bold text-slate-800">1. Pengaturan Umum Post Test</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Judul Post Test <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" required value="{{ old('title', $postTest->title) }}"
                                    placeholder="Contoh: Ujian Evaluasi Pemahaman Bab 1"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Passing Score (KKM)
                                        <span class="text-red-500">*</span></label>
                                    <input type="number" name="passing_score" required min="0" max="100"
                                        value="{{ old('passing_score', $postTest->passing_score) }}"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Durasi (Menit) <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" name="duration" required min="1"
                                        value="{{ old('duration', $postTest->duration) }}"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Instruksi Pengerjaan
                                    (Opsional)</label>
                                <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">{{ old('description', $postTest->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: DAFTAR SOAL PILIHAN GANDA DENGAN FULL TEXT EDITOR -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-bold text-slate-800">2. Daftar Pertanyaan Pilihan Ganda</h2>
                        </div>

                        <template x-for="(q, qIndex) in questions" :key="qIndex">
                            <div :id="'question-' + qIndex"
                                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative group scroll-mt-24 transition-all duration-300">
                                <div
                                    class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider"
                                        x-text="'Pertanyaan Nomor ' + (qIndex + 1)"></span>
                                    <button type="button" @click="removeQuestion(qIndex)" x-show="questions.length > 1"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1">
                                        <i class="fas fa-trash-alt"></i> Hapus Soal
                                    </button>
                                </div>

                                <div class="p-6 space-y-5">
                                    <!-- TEXT EDITOR: Soal -->
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Tulis Pertanyaan
                                            <span class="text-red-500">*</span></label>
                                        <div x-data="{
                                            initEditor() {
                                                createQuestionEditor(this.$refs.editor_q, (html) => {
                                                    q.question = html;
                                                }, q.question);
                                            }
                                        }" x-init="initEditor()">
                                            <div x-ref="editor_q" class="bg-white quill-question"></div>
                                            <input type="hidden" :name="'questions[' + qIndex + '][question]'"
                                                :value="q.question">
                                        </div>
                                    </div>

                                    <!-- TEXT EDITOR: Pilihan Ganda -->
                                    <div class="space-y-3">
                                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Pilihan Jawaban
                                            (Tandai lingkaran untuk jawaban yang benar) <span
                                                class="text-red-500">*</span></label>

                                        <template x-for="(choice, cIndex) in q.choices" :key="cIndex">
                                            <div
                                                class="flex items-start gap-3 bg-slate-50/50 p-3 rounded-xl border border-slate-200">
                                                <input type="radio"
                                                    :name="'questions[' + qIndex + '][correct_choice]'"
                                                    :value="cIndex" x-model="q.correct_choice" required
                                                    class="w-4 h-4 text-[#13416B] focus:ring-[#13416B] border-slate-300 mt-3.5 shrink-0"
                                                    title="Tandai sebagai jawaban benar">

                                                <div class="w-full flex-1">
                                                    <div x-data="{
                                                        initChoiceEditor() {
                                                            createChoiceEditor(this.$refs.editor_c, (html) => {
                                                                q.choices[cIndex] = html;
                                                            }, q.choices[cIndex], 'Pilihan ' + String.fromCharCode(65 + cIndex));
                                                        }
                                                    }" x-init="initChoiceEditor()">
                                                        <div x-ref="editor_c" class="bg-white quill-choice"></div>
                                                        <input type="hidden"
                                                            :name="'questions[' + qIndex + '][choices][' + cIndex + ']'"
                                                            :value="q.choices[cIndex]">
                                                    </div>
                                                </div>

                                                <button type="button" @click="q.choices.splice(cIndex, 1)"
                                                    x-show="q.choices.length > 2"
                                                    class="text-slate-400 hover:text-red-500 p-2 mt-2 shrink-0">
                                                    <i class="fas fa-times text-lg"></i>
                                                </button>
                                            </div>
                                        </template>

                                        <button type="button" @click="q.choices.push('')"
                                            class="mt-3 text-xs font-medium text-[#13416B] hover:text-[#0f3354] flex items-center gap-1.5 px-2">
                                            <i class="fas fa-plus-circle"></i> Tambah Pilihan Opsi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="pt-2">
                            <button type="button"
                                @click="addQuestion(); setTimeout(() => document.getElementById('question-' + (questions.length - 1)).scrollIntoView({ behavior: 'smooth', block: 'start' }), 150)"
                                class="w-full py-3 border-2 border-dashed border-[#13416B]/30 text-[#13416B] bg-[#13416B]/5 hover:bg-[#13416B]/10 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                                <i class="fas fa-plus-circle text-lg"></i> Tambah Pertanyaan Baru
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit Akhir -->
                    <div class="flex justify-end gap-3 pt-4 pb-12">
                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                            class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition shadow-sm">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-amber-600 rounded-xl hover:bg-amber-700 transition shadow-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> Perbarui Post Test & Soal
                        </button>
                    </div>
                </div>

                <!-- KOLOM KANAN: WIDGET NAVIGASI CEPAT (DESKTOP) -->
                <div class="hidden lg:block lg:w-80 xl:w-[340px] shrink-0 lg:sticky lg:top-24 mb-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div
                                class="w-9 h-9 flex items-center justify-center bg-[#13416B] text-white rounded-xl shadow-sm border border-[#0f3354] shrink-0">
                                <i class="fas fa-th-large text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Navigasi Soal</h3>
                                <p class="text-[11px] text-slate-400 font-medium" x-text="questions.length + ' Total Pertanyaan'"></p>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 mb-3.5">Klik nomor di bawah untuk langsung menuju ke soal terkait.</p>

                        <!-- Papan Grid Angka Soal (Rounded Full) -->
                        <div class="grid grid-cols-5 gap-2.5 max-h-[60vh] overflow-y-auto p-1 custom-scrollbar">
                            <template x-for="(q, index) in questions" :key="index">
                                <button type="button"
                                    @click="document.getElementById('question-' + index).scrollIntoView({ behavior: 'smooth', block: 'start' })"
                                    class="aspect-square w-full rounded-full text-xs font-bold transition-all flex items-center justify-center border border-slate-200 bg-slate-50 text-slate-700 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:shadow-md cursor-pointer">
                                    <span x-text="index + 1"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <!-- KUMPULAN TOMBOL MENGAMBANG DI MOBILE/TABLET (BOTTOM RIGHT) -->
        <div class="fixed bottom-6 right-6 z-40 flex flex-col gap-3 lg:hidden">
            <!-- TOMBOL BUKA BOTTOM SHEET NAVIGASI -->
            <button type="button" @click="showMobileNav = true"
                class="w-12 h-12 shrink-0 aspect-square rounded-full bg-[#13416B] text-white shadow-xl hover:bg-[#0f3354] transition-all flex items-center justify-center focus:outline-none border border-[#0f3354]">
                <i class="fas fa-th-large text-lg"></i>
            </button>

            <!-- TOMBOL SCROLL TO TOP -->
            <button type="button" x-data="{ showScroll: false }" @scroll.window="showScroll = window.pageYOffset > 400"
                x-show="showScroll" x-transition @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-12 h-12 shrink-0 aspect-square rounded-full bg-[#13416B] text-white shadow-xl hover:bg-[#0f3354] transition-all flex items-center justify-center focus:outline-none border border-[#0f3354]"
                x-cloak>
                <i class="fas fa-arrow-up text-lg"></i>
            </button>
        </div>

        <!-- BOTTOM SHEET NAVIGASI SOAL (MOBILE & TABLET) -->
        <div x-show="showMobileNav" style="display: none;" class="fixed inset-0 z-[9999] lg:hidden" x-cloak>
            <div x-show="showMobileNav" x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="showMobileNav = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="showMobileNav" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                class="absolute bottom-0 inset-x-0 bg-white rounded-t-2xl shadow-2xl flex flex-col max-h-[85vh]">
                <div
                    class="p-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-[#13416B] text-white rounded-lg shadow-sm border border-[#0f3354] shrink-0">
                            <i class="fas fa-th-large text-sm"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Navigasi Soal Cepat</h3>
                    </div>
                    <button type="button" @click="showMobileNav = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 focus:outline-none shrink-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto">
                    <p class="text-xs text-slate-500 mb-4">Pilih nomor untuk langsung menuju ke soal.</p>
                    <div class="grid grid-cols-5 sm:grid-cols-8 gap-3 pb-4">
                        <template x-for="(q, index) in questions" :key="index">
                            <button type="button"
                                @click="document.getElementById('question-' + index).scrollIntoView({ behavior: 'smooth', block: 'start' }); showMobileNav = false;"
                                class="aspect-square w-full rounded-full text-sm font-bold transition-all flex items-center justify-center border border-slate-200 bg-slate-50 text-slate-700 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] shadow-sm cursor-pointer">
                                <span x-text="index + 1"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: INSERT LINK -->
    <!-- ========================================================================= -->
    <div id="custom-link-modal"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 transform scale-100 transition-transform">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-link text-[#13416B]"></i> Sisipkan Tautan (Link)
                </h3>
                <button type="button" id="btn-close-link"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Teks yang ditampilkan</label>
                    <input type="text" id="link-text-input" placeholder="Contoh: Klik di sini untuk mengunduh"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">URL / Tautan <span
                            class="text-red-500">*</span></label>
                    <input type="url" id="link-url-input" placeholder="https://..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] transition-all">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" id="btn-cancel-link"
                    class="px-5 py-2 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="button" id="btn-save-link"
                    class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-sm rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-check text-xs"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: GAMBAR DENGAN FORM KETERANGAN / CAPTION -->
    <!-- ========================================================================= -->
    <div id="custom-image-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-5 sm:p-6 border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="flex justify-end items-center mb-2">
                <button type="button" id="btn-close-image" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto space-y-4 pr-0.5 custom-scrollbar">
                <!-- Segmented Tab Source Switcher -->
                <div class="p-1 bg-slate-100 rounded-xl flex gap-1 border border-slate-200/80">
                    <button type="button" id="tab-image-upload" class="flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 bg-white text-[#13416B] shadow-xs cursor-pointer">
                        <i class="fas fa-cloud-arrow-up text-sm"></i> Unggah Gambar (File)
                    </button>
                    <button type="button" id="tab-image-link" class="flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800 cursor-pointer">
                        <i class="fas fa-link text-sm"></i> Tautan URL Gambar
                    </button>
                </div>

                <!-- Panel 1: Upload Gambar File (Drag & Drop + Live Preview) -->
                <div id="panel-image-upload" class="block transition-all space-y-3">
                    <input type="file" id="image-file-input" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml" class="hidden">

                    <div id="image-dropzone" class="border-2 border-dashed border-slate-300 hover:border-[#13416B] bg-slate-50/70 hover:bg-blue-50/30 rounded-2xl p-6 sm:p-8 text-center cursor-pointer transition-all duration-200 group">
                        <div class="w-16 h-16 rounded-2xl bg-white shadow-xs border border-slate-200 text-[#13416B] flex items-center justify-center mx-auto mb-3.5 group-hover:scale-105 group-hover:border-[#13416B]/40 transition-all">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-800 mb-1">
                            Tarik & lepas file gambar di sini, atau <span class="text-[#13416B] underline font-extrabold">Telusuri Berkas</span>
                        </p>
                        <p class="text-xs text-slate-400 mb-3">Format didukung: PNG, JPG, JPEG, WEBP, GIF, SVG</p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200/60 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-info-circle text-[10px] text-slate-400"></i> Maksimal ukuran: 10 MB
                        </div>
                    </div>

                    <div id="image-file-card" class="hidden bg-slate-50 border border-slate-200 rounded-2xl p-4 transition-all space-y-3">
                        <div class="relative rounded-xl overflow-hidden bg-slate-900/5 p-2 border border-slate-200/60 flex items-center justify-center min-h-[140px] max-h-[220px]">
                            <img id="image-preview-element" src="" alt="Pratinjau Gambar" class="max-h-[200px] w-auto max-w-full object-contain rounded-lg shadow-2xs">
                        </div>

                        <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center shrink-0">
                                    <i class="fas fa-file-image text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p id="image-file-name" class="text-xs sm:text-sm font-bold text-slate-800 truncate">gambar.png</p>
                                    <p id="image-file-size" class="text-[11px] text-slate-400 font-medium">0 KB</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" id="btn-change-image" class="px-2.5 py-1.5 text-xs font-bold text-[#13416B] hover:bg-[#13416B]/10 rounded-lg transition-colors flex items-center gap-1 cursor-pointer" title="Ganti Berkas">
                                    <i class="fas fa-sync-alt text-[10px]"></i> Ganti
                                </button>
                                <button type="button" id="btn-remove-image" class="px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1 cursor-pointer" title="Hapus Berkas">
                                    <i class="fas fa-trash-alt text-[10px]"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Link URL Gambar -->
                <div id="panel-image-link" class="hidden space-y-3.5 transition-all">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tautan URL Gambar</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-globe text-sm"></i>
                            </span>
                            <input type="url" id="image-url-input" placeholder="https://domain.com/gambar.png" class="w-full rounded-xl border border-slate-300 pl-10 pr-3 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-xs">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Masukkan URL langsung berkas gambar yang dapat diakses publik.</p>
                    </div>

                    <div id="image-link-preview-container" class="hidden rounded-xl overflow-hidden border border-slate-200 bg-slate-900/5 p-2 min-h-[120px] max-h-[220px] flex items-center justify-center">
                        <img id="image-link-preview-element" src="" alt="Pratinjau URL" class="max-h-[200px] w-auto max-w-full object-contain rounded-lg shadow-2xs">
                    </div>
                </div>

                <!-- Form Input Keterangan / Caption Gambar -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan / Caption Gambar (Opsional)</label>
                    <input type="text" id="image-caption-input" placeholder="Contoh: Gambar 1.1 Diagram Rangkaian Listrik..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-xs">
                    <p class="text-[11px] text-slate-400 mt-1">Keterangan akan otomatis dimunculkan rapi tepat di bawah gambar.</p>
                </div>
            </div>

            <!-- Footer Modal Gambar -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" id="btn-cancel-image" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="button" id="btn-save-image" class="px-6 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check text-xs"></i> Sisipkan Gambar
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: VIDEO DENGAN FORM KETERANGAN / CAPTION -->
    <!-- ========================================================================= -->
    <div id="custom-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-5 sm:p-6 border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
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

                <!-- Panel 1: Upload Video File -->
                <div id="panel-upload" class="block transition-all space-y-3">
                    <input type="file" id="video-file-input" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-matroska" class="hidden">

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

                    <div id="youtube-preview-container" class="hidden rounded-xl overflow-hidden border border-slate-200 shadow-xs bg-black aspect-video relative">
                        <iframe id="youtube-preview-frame" class="w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                </div>

                <!-- Form Input Keterangan / Caption Video -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan / Caption Video (Opsional)</label>
                    <input type="text" id="video-caption-input" placeholder="Contoh: Video 1.1 Penjelasan Soal Analisis Kasus..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] shadow-xs">
                    <p class="text-[11px] text-slate-400 mt-1">Keterangan akan otomatis dimunculkan tepat di bawah video.</p>
                </div>
            </div>

            <!-- Footer Modal Video -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" id="btn-cancel-video" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="button" id="btn-save-video" class="px-6 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check text-xs"></i> Sisipkan Video
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: VISUAL EQUATION BUILDER (WORD-STYLE WYSIWYG MATHLIVE) -->
    <!-- ========================================================================= -->
    <div id="custom-formula-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-5 sm:p-6 border border-slate-200 flex flex-col max-h-[92vh] overflow-hidden">
            <div class="flex justify-end mb-1 shrink-0">
                <button type="button" id="btn-close-formula" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Tutup Modal">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="space-y-5 overflow-y-auto pr-1 flex-1 custom-scrollbar">
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

            <!-- Footer Modal Formula -->
            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 mt-4 shrink-0">
                <button type="button" id="btn-cancel-formula" class="px-5 py-2 text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">Batal</button>
                <button type="button" id="btn-save-formula" class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition shadow-xs hover:shadow-sm cursor-pointer flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Sisipkan ke Dokumen
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Highlight.js & KaTeX & MathLive -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/mathlive"></script>

        <!-- Quill Core & Blot Formatter -->
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill-blot-formatter@1.0.5/dist/quill-blot-formatter.min.js"></script>

        <script>
            // Register blot formatter
            Quill.register('modules/blotFormatter', QuillBlotFormatter.default);

            const Parchment = Quill.import('parchment');
            const Font = Quill.import('formats/font');
            Font.whitelist = ['arial', 'times-new-roman', 'calibri', 'verdana', 'courier'];
            Quill.register(Font, true);

            const Size = Quill.import('attributors/style/size');
            Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '24px', '32px'];
            Quill.register(Size, true);

            const LineHeightStyle = new Parchment.Attributor.Style('lineHeight', 'line-height', {
                scope: Parchment.Scope.BLOCK,
                whitelist: ['1', '1.0', '1.2', '1.5', '2.0', '2.5']
            });
            Quill.register(LineHeightStyle, true);

            const LetterSpacingStyle = new Parchment.Attributor.Style('letterSpacing', 'letter-spacing', {
                scope: Parchment.Scope.INLINE,
                whitelist: ['0', '0px', '1px', '2px', '3px', '4px']
            });
            Quill.register(LetterSpacingStyle, true);

            const CaptionClass = new Parchment.Attributor.Class('caption', 'ql-media-caption', { scope: Parchment.Scope.BLOCK });
            Quill.register(CaptionClass, true);

            class VideoElementSpec extends QuillBlotFormatter.BlotSpec {
                constructor(formatter) { super(formatter); this.targetElement = null; }
                init() {
                    this.formatter.quill.root.addEventListener('click', (event) => {
                        const el = event.target;
                        if (el.tagName === 'VIDEO') {
                            this.targetElement = el;
                            this.formatter.show(this);
                        }
                    });
                    this.formatter.quill.on('text-change', () => {
                        if (this.targetElement && !this.formatter.quill.root.contains(this.targetElement)) {
                            this.formatter.hide();
                        }
                    });
                }
                getTargetElement() { return this.targetElement; }
            }

            const BaseImage = Quill.import('formats/image');
            class CustomImage extends BaseImage {
                static formats(domNode) {
                    return ['style', 'width', 'height'].reduce(function(formats, attribute) {
                        if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute);
                        return formats;
                    }, {});
                }
                format(name, value) {
                    if (['style', 'width', 'height'].includes(name)) {
                        if (value) this.domNode.setAttribute(name, value);
                        else this.domNode.removeAttribute(name);
                    } else super.format(name, value);
                }
            }
            Quill.register(CustomImage, true);

            const BlockEmbed = Quill.import('blots/block/embed');
            class CustomVideo extends BlockEmbed {
                static create(value) {
                    let isMp4 = value.match(/\.(mp4|webm|ogg)$/i);
                    let node = document.createElement(isMp4 ? 'video' : 'iframe');
                    if (isMp4) {
                        node.setAttribute('controls', '');
                        node.setAttribute('controlsList', 'nodownload');
                        node.setAttribute('src', value);
                        node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;');
                    } else {
                        node.setAttribute('frameborder', '0');
                        node.setAttribute('allowfullscreen', true);
                        node.setAttribute('src', value);
                        node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;');
                        node.setAttribute('width', '560');
                        node.setAttribute('height', '315');
                    }
                    return node;
                }
                static value(node) { return node.getAttribute('src'); }
                static formats(domNode) {
                    return ['style', 'width', 'height'].reduce(function(formats, attribute) {
                        if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute);
                        return formats;
                    }, {});
                }
                format(name, value) {
                    if (['style', 'width', 'height'].includes(name)) {
                        if (value) this.domNode.setAttribute(name, value);
                        else this.domNode.removeAttribute(name);
                    } else super.format(name, value);
                }
            }
            CustomVideo.blotName = 'video';
            CustomVideo.tagName = ['IFRAME', 'VIDEO'];
            Quill.register(CustomVideo, true);

            hljs.configure({ languages: ['javascript', 'php', 'html', 'css', 'python', 'java', 'sql', 'bash'] });

            // OVERRIDE ICON QUILL DEFAULT DENGAN FONT AWESOME / CUSTOM HTML
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

            // PENGELOLA MULTI-INSTANCE QUILL EDITOR
            window.activeQuillInstance = null;
            window.activeQuillRange = null;

            function setActiveQuill(quill) {
                window.activeQuillInstance = quill;
                let range = quill.getSelection(true);
                if (!range) {
                    range = { index: quill.getLength(), length: 0 };
                }
                window.activeQuillRange = range;
            }

            function setupLabeledPickers(toolbarEl) {
                if (!toolbarEl) return;
                const items = [
                    { selector: '.ql-size', label: 'Ukuran Huruf' },
                    { selector: '.ql-lineHeight', label: 'Jarak Baris' },
                    { selector: '.ql-letterSpacing', label: 'Jarak Huruf' }
                ];
                items.forEach(item => {
                    const picker = toolbarEl.querySelector(item.selector);
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

            function setupTooltips(toolbarEl) {
                if (!toolbarEl) return;
                toolbarEl.querySelectorAll('button, .ql-picker').forEach(el => {
                    if (el.hasAttribute('data-tooltip')) return;
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
            }

            // Inisialisasi Editor Pertanyaan
            function createQuestionEditor(container, onTextChange, initialValue) {
                let quill = new Quill(container, {
                    bounds: container,
                    theme: 'snow',
                    placeholder: 'Tuliskan isi pertanyaan di sini...',
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
                                'link': function() { setActiveQuill(this.quill); openLinkModal(); },
                                'image': function() { setActiveQuill(this.quill); openImageModal(); },
                                'video': function() { setActiveQuill(this.quill); openVideoModal(); },
                                'formula': function() { setActiveQuill(this.quill); openFormulaModal(); }
                            }
                        }
                    }
                });

                if (initialValue) {
                    quill.root.innerHTML = initialValue;
                }

                quill.on('selection-change', function(range) {
                    if (range) {
                        window.activeQuillInstance = quill;
                        window.activeQuillRange = range;
                    }
                });

                quill.on('text-change', function() {
                    const html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                    if (typeof onTextChange === 'function') {
                        onTextChange(html);
                    }
                });

                if (quill.root) {
                    const originalRootFocus = quill.root.focus.bind(quill.root);
                    quill.root.focus = function(options) {
                        originalRootFocus(Object.assign({ preventScroll: true }, options));
                    };
                }

                setTimeout(() => {
                    const toolbarEl = container.previousElementSibling;
                    if (toolbarEl) {
                        setupLabeledPickers(toolbarEl);
                        setupTooltips(toolbarEl);
                    }
                }, 50);

                return quill;
            }

            // Inisialisasi Editor Pilihan Jawaban
            function createChoiceEditor(container, onTextChange, initialValue, placeholder) {
                let quill = new Quill(container, {
                    bounds: container,
                    theme: 'snow',
                    placeholder: placeholder || 'Ketik pilihan jawaban...',
                    modules: {
                        syntax: true,
                        blotFormatter: { specs: [ QuillBlotFormatter.ImageSpec, QuillBlotFormatter.IframeVideoSpec, VideoElementSpec ] },
                        toolbar: {
                            container: [
                                ['bold', 'italic', 'underline'],
                                [{ 'script': 'sub'}, { 'script': 'super' }],
                                ['formula', 'code-block', 'image']
                            ],
                            handlers: {
                                'image': function() { setActiveQuill(this.quill); openImageModal(); },
                                'formula': function() { setActiveQuill(this.quill); openFormulaModal(); }
                            }
                        }
                    }
                });

                if (initialValue) {
                    quill.root.innerHTML = initialValue;
                }

                quill.on('selection-change', function(range) {
                    if (range) {
                        window.activeQuillInstance = quill;
                        window.activeQuillRange = range;
                    }
                });

                quill.on('text-change', function() {
                    const html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                    if (typeof onTextChange === 'function') {
                        onTextChange(html);
                    }
                });

                if (quill.root) {
                    const originalRootFocus = quill.root.focus.bind(quill.root);
                    quill.root.focus = function(options) {
                        originalRootFocus(Object.assign({ preventScroll: true }, options));
                    };
                }

                setTimeout(() => {
                    const toolbarEl = container.previousElementSibling;
                    if (toolbarEl) {
                        setupTooltips(toolbarEl);
                    }
                }, 50);

                return quill;
            }

            // ==========================================
            // LOGIKA MODAL 1: LINK
            // ==========================================
            function openLinkModal() {
                const quill = window.activeQuillInstance;
                const range = window.activeQuillRange;
                const text = range && range.length > 0 && quill ? quill.getText(range.index, range.length) : '';
                document.getElementById('link-text-input').value = text;
                document.getElementById('link-url-input').value = '';
                const modal = document.getElementById('custom-link-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => document.getElementById('link-url-input').focus(), 100);
            }

            function closeLinkModal() {
                const modal = document.getElementById('custom-link-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.getElementById('btn-close-link')?.addEventListener('click', closeLinkModal);
            document.getElementById('btn-cancel-link')?.addEventListener('click', closeLinkModal);

            document.getElementById('btn-save-link')?.addEventListener('click', function() {
                const quill = window.activeQuillInstance;
                if (!quill) return;

                var text = document.getElementById('link-text-input').value.trim();
                var url = document.getElementById('link-url-input').value.trim();
                var range = window.activeQuillRange;

                if (!url) {
                    alert('URL / Tautan wajib diisi!');
                    return;
                }

                if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !/^tel:/i.test(url)) {
                    url = 'https://' + url;
                }
                if (!text) {
                    text = url;
                }

                quill.focus();

                if (range && range.length > 0) {
                    quill.deleteText(range.index, range.length);
                    quill.insertText(range.index, text, 'link', url);
                    quill.setSelection(range.index + text.length);
                } else {
                    var cursorPosition = range ? range.index : quill.getLength();
                    quill.insertText(cursorPosition, text, 'link', url);
                    quill.setSelection(cursorPosition + text.length);
                }
                closeLinkModal();
            });

            // ==========================================
            // LOGIKA MODAL 2: GAMBAR & CAPTION
            // ==========================================
            let currentImageSource = 'upload';
            let selectedImageFile = null;
            let imagePreviewObjectUrl = null;

            const imageFileInput = document.getElementById('image-file-input');
            const imageDropzone = document.getElementById('image-dropzone');
            const imageFileCard = document.getElementById('image-file-card');
            const imagePreviewElement = document.getElementById('image-preview-element');
            const imageFileName = document.getElementById('image-file-name');
            const imageFileSize = document.getElementById('image-file-size');
            const btnChangeImage = document.getElementById('btn-change-image');
            const btnRemoveImage = document.getElementById('btn-remove-image');

            const tabImageUpload = document.getElementById('tab-image-upload');
            const tabImageLink = document.getElementById('tab-image-link');
            const panelImageUpload = document.getElementById('panel-image-upload');
            const panelImageLink = document.getElementById('panel-image-link');
            const imageUrlInput = document.getElementById('image-url-input');
            const imageLinkPreviewContainer = document.getElementById('image-link-preview-container');
            const imageLinkPreviewElement = document.getElementById('image-link-preview-element');
            const imageCaptionInput = document.getElementById('image-caption-input');

            function switchImageTab(source) {
                currentImageSource = source;
                if (source === 'upload') {
                    tabImageUpload.classList.add('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabImageUpload.classList.remove('text-slate-500');
                    tabImageLink.classList.remove('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabImageLink.classList.add('text-slate-500');

                    panelImageUpload.classList.remove('hidden');
                    panelImageUpload.classList.add('block');
                    panelImageLink.classList.remove('block');
                    panelImageLink.classList.add('hidden');
                } else {
                    tabImageLink.classList.add('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabImageLink.classList.remove('text-slate-500');
                    tabImageUpload.classList.remove('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabImageUpload.classList.add('text-slate-500');

                    panelImageLink.classList.remove('hidden');
                    panelImageLink.classList.add('block');
                    panelImageUpload.classList.remove('block');
                    panelImageUpload.classList.add('hidden');
                    setTimeout(() => imageUrlInput?.focus(), 50);
                }
            }

            if (tabImageUpload) tabImageUpload.addEventListener('click', () => switchImageTab('upload'));
            if (tabImageLink) tabImageLink.addEventListener('click', () => switchImageTab('link'));

            function handleImageFileSelect(file) {
                if (!file) return;
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('Ukuran file gambar melebihi batas maksimal 10MB!');
                    return;
                }
                selectedImageFile = file;
                imageFileName.textContent = file.name;
                imageFileSize.textContent = (file.size / 1024 > 1024 ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB');

                if (imagePreviewObjectUrl) {
                    URL.revokeObjectURL(imagePreviewObjectUrl);
                }
                imagePreviewObjectUrl = URL.createObjectURL(file);
                imagePreviewElement.src = imagePreviewObjectUrl;

                imageDropzone.classList.add('hidden');
                imageFileCard.classList.remove('hidden');
            }

            function resetImageFile() {
                selectedImageFile = null;
                if (imageFileInput) imageFileInput.value = '';
                if (imagePreviewObjectUrl) {
                    URL.revokeObjectURL(imagePreviewObjectUrl);
                    imagePreviewObjectUrl = null;
                }
                if (imagePreviewElement) imagePreviewElement.src = '';
                if (imageFileCard) imageFileCard.classList.add('hidden');
                if (imageDropzone) imageDropzone.classList.remove('hidden');
            }

            if (imageDropzone) imageDropzone.addEventListener('click', () => imageFileInput?.click());
            if (btnChangeImage) btnChangeImage.addEventListener('click', () => imageFileInput?.click());
            if (btnRemoveImage) btnRemoveImage.addEventListener('click', resetImageFile);

            if (imageFileInput) {
                imageFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        handleImageFileSelect(this.files[0]);
                    }
                });
            }

            if (imageDropzone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    imageDropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        imageDropzone.classList.add('border-[#13416B]', 'bg-blue-50/60', 'scale-[1.01]');
                    });
                });
                ['dragleave', 'drop'].forEach(eventName => {
                    imageDropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        imageDropzone.classList.remove('border-[#13416B]', 'bg-blue-50/60', 'scale-[1.01]');
                    });
                });
                imageDropzone.addEventListener('drop', (e) => {
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        handleImageFileSelect(files[0]);
                    }
                });
            }

            if (imageUrlInput) {
                imageUrlInput.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (val && /^https?:\/\//i.test(val)) {
                        imageLinkPreviewElement.src = val;
                        imageLinkPreviewContainer.classList.remove('hidden');
                    } else {
                        imageLinkPreviewElement.src = '';
                        imageLinkPreviewContainer.classList.add('hidden');
                    }
                });
            }

            function openImageModal() {
                switchImageTab('upload');
                resetImageFile();
                if (imageUrlInput) imageUrlInput.value = '';
                if (imageCaptionInput) imageCaptionInput.value = '';
                if (imageLinkPreviewContainer) imageLinkPreviewContainer.classList.add('hidden');
                const modal = document.getElementById('custom-image-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeImageModal() {
                const modal = document.getElementById('custom-image-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.getElementById('btn-close-image')?.addEventListener('click', closeImageModal);
            document.getElementById('btn-cancel-image')?.addEventListener('click', closeImageModal);

            function insertImageToQuill(imageUrl) {
                const quill = window.activeQuillInstance;
                if (!quill) return;

                const range = window.activeQuillRange;
                const insertIdx = range ? range.index : quill.getLength();
                const caption = (imageCaptionInput?.value || '').trim();

                if (range && range.length > 0) {
                    quill.deleteText(range.index, range.length);
                }

                quill.insertEmbed(insertIdx, 'image', imageUrl, Quill.sources.USER);
                quill.formatLine(insertIdx, 1, 'align', 'center', Quill.sources.USER);

                if (caption) {
                    quill.insertText(insertIdx + 1, '\n' + caption + '\n', Quill.sources.USER);
                    const captionTextIdx = insertIdx + 2;
                    quill.formatText(captionTextIdx, caption.length, {
                        italic: true,
                        size: '12px',
                        color: '#64748b'
                    }, Quill.sources.USER);
                    quill.formatLine(captionTextIdx, caption.length, 'align', 'center', Quill.sources.USER);
                    quill.formatLine(captionTextIdx, caption.length, 'caption', 'true', Quill.sources.USER);

                    const nextCursor = captionTextIdx + caption.length + 1;
                    quill.setSelection(nextCursor, 0, Quill.sources.SILENT);
                    quill.formatLine(nextCursor, 1, 'align', false, Quill.sources.SILENT);
                } else {
                    quill.insertText(insertIdx + 1, '\n', Quill.sources.USER);
                    quill.setSelection(insertIdx + 2, 0, Quill.sources.SILENT);
                }

                closeImageModal();

                setTimeout(() => {
                    if (quill.root) {
                        const bounds = quill.getBounds(insertIdx);
                        if (bounds) {
                            const editorRect = quill.container.getBoundingClientRect();
                            const targetY = window.pageYOffset + editorRect.top + bounds.top - (window.innerHeight / 3);
                            window.scrollTo({
                                top: Math.max(0, targetY),
                                behavior: 'smooth'
                            });
                        }
                    }
                }, 100);
            }

            document.getElementById('btn-save-image')?.addEventListener('click', function() {
                const btn = this;
                if (currentImageSource === 'upload') {
                    if (!selectedImageFile) return alert('Silakan pilih atau tarik file gambar terlebih dahulu!');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengunggah...';
                    btn.disabled = true;

                    const formData = new FormData();
                    formData.append('image', selectedImageFile);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "{{ route('admin-pusat.management-course.post-tests.upload-image') }}", true);
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.onload = function() {
                        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Sisipkan Gambar';
                        btn.disabled = false;
                        if (xhr.status === 200 || xhr.status === 201) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.success && data.url) {
                                    insertImageToQuill(data.url);
                                } else {
                                    alert('Gagal mengunggah gambar: ' + (data.message || 'Error'));
                                }
                            } catch(e) {
                                alert('Respon server tidak valid.');
                            }
                        } else if (xhr.status === 422) {
                            let errorMsg = 'Validasi gambar gagal.';
                            try {
                                const res = JSON.parse(xhr.responseText);
                                if (res.errors && res.errors.image) errorMsg = res.errors.image.join('\n');
                                else if (res.message) errorMsg = res.message;
                            } catch(e) {}
                            alert(errorMsg);
                        } else {
                            const reader = new FileReader();
                            reader.onload = function(evt) {
                                insertImageToQuill(evt.target.result);
                            };
                            reader.readAsDataURL(selectedImageFile);
                        }
                    };
                    xhr.onerror = function() {
                        btn.innerHTML = '<i class="fas fa-check text-xs"></i> Sisipkan Gambar';
                        btn.disabled = false;
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            insertImageToQuill(evt.target.result);
                        };
                        reader.readAsDataURL(selectedImageFile);
                    };
                    xhr.send(formData);
                } else {
                    const rawUrl = (imageUrlInput?.value || '').trim();
                    if (!rawUrl) return alert('Silakan masukkan tautan URL gambar!');
                    insertImageToQuill(rawUrl);
                }
            });

            // ==========================================
            // LOGIKA MODAL 3: VIDEO & CAPTION
            // ==========================================
            let currentVideoSource = 'upload';
            let selectedVideoFile = null;
            let videoPreviewObjectUrl = null;

            const videoFileInput = document.getElementById('video-file-input');
            const videoDropzone = document.getElementById('video-dropzone');
            const videoFileCard = document.getElementById('video-file-card');
            const videoPreviewPlayer = document.getElementById('video-preview-player');
            const videoFileName = document.getElementById('video-file-name');
            const videoFileSize = document.getElementById('video-file-size');
            const btnChangeVideo = document.getElementById('btn-change-video');
            const btnRemoveVideo = document.getElementById('btn-remove-video');

            const tabVideoUpload = document.getElementById('tab-video-upload');
            const tabVideoLink = document.getElementById('tab-video-link');
            const panelUpload = document.getElementById('panel-upload');
            const panelLink = document.getElementById('panel-link');
            const videoUrlInput = document.getElementById('video-url-input');
            const youtubePreviewContainer = document.getElementById('youtube-preview-container');
            const youtubePreviewFrame = document.getElementById('youtube-preview-frame');
            const uploadProgressContainer = document.getElementById('upload-progress-container');
            const uploadProgressBar = document.getElementById('upload-progress-bar');
            const uploadProgressPercent = document.getElementById('upload-progress-percent');
            const uploadProgressText = document.getElementById('upload-progress-text');
            const videoCaptionInput = document.getElementById('video-caption-input');

            function switchVideoTab(source) {
                currentVideoSource = source;
                if (source === 'upload') {
                    tabVideoUpload.classList.add('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabVideoUpload.classList.remove('text-slate-500');
                    tabVideoLink.classList.remove('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabVideoLink.classList.add('text-slate-500');

                    panelUpload.classList.remove('hidden');
                    panelUpload.classList.add('block');
                    panelLink.classList.remove('block');
                    panelLink.classList.add('hidden');
                } else {
                    tabVideoLink.classList.add('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabVideoLink.classList.remove('text-slate-500');
                    tabVideoUpload.classList.remove('bg-white', 'text-[#13416B]', 'shadow-xs');
                    tabVideoUpload.classList.add('text-slate-500');

                    panelLink.classList.remove('hidden');
                    panelLink.classList.add('block');
                    panelUpload.classList.remove('block');
                    panelUpload.classList.add('hidden');
                    setTimeout(() => videoUrlInput?.focus(), 50);
                }
            }

            if (tabVideoUpload) tabVideoUpload.addEventListener('click', () => switchVideoTab('upload'));
            if (tabVideoLink) tabVideoLink.addEventListener('click', () => switchVideoTab('link'));

            function handleVideoFileSelect(file) {
                if (!file) return;
                const maxSize = 100 * 1024 * 1024; // 100MB
                if (file.size > maxSize) {
                    alert('Ukuran file video melebihi batas maksimal 100MB!');
                    return;
                }
                selectedVideoFile = file;
                videoFileName.textContent = file.name;
                videoFileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                if (videoPreviewObjectUrl) {
                    URL.revokeObjectURL(videoPreviewObjectUrl);
                }
                videoPreviewObjectUrl = URL.createObjectURL(file);
                videoPreviewPlayer.src = videoPreviewObjectUrl;

                videoDropzone.classList.add('hidden');
                videoFileCard.classList.remove('hidden');
            }

            function resetVideoFile() {
                selectedVideoFile = null;
                if (videoFileInput) videoFileInput.value = '';
                if (videoPreviewObjectUrl) {
                    URL.revokeObjectURL(videoPreviewObjectUrl);
                    videoPreviewObjectUrl = null;
                }
                if (videoPreviewPlayer) {
                    videoPreviewPlayer.pause();
                    videoPreviewPlayer.src = '';
                }
                if (videoFileCard) videoFileCard.classList.add('hidden');
                if (videoDropzone) videoDropzone.classList.remove('hidden');
            }

            if (videoDropzone) videoDropzone.addEventListener('click', () => videoFileInput?.click());
            if (btnChangeVideo) btnChangeVideo.addEventListener('click', () => videoFileInput?.click());
            if (btnRemoveVideo) btnRemoveVideo.addEventListener('click', resetVideoFile);

            if (videoFileInput) {
                videoFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        handleVideoFileSelect(this.files[0]);
                    }
                });
            }

            if (videoDropzone) {
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
                        handleVideoFileSelect(files[0]);
                    }
                });
            }

            function parseYouTubeEmbedUrl(url) {
                if (!url) return null;
                const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|shorts\/|watch\?.+&v=))([\w-]{11})/i);
                if (match && match[1]) {
                    return `https://www.youtube.com/embed/${match[1]}`;
                }
                return null;
            }

            if (videoUrlInput) {
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
            }

            function openVideoModal() {
                switchVideoTab('upload');
                resetVideoFile();
                if (videoUrlInput) videoUrlInput.value = '';
                if (videoCaptionInput) videoCaptionInput.value = '';
                if (youtubePreviewFrame) youtubePreviewFrame.src = '';
                if (youtubePreviewContainer) youtubePreviewContainer.classList.add('hidden');
                if (uploadProgressContainer) uploadProgressContainer.classList.add('hidden');
                if (uploadProgressBar) uploadProgressBar.style.width = '0%';
                const modal = document.getElementById('custom-video-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeVideoModal() {
                const modal = document.getElementById('custom-video-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (videoPreviewPlayer) {
                    videoPreviewPlayer.pause();
                }
            }

            document.getElementById('btn-close-video')?.addEventListener('click', closeVideoModal);
            document.getElementById('btn-cancel-video')?.addEventListener('click', closeVideoModal);

            function insertVideoToQuill(videoUrl) {
                const quill = window.activeQuillInstance;
                if (!quill) return;

                const range = window.activeQuillRange;
                const insertIdx = range ? range.index : quill.getLength();
                const caption = (videoCaptionInput?.value || '').trim();

                if (range && range.length > 0) {
                    quill.deleteText(range.index, range.length);
                }

                quill.insertEmbed(insertIdx, 'video', videoUrl, Quill.sources.USER);

                if (caption) {
                    const captionIdx = insertIdx + 1;
                    quill.insertText(captionIdx, caption + '\n', Quill.sources.USER);
                    quill.formatText(captionIdx, caption.length, {
                        italic: true,
                        size: '12px',
                        color: '#64748b'
                    }, Quill.sources.USER);
                    quill.formatLine(captionIdx, caption.length, 'align', 'center', Quill.sources.USER);
                    quill.formatLine(captionIdx, caption.length, 'caption', 'true', Quill.sources.USER);

                    const nextCursor = captionIdx + caption.length + 1;
                    quill.setSelection(nextCursor, 0, Quill.sources.SILENT);
                    quill.formatLine(nextCursor, 1, 'align', false, Quill.sources.SILENT);
                } else {
                    quill.setSelection(insertIdx + 1, 0, Quill.sources.SILENT);
                }

                closeVideoModal();

                setTimeout(() => {
                    if (quill.root) {
                        const bounds = quill.getBounds(insertIdx);
                        if (bounds) {
                            const editorRect = quill.container.getBoundingClientRect();
                            const targetY = window.pageYOffset + editorRect.top + bounds.top - (window.innerHeight / 3);
                            window.scrollTo({
                                top: Math.max(0, targetY),
                                behavior: 'smooth'
                            });
                        }
                    }
                }, 100);
            }

            document.getElementById('btn-save-video')?.addEventListener('click', function() {
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
                                    insertVideoToQuill(data.url);
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
                                if (res.errors && res.errors.video) errorMsg = res.errors.video.join('\n');
                                else if (res.message) errorMsg = res.message;
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
                    insertVideoToQuill(embedUrl);
                }
            });

            // ============================================================
            // LOGIKA MODAL 4: VISUAL EQUATION BUILDER (MATHLIVE WORD-STYLE)
            // ============================================================
            const formulaModal = document.getElementById('custom-formula-modal');
            const mathField = document.getElementById('formula-math-field');

            if (mathField) {
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

            document.querySelectorAll('#btn-close-formula, #btn-cancel-formula').forEach(btn => {
                btn?.addEventListener('click', closeFormulaModal);
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

                // Bersihkan placeholder sisa
                latex = latex.replace(/\\placeholder\{\}/g, '').replace(/#\?/g, '').trim();

                if (!latex) {
                    alert('Silakan pilih struktur rumus atau buat rumus terlebih dahulu!');
                    return;
                }

                const quill = window.activeQuillInstance;
                if (!quill) return;

                const range = window.activeQuillRange;
                const index = range ? range.index : quill.getLength();

                quill.insertEmbed(index, 'formula', latex, 'user');
                quill.setSelection(index + 1, 'silent');
                closeFormulaModal();

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

            // DEFINISI STATE ALPINEJS (DENGAN INJEKSI DATA EXISTING)
            function postTestEditForm() {
                return {
                    showMobileNav: false,
                    questions: @json($formattedQuestions),
                    addQuestion() {
                        this.questions.push({
                            question: '',
                            choices: ['', '', '', ''],
                            correct_choice: 0
                        });
                    },
                    removeQuestion(index) {
                        this.questions.splice(index, 1);
                    }
                }
            }
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
