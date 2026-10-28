<x-dashboard::layouts.dashboard title="Super Admin Dashboard">
    @php
        // ---- Ringkasan akun ----
        $totalAkun = (int) $totalAdminPusat + (int) $totalAdminProvinsi + (int) $totalAdminKabKota + (int) $totalUser;
        $percentOf = fn ($value) => $totalAkun > 0 ? (float) ($value / $totalAkun * 100) : 0;
        $formatPct = fn ($value) => number_format($percentOf($value), 1, ',', '.');

        // Warna kelompok akun (dipakai bersama oleh kartu, tile legenda, dan grafik)
        // Kelas ditulis lengkap agar terdeteksi Tailwind.
        $groups = [
            [
                'label' => 'Admin Pusat',
                'value' => (int) $totalAdminPusat,
                'icon'  => 'fa-user-shield',
                'hint'  => 'Pengelola tingkat pusat',
                'hex'   => '#13416B',
                'chip'  => 'bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20',
                'bar'   => 'bg-[#13416B]',
                'text'  => 'text-[#13416B]',
                'tile'  => 'bg-[#13416B]',
            ],
            [
                'label' => 'Admin Provinsi',
                'value' => (int) $totalAdminProvinsi,
                'icon'  => 'fa-map-marked-alt',
                'hint'  => 'Pengelola tingkat provinsi',
                'hex'   => '#547996',
                'chip'  => 'bg-[#547996]/10 text-[#547996] border-[#547996]/20',
                'bar'   => 'bg-[#547996]',
                'text'  => 'text-[#547996]',
                'tile'  => 'bg-[#547996]',
            ],
            [
                'label' => 'Admin Kab/Kota',
                'value' => (int) $totalAdminKabKota,
                'icon'  => 'fa-building',
                'hint'  => 'Pengelola tingkat kab/kota',
                'hex'   => '#8BB1CC',
                'chip'  => 'bg-[#8BB1CC]/20 text-[#3F7BA6] border-[#8BB1CC]/40',
                'bar'   => 'bg-[#8BB1CC]',
                'text'  => 'text-[#3F7BA6]',
                'tile'  => 'bg-[#8BB1CC]',
            ],
            [
                'label' => 'Peserta E-Learning',
                'value' => (int) $totalUser,
                'icon'  => 'fa-user-graduate',
                'hint'  => 'Pengguna pembelajaran',
                'hex'   => '#059669',
                'chip'  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'bar'   => 'bg-emerald-500',
                'text'  => 'text-emerald-600',
                'tile'  => 'bg-emerald-600',
            ],
        ];

        // Aksi cepat
        $actions = [
            ['label' => 'Tambah User', 'hint' => 'Buat akun baru', 'icon' => 'fa-user-plus', 'url' => route('super-admin.user-management.create'), 'chip' => 'bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20'],
            ['label' => 'Kelola Lembaga', 'hint' => 'Kementerian/Pusat', 'icon' => 'fa-building', 'url' => route('super-admin.lembaga.index'), 'chip' => 'bg-[#547996]/10 text-[#547996] border-[#547996]/20'],
            ['label' => 'Kelola Instansi', 'hint' => 'Dinas Daerah', 'icon' => 'fa-map-marked-alt', 'url' => route('super-admin.instansi.index'), 'chip' => 'bg-[#8BB1CC]/20 text-[#3F7BA6] border-[#8BB1CC]/40'],
            ['label' => 'Role & Permission', 'hint' => 'Hak akses', 'icon' => 'fa-user-lock', 'url' => route('super-admin.roles.index'), 'chip' => 'bg-emerald-50 text-emerald-600 border-emerald-100'],
        ];

        // Ringkasan sistem
        $summary = [
            ['label' => 'Lembaga Pusat', 'name' => 'Kementerian / Lembaga', 'value' => $totalLembaga, 'icon' => 'fa-landmark', 'chip' => 'bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20'],
            ['label' => 'Instansi Daerah', 'name' => 'Dinas Provinsi / Kab / Kota', 'value' => $totalInstansi, 'icon' => 'fa-map-marked-alt', 'chip' => 'bg-[#547996]/10 text-[#547996] border-[#547996]/20'],
            ['label' => 'Total Kursus', 'name' => 'Program Pembelajaran LMS', 'value' => $totalCourses, 'icon' => 'fa-graduation-cap', 'chip' => 'bg-emerald-50 text-emerald-600 border-emerald-100'],
        ];
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6 bg-slate-50 min-h-screen">

        {{-- ===================================== --}}
        {{-- 1. KPI                                --}}
        {{-- ===================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            @foreach ($groups as $group)
                <div
                    class="flex flex-col bg-white rounded-md border border-slate-200 shadow-sm p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $group['label'] }}</p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 tabular-nums">
                                {{ number_format($group['value'], 0, ',', '.') }}</p>
                        </div>
                        <div
                            class="w-11 h-11 rounded-md border flex items-center justify-center shrink-0 {{ $group['chip'] }}">
                            <i class="fas {{ $group['icon'] }}"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $group['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- 2. DISTRIBUSI AKUN & EVENT  |  AKSI CEPAT & RINGKASAN      --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- KIRI --}}
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6">

                {{-- Distribusi Akun Pengguna --}}
                <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
                    <header
                        class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-semibold text-slate-900">Distribusi Akun Pengguna</h2>
                                <p class="text-sm text-slate-500">Perbandingan jumlah peran administrator dan peserta</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center self-start sm:self-auto px-3 h-8 rounded-full bg-[#13416B] text-white text-xs font-semibold tabular-nums shrink-0">
                            Total: {{ number_format($totalAkun, 0, ',', '.') }} Akun
                        </span>
                    </header>

                    <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-5 gap-6 items-center">
                        {{-- Donat --}}
                        <div class="md:col-span-2 relative h-56 flex justify-center items-center">
                            <canvas id="userDistributionChart"></canvas>
                        </div>

                        {{-- Tile legenda: latar sesuai warna segmen --}}
                        <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($groups as $group)
                                <div class="rounded-md px-4 py-3 {{ $group['tile'] }}">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-white/90">{{ $group['label'] }}</p>
                                    <p class="mt-1 flex items-baseline gap-2">
                                        <span class="text-2xl font-extrabold text-white tabular-nums">{{ number_format($group['value'], 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-white/90 tabular-nums">({{ $formatPct($group['value']) }}%)</span>
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Event Penting Terkini --}}
                <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col flex-1">
                    <header class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-md bg-[#547996] text-white flex items-center justify-center shrink-0">
                                <i class="fas fa-history"></i>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-semibold text-slate-900">Event Penting Terkini</h2>
                                <p class="text-sm text-slate-500">Aktivitas administrator terbaru pada sistem</p>
                            </div>
                        </div>
                        <a href="{{ route('super-admin.activity-logs.index') }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#13416B] hover:text-[#0d2a45] bg-[#13416B]/10 hover:bg-[#13416B]/15 px-3 py-1.5 rounded-md transition-colors self-start sm:self-auto shrink-0">
                            <span>Lihat Semua</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </header>

                    <div class="p-4 flex-1 max-h-[360px] overflow-y-auto custom-scrollbar space-y-3">
                        @forelse ($recentActivities as $activity)
                            @php
                                $base = $activity->subject_type ? class_basename($activity->subject_type) : 'Data';
                                $subjectTypeLabel = match ($base) {
                                    'User' => 'Pengguna',
                                    'Role' => 'Peran (Role)',
                                    'Permission' => 'Hak Akses',
                                    'Institution' => 'Instansi / Lembaga',
                                    'Course' => 'Kursus Pembelajaran',
                                    default => $base,
                                };

                                $attrs = $activity->properties['attributes'] ?? $activity->properties['old'] ?? [];
                                $subjectName = $activity->subject?->name ?? $activity->subject?->title ?? ($attrs['name'] ?? $attrs['title'] ?? ($activity->subject_id ? '#' . substr((string) $activity->subject_id, 0, 8) : '-'));

                                $rawDesc = strtolower(trim((string) $activity->description));
                                $title = match ($rawDesc) {
                                    'created' => "Tambah {$subjectTypeLabel}",
                                    'updated' => "Ubah {$subjectTypeLabel}",
                                    'deleted' => "Hapus {$subjectTypeLabel}",
                                    default   => ucfirst($activity->description),
                                };

                                $description = match ($rawDesc) {
                                    'created' => "Menambahkan {$subjectTypeLabel} baru: {$subjectName}",
                                    'updated' => "Mengubah data {$subjectTypeLabel}: {$subjectName}",
                                    'deleted' => "Menghapus {$subjectTypeLabel}: {$subjectName}",
                                    default   => $subjectName !== '-' ? "{$subjectTypeLabel} ({$subjectName}): " . ucfirst($activity->description) : ucfirst($activity->description),
                                };

                                $time     = $activity->created_at ? $activity->created_at->diffForHumans(null, true) : '-';
                                $userName = $activity->causer?->name ?? 'Sistem Otomatis';
                            @endphp
                            <article
                                class="flex items-start gap-3 p-3 rounded-md border border-slate-200 bg-white transition-colors hover:bg-slate-50">
                                <div
                                    class="w-9 h-9 rounded-md border bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-bolt text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $title }}</p>
                                        <span class="text-xs text-slate-500 shrink-0"><i class="far fa-clock mr-1"></i>{{ $time }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500 line-clamp-2">{{ $description }}</p>
                                    <p class="mt-1.5 text-[11px] text-slate-400"><i class="far fa-user mr-1"></i>{{ $userName }}</p>
                                </div>
                            </article>
                        @empty
                            <div class="bg-slate-50 rounded-md p-10 text-center border border-dashed border-slate-200">
                                <div
                                    class="w-12 h-12 bg-white border border-slate-200 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <i class="fas fa-history text-lg"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Belum ada log aktivitas</p>
                                <p class="text-xs text-slate-500 mt-1">Belum ada log aktivitas admin saat ini.</p>
                            </div>
                        @endforelse
                    </div>

                    @if (count($recentActivities) > 0)
                        <footer class="px-5 sm:px-6 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50">
                            <span>Menampilkan {{ count($recentActivities) }} event log terbaru</span>
                            <a href="{{ route('super-admin.activity-logs.index') }}" class="font-semibold text-[#13416B] hover:underline inline-flex items-center gap-1">
                                Kelola Seluruh Log <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </footer>
                    @endif
                </section>
            </div>

            {{-- KANAN --}}
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">

                {{-- Aksi Cepat --}}
                <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
                    <header class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">Aksi Cepat</h2>
                            <p class="text-sm text-slate-500">Pintasan pengelolaan sistem</p>
                        </div>
                    </header>

                    <div class="p-4 grid grid-cols-2 gap-3">
                        @foreach ($actions as $action)
                            <a href="{{ $action['url'] }}"
                                class="group flex flex-col items-center justify-center text-center p-4 rounded-md border border-slate-200 bg-white transition-all duration-200 hover:border-[#13416B]/40 hover:shadow-sm hover:-translate-y-0.5">
                                <div
                                    class="w-11 h-11 rounded-md border flex items-center justify-center mb-3 {{ $action['chip'] }}">
                                    <i class="fas {{ $action['icon'] }}"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-900 group-hover:text-[#13416B] transition-colors">{{ $action['label'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $action['hint'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Ringkasan Sistem --}}
                <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex-1">
                    <header class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-md bg-[#547996] text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">Ringkasan Sistem</h2>
                            <p class="text-sm text-slate-500">Data master dan pembelajaran</p>
                        </div>
                    </header>

                    <div class="p-4 space-y-3">
                        @foreach ($summary as $row)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-md border border-slate-200 bg-white">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-md border flex items-center justify-center shrink-0 {{ $row['chip'] }}">
                                        <i class="fas {{ $row['icon'] }} text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-slate-500">{{ $row['label'] }}</p>
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $row['name'] }}</p>
                                    </div>
                                </div>
                                <span
                                    class="shrink-0 min-w-10 text-center px-3 py-1 rounded-md bg-slate-50 border border-slate-200 text-base font-bold text-slate-900 tabular-nums">
                                    {{ number_format((int) $row['value'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            /* Scrollbar tipis untuk area daftar */
            .custom-scrollbar::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const GROUPS = @json(collect($groups)->map(fn ($g) => ['label' => $g['label'], 'value' => $g['value'], 'hex' => $g['hex']])->values());
                const TOTAL = GROUPS.reduce((sum, g) => sum + Number(g.value || 0), 0);
                const hasData = TOTAL > 0;

                // Teks total di tengah donat
                const centerText = {
                    id: 'centerText',
                    afterDraw(chart) {
                        const {ctx, chartArea} = chart;
                        const cx = (chartArea.left + chartArea.right) / 2;
                        const cy = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#64748b';
                        ctx.font = '600 11px sans-serif';
                        ctx.fillText('TOTAL AKUN', cx, cy - 14);
                        ctx.fillStyle = '#0f172a';
                        ctx.font = '800 26px sans-serif';
                        ctx.fillText(TOTAL.toLocaleString('id-ID'), cx, cy + 10);
                        ctx.restore();
                    }
                };

                new Chart(document.getElementById('userDistributionChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: GROUPS.map(g => g.label),
                        datasets: [{
                            // Jika belum ada akun, tampilkan cincin abu-abu
                            data: hasData ? GROUPS.map(g => Number(g.value || 0)) : [1],
                            backgroundColor: hasData ? GROUPS.map(g => g.hex) : ['#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: hasData,
                                backgroundColor: 'rgba(19, 65, 107, 0.95)',
                                padding: 12,
                                cornerRadius: 6,
                                callbacks: {
                                    label: function(context) {
                                        const value = Number(context.raw || 0);
                                        const pct = TOTAL > 0 ? (value / TOTAL * 100).toFixed(1).replace('.', ',') : '0';
                                        return ` ${context.label}: ${value.toLocaleString('id-ID')} akun (${pct}%)`;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [centerText]
                });
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>