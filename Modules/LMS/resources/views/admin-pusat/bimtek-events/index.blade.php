<x-dashboard::layouts.dashboard title="Periode Event Online - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- Filter Card & Tabel --}}
        <x-dashboard::filter-card
            title="Daftar Gelombang Periode Event"
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
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Pelaksanaan</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="open_registration" @selected(request('status') === 'open_registration')>Pendaftaran Buka</option>
                        <option value="ongoing" @selected(request('status') === 'ongoing')>Sedang Berlangsung</option>
                        <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
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
                            <th class="py-3 px-3.5 min-w-[110px]">Kuota</th>
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
                                       class="font-bold text-slate-900 hover:text-[#13416B] transition-colors line-clamp-2 leading-snug"
                                       title="{{ $item->batch_name }}">
                                        {{ $item->batch_name }}
                                    </a>
                                    <span class="inline-block mt-0.5 text-[11px] font-mono font-medium text-slate-400">
                                        {{ $item->batch_code }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-semibold text-slate-700 line-clamp-2 leading-snug" title="{{ $item->course?->name }}">
                                        {{ $item->course?->name ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-800 text-xs tabular-nums">
                                        {{ $item->start_date ? $item->start_date->format('d M Y') : '-' }} s/d {{ $item->end_date ? $item->end_date->format('d M Y') : '-' }}
                                    </p>
                                    @if ($item->zoom_scheduled_at)
                                        <p class="text-[11px] text-blue-600 font-semibold mt-0.5">
                                            <i class="fas fa-video text-[10px] mr-0.5"></i> Zoom: {{ $item->zoom_schedule_formatted }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            Pukul {{ $item->start_date ? $item->start_date->format('H:i') : '08:00' }} WIB
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5">
                                    @if ($item->quota && $item->quota > 0)
                                        <span class="font-semibold text-slate-800 tabular-nums">
                                            {{ number_format($item->quota, 0, ',', '.') }} Peserta
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            <i class="fas fa-infinity text-[10px] text-blue-600"></i>
                                            <span>Tanpa Batas</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @php
                                        $badge = $item->status_badge;
                                        $isCompleted = ($badge['label'] ?? '') === 'Selesai';
                                    @endphp
                                    <span class="inline-flex items-center justify-center {{ $isCompleted ? 'px-3.5 py-1 text-xs sm:text-[13px] font-bold tracking-wide shadow-2xs' : 'px-3 py-0.5 text-xs font-semibold' }} rounded-full {{ $badge['class'] }}">
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
