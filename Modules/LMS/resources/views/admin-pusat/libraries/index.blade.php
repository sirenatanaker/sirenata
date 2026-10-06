<x-dashboard::layouts.dashboard title="Perpustakaan - E-Learning">
    <div class="p-2 sm:p-6">

        <x-breadcrumb :home="route('admin-pusat.dashboard')" :items="[['label' => 'Perpustakaan']]" />

        <x-validation-errors />

        <x-dashboard::filter-card 
            title="Daftar Materi Perpustakaan" 
            :total="$libraries->total() . ' Materi'"
            :resetUrl="route('admin-pusat.libraries.index')">
            
            <x-slot name="actions">
                <x-button type="button" x-data @click="$dispatch('open-modal', 'create-library')" icon="fas fa-plus">
                    <span class="hidden sm:inline">Tambah Materi</span>
                    <span class="sm:hidden">Tambah</span>
                </x-button>
            </x-slot>

            <x-slot name="filter_inputs">
                <!-- Kategori -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Filter Kategori
                    </label>
                    <div class="relative">
                        <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <select name="library_category_id" class="pl-9 pr-3 py-2.5 w-full rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Kategori</option>
                            @foreach($libraryCategories as $category)
                                <option value="{{ $category->id }}" {{ ($libraryCategoryId ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Per Page -->
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Data per Halaman
                    </label>
                    <select name="per_page" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ([10, 20, 50, 100] as $page)
                            <option value="{{ $page }}" {{ request('per_page') == $page ? 'selected' : '' }}>{{ $page }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[240px] w-full">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Pencarian
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul materi..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </x-slot>

            <x-table.table plain>
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-slate-500 uppercase text-xs">
                        <x-table.th>No.</x-table.th>
                        <x-table.th>Sampul</x-table.th>
                        <x-table.th>Judul</x-table.th>
                        <x-table.th>Kategori</x-table.th>
                        <x-table.th>Lampiran</x-table.th>
                        <x-table.th align="center">Aksi</x-table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($libraries as $key => $library)
                        <tr class="hover:bg-slate-50 transition">
                            <x-table.td><p class="text-slate-600">{{ $key + $libraries->firstItem() }}</p></x-table.td>
                            <x-table.td>
                                @if($library->cover_image)
                                    <img src="{{ $library->cover_image_url }}" alt="Cover" class="w-10 h-14 object-cover rounded shadow-sm">
                                @else
                                    <div class="w-10 h-14 bg-slate-200 rounded flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </x-table.td>
                            <x-table.td>
                                <p class="text-slate-600 font-medium">{{ $library->title }}</p>
                                @if($library->description)
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $library->description }}</p>
                                @endif
                            </x-table.td>
                            <x-table.td>
                                <x-badge color="slate">{{ $library->libraryCategory->name ?? '-' }}</x-badge>
                            </x-table.td>
                            <x-table.td>
                                <div class="flex flex-col gap-1">
                                    @if($library->file_path)
                                        <span class="inline-flex items-center text-xs text-blue-600 cursor-pointer hover:underline" @click="$dispatch('open-modal', 'preview-modal'); $dispatch('open-preview', { url: '{{ Storage::url($library->file_path) }}', type: 'pdf' })">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            File PDF
                                        </span>
                                    @endif
                                    @if($library->video_path)
                                        <span class="inline-flex items-center text-xs text-purple-600 cursor-pointer hover:underline" @click="$dispatch('open-modal', 'preview-modal'); $dispatch('open-preview', { url: '{{ Storage::url($library->video_path) }}', type: 'video' })">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            File Video
                                        </span>
                                    @endif
                                    @if($library->external_link)
                                        @php
                                            $isYoutube = str_contains($library->external_link, 'youtube.com') || str_contains($library->external_link, 'youtu.be');
                                        @endphp
                                        @if($isYoutube)
                                            <span class="inline-flex items-center text-xs text-red-600 cursor-pointer hover:underline" @click="$dispatch('open-modal', 'preview-modal'); $dispatch('open-preview', { url: '{{ $library->external_link }}', type: 'youtube' })">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Link YouTube
                                            </span>
                                        @else
                                            <a href="{{ $library->external_link }}" target="_blank" class="inline-flex items-center text-xs text-indigo-600 hover:underline">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                                Link Eksternal
                                            </a>
                                        @endif
                                    @endif
                                    @if(!$library->file_path && !$library->video_path && !$library->external_link)
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </div>
                            </x-table.td>
                            <x-table.td align="center">
                                    <x-table.action>
                                        <li>
                                            <button type="button" x-data @click="$dispatch('open-modal', 'edit-library-{{ $library->id }}')"
                                                class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-amber-600 cursor-pointer text-left">Ubah</button>
                                        </li>
                                        <li>
                                            <div class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded">
                                                <x-modal-delete :id="'delete-library-' . $library->id" message="Apakah Anda yakin ingin menghapus materi ini?"
                                                    :item-name="$library->title" buttonText="Hapus" buttonClass="w-full text-left text-red-600 outline-none cursor-pointer" :route="route('admin-pusat.libraries.destroy', $library->id)" />
                                            </div>
                                        </li>
                                    </x-table.action>

                            </x-table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-table.td colspan="6" align="center" class="py-12">
                                <p class="text-sm text-slate-500">Tidak ada materi perpustakaan yang ditemukan.</p>
                            </x-table.td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table.table>

            <div class="px-5 py-4 border-t border-slate-200">
                {{ $libraries->links('pagination::tailwind') }}
            </div>
        </x-dashboard::filter-card>
    </div>

    <x-modal name="preview-modal" title="Preview Materi" maxWidth="sm:max-w-4xl">
        <div x-data="{
            url: '',
            type: '',
            init() {
                window.addEventListener('open-preview', (e) => {
                    this.url = e.detail.url;
                    this.type = e.detail.type;
                    if(this.type === 'youtube') {
                        let match = this.url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
                        this.url = match ? 'https://www.youtube.com/embed/' + match[1] : this.url;
                    }
                });
                window.addEventListener('close-modal', (e) => {
                    if (e.detail === 'preview-modal') {
                        this.url = '';
                        this.type = '';
                    }
                });
            }
        }">
            <div class="mt-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden">
                <template x-if="type === 'pdf'">
                    <iframe :src="url" class="w-full h-[300px] sm:h-[500px] rounded-lg" frameborder="0"></iframe>
                </template>
                <template x-if="type === 'video'">
                    <video :src="url" class="w-full h-[300px] sm:h-[500px] rounded-lg bg-black" controls autoplay></video>
                </template>
                <template x-if="type === 'youtube'">
                    <iframe :src="url" class="w-full h-[300px] sm:h-[500px] rounded-lg bg-black" frameborder="0" allowfullscreen></iframe>
                </template>
                <template x-if="!url">
                    <div class="p-8 text-center text-gray-500">
                        Memuat konten...
                    </div>
                </template>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" @click="$dispatch('close-modal', 'preview-modal')" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-medium transition">Tutup</button>
            </div>
        </div>
    </x-modal>

    <x-modal name="create-library" title="Tambah Materi Perpustakaan" maxWidth="sm:max-w-4xl">
        <form action="{{ route('admin-pusat.libraries.store') }}" method="POST" enctype="multipart/form-data"
            x-data="{
                fileType: 'document',
                previewUrl: null,
                videoPreviewUrl: null,
                linkUrl: '',
                handleFileChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.previewUrl = URL.createObjectURL(file);
                    } else {
                        this.previewUrl = null;
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
                    if (this.fileType === 'video' && this.videoPreviewUrl) return { type: 'videofile', url: this.videoPreviewUrl };
                    if (this.fileType === 'link' && this.linkUrl) {
                        let embed = this.getYoutubeEmbedUrl(this.linkUrl);
                        if (embed) return { type: 'youtube', url: embed };
                        return { type: 'link', url: this.linkUrl };
                    }
                    return null;
                }
            }" class="space-y-4">
            @csrf

            <div class="flex flex-col lg:flex-row gap-6">

                <div class="flex-1 space-y-4">

                    <x-form.input name="title" label="Judul Materi" required placeholder="Judul buku / dokumen" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @include('lms::admin-pusat.libraries.partials.category-picker', [
                            'label' => 'Pilih Kategori',
                            'selectId' => 'create-library-category',
                            'selectedCategoryId' => old('library_category_id', ''),
                        ])

                        <!-- Cover / Thumbnail Section dengan Pilihan Mode Otomatis & Upload -->
                    <div class="col-span-1 sm:col-span-2 space-y-3" x-data="{ thumbMode: 'auto' }">
                        <label class="block text-sm font-medium text-gray-700">Gambar Sampul</label>
                        
                        <!-- Pilihan Mode Thumbnail -->
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="radio" name="thumb_mode" value="auto" x-model="thumbMode" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Otomatis (Inisial & 4 Warna Tema)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="radio" name="thumb_mode" value="upload" x-model="thumbMode" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Upload Gambar Kustom</span>
                            </label>
                        </div>

                        <!-- Mode 1: Pilihan Warna Kotak Tipis & Checkbox -->
                        <div x-show="thumbMode === 'auto'" class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                            <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">Pilih Warna Background Thumbnail:</span>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Navy -->
                                <label class="relative flex items-center justify-between p-2.5 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-sm bg-[#13416B] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Navy</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="13416B" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" checked onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Slate Blue -->
                                <label class="relative flex items-center justify-between p-2.5 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-sm bg-[#547996] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Slate Blue</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="547996" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Light Blue -->
                                <label class="relative flex items-center justify-between p-2.5 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-sm bg-[#8BB1CC] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Light Blue</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="8BB1CC" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                                <!-- Muted Green -->
                                <label class="relative flex items-center justify-between p-2.5 rounded-lg border border-slate-200 cursor-pointer transition-all bg-white hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/30">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-sm bg-[#79A736] shrink-0"></span>
                                        <span class="text-xs font-bold text-slate-700">Muted Green</span>
                                    </div>
                                    <input type="checkbox" name="bg_color" value="79A736" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" onclick="if(this.checked){document.querySelectorAll('input[name=\'bg_color\']').forEach(el=>el.checked=false);this.checked=true;}">
                                </label>
                            </div>
                        </div>

                        <!-- Mode 2: Upload File -->
                        <div x-show="thumbMode === 'upload'" class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <input type="file" name="cover_image" accept="image/*"
                                class="w-full border border-gray-300 rounded-md p-1 text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[10px] text-gray-500 mt-1 font-medium">(max 2MB)</p>
                        </div>
                    </div>
                    </div>

                    <x-form.textarea name="description" label="Deskripsi Singkat" rows="2" placeholder="Deskripsi materi..." />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis File <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                :class="fileType === 'document' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                <input type="radio" name="file_type_ui" value="document" x-model="fileType" class="sr-only"
                                    @change="previewUrl = null; linkUrl = ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Dokumen
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                :class="fileType === 'video' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                <input type="radio" name="file_type_ui" value="video" x-model="fileType" class="sr-only"
                                    @change="previewUrl = null; linkUrl = ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Video
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                :class="fileType === 'link' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                <input type="radio" name="file_type_ui" value="link" x-model="fileType" class="sr-only"
                                    @change="previewUrl = null; linkUrl = ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                Link
                            </label>
                        </div>
                    </div>

                    <div x-show="fileType === 'document'">
                        <x-form.input type="file" name="file_path" label="File Dokumen PDF" helper="(max 20MB)" accept=".pdf" @change="handleFileChange($event)"
                            class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>

                    <div x-show="fileType === 'video'">
                        <x-form.input type="file" name="video_path" label="File Video" helper="(mp4, webm, max 50MB)" accept="video/mp4,video/webm" @change="handleVideoChange($event)"
                            x-bind:disabled="fileType !== 'video'"
                            class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>

                    <div x-show="fileType === 'link'">
                        <x-form.input type="url" name="external_link" label="URL Link Eksternal" placeholder="https://" x-model="linkUrl" x-bind:required="fileType === 'link'" />
                    </div>
                </div>

                <div class="lg:w-96 shrink-0">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
                    <div class="border border-gray-200 rounded-lg bg-gray-50 min-h-[280px] flex items-center justify-center overflow-hidden">

                        <template x-if="previewContent && previewContent.type === 'pdf'">
                            <iframe :src="previewContent.url" class="w-full h-[350px] rounded-lg" frameborder="0"></iframe>
                        </template>

                        <template x-if="previewContent && previewContent.type === 'videofile'">
                            <video :src="previewContent.url" class="w-full h-[280px] rounded-lg" controls></video>
                        </template>

                        <template x-if="previewContent && previewContent.type === 'youtube'">
                            <iframe :src="previewContent.url" class="w-full h-[280px] rounded-lg" frameborder="0" allowfullscreen></iframe>
                        </template>

                        <template x-if="previewContent && previewContent.type === 'link'">
                            <div class="p-4 text-center w-full">
                                <svg class="mx-auto w-10 h-10 text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                <p class="text-xs text-gray-500 mb-2">Link Eksternal:</p>
                                <a :href="linkUrl" target="_blank" class="text-sm text-indigo-600 hover:underline break-all" x-text="linkUrl"></a>
                            </div>
                        </template>

                        <template x-if="!previewContent">
                            <div class="text-center p-6">
                                <svg class="mx-auto w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <p class="text-xs text-gray-400">Pilih jenis file dan masukkan konten untuk melihat preview</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <x-button type="button" x-data @click="$dispatch('close-modal', 'create-library')" variant="white" class="flex-1">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" class="flex-1">
                    Simpan Materi
                </x-button>
            </div>
        </form>
    </x-modal>

    @foreach($libraries as $library)
        @php
            $initialFileType = 'document';
            $initialLinkUrl = '';
            $initialPreviewUrl = null;
            if ($library->external_link) {
                if (str_contains($library->external_link, 'youtube.com') || str_contains($library->external_link, 'youtu.be')) {
                    $initialFileType = 'video';
                } else {
                    $initialFileType = 'link';
                }
                $initialLinkUrl = $library->external_link;
            } elseif ($library->file_path) {
                $initialFileType = 'document';
                $initialPreviewUrl = Storage::url($library->file_path);
            }
        @endphp

        <x-modal name="edit-library-{{ $library->id }}" title="Edit Materi Perpustakaan" maxWidth="sm:max-w-4xl">
            <form action="{{ route('admin-pusat.libraries.update', $library->id) }}" method="POST" enctype="multipart/form-data"
                x-data="{
                    fileType: '{{ $initialFileType }}',
                    previewUrl: @json($initialPreviewUrl),
                    videoPreviewUrl: null,
                    linkUrl: '{{ $initialLinkUrl }}',
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
                        if (this.fileType === 'video' && this.videoPreviewUrl) return { type: 'videofile', url: this.videoPreviewUrl };
                        if (this.fileType === 'link' && this.linkUrl) {
                            let embed = this.getYoutubeEmbedUrl(this.linkUrl);
                            if (embed) return { type: 'youtube', url: embed };
                            return { type: 'link', url: this.linkUrl };
                        }
                        return null;
                    }
                }" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex flex-col lg:flex-row gap-6">

                    <div class="flex-1 space-y-4">

                        <x-form.input name="title" label="Judul Materi" required placeholder="Judul buku / dokumen" :value="$library->title" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @include('lms::admin-pusat.libraries.partials.category-picker', [
                                'label' => 'Pilih Kategori',
                                'selectId' => 'edit-library-category-' . $library->id,
                                'selectedCategoryId' => old('library_category_id', $library->library_category_id),
                            ])

                            <div>
                                <x-form.input type="file" name="cover_image" label="Gambar Sampul" helper="(abaikan jika tidak ubah)" accept="image/*"
                                    class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                @if($library->cover_image)
                                    <p class="text-xs text-gray-500 mt-1">Sampul saat ini sudah terlampir.</p>
                                @endif
                            </div>
                        </div>

                        <x-form.textarea name="description" label="Deskripsi Singkat" rows="2" placeholder="Deskripsi materi..." :value="$library->description" />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis File <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border transition-colors text-sm"
                                    :class="fileType === 'document' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="file_type_ui" value="document" x-model="fileType" class="sr-only"
                                        @change="linkUrl = ''">
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
                                        @change="previewUrl = null">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                    Link
                                </label>
                            </div>
                        </div>

                        <div x-show="fileType === 'document'">
                            <x-form.input type="file" name="file_path" label="File Dokumen PDF" helper="(max 20MB, abaikan jika tidak ubah)" accept=".pdf" @change="handleFileChange($event)"
                                class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @if($library->file_path)
                                <p class="text-xs text-gray-500 mt-1">File PDF saat ini sudah terlampir.</p>
                            @endif
                        </div>

                        <div x-show="fileType === 'video'">
                            <x-form.input type="file" name="video_path" label="File Video" helper="(mp4, webm, max 50MB, abaikan jika tidak ubah)" accept="video/mp4,video/webm" @change="handleVideoChange($event)"
                                x-bind:disabled="fileType !== 'video'"
                                class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        </div>

                        <div x-show="fileType === 'link'">
                            <x-form.input type="url" name="external_link" label="URL Link Eksternal" placeholder="https://" x-model="linkUrl" x-bind:required="fileType === 'link'" />
                        </div>
                    </div>

                    <div class="lg:w-96 shrink-0">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
                        <div class="border border-gray-200 rounded-lg bg-gray-50 min-h-[280px] flex items-center justify-center overflow-hidden">
                            <template x-if="previewContent && previewContent.type === 'pdf'">
                                <iframe :src="previewContent.url" class="w-full h-[350px] rounded-lg" frameborder="0"></iframe>
                            </template>
                            <template x-if="previewContent && previewContent.type === 'videofile'">
                                <video :src="previewContent.url" class="w-full h-[280px] rounded-lg" controls></video>
                            </template>
                            <template x-if="previewContent && previewContent.type === 'youtube'">
                                <iframe :src="previewContent.url" class="w-full h-[280px] rounded-lg" frameborder="0" allowfullscreen></iframe>
                            </template>
                            <template x-if="previewContent && previewContent.type === 'link'">
                                <div class="p-4 text-center w-full">
                                    <svg class="mx-auto w-10 h-10 text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    <p class="text-xs text-gray-500 mb-2">Link Eksternal:</p>
                                    <a :href="linkUrl" target="_blank" class="text-sm text-indigo-600 hover:underline break-all" x-text="linkUrl"></a>
                                </div>
                            </template>
                            <template x-if="!previewContent">
                                <div class="text-center p-6">
                                    <svg class="mx-auto w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <p class="text-xs text-gray-400">Preview konten akan muncul di sini</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <x-button type="button" x-data @click="$dispatch('close-modal', 'edit-library-{{ $library->id }}')" variant="white" class="flex-1">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" class="flex-1">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-dashboard::layouts.dashboard>