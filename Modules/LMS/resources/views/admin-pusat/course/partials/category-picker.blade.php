@php
    $categoryOptions = $categories->map(fn ($category) => [
        'id' => (string) $category->id,
        'name' => $category->name,
    ])->values();
@endphp

<div
    x-data="courseCategoryPicker(
        @js($categoryOptions),
        @js((string) $selectedCategoryId),
        @js(route('admin-pusat.management-course.categories.store')),
        @js(route('admin-pusat.management-course.categories.update', '__CATEGORY_ID__')),
        @js(route('admin-pusat.management-course.categories.destroy', '__CATEGORY_ID__'))
    )"
    class="col-span-1 md:col-span-2">
    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div class="flex-1">
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                Kategori
            </label>
            <select
                id="category_id"
                name="category_id"
                x-model="selectedCategoryId"
                required
                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white text-slate-800">
                <option value="">-- Pilih Kategori --</option>
                <template x-for="category in categories" :key="category.id">
                    <option :value="category.id" x-text="category.name"></option>
                </template>
            </select>
            @error('category_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button
            type="button"
            @click="showManager = true; error = ''; notice = ''"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-[#13416B] text-[#13416B] font-semibold text-sm hover:bg-blue-50 transition-colors whitespace-nowrap">
            <i class="fas fa-sliders-h" aria-hidden="true"></i>
            Kelola kategori
        </button>
    </div>

    <div
        x-show="showManager"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="showManager = false"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <button type="button" class="absolute inset-0 bg-slate-900/50" @click="showManager = false" aria-label="Tutup"></button>

        <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="category-manager-title"
            @click.stop
            class="relative z-10 w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
                <div>
                    <h2 id="category-manager-title" class="text-lg font-bold text-slate-900">Kelola Kategori Kursus</h2>
                    <p class="mt-1 text-sm text-slate-500">Tambah, ubah, atau hapus kategori dari daftar.</p>
                </div>
                <button type="button" @click="showManager = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="Tutup dialog">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </header>

            <div class="space-y-5 p-5 sm:p-6">
                <div x-show="notice" x-text="notice" role="status" class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700"></div>
                <div x-show="error" x-text="error" role="alert" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700"></div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <label for="new-category-name" class="sr-only">Nama kategori baru</label>
                    <input
                        id="new-category-name"
                        type="text"
                        x-model="newCategoryName"
                        maxlength="255"
                        required
                        placeholder="Nama kategori baru"
                        class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[#13416B] focus:outline-none focus:ring-2 focus:ring-[#13416B]/20">
                    <button
                        type="button"
                        @click="createCategory()"
                        :disabled="busy"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#13416B] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#103355] disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Tambah
                    </button>
                </div>

                <div>
                    <h3 class="mb-2 text-sm font-semibold text-slate-700">Kategori terdaftar</h3>
                    <p x-show="categories.length === 0" class="rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">
                        Belum ada kategori. Tambahkan kategori terlebih dahulu.
                    </p>
                    <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                        <template x-for="category in categories" :key="category.id">
                            <li class="flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
                                <template x-if="editingId !== category.id">
                                    <span class="min-w-0 flex-1 break-words text-sm font-medium text-slate-700" x-text="category.name"></span>
                                </template>
                                <template x-if="editingId === category.id">
                                    <input
                                        type="text"
                                        x-model="editingName"
                                        maxlength="255"
                                        :aria-label="`Ubah nama kategori ${category.name}`"
                                        class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#13416B] focus:outline-none focus:ring-2 focus:ring-[#13416B]/20">
                                </template>

                                <div class="flex shrink-0 items-center gap-2">
                                    <template x-if="editingId !== category.id">
                                        <button type="button" @click="startEditing(category)" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-[#13416B] hover:bg-blue-50">
                                            Ubah
                                        </button>
                                    </template>
                                    <template x-if="editingId === category.id">
                                        <div class="flex gap-2">
                                            <button type="button" @click="updateCategory(category)" :disabled="busy" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 disabled:opacity-60">
                                                Simpan
                                            </button>
                                            <button type="button" @click="editingId = null; editingName = ''" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                                Batal
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="deleteCategory(category)" :disabled="busy" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 disabled:opacity-60">
                                        Hapus
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('courseCategoryPicker', (categories, selectedCategoryId, storeUrl, updateUrl, deleteUrl) => ({
                    categories,
                    selectedCategoryId,
                    storeUrl,
                    updateUrl,
                    deleteUrl,
                    showManager: false,
                    newCategoryName: '',
                    editingId: null,
                    editingName: '',
                    error: '',
                    notice: '',
                    busy: false,

                    categoryUrl(url, category) {
                        return url.replace('__CATEGORY_ID__', encodeURIComponent(category.id));
                    },

                    async send(url, method, body = null) {
                        const response = await fetch(url, {
                            method,
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            ...(body ? { body: JSON.stringify(body) } : {}),
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            const validationError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                            throw new Error(validationError || data.message || 'Permintaan kategori gagal diproses.');
                        }

                        return data;
                    },

                    async createCategory() {
                        this.busy = true;
                        this.error = '';
                        this.notice = '';

                        try {
                            const data = await this.send(this.storeUrl, 'POST', { name: this.newCategoryName });
                            this.categories.push(data.category);
                            this.categories.sort((first, second) => first.name.localeCompare(second.name));
                            this.selectedCategoryId = data.category.id;
                            this.newCategoryName = '';
                            this.notice = data.message;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.busy = false;
                        }
                    },

                    startEditing(category) {
                        this.error = '';
                        this.notice = '';
                        this.editingId = category.id;
                        this.editingName = category.name;
                    },

                    async updateCategory(category) {
                        this.busy = true;
                        this.error = '';
                        this.notice = '';

                        try {
                            const data = await this.send(this.categoryUrl(this.updateUrl, category), 'PUT', { name: this.editingName });
                            const index = this.categories.findIndex((item) => item.id === category.id);
                            this.categories[index] = data.category;
                            this.categories.sort((first, second) => first.name.localeCompare(second.name));
                            this.editingId = null;
                            this.editingName = '';
                            this.notice = data.message;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.busy = false;
                        }
                    },

                    async deleteCategory(category) {
                        if (!window.confirm(`Hapus kategori "${category.name}"?`)) {
                            return;
                        }

                        this.busy = true;
                        this.error = '';
                        this.notice = '';

                        try {
                            const data = await this.send(this.categoryUrl(this.deleteUrl, category), 'DELETE');
                            this.categories = this.categories.filter((item) => item.id !== category.id);
                            if (this.selectedCategoryId === category.id) {
                                this.selectedCategoryId = '';
                            }
                            if (this.editingId === category.id) {
                                this.editingId = null;
                                this.editingName = '';
                            }
                            this.notice = data.message;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
