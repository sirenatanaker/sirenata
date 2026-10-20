<x-dashboard::layouts.dashboard title="Hasil Kuesioner Pemanfaatan RTKD">
    <div class="p-2 sm:p-6  pt-6 sm:pt-8">
        
        <x-breadcrumb :home="route('admin-pusat.dashboard')" :items="[['label' => 'Hasil Pemanfaatan RTKD']]" />

        <x-flash-message class="mb-4" />

        <x-dashboard::filter-card 
            title="Daftar Hasil Kuesioner" 
            :total="$submissions->total() . ' Entri'"
            :resetUrl="route('admin-pusat.hasil-pemanfaatan-rtkd.index')">
            
            <x-slot name="actions">
                <x-button href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.export') }}?{{ http_build_query(request()->only(['period_id', 'q1_punya_rtkd', 'q2_jadi_acuan', 'search'])) }}"
                    variant="success" icon="fas fa-download" title="Ekspor Data">
                    Ekspor
                </x-button>
            </x-slot>

            <x-slot name="filter_inputs">
                <!-- Periode -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Filter Periode
                    </label>
                    <div class="relative">
                        <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <select name="period_id" class="pl-9 pr-3 py-2.5 w-full rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Periode</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}" {{ $selectedPeriodId == $period->id ? 'selected' : '' }}>
                                    {{ $period->nama }} ({{ $period->tahun }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Kepemilikan -->
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Kepemilikan RTKD
                    </label>
                    <select name="q1_punya_rtkd" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Kepemilikan</option>
                        <option value="ya" {{ request('q1_punya_rtkd') == 'ya' ? 'selected' : '' }}>Punya RTKD</option>
                        <option value="tidak" {{ request('q1_punya_rtkd') == 'tidak' ? 'selected' : '' }}>Tidak Punya</option>
                    </select>
                </div>
                <!-- Status Acuan -->
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Status Acuan
                    </label>
                    <select name="q2_jadi_acuan" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status Acuan</option>
                        <option value="ya" {{ request('q2_jadi_acuan') == 'ya' ? 'selected' : '' }}>Jadi Acuan</option>
                        <option value="tidak" {{ request('q2_jadi_acuan') == 'tidak' ? 'selected' : '' }}>Belum Acuan</option>
                    </select>
                </div>
                <!-- Pencarian -->
                <div class="flex-1 min-w-[240px] w-full">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Pencarian
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Provinsi..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </x-slot>

            <x-table.table plain>
                <thead>
                    <tr>
                        <x-table.th align="center" class="w-12">No</x-table.th>
                        <x-table.th>Provinsi</x-table.th>
                        <x-table.th>Tanggal Isi</x-table.th>
                        <x-table.th align="center">Punya RTKD</x-table.th>
                        <x-table.th align="center">Masa Berlaku</x-table.th>
                        <x-table.th align="center">Jadi Acuan</x-table.th>
                        <x-table.th align="center">Dok. Acuan</x-table.th>
                        <x-table.th align="center">Status</x-table.th>
                        <x-table.th align="center">Oleh</x-table.th>
                        <x-table.th align="center">Aksi</x-table.th>
                    </tr>
                </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($submissions as $key => $sub)
                            <tr class="hover:bg-slate-50 transition">
                                <x-table.td align="center">
                                    {{ $key + $submissions->firstItem() }}
                                </x-table.td>
                                <x-table.td class="font-medium text-slate-800">
                                    {{ $sub->user->scopeArea?->province?->name ?? $sub->user->name ?? 'Unknown' }}
                                </x-table.td>
                                <x-table.td>
                                    {{ $sub->created_at->format('d M Y H:i') }}
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($sub->rtk_document_id)
                                        <x-badge color="success-solid" text="Ya" />
                                    @else
                                        <x-badge color="danger-solid" text="Tidak" />
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    {{ $sub->rtk_document_id ? $sub->rtkDocument->start_date . ' - ' . $sub->rtkDocument->end_date : '—' }}
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($sub->q2_jadi_acuan === 'ya')
                                        <x-badge color="indigo-solid" text="Ya" />
                                    @elseif($sub->q2_jadi_acuan === 'tidak')
                                        <x-badge color="amber-solid" text="Belum" />
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($sub->dokumen_acuan && count($sub->dokumen_acuan) > 0)
                                        <div class="flex justify-center gap-1 flex-wrap">
                                            @foreach($sub->dokumen_acuan as $doc)
                                                <x-badge color="slate" :text="strtoupper($doc['doc_type'])" />
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    @php
                                        $verifColor = match($sub->status_verifikasi) {
                                            'verified' => 'success-solid',
                                            'rejected' => 'danger-solid',
                                            default => 'amber-solid'
                                        };
                                        $verifText = match($sub->status_verifikasi) {
                                            'verified' => 'Disetujui',
                                            'rejected' => 'Revisi',
                                            default => 'Pending'
                                        };
                                    @endphp
                                    <x-badge :color="$verifColor" :text="$verifText" />
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($sub->creator && $sub->creator->hasRole('admin-pusat'))
                                        <x-badge color="red" text="Admin Pusat" />
                                    @else
                                        <span class="text-xs text-slate-500">Mandiri</span>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    <x-table.action>
                                        @if($sub->status_verifikasi === 'verified' || $sub->status_verifikasi === 'rejected')
                                            <li>
                                                <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.show', $sub->id) }}"
                                                    class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-blue-600 cursor-pointer">
                                                    Detail
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.show', $sub->id) }}"
                                                    class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-indigo-600 cursor-pointer font-medium">
                                                    Verifikasi
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.edit-on-behalf', $sub->id) }}"
                                                class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-amber-600 cursor-pointer">
                                                Ubah
                                            </a>
                                        </li>
                                    </x-table.action>
                            </x-table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-table.td colspan="10" align="center" class="py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-slate-500">Belum ada data kuesioner yang sesuai dengan filter.</p>
                            </x-table.td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table.table>

            <div class="px-5 py-4 border-t border-slate-200">
                {{ $submissions->links('pagination::tailwind') }}
            </div>
        </x-dashboard::filter-card>
    </div>
</x-dashboard::layouts.dashboard>
