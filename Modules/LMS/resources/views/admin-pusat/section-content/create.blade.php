<x-dashboard::layouts.dashboard title="Tambah Materi: {{ $section->name }}">
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
                        <span class="ml-1 text-sm font-bold text-slate-700 md:ml-2">{{ $section->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Form Card Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Tambah Materi Baru</h2>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Bagian: <span class="text-[#13416B]">{{ $section->name }}</span></p>
                </div>
            </div>

            <x-validation-errors class="p-6 pb-0" />

            <form action="{{ route('admin-pusat.management-course.course-sections-contents.store') }}" method="POST"
                enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="course_slug" value="{{ $course->slug }}" />
                <input type="hidden" name="course_section_id" value="{{ $section->id }}" />

                <!-- Nama / Judul Materi -->
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                        Judul Materi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                        placeholder="Contoh: Pengenalan Dasar HTML & CSS"
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
                        <input type="hidden" name="content_text" id="content_text" value="{{ old('content_text') }}">
                        <!-- Container Editor -->
                        <div id="editor-container">{!! old('content_text') !!}</div>
                    </div>
                </div>

                <!-- Dokumen Lampiran -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Dokumen Lampiran <span class="text-slate-400 font-normal">(Opsional - Bisa pilih banyak file)</span>
                    </label>
                    <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#13416B]/10 file:text-[#13416B] hover:file:bg-[#13416B]/20 transition-all border border-slate-200 rounded-xl" />
                    <p class="text-[11px] font-medium text-slate-400 mt-1.5"><i class="fas fa-info-circle mr-1"></i>Format: PDF, DOC, DOCX. Anda dapat menyorot beberapa file sekaligus (Max: 10MB/file).</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                        class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Materi
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
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 w-[110px] text-[11px]">Arial <i class="fas fa-caret-down"></i></span>
                            <span>Memilih jenis huruf (Font Family).</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 w-[55px] text-[11px]">14 <i class="fas fa-caret-down"></i></span>
                            <span>Menentukan ukuran huruf.</span>
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
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 w-[140px] text-[11px]">Jarak Baris (Normal) <i class="fas fa-caret-down"></i></span>
                            <span>Mengatur kerenggangan spasi baris atas dan bawah.</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="inline-flex items-center justify-between border border-slate-300 rounded px-2 py-0.5 bg-white shrink-0 w-[140px] text-[11px]">Jarak Huruf (Normal) <i class="fas fa-caret-down"></i></span>
                            <span>Mengatur spasi / jarak antar huruf.</span>
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
    <div id="custom-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-slate-700 flex items-center gap-2"><i class="fas fa-video text-[#13416B]"></i> Sisipkan Video</h3>
                <button type="button" id="btn-close-video" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Sumber Video:</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex-1 hover:bg-slate-100 transition">
                            <input type="radio" name="video_source" value="upload" checked class="w-4 h-4 text-[#13416B] focus:ring-[#13416B]">
                            <span class="text-sm font-bold text-slate-700">Upload (.mp4)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex-1 hover:bg-slate-100 transition">
                            <input type="radio" name="video_source" value="link" class="w-4 h-4 text-[#13416B] focus:ring-[#13416B]">
                            <span class="text-sm font-bold text-slate-700">Link YouTube</span>
                        </label>
                    </div>
                </div>

                <div id="panel-upload" class="p-4 bg-slate-50 border border-slate-200 rounded-xl block transition-all">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Video (Internal)</label>
                    <input type="file" id="video-file-input" accept="video/mp4,video/webm,video/ogg" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-[#13416B]/10 file:text-[#13416B] hover:file:bg-[#13416B]/20">
                    <p class="text-[10px] font-medium text-slate-400 mt-2">Maksimal ukuran file: 100MB.</p>
                    
                    <div id="upload-progress-container" class="hidden mt-4">
                        <div class="flex justify-between text-xs font-bold text-slate-600 mb-1.5">
                            <span id="upload-progress-text">0 MB / 0 MB</span>
                            <span id="upload-progress-percent" class="text-[#13416B]">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div id="upload-progress-bar" class="bg-[#13416B] h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div id="panel-link" class="p-4 bg-slate-50 border border-slate-200 rounded-xl hidden transition-all">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Masukkan Tautan YouTube</label>
                    <input type="url" id="video-url-input" placeholder="https://youtube.com/..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="btn-cancel-video" class="px-5 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="button" id="btn-save-video" class="px-5 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors"><i class="fas fa-check mr-1.5"></i> Sisipkan</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
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
            const LineHeightStyle = new Parchment.Attributor.Style('lineHeight', 'line-height', { scope: Parchment.Scope.BLOCK, whitelist: ['1', '1.2', '1.5', '2.0', '2.5'] }); Quill.register(LineHeightStyle, true);
            const LetterSpacingStyle = new Parchment.Attributor.Style('letterSpacing', 'letter-spacing', { scope: Parchment.Scope.INLINE, whitelist: ['1px', '2px', '3px', '4px'] }); Quill.register(LetterSpacingStyle, true);

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
            icons['bold'] = '<strong class="text-[14px] font-sans">B</strong>';
            icons['italic'] = '<em class="text-[14px] font-serif">I</em>';
            icons['underline'] = '<u class="text-[14px] font-sans">U</u>';
            icons['strike'] = '<s class="text-[14px] font-sans">S</s>';
            icons['script']['sub'] = '<span class="text-[13px] font-sans">X<sub class="text-[10px]">2</sub></span>';
            icons['script']['super'] = '<span class="text-[13px] font-sans">X<sup class="text-[10px]">2</sup></span>';
            icons['link'] = '<i class="fas fa-link text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['image'] = '<i class="fas fa-image text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['video'] = '<i class="fas fa-video text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['blockquote'] = '<i class="fas fa-quote-right text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['code-block'] = '<i class="fas fa-code text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['formula'] = '<i class="fas fa-square-root-variable text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['clean'] = '<i class="fas fa-eraser text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['list']['ordered'] = '<i class="fas fa-list-ol text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['list']['bullet'] = '<i class="fas fa-list-ul text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['indent']['-1'] = '<i class="fas fa-outdent text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['indent']['+1'] = '<i class="fas fa-indent text-[12px] text-slate-500 ql-icon-custom"></i>';
            icons['header']['1'] = '<b class="text-[12px] font-sans">H1</b>';
            icons['header']['2'] = '<b class="text-[12px] font-sans">H2</b>';

            var quill = new Quill('#editor-container', {
                bounds: '#editor-container', // <--- PENTING: Mencegah modal/tooltip rumus terpotong ke luar editor
                modules: {
                    syntax: true,
                    blotFormatter: { specs: [ QuillBlotFormatter.ImageSpec, QuillBlotFormatter.IframeVideoSpec, VideoElementSpec ] },
                    toolbar: {
                        container: [
                            [{ 'font': ['arial', 'times-new-roman', 'calibri', 'verdana', 'courier'] }, { 'size': ['10px', '12px', '14px', '16px', '18px', '24px', '32px'] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'lineHeight': ['1', '1.2', '1.5', '2.0', '2.5'] }, { 'letterSpacing': ['1px', '2px', '3px', '4px'] }],
                            [{ 'align': [] }, { 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                            ['link', 'image', 'video'],
                            ['blockquote', 'code-block', 'formula', 'clean']
                        ],
                        handlers: {
                            'link': function() { var range = quill.getSelection(true); var text = range && range.length > 0 ? quill.getText(range.index, range.length) : ''; document.getElementById('link-text-input').value = text; document.getElementById('link-url-input').value = ''; const modal = document.getElementById('custom-link-modal'); modal.classList.remove('hidden'); modal.classList.add('flex'); setTimeout(() => document.getElementById('link-url-input').focus(), 100); window.quillLinkRange = range; },
                            'video': function() { var range = quill.getSelection(true); window.quillVideoRange = range; document.getElementById('video-url-input').value = ''; document.getElementById('video-file-input').value = ''; document.getElementById('upload-progress-container').classList.add('hidden'); document.getElementById('upload-progress-bar').style.width = '0%'; const modal = document.getElementById('custom-video-modal'); modal.classList.remove('hidden'); modal.classList.add('flex'); }
                        }
                    }
                },
                placeholder: 'Ketik dokumen / materi pembelajaran di sini...',
                theme: 'snow'
            });

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
                    else if(el.classList.contains('ql-lineHeight')) title = 'Jarak Antar Baris (Line Spacing)';
                    else if(el.classList.contains('ql-letterSpacing')) title = 'Spasi Huruf';
                    if(title) { el.setAttribute('data-tooltip', title); el.classList.add('quill-tooltip'); }
                });
            }, 500);

            function closeLinkModal() { document.getElementById('custom-link-modal').classList.add('hidden'); document.getElementById('custom-link-modal').classList.remove('flex'); }
            document.getElementById('btn-close-link').addEventListener('click', closeLinkModal); document.getElementById('btn-cancel-link').addEventListener('click', closeLinkModal);
            document.getElementById('btn-save-link').addEventListener('click', function() { var text = document.getElementById('link-text-input').value.trim(); var url = document.getElementById('link-url-input').value.trim(); var range = window.quillLinkRange; if (!url) return alert('URL wajib diisi!'); if (!/^https?:\/\//i.test(url)) url = 'https://' + url; if (!text) text = url; quill.focus(); if (range && range.length > 0) { quill.deleteText(range.index, range.length); quill.insertText(range.index, text, 'link', url); quill.setSelection(range.index + text.length); } else { var cursorPosition = range ? range.index : quill.getLength(); quill.insertText(cursorPosition, text, 'link', url); quill.setSelection(cursorPosition + text.length); } closeLinkModal(); });

            document.querySelectorAll('input[name="video_source"]').forEach(radio => { radio.addEventListener('change', function() { if (this.value === 'upload') { document.getElementById('panel-upload').classList.remove('hidden'); document.getElementById('panel-upload').classList.add('block'); document.getElementById('panel-link').classList.remove('block'); document.getElementById('panel-link').classList.add('hidden'); } else { document.getElementById('panel-link').classList.remove('hidden'); document.getElementById('panel-link').classList.add('block'); document.getElementById('panel-upload').classList.remove('block'); document.getElementById('panel-upload').classList.add('hidden'); } }); });

            function closeVideoModal() { document.getElementById('custom-video-modal').classList.add('hidden'); document.getElementById('custom-video-modal').classList.remove('flex'); }
            document.getElementById('btn-close-video').addEventListener('click', closeVideoModal); document.getElementById('btn-cancel-video').addEventListener('click', closeVideoModal);
            
            document.getElementById('btn-save-video').addEventListener('click', function() {
                var source = document.querySelector('input[name="video_source"]:checked').value; var range = window.quillVideoRange; var btn = this;
                if (source === 'upload') {
                    var fileInput = document.getElementById('video-file-input').files[0]; if (!fileInput) return alert('Silakan pilih file video terlebih dahulu!');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengunggah...'; btn.disabled = true; document.getElementById('upload-progress-container').classList.remove('hidden');
                    const formData = new FormData(); formData.append('video', fileInput); formData.append('_token', '{{ csrf_token() }}'); const xhr = new XMLHttpRequest(); xhr.open('POST', "{{ route('admin-pusat.management-course.upload-video-editor') }}", true); xhr.setRequestHeader('Accept', 'application/json'); 
                    xhr.upload.onprogress = function(e) { if (e.lengthComputable) { const percentComplete = (e.loaded / e.total) * 100; const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2); const totalMB = (e.total / (1024 * 1024)).toFixed(2); document.getElementById('upload-progress-text').innerText = `${loadedMB} MB / ${totalMB} MB`; document.getElementById('upload-progress-percent').innerText = `${Math.round(percentComplete)}%`; document.getElementById('upload-progress-bar').style.width = percentComplete + '%'; } };
                    xhr.onload = function() { if (xhr.status === 200 || xhr.status === 201) { try { const data = JSON.parse(xhr.responseText); if (data.success) { quill.insertEmbed(range ? range.index : quill.getLength(), 'video', data.url); quill.setSelection((range ? range.index : quill.getLength()) + 1); closeVideoModal(); } else alert('Gagal mengunggah video: ' + (data.message || 'Error')); } catch (error) { alert('Respon server tidak valid.'); } } else if (xhr.status === 422) { alert('Validasi Gagal.'); } else if (xhr.status === 413) { alert('Ukuran terlalu besar.'); } else alert('Kesalahan server (Error ' + xhr.status + ').'); btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan'; btn.disabled = false; document.getElementById('upload-progress-container').classList.add('hidden'); };
                    xhr.onerror = function() { alert('Gagal mengunggah.'); btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan'; btn.disabled = false; document.getElementById('upload-progress-container').classList.add('hidden'); }; xhr.send(formData);
                } else { var urlInput = document.getElementById('video-url-input').value.trim(); if (!urlInput) return alert('Masukkan Link URL!'); quill.insertEmbed(range ? range.index : quill.getLength(), 'video', urlInput); quill.setSelection((range ? range.index : quill.getLength()) + 1); closeVideoModal(); }
            });

            quill.on('text-change', function() {
                var contentText = document.getElementById('content_text');
                if (quill.getText().trim().length === 0 && !quill.root.innerHTML.includes('<img') && !quill.root.innerHTML.includes('<iframe') && !quill.root.innerHTML.includes('<video')) { contentText.value = ''; } else { contentText.value = quill.root.innerHTML; }
            });
        </script>
        
        <style>
            /* Custom Scrollbar untuk Modal Panduan */
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

            /* TOOLTIP UI */
            .quill-tooltip { position: relative; }
            .quill-tooltip:hover::after { content: attr(data-tooltip); position: absolute; bottom: -32px; left: 50%; transform: translateX(-50%); background-color: #1e293b; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; white-space: nowrap; z-index: 1000; pointer-events: none; opacity: 0; animation: fadeInTooltip 0.2s forwards; animation-delay: 0.3s; }
            .quill-tooltip:hover::before { content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); border-width: 5px; border-style: solid; border-color: transparent transparent #1e293b transparent; opacity: 0; animation: fadeInTooltip 0.2s forwards; animation-delay: 0.3s; }
            @keyframes fadeInTooltip { to { opacity: 1; } }

            /* ========================================================
               DESAIN UI TOOLBAR (Sesuai Panduan Border Kotak Rounded)
               ======================================================== */
            .ql-toolbar.ql-snow {
                border-radius: 0.75rem 0.75rem 0 0; border-color: #cbd5e1; background-color: #ffffff;
                padding: 12px 16px; display: flex; flex-wrap: wrap; gap: 8px 14px; border-bottom: 1px solid #cbd5e1;
            }
            .ql-toolbar.ql-snow .ql-formats { display: flex; align-items: center; gap: 6px; margin-right: 0 !important; }
            .ql-toolbar.ql-snow .ql-formats::after { content: ''; display: inline-block; width: 1px; height: 20px; background-color: #e2e8f0; margin-left: 8px; }
            .ql-toolbar.ql-snow .ql-formats:last-child::after { display: none; }
            .ql-toolbar.ql-snow button {
                border: 1px solid #cbd5e1 !important; border-radius: 6px !important; background-color: #ffffff !important;
                width: 28px !important; height: 28px !important; display: inline-flex !important; align-items: center !important;
                justify-content: center !important; color: #475569 !important; transition: all 0.2s ease; padding: 0 !important;
            }
            .ql-toolbar.ql-snow button svg { display: none !important; }
            .ql-toolbar.ql-snow button:hover { background-color: #f8fafc !important; border-color: #94a3b8 !important; color: #1e293b !important; }
            .ql-toolbar.ql-snow button:hover .ql-icon-custom { color: #1e293b !important; }
            .ql-toolbar.ql-snow button.ql-active { background-color: #eff6ff !important; border-color: #93c5fd !important; color: #1e3a8a !important; }
            .ql-toolbar.ql-snow button.ql-active .ql-icon-custom { color: #1e3a8a !important; }

            .ql-toolbar.ql-snow .ql-picker {
                border: 1px solid #cbd5e1 !important; border-radius: 6px !important; background-color: #ffffff !important;
                height: 28px !important; color: #475569 !important;
            }
            .ql-toolbar.ql-snow .ql-picker-label { padding: 0 8px !important; border: none !important; line-height: 26px !important; display: flex; align-items: center; justify-content: space-between; }
            .ql-toolbar.ql-snow .ql-picker-label::before { line-height: 26px !important; }
            .ql-toolbar.ql-snow .ql-picker:hover { border-color: #94a3b8 !important; }
            
            .ql-toolbar.ql-snow .ql-color, .ql-toolbar.ql-snow .ql-background { width: 36px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label, .ql-toolbar.ql-snow .ql-background .ql-picker-label { padding: 0 2px 0 6px !important; }
            .ql-toolbar.ql-snow .ql-color .ql-picker-label svg, .ql-toolbar.ql-snow .ql-background .ql-picker-label svg { width: 16px !important; height: 16px !important; display: block !important; margin-right: 2px; }

            /* LABEL DINAMIS: FONT SIZE & FAMILY */
            .ql-picker.ql-size { width: 60px !important; }
            .ql-picker.ql-size .ql-picker-item[data-value="10px"]::before { content: '10'; font-size: 10px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12'; font-size: 12px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14'; font-size: 14px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16'; font-size: 16px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18'; font-size: 18px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before { content: '24'; font-size: 24px !important;}
            .ql-picker.ql-size .ql-picker-item[data-value="32px"]::before { content: '32'; font-size: 32px !important;}
            .ql-picker.ql-size .ql-picker-item:not([data-value])::before { content: 'Bawaan'; }
            .ql-picker.ql-size .ql-picker-label[data-value="10px"]::before { content: '10'; }
            .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before { content: '12'; }
            .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before { content: '14'; }
            .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before { content: '16'; }
            .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before { content: '18'; }
            .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before { content: '24'; }
            .ql-picker.ql-size .ql-picker-label[data-value="32px"]::before { content: '32'; }
            .ql-picker.ql-size .ql-picker-label:not([data-value])::before { content: '14'; }

            .ql-picker.ql-font { width: 130px !important; }
            .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before { content: 'Arial'; font-family: 'Arial'; }
            .ql-picker.ql-font .ql-picker-item[data-value="times-new-roman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }
            .ql-picker.ql-font .ql-picker-item[data-value="calibri"]::before { content: 'Calibri'; font-family: 'Calibri', sans-serif; }
            .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before { content: 'Verdana'; font-family: 'Verdana'; }
            .ql-picker.ql-font .ql-picker-item[data-value="courier"]::before { content: 'Courier New'; font-family: 'Courier New', monospace; }
            .ql-picker.ql-font .ql-picker-item:not([data-value])::before { content: 'System Font'; }
            .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before { content: 'Arial'; font-family: 'Arial'; }
            .ql-picker.ql-font .ql-picker-label[data-value="times-new-roman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }
            .ql-picker.ql-font .ql-picker-label[data-value="calibri"]::before { content: 'Calibri'; font-family: 'Calibri', sans-serif; }
            .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before { content: 'Verdana'; font-family: 'Verdana'; }
            .ql-picker.ql-font .ql-picker-label[data-value="courier"]::before { content: 'Courier New'; font-family: 'Courier New', monospace; }
            .ql-picker.ql-font .ql-picker-label:not([data-value])::before { content: 'Arial'; }

            /* LABEL DINAMIS: JARAK BARIS & HURUF (TEKS/NORMAL) */
            .ql-picker.ql-lineHeight, .ql-picker.ql-letterSpacing { width: 175px !important; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1"]::before { content: 'Jarak Baris (Rapat)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.2"]::before { content: 'Jarak Baris (Normal)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.5"]::before { content: 'Jarak Baris (Sedang)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.0"]::before { content: 'Jarak Baris (Lebar)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.5"]::before { content: 'Jarak Baris (Sangat Lebar)'; }
            .ql-picker.ql-lineHeight .ql-picker-item:not([data-value])::before { content: 'Jarak Baris (Bawaan)'; }
            
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1"]::before { content: 'Jarak Baris (Rapat)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.2"]::before { content: 'Jarak Baris (Normal)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.5"]::before { content: 'Jarak Baris (Sedang)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.0"]::before { content: 'Jarak Baris (Lebar)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.5"]::before { content: 'Jarak Baris (Sangat Lebar)'; }
            .ql-picker.ql-lineHeight .ql-picker-label:not([data-value])::before { content: 'Jarak Baris (Normal)'; }

            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="1px"]::before { content: 'Jarak Huruf (Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="2px"]::before { content: 'Jarak Huruf (Lebih Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="3px"]::before { content: 'Jarak Huruf (Sangat Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="4px"]::before { content: 'Jarak Huruf (Ekstra Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item:not([data-value])::before { content: 'Jarak Huruf (Normal)'; }

            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="1px"]::before { content: 'Jarak Huruf (Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="2px"]::before { content: 'Jarak Huruf (Lebih Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="3px"]::before { content: 'Jarak Huruf (Sangat Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="4px"]::before { content: 'Jarak Huruf (Ekstra Renggang)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label:not([data-value])::before { content: 'Jarak Huruf (Normal)'; }

            /* STYLE EDITOR CONTAINER BAWAAN */
            .ql-container.ql-snow { border-radius: 0 0 0.75rem 0.75rem; border-color: #cbd5e1; min-height: 500px; font-family: inherit; font-size: 0.95rem; line-height: 1.7; }
            .ql-editor { min-height: 500px; padding: 24px; }
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