<x-dashboard::layouts.dashboard title="Master Kursus">
    <div class="p-2 sm:p-6">

        <x-dashboard::filter-card title="Master Kursus" :total="$meta['total'] ?? 0" :resetUrl="route('admin-pusat.management-course.courses.index')">

            <x-slot name="actions">
                <x-button :href="route('admin-pusat.management-course.courses.create')" variant="primary" icon="fas fa-plus">
                    Tambah Master Kursus
                </x-button>
            </x-slot>

            <x-slot name="filter_inputs">

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
                            placeholder="Cari nama kursus..."
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
                            <x-table.th align="center" class="min-w-[190px]">Aksi</x-table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($courses as $index => $course)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <x-table.td class="text-sm sm:text-base font-medium text-slate-600">
                                    {{ (($meta['current_page'] ?? 1) - 1) * request('row_per_page', 12) + $index + 1 }}
                                </x-table.td>
                                <x-table.td>
                                    @if (!empty($course->thumbnail))
                                        <img src="{{ str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail) }}" alt="Sampul {{ $course->name }}"
                                            class="w-20 h-14 rounded-md object-cover border border-slate-200">
                                    @else
                                        <div class="w-20 h-14 rounded-md bg-slate-100 text-slate-400 flex items-center justify-center">
                                            <i class="fas fa-image text-lg" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </x-table.td>
                                <x-table.td>
                                    <p class="text-sm sm:text-base font-bold text-slate-800 line-clamp-1" title="{{ $course->name }}">{{ $course->name }}</p>
                                    @if ($course->description)
                                        <p class="mt-1 max-w-2xl text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ $course->description }}
                                        </p>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin-pusat.management-course.courses.show', $course->slug) }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-semibold text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B]/20 transition-colors whitespace-nowrap"
                                            title="Kelola kurikulum dan informasi kursus" aria-label="Kelola {{ $course->name }}">
                                            <i class="fas fa-folder-open text-xs" aria-hidden="true"></i>
                                            <span>Kelola</span>
                                        </a>
                                        <x-modal-delete :id="$course->slug"
                                            message="Apakah Anda yakin ingin menghapus kursus {{ $course->name }}?"
                                            :item-name="$course->name"
                                            :route="route('admin-pusat.management-course.courses.destroy', $course->slug)"
                                            button-text="Hapus"
                                            button-icon="fas fa-trash-alt text-xs"
                                            button-class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition-colors whitespace-nowrap cursor-pointer" />
                                    </div>
                                </x-table.td>
                            </tr>
                        @empty
                            <tr>
                                <x-table.td colspan="4" align="center" class="py-12">
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
