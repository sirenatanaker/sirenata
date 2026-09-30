<x-dashboard::layouts.dashboard title="Proyek - E-Learning">
    <div class="p-2 sm:p-6">
        <x-breadcrumb :items="[['label' => 'Proyek']]" />
        <!-- Mengubah Judul Tabel secara dinamis -->
        <x-dashboard::filter-card
            title="{{ $projectScope === 'daerah' ? 'Proyek Daerah' : 'Proyek Pusat' }}"
            :total="$projects->total() . ' Proyek'" :resetUrl="route($routePrefix . 'index', ['type' => $projectScope])"
            :defaultOpen="str_contains($routePrefix, 'admin-province') || str_contains($routePrefix, 'admin-kab-kota')">

            <x-slot name="actions">
                <x-button :href="route('admin-pusat.project.export') .
                    '?' .
                    http_build_query(request()->only(['search', 'status', 'type']))" variant="success" icon="fas fa-download" title="Ekspor Data">
                    <span class="hidden sm:inline">Ekspor</span>
                </x-button>

                <!-- Menyembunyikan tombol Tambah jika sedang di halaman Persetujuan (Draft) -->
                @if ($projectScope === 'pusat' || !str_contains($routePrefix, 'admin-pusat'))
                    @can('project-create')
                        <x-button :href="route($routePrefix . 'create')" variant="primary" icon="fas fa-plus">
                            <span class="hidden sm:inline">Tambah Proyek</span>
                            <span class="sm:hidden">Tambah</span>
                        </x-button>
                    @endcan
                @endif
            </x-slot>

            <x-slot name="filter_inputs">
                @if (str_contains($routePrefix, 'admin-pusat'))
                    <input type="hidden" name="type" value="{{ $projectScope }}">
                @endif

                <!-- Status -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Filter Status
                    </label>
                    <div class="relative">
                        <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <select name="status"
                            class="pl-9 pr-3 py-2.5 w-full rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Status</option>
                            @foreach (['Draft', 'Menunggu Tim', 'On Progress', 'Completed', 'Kedaluwarsa'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ $status }}
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
                    <select name="per_page"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ([10, 20, 50, 100] as $page)
                            <option value="{{ $page }}" {{ request('per_page') == $page ? 'selected' : '' }}>
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
                            placeholder="Cari nama proyek..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </x-slot>

            <x-table.table plain>
                <thead>
                    <tr>
                        <x-table.th>No.</x-table.th>
                        <x-table.th>Nama</x-table.th>
                        <x-table.th>Tipe</x-table.th>
                        @if ($projectScope === 'daerah')
                            <x-table.th>Status</x-table.th>
                        @endif
                        <x-table.th>Asal Wilayah</x-table.th>
                        <x-table.th>Periode</x-table.th>
                        <x-table.th>Ketua Tim</x-table.th>
                        <x-table.th>Persentase</x-table.th>
                        <x-table.th align="center">Aksi</x-table.th>
                    </tr>
                </thead>

                <tbody id="admin-table-body" class="divide-y divide-slate-200">
                    @forelse ($projects as $key => $project)
                        <tr class="hover:bg-slate-50 transition">
                            <x-table.td>
                                <span class="text-slate-600">{{ $key + $projects->firstItem() }}</span>
                            </x-table.td>
                            <x-table.td>
                                <span class="text-slate-600 font-medium">{{ $project->name }}</span>
                            </x-table.td>
                            <x-table.td>
                                <x-badge color="slate" :text="$project->type" />
                            </x-table.td>
                            @if ($projectScope === 'daerah')
                                <x-table.td>
                                    @php
                                        $status = $project->status ?? 'Draft';
                                        $statusColor = match ($status) {
                                            'Menunggu Tim' => 'amber-solid',
                                            'On Progress' => 'blue-solid',
                                            'Completed' => 'green-solid',
                                            'Kedaluwarsa' => 'red-solid',
                                            default => 'slate-solid',
                                        };
                                    @endphp
                                    <x-badge :color="$statusColor" :text="$status === 'Completed' ? 'Selesai' : $status" />
                                </x-table.td>
                            @endif
                            <x-table.td>
                                @php
                                    $creatorScope = $project->creator?->scopeArea;
                                @endphp
                                @if ($creatorScope?->regency)
                                    <div class="flex items-start gap-2">
                                        <div>
                                            <span class="block text-sm font-semibold text-slate-700">{{ $creatorScope->regency->name }}</span>
                                            <span class="block text-xs text-slate-400">{{ $creatorScope->province->name ?? 'Kab/Kota' }}</span>
                                        </div>
                                    </div>
                                @elseif ($creatorScope?->province)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-700">{{ $creatorScope->province->name }}</span>
                                    </div>
                                @elseif ($project->type === 'Nasional')
                                    <span class="text-sm text-slate-500">Pusat</span>
                                @else
                                    <span class="text-sm text-slate-400">Belum tercatat</span>
                                @endif
                            </x-table.td>
                            <x-table.td>
                                <span class="text-slate-600">
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}
                                    -
                                    {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}
                                </span>
                            </x-table.td>
                            <x-table.td>
                                @if ($project->leader)
                                    <div class="space-y-0.5">
                                        <span class="block text-sm font-semibold text-slate-700">{{ $project->leader->name }}</span>
                                        <span class="block text-xs text-slate-400">{{ $project->leader->profile->instansi ?? '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </x-table.td>
                            <x-table.td>
                                @php
                                    $projectProgress = $project->progress ?? 0;
                                    $endDate = $project->end_date ? \Carbon\Carbon::parse($project->end_date)->startOfDay() : null;
                                    $remainingLabel = null;

                                    if ($project->status === 'Draft') {
                                        $remainingLabel = 'Menunggu persetujuan';
                                    } elseif ($project->status === 'Menunggu Tim') {
                                        $remainingLabel = 'Batas Tim: ' . $project->sisa_hari_penentuan_tim . ' hari lagi';
                                    } elseif ($project->status === 'Kedaluwarsa') {
                                        $remainingLabel = 'Penentuan tim kedaluwarsa';
                                    } elseif ($endDate) {
                                        $today = now()->startOfDay();

                                        if ($project->status === 'Completed' || $endDate->lessThan($today)) {
                                            $remainingLabel = $project->status === 'Completed' ? 'Selesai' : 'Berakhir';
                                        } else {
                                            $remainingDays = $today->diffInDays($endDate);
                                            $remainingLabel = $remainingDays > 0 ? "Sisa {$remainingDays} hari" : 'Berakhir hari ini';
                                        }
                                    }
                                @endphp
                                <div class="flex items-center">
                                    <span
                                        class="text-indigo-600 font-medium whitespace-nowrap mr-2">{{ $projectProgress }}%</span>
                                    <div class="w-24 lg:w-32 bg-slate-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full"
                                            style="width: {{ $projectProgress }}%"></div>
                                    </div>
                                </div>
                                @if ($projectScope === 'daerah' && $remainingLabel)
                                    <span class="block mt-1 text-xs {{ $project->status === 'Kedaluwarsa' ? 'text-red-500 font-semibold' : ($project->status === 'Menunggu Tim' ? 'text-amber-600 font-semibold' : 'text-slate-500') }}">
                                        {{ $remainingLabel }}
                                    </span>
                                @endif
                            </x-table.td>
                            <x-table.td align="center">
                                <x-table.action>
                                    @can('project-view')
                                        <li>
                                            <a href="{{ route($routePrefix . 'show', $project->id) }}"
                                                class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-blue-600">Detail</a>
                                        </li>
                                    @endcan

                                    <!-- LOGIKA PEMISAHAN TOMBOL YANG LEBIH KUAT -->
                                    @php
                                        $isProyekDaerah = in_array($project->type, [
                                            'Provinsi',
                                            'Kab/Kota',
                                            'Kabupaten/Kota',
                                            'provinsi',
                                            'kab_kota',
                                        ]);
                                    @endphp

                                    <!-- KONDISI BARU: Cek apakah ini Admin Pusat DAN proyeknya milik Daerah -->
                                    @if (str_contains($routePrefix, 'pusat') && $isProyekDaerah)
                                        <!-- KHUSUS PUSAT: Tinjau & Setujui -->
                                        @can('project-edit')
                                            <li>
                                                <a href="{{ route('admin-pusat.project.prerequisite', $project->id) }}"
                                                    class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-indigo-600 font-medium">
                                                    <i class="fas fa-clipboard-check w-5 text-center mr-1"></i>
                                                    {{ $project->status === 'Draft' ? 'Tinjau & Setujui' : ($project->status === 'Kedaluwarsa' ? 'Buka Kembali' : 'Atur Prasyarat') }}
                                                </a>
                                            </li>
                                        @endcan
                                    @else
                                        <!-- UNTUK DAERAH ATAU PUSAT (saat melihat proyek Nasional): Tombol Normal -->
                                        @can('project-edit')
                                            <li>
                                                <a href="{{ route($routePrefix . 'edit', $project->id) }}"
                                                    class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-amber-600">Ubah
                                                    Proyek</a>
                                            </li>
                                        @endcan

                                        @can('project-delete')
                                            <li>
                                                <div class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded">
                                                    <x-modal-delete :id="'delete-project-' . $project->id"
                                                        message="Apakah Anda yakin ingin menghapus proyek ini?"
                                                        :item-name="$project->name" buttonText="Hapus"
                                                        buttonClass="w-full text-left text-red-600 outline-none cursor-pointer"
                                                        :route="route($routePrefix . 'destroy', $project->id)" />
                                                </div>
                                            </li>
                                        @endcan
                                    @endif
                                </x-table.action>

                            </x-table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-table.td colspan="{{ $projectScope === 'daerah' ? 9 : 8 }}" align="center" class="py-12">
                                <span class="text-sm text-slate-500">Tidak ada proyek yang ditemukan.</span>
                            </x-table.td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table.table>

            <div class="px-5 py-4 border-t border-slate-200">
                {{ $projects->links('pagination::tailwind') }}
            </div>
        </x-dashboard::filter-card>
    </div>

    @include('project::partials.index-modal')

    @push('scripts')
        @include('project::partials.index-scripts')
    @endpush
</x-dashboard::layouts.dashboard>