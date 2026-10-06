@php
    $initialFileType = 'document';
    $initialLinkUrl = '';
    $initialPreviewUrl = null;
    $initialVideoPreviewUrl = null;
    
    if ($library->external_link) {
        if (str_contains($library->external_link, 'youtube.com') || str_contains($library->external_link, 'youtu.be')) {
            $initialFileType = 'video';
        } else {
            $initialFileType = 'link';
        }
        $initialLinkUrl = $library->external_link;
    } elseif ($library->video_path) {
        $initialFileType = 'video';
        $initialVideoPreviewUrl = Storage::url($library->video_path);
    } elseif ($library->file_path) {
        $initialFileType = 'document';
        $initialPreviewUrl = Storage::url($library->file_path);
    }
@endphp
<x-dashboard::layouts.dashboard title="Edit Materi Perpustakaan - E-Learning">
    <div class="p-2 sm:p-6">
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1">
                <li class="inline-flex items-center">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                            </path>
                        </svg>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('admin-pusat.libraries.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Perpustakaan</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit Materi</span>
                    </div>
                </li>
            </ol>
        </nav>

        @if ($errors->any())
            <div class="mt-2 mb-4 bg-red-50 text-red-600 p-4 rounded-lg border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-base font-semibold text-slate-800">Edit Materi Perpustakaan</h2>
            </div>
            <div class="p-5">
                <form action="{{ route('admin-pusat.libraries.update', $library->id) }}" method="POST" enctype="multipart/form-data"
                    x-data="editLibraryForm()" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col lg:flex-row gap-8">
                        <div class="flex-1 space-y-5">
                            <div>
                                <label for="edit-title" class="block text-sm font-medium text-gray-700 mb-1">
                                    Judul Materi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="edit-title" name="title" required
                                    value="{{ old('title', $library->title) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    placeholder="Judul buku / dokumen">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="edit-category" class="block text-sm font-medium text-gray-700 mb-1">
                                        Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select id="edit-category" name="library_category_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($libraryCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('library_category_id', $library->library_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Gambar Sampul <span class="text-xs text-gray-500">(abaikan jika tidak ubah)</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        @if($library->cover_image)
                                            <img src="{{ $library->cover_image_url }}" alt="Current Cover" class="w-12 h-16 object-cover rounded border border-gray-200 shadow-sm">
                                        @endif
                                        <input type="file" name="cover_image" accept="image/*"
                                            class="flex-1 border border-gray-300 rounded-md p-1 text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="edit-desc" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                                <textarea id="edit-desc" name="description" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    placeholder="Deskripsi materi...">{{ old('description', $library->description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis File <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                        :class="fileType === 'document' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                        <input type="radio" name="file_type_ui" value="document" x-model="fileType" class="sr-only"
                                            @change="linkUrl = ''; videoPreviewUrl = null">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Dokumen
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                        :class="fileType === 'video' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                        <input type="radio" name="file_type_ui" value="video" x-model="fileType" class="sr-only"
                                            @change="previewUrl = null">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Video
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                        :class="fileType === 'link' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                        <input type="radio" name="file_type_ui" value="link" x-model="fileType" class="sr-only"
                                            @change="previewUrl = null; videoPreviewUrl = null">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        Link
                                    </label>
                                </div>
                            </div>

                            <div x-show="fileType === 'document'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    File Dokumen PDF <span class="text-xs text-gray-500">(max 20MB, abaikan jika tidak ubah)</span>
                                </label>
                                @if($library->file_path)
                                    <p class="text-xs text-indigo-600 mb-2 flex items-center gap-1 font-medium bg-indigo-50 px-3 py-2 rounded-md border border-indigo-100">
                                        <i class="fas fa-check-circle"></i> File saat ini: {{ basename($library->file_path) }}
                                    </p>
                                @endif
                                <input type="file" name="file_path" accept=".pdf" @change="handleFileChange($event)"
                                    class="w-full border border-gray-300 rounded-md p-1 text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                            </div>

                            <div x-show="fileType === 'video'" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Upload File Video <span class="text-xs text-gray-500">(mp4, webm, max 50MB, abaikan jika tidak ubah)</span>
                                    </label>
                                    @if($library->video_path)
                                        <p class="text-xs text-indigo-600 mb-2 flex items-center gap-1 font-medium bg-indigo-50 px-3 py-2 rounded-md border border-indigo-100">
                                            <i class="fas fa-check-circle"></i> Video saat ini: {{ basename($library->video_path) }}
                                        </p>
                                    @endif
                                    <input type="file" name="video_path" accept="video/mp4,video/webm" @change="handleVideoChange($event)"
                                        x-bind:disabled="fileType !== 'video'"
                                        class="w-full border border-gray-300 rounded-md p-1 text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                </div>
                                <div class="relative mt-4 mb-4">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="px-2 bg-white text-xs text-gray-400">Atau gunakan YouTube Link</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">URL YouTube</label>
                                    <input type="url" name="external_link_video" x-model="linkUrl"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                        placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                            </div>

                            <div x-show="fileType === 'link'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    URL Link Eksternal <span class="text-red-500">*</span>
                                </label>
                                <input type="url" name="external_link" x-model="linkUrl" x-bind:required="fileType === 'link'"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    placeholder="https://...">
                            </div>
                        </div>

                        <div class="lg:w-96 shrink-0">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preview Konten Saat Ini / Baru</label>
                            <div class="border border-gray-200 rounded-xl bg-gray-50 min-h-[350px] flex items-center justify-center overflow-hidden">
                                <template x-if="previewContent && previewContent.type === 'pdf'">
                                    <iframe :src="previewContent.url" class="w-full h-[400px] rounded-lg" frameborder="0"></iframe>
                                </template>
                                <template x-if="previewContent && previewContent.type === 'videofile'">
                                    <video :src="previewContent.url" class="w-full rounded-lg" controls></video>
                                </template>
                                <template x-if="previewContent && previewContent.type === 'youtube'">
                                    <iframe :src="previewContent.url" class="w-full h-[250px] rounded-lg" frameborder="0" allowfullscreen></iframe>
                                </template>
                                <template x-if="previewContent && previewContent.type === 'link'">
                                    <div class="p-6 text-center w-full">
                                        <svg class="mx-auto w-12 h-12 text-indigo-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        <p class="text-sm text-gray-500 mb-2">Link Eksternal:</p>
                                        <a :href="linkUrl" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline break-all" x-text="linkUrl"></a>
                                    </div>
                                </template>
                                <template x-if="!previewContent">
                                    <div class="text-center p-8">
                                        <svg class="mx-auto w-14 h-14 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <p class="text-sm text-gray-400">Preview konten akan muncul di sini</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-6 mt-6 border-t border-slate-200">
                        <a href="{{ route('admin-pusat.libraries.index') }}"
                            class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition text-sm">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm transition text-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editLibraryForm() {
            return {
                fileType: @json($initialFileType),
                previewUrl: @json($initialPreviewUrl),
                videoPreviewUrl: @json($initialVideoPreviewUrl),
                linkUrl: @json($initialLinkUrl),
                handleFileChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.previewUrl = URL.createObjectURL(file);
                    }
                },
                handleVideoChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.videoPreviewUrl = URL.createObjectURL(file);
                    } else {
                        this.videoPreviewUrl = null;
                    }
                },
                getYoutubeEmbedUrl(url) {
                    if (!url) return null;
                    let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
                    return match ? 'https://www.youtube.com/embed/' + match[1] : null;
                },
                get previewContent() {
                    if (this.fileType === 'document' && this.previewUrl) return { type: 'pdf', url: this.previewUrl };
                    if (this.fileType === 'video') {
                        if (this.videoPreviewUrl) return { type: 'videofile', url: this.videoPreviewUrl };
                        let embed = this.getYoutubeEmbedUrl(this.linkUrl);
                        if (embed) return { type: 'youtube', url: embed };
                    }
                    if (this.fileType === 'link' && this.linkUrl) {
                        return { type: 'link', url: this.linkUrl };
                    }
                    return null;
                }
            };
        }
    </script>
    @endpush
</x-dashboard::layouts.dashboard>
