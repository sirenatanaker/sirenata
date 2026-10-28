<x-dashboard::layouts.dashboard title="Log Aktivitas - Super Admin">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-5 bg-slate-50 min-h-screen"
         x-data="{
             detailModalOpen: false,
             activeLog: null,
             openDetail(log) {
                 this.activeLog = log;
                 this.detailModalOpen = true;
             }
         }">

        {{-- Kartu Ringkasan Sederhana (3 Indikator Utama) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Catatan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5 tabular-nums">
                        {{ number_format($totalActivities, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Semua riwayat aktivitas</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center shrink-0">
                    <i class="fas fa-history text-base"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Aktivitas Hari Ini</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-0.5 tabular-nums">
                        {{ number_format($todayActivities, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Aktivitas baru hari ini</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-calendar-check text-base"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-medium text-slate-500">Admin Aktif</p>
                    <p class="text-2xl font-bold text-[#547996] mt-0.5 tabular-nums">
                        {{ number_format($uniqueAdminsCount, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Jumlah administrator yang tercatat</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#547996]/10 text-[#547996] flex items-center justify-center shrink-0">
                    <i class="fas fa-user-shield text-base"></i>
                </div>
            </div>
        </div>

        {{-- Filter Sederhana & Kotak Pencarian --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4">
            <form method="GET" action="{{ route('super-admin.activity-logs.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    {{-- Pencarian --}}
                    <div class="sm:col-span-2 lg:col-span-5">
                        <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">
                            Pencarian
                        </label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Ketik nama admin atau keterangan..."
                                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B] text-slate-800 placeholder-slate-400">
                        </div>
                    </div>

                    {{-- Jenis Tindakan --}}
                    <div class="lg:col-span-3">
                        <label for="event" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenis Tindakan
                        </label>
                        <select id="event" name="event"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B] text-slate-800">
                            <option value="">Semua Tindakan</option>
                            <option value="created" @selected(request('event') === 'created')>Tambah Data Baru</option>
                            <option value="updated" @selected(request('event') === 'updated')>Ubah / Edit Data</option>
                            <option value="deleted" @selected(request('event') === 'deleted')>Hapus Data</option>
                            <option value="login" @selected(request('event') === 'login')>Login Sistem</option>
                        </select>
                    </div>

                    {{-- Admin Pelaku --}}
                    <div class="lg:col-span-4">
                        <label for="causer_id" class="block text-xs font-semibold text-slate-700 mb-1">
                            Administrator
                        </label>
                        <select id="causer_id" name="causer_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B] text-slate-800">
                            <option value="">Semua Administrator</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected(request('causer_id') == $admin->id)>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Baris Bawah: Rentang Tanggal & Tombol Aksi --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-slate-500 font-medium">Tanggal:</span>
                            <input type="date"
                                   name="date_from"
                                   value="{{ request('date_from') }}"
                                   title="Dari Tanggal"
                                   class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:ring-1 focus:ring-[#13416B]">
                            <span class="text-xs text-slate-400">s/d</span>
                            <input type="date"
                                   name="date_to"
                                   value="{{ request('date_to') }}"
                                   title="Sampai Tanggal"
                                   class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:ring-1 focus:ring-[#13416B]">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 justify-end">
                        @if (request()->hasAny(['search', 'event', 'causer_id', 'date_from', 'date_to', 'per_page']))
                            <a href="{{ route('super-admin.activity-logs.index') }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                <i class="fas fa-rotate-left text-[10px]"></i>
                                <span>Reset</span>
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                            <i class="fas fa-search text-[10px]"></i>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Data Log Aktivitas --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800">
                        Daftar Aktivitas Administrator
                    </h2>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-xs text-slate-500 tabular-nums">
                        Total: <strong class="text-slate-700 font-semibold">{{ $activities->total() }}</strong> aktivitas
                    </span>
                    <button type="button" onclick="window.location.reload()"
                            title="Segarkan Data"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-md text-slate-400 hover:text-[#13416B] hover:bg-slate-100 transition-colors cursor-pointer">
                        <i class="fas fa-rotate-right text-[11px]"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center">No.</th>
                            <th class="py-3 px-3.5 min-w-[160px]">Waktu</th>
                            <th class="py-3 px-3.5 min-w-[180px]">Administrator</th>
                            <th class="py-3 px-3.5 min-w-[120px]">Tindakan</th>
                            <th class="py-3 px-3.5 min-w-[260px]">Keterangan Aktivitas</th>
                            <th class="py-3 px-3.5 w-24 text-center">Rincian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($activities as $index => $activity)
                            @php
                                $badgeClass = match($activity->friendly_badge ?? 'slate') {
                                    'emerald' => 'bg-emerald-600 text-white',
                                    'amber'   => 'bg-amber-600 text-white',
                                    'rose'    => 'bg-rose-600 text-white',
                                    'blue'    => 'bg-blue-600 text-white',
                                    default   => 'bg-slate-600 text-white',
                                };

                                $adminName = $activity->causer?->name ?? 'Sistem Otomatis';
                                $adminEmail = $activity->causer?->email;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                {{-- Nomor urut --}}
                                <td class="py-3 px-3.5 text-center text-xs font-semibold text-slate-400">
                                    {{ $activities->firstItem() + $index }}
                                </td>

                                {{-- Waktu --}}
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-800 text-xs tabular-nums">
                                        {{ $activity->friendly_time ?? '-' }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $activity->friendly_relative_time ?? '-' }}
                                    </p>
                                </td>

                                {{-- Administrator --}}
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-[#13416B]/10 text-[#13416B] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($adminName, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800 text-xs truncate">
                                                {{ $adminName }}
                                            </p>
                                            @if ($adminEmail)
                                                <p class="text-[11px] text-slate-400 truncate">
                                                    {{ $adminEmail }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Jenis Tindakan --}}
                                <td class="py-3 px-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold shadow-2xs {{ $badgeClass }}">
                                        {{ $activity->friendly_action ?? 'Aktivitas' }}
                                    </span>
                                </td>

                                {{-- Keterangan Aktivitas --}}
                                <td class="py-3 px-3.5">
                                    <p class="text-xs font-medium text-slate-800 leading-snug">
                                        {{ $activity->friendly_description ?? $activity->description }}
                                    </p>
                                </td>

                                {{-- Tombol Rincian --}}
                                <td class="py-3 px-3.5 text-center">
                                    <button type="button"
                                            @click="openDetail(@js($activity->modal_payload))"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B]/20 transition-colors cursor-pointer">
                                        <i class="fas fa-eye text-[10px]"></i>
                                        <span>Rincian</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fas fa-clipboard-check text-base"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">Belum Ada Aktivitas</p>
                                        <p class="text-xs text-slate-500">
                                            Tidak ada riwayat aktivitas yang sesuai dengan filter pencarian.
                                        </p>
                                        @if (request()->hasAny(['search', 'event', 'causer_id', 'date_from', 'date_to']))
                                            <a href="{{ route('super-admin.activity-logs.index') }}"
                                               class="inline-block mt-2 px-3 py-1.5 text-xs font-semibold text-white bg-[#13416B] rounded-lg">
                                                Tampilkan Semua
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Halaman (Pagination) --}}
            @if ($activities->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL RINCIAN AKTIVITAS (Simpel & Mudah Dipahami)        --}}
        {{-- ======================================================== --}}
        <div x-show="detailModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             @keydown.escape.window="detailModalOpen = false">

            {{-- Backdrop --}}
            <div class="fixed inset-0" @click="detailModalOpen = false"></div>

            {{-- Dialog Box --}}
            <div class="relative w-full max-w-lg bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh] z-10"
                 @click.stop>

                {{-- Header Modal --}}
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#13416B]/10 text-[#13416B] flex items-center justify-center">
                            <i class="fas fa-info-circle text-xs"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Rincian Aktivitas Administrator</h3>
                    </div>
                    <button type="button"
                            @click="detailModalOpen = false"
                            class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Konten Modal --}}
                <div class="p-5 overflow-y-auto space-y-4 text-xs sm:text-sm custom-scrollbar">
                    {{-- Kotak Info Administrator & Waktu --}}
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[11px] font-medium text-slate-500">Administrator (Pelaku):</span>
                                <p class="font-bold text-slate-800 text-sm" x-text="activeLog?.admin_name || '-'"></p>
                                <p class="text-xs text-slate-500" x-text="activeLog?.admin_email || '-'"></p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold text-white shadow-2xs"
                                  :class="{
                                      'bg-emerald-600': activeLog?.action_badge === 'emerald',
                                      'bg-amber-600': activeLog?.action_badge === 'amber',
                                      'bg-rose-600': activeLog?.action_badge === 'rose',
                                      'bg-blue-600': activeLog?.action_badge === 'blue',
                                      'bg-slate-600': !activeLog?.action_badge || activeLog?.action_badge === 'slate'
                                  }"
                                  x-text="activeLog?.action_label || '-'"></span>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                            <span>Waktu Kejadian:</span>
                            <span class="font-semibold text-slate-700" x-text="activeLog?.time || '-'"></span>
                        </div>
                    </div>

                    {{-- Penjelasan Aktivitas --}}
                    <div>
                        <span class="text-xs font-semibold text-slate-700 block mb-1">Keterangan:</span>
                        <div class="p-3 rounded-lg bg-white border border-slate-200 text-slate-800 font-medium">
                            <span x-text="activeLog?.description || '-'"></span>
                        </div>
                    </div>

                    {{-- Rincian Data yang Diinput / Diubah --}}
                    <div>
                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">Data Terkait:</span>

                        {{-- Mode Tambah Data / Nilai Baru --}}
                        <template x-if="activeLog && activeLog.attributes && Object.keys(activeLog.attributes).length > 0">
                            <div class="rounded-lg border border-slate-200 overflow-hidden">
                                <div class="bg-slate-100/70 px-3 py-1.5 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    Informasi Data
                                </div>
                                <div class="divide-y divide-slate-100">
                                    <template x-for="(val, label) in activeLog.attributes" :key="label">
                                        <div class="px-3 py-2 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs hover:bg-slate-50">
                                            <span class="font-medium text-slate-500" x-text="label"></span>
                                            <span class="font-semibold text-slate-800 break-all" x-text="val"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Mode Perubahan (Jika ada nilai lama / Old) --}}
                        <template x-if="activeLog && activeLog.old && Object.keys(activeLog.old).length > 0">
                            <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50/40 p-3">
                                <span class="text-xs font-bold text-amber-800 block mb-1">Perubahan Nilai Sebelumnya:</span>
                                <div class="space-y-1 text-xs">
                                    <template x-for="(val, label) in activeLog.old" :key="label">
                                        <div class="flex items-center justify-between gap-2 text-slate-700">
                                            <span class="text-slate-500" x-text="label + ':'"></span>
                                            <span class="line-through text-rose-600 font-mono" x-text="val"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Jika tidak ada atribut tambahan --}}
                        <template x-if="!activeLog || (!activeLog.attributes || Object.keys(activeLog.attributes).length === 0)">
                            <div class="p-3 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-500">
                                Tidak ada rincian data tambahan yang disimpan.
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-end">
                    <button type="button"
                            @click="detailModalOpen = false"
                            class="px-4 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
            [x-cloak] { display: none !important; }
        </style>
    @endpush
</x-dashboard::layouts.dashboard>