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

                <!-- Rich Text Editor (Word-like Experience) -->
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <label class="block text-sm font-bold text-slate-700">
                            Isi Materi Pembelajaran <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                    </div>
                    <!-- Quill Container -->
                    <div class="word-like-editor-wrapper bg-slate-100 rounded-xl overflow-hidden border border-slate-300">
                        <input type="hidden" name="content_text" id="content_text" value="{{ old('content_text') }}">
                        <div id="editor-container">{!! old('content_text') !!}</div>
                    </div>

                    <!-- KOTAK PANDUAN -->
                    <div class="mt-3 flex items-start gap-2 text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100">
                        <i class="fas fa-info-circle text-[#13416B] mt-0.5"></i>
                        <p><b>Tips MS Word Mode:</b> Editor ini didesain layaknya MS Word. Teks yang Anda ketik di area "kertas putih" akan menyesuaikan bentuk dan jarak aslinya. Sorot ikon di atas untuk melihat panduan fungsinya.</p>
                    </div>
                </div>

                <!-- Dokumen Lampiran -->
                <div class="pt-2">
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
            Quill.register('modules/blotFormatter', QuillBlotFormatter.default);

            const Parchment = Quill.import('parchment');
            
            // CUSTOM FONT (Seperti Word)
            const Font = Quill.import('formats/font');
            Font.whitelist = ['arial', 'times-new-roman', 'calibri', 'verdana', 'courier'];
            Quill.register(Font, true);

            // CUSTOM FONT SIZE (Angka seperti Word)
            const Size = Quill.import('attributors/style/size');
            Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '24px', '32px'];
            Quill.register(Size, true);

            // CUSTOM LINE HEIGHT
            const LineHeightStyle = new Parchment.Attributor.Style('lineHeight', 'line-height', {
                scope: Parchment.Scope.BLOCK,
                whitelist: ['1', '1.2', '1.5', '2.0', '2.5']
            });
            Quill.register(LineHeightStyle, true);

            // CUSTOM LETTER SPACING
            const LetterSpacingStyle = new Parchment.Attributor.Style('letterSpacing', 'letter-spacing', {
                scope: Parchment.Scope.INLINE,
                whitelist: ['1px', '2px', '3px', '4px']
            });
            Quill.register(LetterSpacingStyle, true);

            // CUSTOM VIDEO & IMAGE SPEC (Supaya bisa diresize)
            class VideoElementSpec extends QuillBlotFormatter.BlotSpec {
                constructor(formatter) { super(formatter); this.targetElement = null; }
                init() {
                    this.formatter.quill.root.addEventListener('click', (event) => {
                        const el = event.target;
                        if (el.tagName === 'VIDEO') { this.targetElement = el; this.formatter.show(this); }
                    });
                    this.formatter.quill.on('text-change', () => {
                        if (this.targetElement && !this.formatter.quill.root.contains(this.targetElement)) this.formatter.hide();
                    });
                }
                getTargetElement() { return this.targetElement; }
            }
            
            const BaseImage = Quill.import('formats/image');
            class CustomImage extends BaseImage {
                static formats(domNode) {
                    return ['style', 'width', 'height'].reduce(function(formats, attribute) {
                        if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute); return formats;
                    }, {});
                }
                format(name, value) {
                    if (['style', 'width', 'height'].includes(name)) {
                        if (value) this.domNode.setAttribute(name, value); else this.domNode.removeAttribute(name);
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
                        node.setAttribute('controls', ''); node.setAttribute('controlsList', 'nodownload');
                        node.setAttribute('src', value); node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;');
                    } else {
                        node.setAttribute('frameborder', '0'); node.setAttribute('allowfullscreen', true);
                        node.setAttribute('src', value); node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;');
                        node.setAttribute('width', '560'); node.setAttribute('height', '315');
                    }
                    return node;
                }
                static value(node) { return node.getAttribute('src'); }
                static formats(domNode) {
                    return ['style', 'width', 'height'].reduce(function(formats, attribute) {
                        if (domNode.hasAttribute(attribute)) formats[attribute] = domNode.getAttribute(attribute); return formats;
                    }, {});
                }
                format(name, value) {
                    if (['style', 'width', 'height'].includes(name)) {
                        if (value) this.domNode.setAttribute(name, value); else this.domNode.removeAttribute(name);
                    } else super.format(name, value);
                }
            }
            CustomVideo.blotName = 'video';
            CustomVideo.tagName = ['IFRAME', 'VIDEO'];
            Quill.register(CustomVideo, true);

            hljs.configure({ languages: ['javascript', 'php', 'html', 'css', 'python', 'java', 'sql', 'bash'] });

            // INISIALISASI QUILL (WORD MODE)
            var quill = new Quill('#editor-container', {
                modules: {
                    syntax: true,
                    blotFormatter: { specs: [ QuillBlotFormatter.ImageSpec, QuillBlotFormatter.IframeVideoSpec, VideoElementSpec ] },
                    toolbar: {
                        container: [
                            // Grup 1: Text Styles (Font, Size, Header)
                            [{ 'font': ['arial', 'times-new-roman', 'calibri', 'verdana', 'courier'] }, { 'size': ['10px', '12px', '14px', '16px', '18px', '24px', '32px'] }],
                            [{ 'header': [1, 2, 3, 4, false] }],
                            
                            // Grup 2: Format & Color
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'color': [] }, { 'background': [] }],
                            
                            // Grup 3: Paragraf & Spasi
                            [{ 'align': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                            [{ 'lineHeight': ['1', '1.2', '1.5', '2.0', '2.5'] }, { 'letterSpacing': ['1px', '2px', '3px', '4px'] }],
                            
                            // Grup 4: Media & Insert
                            ['blockquote', 'code-block', 'formula'],
                            ['link', 'image', 'video'],
                            ['clean']
                        ],
                        handlers: {
                            'link': function() {
                                var range = quill.getSelection(true);
                                var text = range && range.length > 0 ? quill.getText(range.index, range.length) : '';
                                document.getElementById('link-text-input').value = text;
                                document.getElementById('link-url-input').value = '';
                                const modal = document.getElementById('custom-link-modal');
                                modal.classList.remove('hidden'); modal.classList.add('flex');
                                setTimeout(() => document.getElementById('link-url-input').focus(), 100);
                                window.quillLinkRange = range;
                            },
                            'video': function() {
                                var range = quill.getSelection(true);
                                window.quillVideoRange = range;
                                document.getElementById('video-url-input').value = ''; document.getElementById('video-file-input').value = '';
                                document.getElementById('upload-progress-container').classList.add('hidden'); document.getElementById('upload-progress-bar').style.width = '0%';
                                const modal = document.getElementById('custom-video-modal');
                                modal.classList.remove('hidden'); modal.classList.add('flex');
                            }
                        }
                    }
                },
                placeholder: 'Ketik dokumen / materi pembelajaran di sini...',
                theme: 'snow'
            });

            // MENGUBAH TAMPILAN ICON SUPAYA MEMILIKI TOOLTIP
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
                    else if(el.classList.contains('ql-header')) title = 'Judul / Heading';
                    else if(el.classList.contains('ql-color')) title = 'Warna Teks';
                    else if(el.classList.contains('ql-background')) title = 'Warna Sorotan (Highlight)';
                    else if(el.classList.contains('ql-align')) title = 'Rata Teks (Alignment)';
                    else if(el.classList.contains('ql-lineHeight')) title = 'Jarak Antar Baris (Line Spacing)';
                    else if(el.classList.contains('ql-letterSpacing')) title = 'Spasi Huruf';

                    if(title) {
                        el.setAttribute('data-tooltip', title);
                        el.classList.add('quill-tooltip');
                    }
                });
            }, 500);

            // LOGIKA MODAL LINK & VIDEO (SAMA SEPERTI SEBELUMNYA)
            function closeLinkModal() { document.getElementById('custom-link-modal').classList.add('hidden'); document.getElementById('custom-link-modal').classList.remove('flex'); }
            document.getElementById('btn-close-link').addEventListener('click', closeLinkModal); document.getElementById('btn-cancel-link').addEventListener('click', closeLinkModal);
            document.getElementById('btn-save-link').addEventListener('click', function() {
                var text = document.getElementById('link-text-input').value.trim(); var url = document.getElementById('link-url-input').value.trim(); var range = window.quillLinkRange;
                if (!url) return alert('URL wajib diisi!'); if (!/^https?:\/\//i.test(url)) url = 'https://' + url; if (!text) text = url;
                quill.focus();
                if (range && range.length > 0) { quill.deleteText(range.index, range.length); quill.insertText(range.index, text, 'link', url); quill.setSelection(range.index + text.length); } 
                else { var cursorPosition = range ? range.index : quill.getLength(); quill.insertText(cursorPosition, text, 'link', url); quill.setSelection(cursorPosition + text.length); }
                closeLinkModal();
            });

            document.querySelectorAll('input[name="video_source"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'upload') {
                        document.getElementById('panel-upload').classList.remove('hidden'); document.getElementById('panel-upload').classList.add('block');
                        document.getElementById('panel-link').classList.remove('block'); document.getElementById('panel-link').classList.add('hidden');
                    } else {
                        document.getElementById('panel-link').classList.remove('hidden'); document.getElementById('panel-link').classList.add('block');
                        document.getElementById('panel-upload').classList.remove('block'); document.getElementById('panel-upload').classList.add('hidden');
                    }
                });
            });

            function closeVideoModal() { document.getElementById('custom-video-modal').classList.add('hidden'); document.getElementById('custom-video-modal').classList.remove('flex'); }
            document.getElementById('btn-close-video').addEventListener('click', closeVideoModal); document.getElementById('btn-cancel-video').addEventListener('click', closeVideoModal);
            
            document.getElementById('btn-save-video').addEventListener('click', function() {
                var source = document.querySelector('input[name="video_source"]:checked').value;
                var range = window.quillVideoRange; var btn = this;
                if (source === 'upload') {
                    var fileInput = document.getElementById('video-file-input').files[0];
                    if (!fileInput) return alert('Silakan pilih file video terlebih dahulu!');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengunggah...'; btn.disabled = true;
                    document.getElementById('upload-progress-container').classList.remove('hidden');
                    const formData = new FormData(); formData.append('video', fileInput); formData.append('_token', '{{ csrf_token() }}'); 
                    const xhr = new XMLHttpRequest(); xhr.open('POST', "{{ route('admin-pusat.management-course.upload-video-editor') }}", true); xhr.setRequestHeader('Accept', 'application/json'); 
                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100; const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2); const totalMB = (e.total / (1024 * 1024)).toFixed(2);
                            document.getElementById('upload-progress-text').innerText = `${loadedMB} MB / ${totalMB} MB`; document.getElementById('upload-progress-percent').innerText = `${Math.round(percentComplete)}%`; document.getElementById('upload-progress-bar').style.width = percentComplete + '%';
                        }
                    };
                    xhr.onload = function() {
                        if (xhr.status === 200 || xhr.status === 201) {
                            try { const data = JSON.parse(xhr.responseText); if (data.success) { quill.insertEmbed(range ? range.index : quill.getLength(), 'video', data.url); quill.setSelection((range ? range.index : quill.getLength()) + 1); closeVideoModal(); } else alert('Gagal mengunggah video: ' + (data.message || 'Error')); } catch (error) { alert('Respon server tidak valid.'); }
                        } else if (xhr.status === 422) { alert('Validasi Gagal.'); } else if (xhr.status === 413) { alert('Ukuran terlalu besar.'); } else alert('Kesalahan server (Error ' + xhr.status + ').');
                        btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan'; btn.disabled = false; document.getElementById('upload-progress-container').classList.add('hidden');
                    };
                    xhr.onerror = function() { alert('Gagal mengunggah.'); btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan'; btn.disabled = false; document.getElementById('upload-progress-container').classList.add('hidden'); };
                    xhr.send(formData);
                } else {
                    var urlInput = document.getElementById('video-url-input').value.trim(); if (!urlInput) return alert('Masukkan Link URL!');
                    quill.insertEmbed(range ? range.index : quill.getLength(), 'video', urlInput); quill.setSelection((range ? range.index : quill.getLength()) + 1); closeVideoModal();
                }
            });

            quill.on('text-change', function() {
                var contentText = document.getElementById('content_text');
                if (quill.getText().trim().length === 0 && !quill.root.innerHTML.includes('<img') && !quill.root.innerHTML.includes('<iframe') && !quill.root.innerHTML.includes('<video')) { contentText.value = ''; } 
                else { contentText.value = quill.root.innerHTML; }
            });
        </script>
        
        <style>
            /* ====== UI/UX MS WORD MODE ====== */
            
            /* Container utama: Meja abu-abu */
            .word-like-editor-wrapper {
                background-color: #f1f5f9; /* Meja / Latar belakang luar */
                border-radius: 0.5rem;
                display: flex;
                flex-direction: column;
            }

            /* Toolbar MS Word (di atas, lengket) */
            .ql-toolbar.ql-snow {
                background-color: #f8fafc;
                border: none !important;
                border-bottom: 1px solid #cbd5e1 !important;
                padding: 12px 16px;
                position: sticky;
                top: 0;
                z-index: 40;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            /* Grup-grup ikon di Toolbar agar terlihat rapi (divider bayangan) */
            .ql-formats {
                border-right: 1px solid #e2e8f0;
                padding-right: 8px;
                margin-right: 4px !important;
            }
            .ql-formats:last-child { border-right: none; }

            /* Scroll area meja abu-abu */
            .ql-container.ql-snow {
                border: none !important;
                height: 600px; /* Ketinggian ruang kerja */
                overflow-y: auto;
                padding: 2rem 1rem;
                font-family: 'Arial', sans-serif;
            }

            /* Kertas A4 Putih di Tengah */
            .ql-editor {
                background-color: #ffffff;
                width: 100%;
                max-width: 800px; /* Ukuran kertas rata-rata */
                min-height: 1100px; /* A4 aspect ratio (Panjang ke bawah) */
                margin: 0 auto;
                padding: 2.5cm 2cm; /* Margin dalam kertas layaknya Word */
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
                border: 1px solid #cbd5e1;
                font-size: 14px;
                line-height: 1.5;
            }

            /* Responsif Kertas Putih untuk Layar Kecil */
            @media (max-width: 768px) {
                .ql-editor {
                    padding: 1rem;
                    min-height: 500px;
                }
                .ql-container.ql-snow { padding: 1rem 0.5rem; }
            }

            /* TOOLTIP UI */
            .quill-tooltip { position: relative; }
            .quill-tooltip:hover::after {
                content: attr(data-tooltip); position: absolute; bottom: -32px; left: 50%; transform: translateX(-50%);
                background-color: #1e293b; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; white-space: nowrap; z-index: 1000; pointer-events: none; opacity: 0; animation: fadeInTooltip 0.2s forwards; animation-delay: 0.3s;
            }
            .quill-tooltip:hover::before {
                content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); border-width: 5px; border-style: solid; border-color: transparent transparent #1e293b transparent; opacity: 0; animation: fadeInTooltip 0.2s forwards; animation-delay: 0.3s;
            }
            @keyframes fadeInTooltip { to { opacity: 1; } }


            /* LABEL DINAMIS: FONT SIZE (UKURAN ANGKA) */
            .ql-picker.ql-size { width: 70px !important; }
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

            /* LABEL DINAMIS: FONT FAMILY */
            .ql-picker.ql-font { width: 140px !important; }
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
            .ql-picker.ql-font .ql-picker-label:not([data-value])::before { content: 'Arial (Utama)'; }

            /* LABEL DINAMIS: JARAK BARIS & HURUF */
            .ql-picker.ql-lineHeight, .ql-picker.ql-letterSpacing { width: 130px !important; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1"]::before { content: 'Rapat (1.0)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.2"]::before { content: 'Biasa (1.2)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="1.5"]::before { content: 'Longgar (1.5)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.0"]::before { content: 'Ganda (2.0)'; }
            .ql-picker.ql-lineHeight .ql-picker-item[data-value="2.5"]::before { content: 'Ekstra (2.5)'; }
            .ql-picker.ql-lineHeight .ql-picker-item:not([data-value])::before { content: 'Bawaan (Normal)'; }

            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1"]::before { content: 'Rapat (1.0)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.2"]::before { content: 'Biasa (1.2)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="1.5"]::before { content: 'Longgar (1.5)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.0"]::before { content: 'Ganda (2.0)'; }
            .ql-picker.ql-lineHeight .ql-picker-label[data-value="2.5"]::before { content: 'Ekstra (2.5)'; }
            .ql-picker.ql-lineHeight .ql-picker-label:not([data-value])::before { content: 'Jarak Baris'; }

            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="1px"]::before { content: 'Lebar (+1)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="2px"]::before { content: 'Lebar (+2)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="3px"]::before { content: 'Ekstra (+3)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item[data-value="4px"]::before { content: 'Ekstra (+4)'; }
            .ql-picker.ql-letterSpacing .ql-picker-item:not([data-value])::before { content: 'Bawaan (Normal)'; }

            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="1px"]::before { content: 'Lebar (+1)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="2px"]::before { content: 'Lebar (+2)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="3px"]::before { content: 'Ekstra (+3)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label[data-value="4px"]::before { content: 'Ekstra (+4)'; }
            .ql-picker.ql-letterSpacing .ql-picker-label:not([data-value])::before { content: 'Spasi Huruf'; }

            /* Supaya blok code (kode HTML) tetap berwarna gelap saat Word Mode */
            .ql-editor pre.ql-syntax { background-color: #0f172a; color: #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; }
        </style>
    @endpush
</x-dashboard::layouts.dashboard>