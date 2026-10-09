<x-dashboard::layouts.dashboard title="Tambah Materi: {{ $section->name }}">
    <div class="p-2 sm:p-6 max-w-full mx-auto">
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-3 flex-wrap">
                <li>
                    <div class="flex items-center">
                        <a href="{{ route('admin-pusat.management-course.courses.index') }}"
                            class="ml-1 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors md:ml-2">
                            <i class="fas fa-home mr-2"></i> Daftar Course
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-300 text-xs mx-1"></i>
                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                            class="ml-1 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors md:ml-2">
                            {{ $course->name }}
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
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Bagian: <span
                            class="text-[#13416B]">{{ $section->name }}</span></p>
                </div>
                <!-- Tombol kembali dihapus sesuai permintaan -->
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

                <!-- Dokumen Lampiran -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Dokumen Lampiran <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="file" name="document" accept=".pdf,.doc,.docx"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#13416B]/10 file:text-[#13416B] hover:file:bg-[#13416B]/20 transition-all border border-slate-200 rounded-xl" />
                    <p class="text-[11px] font-medium text-slate-400 mt-1.5"><i
                            class="fas fa-info-circle mr-1"></i>Format yang diizinkan: PDF, DOC, DOCX (Max: 10MB)</p>
                </div>

                <!-- Rich Text Editor (Quill.js) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Isi Materi Pembelajaran <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="prose max-w-none bg-white">
                        <input type="hidden" name="content_text" id="content_text" value="{{ old('content_text') }}">
                        <div id="editor-container">{!! old('content_text') !!}</div>
                    </div>

                    <!-- KOTAK PANDUAN VIDEO -->
                    <div class="mt-4 p-4 bg-[#13416B]/5 border border-[#13416B]/10 rounded-xl flex items-start gap-3">
                        <i class="fas fa-lightbulb text-[#13416B] mt-0.5 text-lg"></i>
                        <div class="text-sm text-slate-600 leading-relaxed">
                            <p class="font-bold text-[#13416B] mb-1">Tips Memasukkan & Mengatur Video:</p>
                            <p>Gunakan ikon <i class="fas fa-video mx-1 text-slate-400"></i> pada toolbar di atas untuk
                                menyisipkan video ke dalam teks materi. Anda dapat mengklik video yang sudah disisipkan
                                di dalam editor untuk <b>mengubah ukuran (resize)</b> atau menggeser posisinya.</p>
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
                        <i class="fas fa-save"></i> Simpan Materi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL LINK -->
    <div id="custom-link-modal"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i
                        class="fas fa-link text-[#13416B]"></i> Tambahkan Tautan</h3>
                <button type="button" id="btn-close-link"
                    class="text-slate-400 hover:text-slate-600 transition-colors"><i
                        class="fas fa-times text-lg"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Teks yang ditampilkan</label>
                    <input type="text" id="link-text-input" placeholder="Contoh: Klik disini"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">URL / Tautan <span
                            class="text-red-500">*</span></label>
                    <input type="url" id="link-url-input" placeholder="https://..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" id="btn-cancel-link"
                    class="px-5 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="button" id="btn-save-link"
                    class="px-5 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors"><i
                        class="fas fa-check mr-1.5"></i> Simpan</button>
            </div>
        </div>
    </div>

    <!-- MODAL VIDEO BARU (RADIO BUTTON & PROGRESS BAR) -->
    <div id="custom-video-modal"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i
                        class="fas fa-video text-[#13416B]"></i> Sisipkan Video</h3>
                <button type="button" id="btn-close-video"
                    class="text-slate-400 hover:text-slate-600 transition-colors"><i
                        class="fas fa-times text-lg"></i></button>
            </div>

            <div class="space-y-4">
                <!-- Pilihan Sumber Video -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Sumber Video:</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <label
                            class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex-1 hover:bg-slate-100 transition">
                            <input type="radio" name="video_source" value="upload" checked
                                class="w-4 h-4 text-[#13416B] focus:ring-[#13416B]">
                            <span class="text-sm font-bold text-slate-700">Upload (.mp4)</span>
                        </label>
                        <label
                            class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex-1 hover:bg-slate-100 transition">
                            <input type="radio" name="video_source" value="link"
                                class="w-4 h-4 text-[#13416B] focus:ring-[#13416B]">
                            <span class="text-sm font-bold text-slate-700">Link YouTube</span>
                        </label>
                    </div>
                </div>

                <!-- Panel Upload Video Internal -->
                <div id="panel-upload"
                    class="p-4 bg-slate-50 border border-slate-200 rounded-xl block transition-all">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Video (Internal)</label>
                    <input type="file" id="video-file-input" accept="video/mp4,video/webm,video/ogg"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-[#13416B]/10 file:text-[#13416B] hover:file:bg-[#13416B]/20">
                    <p class="text-[10px] font-medium text-slate-400 mt-2">Maksimal ukuran file: 100MB.</p>

                    <!-- Progress Bar Component -->
                    <div id="upload-progress-container" class="hidden mt-4">
                        <div class="flex justify-between text-xs font-bold text-slate-600 mb-1.5">
                            <span id="upload-progress-text">0 MB / 0 MB</span>
                            <span id="upload-progress-percent" class="text-[#13416B]">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div id="upload-progress-bar"
                                class="bg-[#13416B] h-2.5 rounded-full transition-all duration-300" style="width: 0%">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Link Video Eksternal -->
                <div id="panel-link" class="p-4 bg-slate-50 border border-slate-200 rounded-xl hidden transition-all">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Masukkan Tautan YouTube</label>
                    <input type="url" id="video-url-input" placeholder="https://youtube.com/..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B]">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="btn-cancel-video"
                    class="px-5 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="button" id="btn-save-video"
                    class="px-5 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-xl transition-colors"><i
                        class="fas fa-check mr-1.5"></i> Sisipkan</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill-blot-formatter@1.0.5/dist/quill-blot-formatter.min.js"></script>

        <script>
            Quill.register('modules/blotFormatter', QuillBlotFormatter.default);

            // CUSTOM SPEC UNTUK MENGIZINKAN RESIZE PADA TAG <VIDEO> INTERNAL
            class VideoElementSpec extends QuillBlotFormatter.BlotSpec {
                constructor(formatter) {
                    super(formatter);
                    this.targetElement = null;
                }
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
                getTargetElement() {
                    return this.targetElement;
                }
            }

            // CUSTOM IMAGE FORMAT
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

           // CUSTOM VIDEO (HTML5 VIDEO SUPPORT & YOUTUBE IFRAME)
            const BlockEmbed = Quill.import('blots/block/embed');
            class CustomVideo extends BlockEmbed {
                static create(value) {
                    let isMp4 = value.match(/\.(mp4|webm|ogg)$/i);
                    let node = document.createElement(isMp4 ? 'video' : 'iframe');
                    
                    if (isMp4) {
                        node.setAttribute('controls', '');
                        node.setAttribute('controlsList', 'nodownload');
                        node.setAttribute('src', value);
                        // PERBAIKAN 1: Menghapus 'width: 100%;' dari style agar bisa dikecilkan
                        node.setAttribute('style', 'max-width: 100%; display: block; margin: auto; border-radius: 0.5rem;');
                    } else {
                        node.setAttribute('frameborder', '0');
                        node.setAttribute('allowfullscreen', true);
                        node.setAttribute('src', value);
                        // PERBAIKAN 2: Menggunakan atribut width dan height awal untuk iframe, dan menghapus width: 100%
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

            hljs.configure({
                languages: ['javascript', 'php', 'html', 'css', 'python', 'java', 'sql', 'bash']
            });

            var quill = new Quill('#editor-container', {
                modules: {
                    syntax: true,
                    blotFormatter: {
                        specs: [
                            QuillBlotFormatter.ImageSpec,
                            QuillBlotFormatter.IframeVideoSpec,
                            VideoElementSpec // Daftarkan spec custom kita disini!
                        ]
                    },
                    toolbar: {
                        container: [
                            [{
                                'size': ['small', false, 'large', 'huge']
                            }],
                            [{
                                'header': [1, 2, 3, false]
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                'color': []
                            }, {
                                'background': []
                            }],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'indent': '-1'
                            }, {
                                'indent': '+1'
                            }, {
                                'align': []
                            }],
                            ['blockquote', 'code-block', 'formula'],
                            ['link', 'image', 'video'],
                            ['clean']
                        ],
                        handlers: {
                            'link': function() {
                                var range = quill.getSelection(true);
                                var text = range && range.length > 0 ? quill.getText(range.index, range.length) :
                                '';
                                document.getElementById('link-text-input').value = text;
                                document.getElementById('link-url-input').value = '';
                                const modal = document.getElementById('custom-link-modal');
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                                setTimeout(() => document.getElementById('link-url-input').focus(), 100);
                                window.quillLinkRange = range;
                            },
                            'video': function() {
                                var range = quill.getSelection(true);
                                window.quillVideoRange = range;

                                // Reset form upload
                                document.getElementById('video-url-input').value = '';
                                document.getElementById('video-file-input').value = '';
                                document.getElementById('upload-progress-container').classList.add('hidden');
                                document.getElementById('upload-progress-bar').style.width = '0%';

                                const modal = document.getElementById('custom-video-modal');
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                            }
                        }
                    }
                },
                placeholder: 'Tuliskan materi pembelajaran di sini...',
                theme: 'snow'
            });

            // LOGIKA LINK
            function closeLinkModal() {
                document.getElementById('custom-link-modal').classList.add('hidden');
                document.getElementById('custom-link-modal').classList.remove('flex');
            }
            document.getElementById('btn-close-link').addEventListener('click', closeLinkModal);
            document.getElementById('btn-cancel-link').addEventListener('click', closeLinkModal);
            document.getElementById('btn-save-link').addEventListener('click', function() {
                var text = document.getElementById('link-text-input').value.trim();
                var url = document.getElementById('link-url-input').value.trim();
                var range = window.quillLinkRange;
                if (!url) return alert('URL wajib diisi!');
                if (!/^https?:\/\//i.test(url)) url = 'https://' + url;
                if (!text) text = url;
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

            // LOGIKA RADIO BUTTON VIDEO
            document.querySelectorAll('input[name="video_source"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'upload') {
                        document.getElementById('panel-upload').classList.remove('hidden');
                        document.getElementById('panel-upload').classList.add('block');
                        document.getElementById('panel-link').classList.remove('block');
                        document.getElementById('panel-link').classList.add('hidden');
                    } else {
                        document.getElementById('panel-link').classList.remove('hidden');
                        document.getElementById('panel-link').classList.add('block');
                        document.getElementById('panel-upload').classList.remove('block');
                        document.getElementById('panel-upload').classList.add('hidden');
                    }
                });
            });

            // LOGIKA UPLOAD VIDEO DENGAN PROGRESS BAR (XHR)
            function closeVideoModal() {
                document.getElementById('custom-video-modal').classList.add('hidden');
                document.getElementById('custom-video-modal').classList.remove('flex');
            }
            document.getElementById('btn-close-video').addEventListener('click', closeVideoModal);
            document.getElementById('btn-cancel-video').addEventListener('click', closeVideoModal);

            document.getElementById('btn-save-video').addEventListener('click', function() {
                var source = document.querySelector('input[name="video_source"]:checked').value;
                var range = window.quillVideoRange;
                var btn = this;

                // JIKA PILIH UPLOAD INTERNAL
                if (source === 'upload') {
                    var fileInput = document.getElementById('video-file-input').files[0];
                    if (!fileInput) return alert('Silakan pilih file video terlebih dahulu!');

                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengunggah...';
                    btn.disabled = true;

                    // Munculkan progress bar
                    document.getElementById('upload-progress-container').classList.remove('hidden');

                    const formData = new FormData();
                    formData.append('video', fileInput);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "{{ route('admin-pusat.management-course.upload-video-editor') }}", true);

                    // 1. TAMBAHKAN KODE INI AGAR LARAVEL TAHU INI ADALAH AJAX / JSON
                    xhr.setRequestHeader('Accept', 'application/json');

                    // Track Progress
                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                            const totalMB = (e.total / (1024 * 1024)).toFixed(2);

                            document.getElementById('upload-progress-text').innerText =
                                `${loadedMB} MB / ${totalMB} MB`;
                            document.getElementById('upload-progress-percent').innerText =
                                `${Math.round(percentComplete)}%`;
                            document.getElementById('upload-progress-bar').style.width = percentComplete + '%';
                        }
                    };

                    // On Success & Error Handling yang Diperbarui
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
                                alert(
                                'Respon server tidak valid. Pastikan endpoint controller mengembalikan JSON.');
                            }
                        }
                        // 2. TANGKAP ERROR VALIDASI DARI LARAVEL (Misal file > 100MB atau bukan mp4)
                        else if (xhr.status === 422) {
                            const errorData = JSON.parse(xhr.responseText);
                            alert('Validasi Gagal: ' + errorData.message);
                        }
                        // 3. TANGKAP ERROR FILE TERLALU BESAR DARI SERVER (php.ini)
                        else if (xhr.status === 413) {
                            alert('Gagal: Ukuran video terlalu besar melebihi kapasitas server (post_max_size).');
                        } else {
                            alert('Terjadi kesalahan server (Error ' + xhr.status + '). Coba periksa file Anda.');
                        }

                        // Reset button
                        btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan';
                        btn.disabled = false;
                        document.getElementById('upload-progress-container').classList.add('hidden');
                    };

                    xhr.onerror = function() {
                        alert('Terjadi kesalahan jaringan atau server tidak merespon.');
                        btn.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sisipkan';
                        btn.disabled = false;
                        document.getElementById('upload-progress-container').classList.add('hidden');
                    };

                    xhr.send(formData);
                }
                // JIKA PILIH LINK YOUTUBE
                else {
                    var urlInput = document.getElementById('video-url-input').value.trim();
                    if (!urlInput) return alert('Masukkan Link URL YouTube!');

                    quill.insertEmbed(range ? range.index : quill.getLength(), 'video', urlInput);
                    quill.setSelection((range ? range.index : quill.getLength()) + 1);
                    closeVideoModal();
                }
            });

            // SYNC VALUE UNTUK DIKIRIM KE BACKEND
            quill.on('text-change', function() {
                var contentText = document.getElementById('content_text');
                if (quill.getText().trim().length === 0 && !quill.root.innerHTML.includes('<img') && !quill.root
                    .innerHTML.includes('<iframe') && !quill.root.innerHTML.includes('<video')) {
                    contentText.value = '';
                } else {
                    contentText.value = quill.root.innerHTML;
                }
            });
        </script>
        <style>
            .ql-toolbar.ql-snow {
                border-radius: 0.75rem 0.75rem 0 0;
                border-color: #cbd5e1;
                background-color: #f8fafc;
                padding: 12px;
            }

            .ql-container.ql-snow {
                border-radius: 0 0 0.75rem 0.75rem;
                border-color: #cbd5e1;
                min-height: 500px;
                font-family: inherit;
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .ql-editor {
                min-height: 500px;
                padding: 20px;
            }

            .ql-editor pre.ql-syntax {
                background-color: #0f172a;
                color: #e2e8f0;
                padding: 1.25rem;
                border-radius: 0.5rem;
            }
        </style>
    @endpush
</x-dashboard::layouts.dashboard>
