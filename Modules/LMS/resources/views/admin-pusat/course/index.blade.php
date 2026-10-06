<x-dashboard::layouts.dashboard title="Daftar Course">
    <div class="p-2 sm:p-6">

        <x-dashboard::filter-card title="Daftar Course" :total="$meta['total'] ?? 0" :resetUrl="route('admin-pusat.management-course.courses.index')">

            <x-slot name="actions">
                <x-button :href="route('admin-pusat.management-course.courses.create')" variant="primary" icon="fas fa-plus">
                    Tambah Course
                </x-button>
            </x-slot>

            <x-slot name="filter_inputs">

                <!-- Kategori Course -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Kategori Course
                    </label>

                    <div class="relative">
                        <i
                            class="fas fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                        <select name="category_id"
                            class="pl-9 pr-3 py-2.5 w-full rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Kategori</option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Per Page -->
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Data per Halaman
                    </label>

                    <select name="row_per_page"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ([10, 20, 50, 100] as $page)
                            <option value="{{ $page }}" {{ request('row_per_page', 12) == $page ? 'selected' : '' }}>
                                {{ $page }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pencarian -->
                <div class="flex-1 min-w-[240px] w-full">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Pencarian
                    </label>

                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama course..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

            </x-slot>

            <div class="p-3 sm:p-5 mt-2">
                <x-table.table plain>
                    <thead>
                        <tr>
                            <x-table.th>No.</x-table.th>
                            <x-table.th>Sampul</x-table.th>
                            <x-table.th>Nama Kursus</x-table.th>
                            <x-table.th>Kategori</x-table.th>
                            <x-table.th align="center">Peserta</x-table.th>
                            <x-table.th align="center">Aksi</x-table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($courses as $index => $course)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <x-table.td>
                                    {{ (($meta['current_page'] ?? 1) - 1) * request('row_per_page', 12) + $index + 1 }}
                                </x-table.td>
                                <x-table.td>
                                    @if (!empty($course->thumbnail))
                                        <img src="{{ $course->thumbnail }}" alt="Sampul {{ $course->name }}"
                                            class="w-16 h-11 rounded-md object-cover border border-slate-200">
                                    @else
                                        <div class="w-16 h-11 rounded-md bg-slate-100 text-slate-400 flex items-center justify-center">
                                            <i class="fas fa-image" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </x-table.td>
                                <x-table.td>
                                    <p class="font-semibold text-slate-800">{{ $course->name }}</p>
                                    <p class="mt-1 max-w-xl text-xs text-slate-500 line-clamp-2">
                                        {{ $course->description ?? 'Tidak ada deskripsi tersedia untuk kursus ini.' }}
                                    </p>
                                </x-table.td>
                                <x-table.td>
                                    <x-badge color="slate">{{ $course->category->name ?? 'Tanpa Kategori' }}</x-badge>
                                </x-table.td>
                                <x-table.td align="center">
                                    {{ number_format($course->students_count ?? 0) }}
                                </x-table.td>
                                <x-table.td align="center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-[#13416B] hover:bg-blue-50"
                                            title="Lihat detail kursus" aria-label="Lihat detail {{ $course->name }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </a>
                                        <a href="{{ route('admin-pusat.management-course.courses.edit', $course->slug) }}"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-amber-600 hover:bg-amber-50"
                                            title="Edit kursus" aria-label="Edit {{ $course->name }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <x-modal-delete :id="$course->slug"
                                            message="Apakah Anda yakin ingin menghapus kursus {{ $course->name }}?"
                                            :item-name="$course->name"
                                            :route="route('admin-pusat.management-course.courses.destroy', $course->slug)"
                                            :icon-only="true" />
                                    </div>
                                </x-table.td>
                            </tr>
                        @empty
                            <tr>
                                <x-table.td colspan="6" align="center" class="py-12">
                                    <div class="flex flex-col items-center gap-3">
                                        <span class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <i class="fas fa-book-open text-xl" aria-hidden="true"></i>
                                        </span>
                                        <span class="font-medium text-slate-700">Kursus belum tersedia</span>
                                        <span class="text-sm text-slate-500">Belum ada kursus yang cocok dengan filter pencarian.</span>
                                    </div>
                                </x-table.td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table.table>
            </div>

            {{-- Pagination --}}
            @if (!empty($courses))
                <div class="mt-4 mb-6 flex justify-center gap-2">
                    <x-api-pagination :meta="$meta" />
                </div>
            @endif

        </x-dashboard::filter-card>

    </div>
</x-dashboard::layouts.dashboard>
