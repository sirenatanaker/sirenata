<x-dashboard::layouts.dashboard title="Bimtek & Event Online - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- KPI Cards Ringkas --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Gelombang Bimtek</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                        {{ number_format($totalEvents, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Semua batch yang pernah dibuat</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-[#13416B]/10 text-[#13416B] flex items-center justify-center shrink-0">
                    <i class="fas fa-video text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Berlangsung</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1 tabular-nums">
                        {{ number_format($ongoingEvents, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bimtek aktif hari ini</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-satellite-dish text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Peserta Terdaftar</p>
                    <p class="text-2xl font-extrabold text-[#547996] mt-1 tabular-nums">
                        {{ number_format($totalParticipants, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Total ASN yang mengikuti</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-[#547996]/10 text-[#547996] flex items-center justify-center shrink-0">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Filter Card & Tabel --}}
        <x-dashboard::filter-card
            title="Daftar Gelombang Bimtek & Event"
            :total="$events->total() . ' Gelombang'"
            :resetUrl="route('admin-pusat.management-course.bimtek-events.index')">

            <x-slot name="actions">
                <a href="{{ route('admin-pusat.management-course.bimtek-events.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Buka Gelombang Baru</span>
                </a>
            </x-slot>

            <x-slot name="filter_inputs">
                {{-- Status --}}
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Pelaksanaan</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="open_registration" @selected(request('status') === 'open_registration')>Pendaftaran Buka</option>
                        <option value="ongoing" @selected(request('status') === 'ongoing')>Sedang Berlangsung</option>
                        <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    </select>
                </div>

                {{-- Tipe --}}
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Metode Pelaksanaan</label>
                    <select name="execution_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                        <option value="">Semua Metode</option>
                        <option value="online" @selected(request('execution_type') === 'online')>Online (Zoom)</option>
                        <option value="offline" @selected(request('execution_type') === 'offline')>Offline (Tatap Muka)</option>
                        <option value="hybrid" @selected(request('execution_type') === 'hybrid')>Hybrid</option>
                    </select>
                </div>

                {{-- Cari --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Cari Bimtek / Kursus</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama gelombang atau kursus..."
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                    </div>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center">No.</th>
                            <th class="py-3 px-3.5 min-w-[220px]">Gelombang & Kode</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Master Kursus</th>
                            <th class="py-3 px-3.5 min-w-[170px]">Jadwal Pelaksanaan</th>
                            <th class="py-3 px-3.5 min-w-[130px]">Metode / Kuota</th>
                            <th class="py-3 px-3.5 min-w-[120px] text-center">Status</th>
                            <th class="py-3 px-3.5 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($events as $index => $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3.5 text-center text-xs font-semibold text-slate-400">
                                    {{ $events->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <a href="{{ route('admin-pusat.management-course.bimtek-events.show', $item->id) }}"
                                       class="font-bold text-slate-900 hover:text-[#13416B] transition-colors line-clamp-1">
                                        {{ $item->batch_name }}
                                    </a>
                                    <span class="inline-block mt-0.5 text-[11px] font-mono font-medium text-slate-400">
                                        {{ $item->batch_code }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-semibold text-slate-700 line-clamp-1">
                                        {{ $item->course?->name ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-800 text-xs tabular-nums">
                                        {{ $item->start_date ? $item->start_date->format('d M Y') : '-' }} s/d {{ $item->end_date ? $item->end_date->format('d M Y') : '-' }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Pukul {{ $item->start_date ? $item->start_date->format('H:i') : '08:00' }} WIB
                                    </p>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center gap-1.5">
                                        @if ($item->execution_type === 'online')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                                                <i class="fas fa-video text-[10px]"></i> Zoom
                                            </span>
                                        @elseif ($item->execution_type === 'offline')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700">
                                                <i class="fas fa-building text-[10px]"></i> Tatap Muka
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700">
                                                <i class="fas fa-globe text-[10px]"></i> Hybrid
                                            </span>
                                        @endif
                                        <span class="text-xs text-slate-500">
                                            ({{ $item->quota }} Peserta)
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @php $badge = $item->status_badge; @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', $item->id) }}"
                                           title="Detail & Monitoring Peserta"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B]/20 transition-colors">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin-pusat.management-course.bimtek-events.edit', $item->id) }}"
                                           title="Edit Pengaturan"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fas fa-calendar-xmark text-lg"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">Belum Ada Gelombang Bimtek</p>
                                        <p class="text-xs text-slate-500">Silakan buat gelombang bimtek online baru yang terhubung ke master kursus.</p>
                                        <a href="{{ route('admin-pusat.management-course.bimtek-events.create') }}"
                                           class="inline-block mt-2 px-3.5 py-1.5 text-xs font-bold text-white bg-[#13416B] rounded-lg">
                                            Buka Gelombang Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($events->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $events->links() }}
                </div>
            @endif
        </x-dashboard::filter-card>

    </div>
</x-dashboard::layouts.dashboard>
