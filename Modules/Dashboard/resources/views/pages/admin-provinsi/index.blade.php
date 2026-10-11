<x-dashboard::layouts.dashboard title="Dashboard Admin Provinsi">
    @php
        // ---- Angka ringkasan RTK ----
        $rtkApproved = (int) $rtkStatusDistribution->get('approved', 0);
        $rtkPending  = (int) $rtkStatusDistribution->get('pending', 0);
        $rtkRejected = (int) $rtkStatusDistribution->get('rejected', 0);
        $rtkTotal    = $rtkApproved + $rtkPending + $rtkRejected;
        $rtkAktif    = (int) $rtkMasaAktifPerKabKota->count();
        $percentOf   = fn (int $value) => $rtkTotal > 0 ? (int) round($value / $rtkTotal * 100) : 0;

        // Kelas ditulis lengkap agar terdeteksi Tailwind
        $tones = [
            'emerald' => ['chip' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'bar' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
            'amber'   => ['chip' => 'bg-amber-50 text-amber-600 border-amber-100', 'bar' => 'bg-amber-500', 'text' => 'text-amber-600'],
            'red'     => ['chip' => 'bg-red-50 text-red-600 border-red-100', 'bar' => 'bg-red-500', 'text' => 'text-red-600'],
            'navy'    => ['chip' => 'bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20', 'bar' => 'bg-[#13416B]', 'text' => 'text-[#13416B]'],
        ];

        $kpis = [
            ['label' => 'RTK Disetujui', 'value' => $rtkApproved, 'hint' => 'Verifikasi disetujui', 'icon' => 'fa-check-double', 'tone' => 'emerald', 'pct' => $percentOf($rtkApproved)],
            ['label' => 'Menunggu Verifikasi', 'value' => $rtkPending, 'hint' => 'RTK kab/kota menunggu verifikasi', 'icon' => 'fa-hourglass-half', 'tone' => 'amber', 'pct' => $percentOf($rtkPending)],
            ['label' => 'RTK Ditolak', 'value' => $rtkRejected, 'hint' => 'Dikembalikan ke daerah', 'icon' => 'fa-ban', 'tone' => 'red', 'pct' => $percentOf($rtkRejected)],
            ['label' => 'RTK Aktif', 'value' => $rtkAktif, 'hint' => 'Kabupaten/Kota aktif', 'icon' => 'fa-file-contract', 'tone' => 'navy', 'pct' => null],
        ];
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6 bg-slate-50 min-h-screen">

        {{-- Peringatan Wilayah Belum Ditetapkan --}}
        @if (!$user->hasCompleteScope())
            <div class="bg-amber-50 border border-amber-200 rounded-md p-4 sm:p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-md bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-amber-800 mb-1">Wilayah Provinsi Belum Ditetapkan</h2>
                    <p class="text-sm text-amber-700 leading-relaxed">
                        Akun ini belum memiliki penetapan wilayah provinsi pada sistem.
                        Untuk melanjutkan pengelolaan data, silakan hubungi Admin Pusat agar wilayah dapat dikonfigurasi terlebih dahulu.
                    </p>
                </div>
            </div>
        @endif

        {{-- ===================================== --}}
        {{-- 1. KPI                                --}}
        {{-- ===================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            @foreach ($kpis as $kpi)
                @php $tone = $tones[$kpi['tone']]; @endphp
                <div
                    class="flex flex-col bg-white rounded-md border border-slate-200 shadow-sm p-5 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 tabular-nums">
                                {{ number_format($kpi['value'], 0, ',', '.') }}</p>
                        </div>
                        <div
                            class="w-11 h-11 rounded-md border flex items-center justify-center shrink-0 {{ $tone['chip'] }}">
                            <i class="fas {{ $kpi['icon'] }}"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $kpi['hint'] }}</p>

                    <div class="mt-auto pt-4">
                        @if ($kpi['pct'] !== null)
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1.5">
                                <span>Porsi dari total RTK</span>
                                <span class="font-semibold tabular-nums {{ $tone['text'] }}">{{ $kpi['pct'] }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $tone['bar'] }}" style="width: {{ $kpi['pct'] }}%"></div>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Total RTK tercatat</span>
                                <span class="font-semibold tabular-nums {{ $tone['text'] }}">
                                    {{ number_format($rtkTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- 2. GRAFIK MASA BERLAKU RTK & TINDAKAN                  --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- KIRI: GRAFIK MASA BERLAKU RTK --}}
            <section
                class="lg:col-span-7 xl:col-span-8 bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <header
                    class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">Komparasi Masa Berlaku RTK</h2>
                            <p class="text-sm text-slate-500">Tahun penyusunan dan masa berakhir dokumen per Kabupaten/Kota</p>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto sm:max-w-xs shrink-0">
                        <label for="rtkYearFilter" class="sr-only">Filter tahun penyusunan RTK</label>
                        <select id="rtkYearFilter"
                            class="w-full text-sm border-slate-200 rounded-md focus:ring-[#13416B] focus:border-[#13416B] text-ellipsis overflow-hidden cursor-pointer bg-white disabled:opacity-60">
                            <option value="all" {{ $selectedRtkYear === 'all' ? 'selected' : '' }}>Semua Tahun (Default)
                            </option>
                            @foreach ($rtkYearsOptions as $y)
                                <option value="{{ $y }}"
                                    {{ (string) $y === (string) $selectedRtkYear ? 'selected' : '' }}>
                                    Mulai {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </header>

                {{-- Legenda status masa berlaku --}}
                <div class="px-5 sm:px-6 pt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-[#13416B]"></span>Masih berlaku</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>Segera berakhir (&le; 1 tahun)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-slate-400"></span>Sudah berakhir</span>
                    <span class="inline-flex items-center gap-1.5"><span class="inline-block w-4 border-t-2 border-dashed border-red-500"></span>Tahun ini ({{ now()->year }})</span>
                </div>

                <div class="p-5 sm:p-6 flex-1 flex flex-col">
                    <p id="rtkChartError" class="hidden mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                        Gagal memuat data RTK. Coba pilih ulang tahun atau muat ulang halaman.
                    </p>

                    <div class="relative flex-1 min-h-[400px]">
                        {{-- Area scroll: label sumbu Y ikut scroll, sumbu X tetap menempel di bawah --}}
                        <div id="rtkChartScroll"
                            class="absolute inset-0 overflow-y-auto custom-scrollbar transition-opacity duration-200 {{ $rtkMasaAktifPerKabKota->count() > 0 ? '' : 'hidden' }}">
                            <div class="flex flex-col min-h-full">
                                <div id="rtkCombinedChartContainer" class="relative w-full shrink-0">
                                    <canvas id="rtkCombinedBarChart"></canvas>
                                </div>
                                <div id="rtkAxisX"
                                    class="sticky bottom-0 z-10 mt-auto h-7 shrink-0 bg-white border-t border-slate-200">
                                </div>
                            </div>
                        </div>

                        <div id="rtkCombinedEmptyState"
                            class="bg-slate-50 rounded-md p-10 text-center border border-dashed border-slate-200 {{ $rtkMasaAktifPerKabKota->count() > 0 ? 'hidden' : '' }}">
                            <div
                                class="w-12 h-12 bg-white border border-slate-200 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <i class="fas fa-chart-area text-lg"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">Belum ada data RTK</p>
                            <p class="text-xs text-slate-500 mt-1">Belum ada penyusunan dokumen RTK Kab/Kota yang tercatat.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- KANAN: PERLU PERSETUJUAN --}}
            <section id="pendingSection"
                class="lg:col-span-5 xl:col-span-4 bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col scroll-mt-6">
                <header class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">Perlu Tindakan</h2>
                        </div>
                        <span
                            class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-full bg-[#13416B] text-white text-sm font-semibold tabular-nums">
                            {{ $totalPendingApprovals }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">RTK yang masih memerlukan proses verifikasi atau dokumen</p>
                </header>

                {{-- Satu-satunya elemen yang scroll, dipakai untuk infinite scroll --}}
                <div id="pendingScrollContainer" class="p-4 flex-1 max-h-[500px] overflow-y-auto custom-scrollbar">
                    <div id="pendingApprovalList" class="space-y-3"></div>

                    <div id="pendingEmptyState" class="hidden flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-md bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 mb-3">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Semua Terproses</p>
                        <p class="text-xs text-slate-500 mt-1">Tidak ada RTK yang memerlukan tindakan.</p>
                    </div>

                    <div id="pendingLoadingSpinner" class="hidden py-3 text-center">
                        <i class="fas fa-spinner fa-spin text-[#13416B]"></i>
                        <span class="text-xs text-slate-500 ml-2">Memuat data berikutnya...</span>
                    </div>

                    <div id="pendingError" class="hidden mt-3 text-center text-xs text-red-600">
                        Gagal memuat data.
                        <button type="button" id="pendingRetry" class="font-semibold underline underline-offset-2 hover:text-red-700">Coba lagi</button>
                    </div>

                    <p id="pendingEndMessage"
                        class="hidden pt-3 mt-3 text-center text-xs text-slate-500 border-t border-slate-100">
                        Semua data tindakan telah ditampilkan
                    </p>
                </div>
            </section>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. DISTRIBUSI PENDAFTAR E-LEARNING                        --}}
        {{-- ========================================================= --}}
        <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <header
                class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-md bg-[#547996] text-white flex items-center justify-center shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-slate-900">Distribusi Pendaftar E-Learning</h2>
                        <p class="text-sm text-slate-500">Jumlah pengguna terdaftar berdasarkan Kabupaten/Kota</p>
                    </div>
                </div>

                <div class="w-full sm:w-auto sm:max-w-xs shrink-0">
                    <label for="sdmYearFilter" class="sr-only">Filter tahun registrasi</label>
                    <select id="sdmYearFilter"
                        class="w-full text-sm border-slate-200 rounded-md focus:ring-[#13416B] focus:border-[#13416B] text-ellipsis overflow-hidden cursor-pointer bg-white disabled:opacity-60">
                        @foreach ($sdmYears as $year)
                            <option value="{{ $year }}" {{ $selectedSdmYear == $year ? 'selected' : '' }}>
                                Tahun Registrasi {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </header>

            <div class="p-5 sm:p-6">
                {{-- Ringkasan --}}
                <div id="sdmSummary" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    {{-- Total: biru muda (serasi dengan navy & slate), teks putih --}}
                    <div class="rounded-md bg-[#8BB1CC] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/90">Total Peserta</p>
                        <p id="sdmTotal" class="mt-1 text-2xl font-extrabold text-white tabular-nums">0</p>
                    </div>
                    {{-- Laki-laki: navy (sama dengan batang laki-laki di daftar) --}}
                    <div class="rounded-md bg-[#13416B] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Laki-laki</p>
                        <p class="mt-1 flex items-baseline gap-2">
                            <span id="sdmMale" class="text-2xl font-extrabold text-white tabular-nums">0</span>
                            <span id="sdmMalePct" class="text-xs font-semibold text-white/80 tabular-nums">(0%)</span>
                        </p>
                    </div>
                    {{-- Perempuan: slate blue (sama dengan batang perempuan di daftar) --}}
                    <div class="rounded-md bg-[#547996] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/85">Perempuan</p>
                        <p class="mt-1 flex items-baseline gap-2">
                            <span id="sdmFemale" class="text-2xl font-extrabold text-white tabular-nums">0</span>
                            <span id="sdmFemalePct" class="text-xs font-semibold text-white/85 tabular-nums">(0%)</span>
                        </p>
                    </div>
                </div>

                <p id="sdmError" class="hidden mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                    Gagal memuat data pendaftar. Coba pilih ulang tahun atau muat ulang halaman.
                </p>

                <div id="sdmListContainer"
                    class="space-y-1 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar transition-opacity duration-200 {{ $sdmPerKabKota->count() > 0 ? '' : 'hidden' }}">
                </div>

                <div id="sdmEmptyState"
                    class="bg-slate-50 rounded-md p-10 text-center border border-dashed border-slate-200 {{ $sdmPerKabKota->count() > 0 ? 'hidden' : '' }}">
                    <div
                        class="w-12 h-12 bg-white border border-slate-200 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i class="fas fa-users-slash text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Belum Ada Pendaftar</p>
                    <p class="text-xs text-slate-500 mt-1">Belum ada pengguna yang mendaftar pada tahun tersebut.</p>
                </div>
            </div>
        </section>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // =========================================================
                // CONFIG & HELPERS
                // =========================================================
                const DASHBOARD_URL = @json(route('admin-province.dashboard'));
                const DEFAULT_BADGE = 'bg-[#13416B] text-white border-transparent';

                const $ = (id) => document.getElementById(id);
                const show = (el, visible = true) => el.classList.toggle('hidden', !visible);
                const toList = (value) => Array.isArray(value) ? value : Object.values(value || {});
                const formatNumber = (n) => Number(n || 0).toLocaleString('id-ID');

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));

                async function fetchDashboard(params) {
                    const query = new URLSearchParams(params).toString();
                    const response = await fetch(`${DASHBOARD_URL}?${query}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!response.ok) throw new Error(`Request gagal (${response.status})`);
                    return response.json();
                }

                function formatMultilineLabel(text) {
                    if (!text) return text;
                    const maxChars = window.innerWidth >= 640 ? 25 : 15;
                    const words = text.split(' ');
                    const lines = [];
                    let currentLine = '';

                    words.forEach((word) => {
                        if ((currentLine + word).length > maxChars) {
                            if (currentLine.trim() !== '') lines.push(currentLine.trim());
                            currentLine = word + ' ';
                        } else {
                            currentLine += word + ' ';
                        }
                    });
                    if (currentLine.trim() !== '') lines.push(currentLine.trim());
                    return lines;
                }

                // =========================================================
                // 1. GRAFIK MASA BERLAKU RTK (HORIZONTAL FLOATING BAR)
                //    Sumbu X berupa elemen HTML sticky di bawah area scroll,
                //    jadi tetap terlihat saat label sumbu Y di-scroll.
                //    Warna batang menunjukkan status masa berlaku.
                // =========================================================
                const RTK_MIN_ROW_HEIGHT = 50;
                const RTK_AXIS_HEIGHT = 28;
                const CURRENT_YEAR = new Date().getFullYear();

                const RTK_COLORS = {
                    active: '#13416B',
                    soon: '#f59e0b',
                    expired: '#94a3b8'
                };
                const RTK_STATUS_LABEL = {
                    active: 'Masih berlaku',
                    soon: 'Segera berakhir (≤ 1 tahun)',
                    expired: 'Sudah berakhir'
                };
                const rtkStatusOf = (endYear) => {
                    if (endYear < CURRENT_YEAR) return 'expired';
                    return endYear - CURRENT_YEAR <= 1 ? 'soon' : 'active';
                };

                const rtkScrollEl = $('rtkChartScroll');
                const rtkEmptyEl = $('rtkCombinedEmptyState');
                const rtkErrorEl = $('rtkChartError');
                let rtkChart = null;

                // Data sedikit: baris melebar mengisi area (tanpa ruang kosong).
                // Data banyak: 50px per baris, sisanya di-scroll.
                function fitRtkChartHeight(rowCount) {
                    if (!rtkScrollEl.clientHeight || !rowCount) return;

                    const available = rtkScrollEl.clientHeight - RTK_AXIS_HEIGHT;
                    const rowHeight = Math.max(RTK_MIN_ROW_HEIGHT, Math.floor(available / rowCount));
                    $('rtkCombinedChartContainer').style.height = (rowHeight * rowCount) + 'px';
                }

                // Label tahun sumbu X (HTML), sejajar dengan area plot chart
                function renderRtkAxis(chart) {
                    const xScale = chart.scales.x;
                    let html = '';

                    for (let year = Math.ceil(xScale.min); year <= Math.floor(xScale.max); year++) {
                        const isNow = year === CURRENT_YEAR;
                        const cls = isNow ? 'font-bold text-red-500' : 'text-slate-500';
                        html += `<span class="absolute text-xs ${cls}" style="top:6px;left:${xScale.getPixelForValue(year)}px;transform:translateX(-50%)">${year}</span>`;
                    }
                    $('rtkAxisX').innerHTML = html;
                }

                function rtkDataset(rows) {
                    const rawLabels = rows.map((r) => r.regency_name);
                    const start = rows.map((r) => Number(r.start_date));
                    const end = rows.map((r) => Number(r.end_date));
                    const min = Math.min(...start) - 1;
                    const max = Math.max(...end) + 1;

                    return {
                        count: rawLabels.length,
                        labels: rawLabels.map((label) => formatMultilineLabel(label)),
                        ranges: start.map((s, i) => [s, end[i]]),
                        colors: end.map((e) => RTK_COLORS[rtkStatusOf(e)]),
                        min: isFinite(min) ? min : 2020,
                        max: isFinite(max) ? max : 2030
                    };
                }

                const rtkPlugins = [{
                        id: 'rtkAxisSync',
                        afterLayout: (chart) => renderRtkAxis(chart)
                    },
                    {
                        // Garis putus-putus penanda tahun berjalan
                        id: 'rtkTodayLine',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea,
                                scales
                            } = chart;
                            const x = scales.x;
                            if (CURRENT_YEAR < x.min || CURRENT_YEAR > x.max) return;

                            const px = x.getPixelForValue(CURRENT_YEAR);
                            ctx.save();
                            ctx.setLineDash([4, 4]);
                            ctx.lineWidth = 1.5;
                            ctx.strokeStyle = '#ef4444';
                            ctx.beginPath();
                            ctx.moveTo(px, chartArea.top);
                            ctx.lineTo(px, chartArea.bottom);
                            ctx.stroke();
                            ctx.restore();
                        }
                    }
                ];

                function buildRtkChart(data) {
                    rtkChart = new Chart($('rtkCombinedBarChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Periode Aktif',
                                data: data.ranges,
                                backgroundColor: data.colors,
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8,
                                maxBarThickness: 48
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    right: 16
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(19, 65, 107, 0.95)',
                                    padding: 12,
                                    cornerRadius: 6,
                                    titleFont: {
                                        size: 13,
                                        weight: 'bold'
                                    },
                                    callbacks: {
                                        title: (context) => Array.isArray(context[0].label) ? context[0].label.join(' ') : context[0].label,
                                        label: (context) => {
                                            const [startYear, endYear] = context.raw;
                                            return [
                                                ` Masa Berlaku: ${startYear} s.d. ${endYear}`,
                                                ` Status: ${RTK_STATUS_LABEL[rtkStatusOf(endYear)]}`
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    min: data.min,
                                    max: data.max,
                                    border: {
                                        display: false
                                    },
                                    grid: {
                                        color: '#f1f5f9',
                                        drawTicks: false
                                    },
                                    // Label tahun digambar di #rtkAxisX (sticky), bukan di canvas
                                    ticks: {
                                        display: false,
                                        stepSize: 1
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', sans-serif"
                                        },
                                        autoSkip: false
                                    },
                                    afterFit: (scaleInstance) => {
                                        scaleInstance.width = window.innerWidth >= 640 ? 160 : 120;
                                    }
                                }
                            }
                        },
                        plugins: rtkPlugins
                    });
                    window.rtkCombinedChartInstance = rtkChart;
                }

                function renderRtk(rows) {
                    const hasData = rows.length > 0;
                    show(rtkScrollEl, hasData);
                    show(rtkEmptyEl, !hasData);
                    if (!hasData) return;

                    const data = rtkDataset(rows);
                    fitRtkChartHeight(data.count);

                    if (!rtkChart) {
                        buildRtkChart(data);
                        return;
                    }

                    rtkChart.data.labels = data.labels;
                    rtkChart.data.datasets[0].data = data.ranges;
                    rtkChart.data.datasets[0].backgroundColor = data.colors;
                    rtkChart.options.scales.x.min = data.min;
                    rtkChart.options.scales.x.max = data.max;
                    rtkChart.update();
                    rtkScrollEl.scrollTop = 0;
                }

                // Sesuaikan tinggi chart ketika ukuran area berubah (resize / kartu sebelah selesai dirender)
                new ResizeObserver(() => {
                    if (rtkChart) fitRtkChartHeight(rtkChart.data.labels.length);
                }).observe(rtkScrollEl);

                let rtkRequestId = 0;
                async function loadRtk(year) {
                    const requestId = ++rtkRequestId;
                    const select = $('rtkYearFilter');
                    select.disabled = true;
                    rtkScrollEl.classList.add('opacity-50');
                    show(rtkErrorEl, false);

                    try {
                        const res = await fetchDashboard({
                            rtk_year: year
                        });
                        if (requestId !== rtkRequestId) return;
                        renderRtk(toList(res.rtkMasaAktifPerKabKota));
                    } catch (err) {
                        console.error('Gagal memuat data RTK:', err);
                        if (requestId === rtkRequestId) show(rtkErrorEl, true);
                    } finally {
                        if (requestId === rtkRequestId) {
                            select.disabled = false;
                            rtkScrollEl.classList.remove('opacity-50');
                        }
                    }
                }
                window.fetchRtkProvinsiData = loadRtk;

                // =========================================================
                // 2. PERSETUJUAN (INFINITE SCROLL)
                // =========================================================
                const PendingApprovals = (() => {
                    const scrollEl = $('pendingScrollContainer');
                    const listEl = $('pendingApprovalList');
                    const spinnerEl = $('pendingLoadingSpinner');
                    const endEl = $('pendingEndMessage');
                    const emptyEl = $('pendingEmptyState');
                    const errorEl = $('pendingError');
                    const SCROLL_BUFFER = 30;

                    const state = {
                        page: 1,
                        hasMore: @json($hasMorePendingApprovals),
                        loading: false,
                        count: 0
                    };

                    // Menyamakan nama field data awal (server) dan data AJAX
                    const normalize = (i) => ({
                        type: i.type ?? '',
                        category: i.category ?? '',
                        badge: i.badge_color || DEFAULT_BADGE,
                        date: i.date_formatted ?? '',
                        title: i.title ?? '',
                        subtitle: i.subtitle ?? '',
                        url: i.url ?? '#',
                        action: i.action_label || 'Tinjau Persetujuan',
                        verif: {
                            label: i.verification_label ?? i.status_verif_label ?? '-',
                            cls: i.verification_color ?? i.status_verif_badge ?? ''
                        },
                        doc: {
                            label: i.document_label ?? i.status_doc_label ?? '-',
                            cls: i.document_color ?? i.status_doc_badge ?? ''
                        },
                    });

                    // Warna solid + ikon putih, ditentukan dari warna class bawaan server
                    const STATUS_TONES = [{
                            match: /green|emerald|teal|lime/,
                            bg: 'bg-emerald-500',
                            icon: 'fa-check-circle'
                        },
                        {
                            match: /red|rose|pink/,
                            bg: 'bg-red-500',
                            icon: 'fa-times-circle'
                        },
                        {
                            match: /amber|yellow|orange/,
                            bg: 'bg-amber-500',
                            icon: 'fa-clock'
                        },
                        {
                            match: /blue|sky|indigo|cyan/,
                            bg: 'bg-[#13416B]',
                            icon: 'fa-info-circle'
                        },
                    ];
                    const FALLBACK_TONE = {
                        bg: 'bg-slate-500',
                        icon: 'fa-info-circle'
                    };

                    const statusTone = (cls) => STATUS_TONES.find((t) => t.match.test(cls)) || FALLBACK_TONE;

                    const statusRow = (caption, {
                        label,
                        cls
                    }) => {
                        const tone = statusTone(cls);
                        return `
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-500 shrink-0">${caption}</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium text-white ${tone.bg}">
                                    <i class="fas ${tone.icon} text-white"></i>${escapeHtml(label)}
                                </span>
                            </div>`;
                    };

                    const cardTemplate = (raw) => {
                        const item = normalize(raw);
                        const accent = 'bg-[#13416B]';
                        const typeIcon = 'fa-file-signature';
                        const statuses = `
                            <div class="mt-3 space-y-2">
                                ${statusRow('Verifikasi', item.verif)}
                                ${statusRow('Dokumen', item.doc)}
                            </div>`;

                        return `
                            <article class="relative pl-5 pr-4 py-4 rounded-md border border-slate-200 bg-white overflow-hidden transition-all duration-200 hover:bg-slate-50 hover:shadow-sm">
                                <span class="absolute left-0 top-0 bottom-0 w-1 ${accent}"></span>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md border text-xs font-medium ${item.badge}">
                                        <i class="fas ${typeIcon} text-[10px]"></i>${escapeHtml(item.category)}
                                    </span>
                                    <span class="text-xs text-slate-500 shrink-0"><i class="far fa-clock mr-1"></i>${escapeHtml(item.date)}</span>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-900 line-clamp-2" title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</h3>
                                <p class="mt-0.5 text-xs text-slate-500 truncate">${escapeHtml(item.subtitle)}</p>
                                ${statuses}
                                <div class="flex justify-end mt-3 pt-3 border-t border-slate-100">
                                    <a href="${escapeHtml(item.url)}" class="inline-flex items-center text-sm font-semibold text-[#13416B] hover:text-[#547996] transition-colors">
                                        ${escapeHtml(item.action)}<i class="fas fa-chevron-right ml-1.5 text-xs"></i>
                                    </a>
                                </div>
                            </article>`;
                    };

                    function append(items) {
                        listEl.insertAdjacentHTML('beforeend', items.map(cardTemplate).join(''));
                        state.count += items.length;
                        syncStatus();
                    }

                    function syncStatus() {
                        const isEmpty = state.count === 0;
                        emptyEl.classList.toggle('hidden', !isEmpty);
                        emptyEl.classList.toggle('flex', isEmpty);
                        show(endEl, !state.hasMore && !isEmpty);
                    }

                    async function loadMore() {
                        if (state.loading || !state.hasMore) return;
                        state.loading = true;
                        show(spinnerEl, true);
                        show(errorEl, false);
                        let loadedCount = 0;

                        try {
                            const res = await fetchDashboard({
                                pending_page: state.page + 1
                            });
                            state.page = res.page;
                            state.hasMore = res.has_more;
                            loadedCount = res.data.length;
                            append(res.data);
                        } catch (err) {
                            console.error('Gagal memuat data tindakan:', err);
                            show(errorEl, true);
                        } finally {
                            state.loading = false;
                            show(spinnerEl, false);
                            syncStatus();
                        }

                        if (loadedCount > 0) requestAnimationFrame(fillIfShort);
                    }

                    // Jika data awal belum cukup untuk memunculkan scroll, muat halaman berikutnya
                    // (hanya sampai area scroll terisi; selebihnya dimuat saat user menggulir).
                    function fillIfShort() {
                        if (state.hasMore && !state.loading &&
                            scrollEl.scrollHeight <= scrollEl.clientHeight + SCROLL_BUFFER) {
                            loadMore();
                        }
                    }

                    function init(initialItems) {
                        append(initialItems);
                        scrollEl.addEventListener('scroll', () => {
                            const nearBottom = scrollEl.scrollTop + scrollEl.clientHeight >=
                                scrollEl.scrollHeight - SCROLL_BUFFER;
                            if (nearBottom) loadMore();
                        });
                        $('pendingRetry').addEventListener('click', loadMore);
                        requestAnimationFrame(fillIfShort);
                    }

                    return {
                        init
                    };
                })();

                // =========================================================
                // 3. LEADERBOARD E-LEARNING
                // =========================================================
                const SdmLeaderboard = (() => {
                    const listEl = $('sdmListContainer');
                    const emptyEl = $('sdmEmptyState');
                    const errorEl = $('sdmError');
                    let requestId = 0;

                    const rankClass = (index) => index < 3 ?
                        'bg-[#13416B] text-white' :
                        'bg-slate-100 text-slate-500';

                    const rowTemplate = (item, index, maxTotal, grandTotal) => {
                        const malePct = (item.male / maxTotal) * 100;
                        const femalePct = (item.female / maxTotal) * 100;
                        const share = grandTotal > 0 ? ((item.total / grandTotal) * 100).toFixed(1).replace('.', ',') : '0';

                        return `
                            <div class="flex items-start gap-4 px-2 py-3 rounded-md transition-colors duration-200 hover:bg-slate-50">
                                <span class="w-7 h-7 rounded-md flex items-center justify-center text-xs font-bold tabular-nums shrink-0 ${rankClass(index)}">${index + 1}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="text-sm font-semibold text-slate-900 truncate">${escapeHtml(item.regency_name)}</span>
                                        <span class="shrink-0 text-sm text-slate-500">
                                            <strong class="text-base font-bold text-[#13416B] tabular-nums">${formatNumber(item.total)}</strong> peserta
                                            <span class="hidden sm:inline text-xs text-slate-400 ml-1">(${share}%)</span>
                                        </span>
                                    </div>
                                    <div class="mt-2 w-full h-2 rounded-full bg-slate-100 overflow-hidden flex">
                                        <div class="bg-[#13416B] h-full transition-all duration-500 ease-out" style="width: ${malePct}%" title="Laki-laki: ${item.male}"></div>
                                        <div class="bg-[#547996] h-full transition-all duration-500 ease-out" style="width: ${femalePct}%" title="Perempuan: ${item.female}"></div>
                                    </div>
                                    <div class="mt-1.5 flex gap-4 text-xs text-slate-500">
                                        <span><i class="fas fa-male text-[#13416B] mr-1"></i>${formatNumber(item.male)}</span>
                                        <span><i class="fas fa-female text-[#547996] mr-1"></i>${formatNumber(item.female)}</span>
                                    </div>
                                </div>
                            </div>`;
                    };

                    function renderSummary(data) {
                        const male = data.reduce((sum, i) => sum + Number(i.male || 0), 0);
                        const female = data.reduce((sum, i) => sum + Number(i.female || 0), 0);
                        const total = data.reduce((sum, i) => sum + Number(i.total || 0), 0);
                        const pct = (v) => '(' + (total > 0 ? Math.round((v / total) * 100) : 0) + '%)';

                        $('sdmTotal').textContent = formatNumber(total);
                        $('sdmMale').textContent = formatNumber(male);
                        $('sdmFemale').textContent = formatNumber(female);
                        $('sdmMalePct').textContent = pct(male);
                        $('sdmFemalePct').textContent = pct(female);
                        return total;
                    }

                    function render(data) {
                        const list = toList(data);
                        const hasData = list.length > 0;
                        show(listEl, hasData);
                        show(emptyEl, !hasData);
                        const grandTotal = renderSummary(list);
                        if (!hasData) return;

                        const sorted = [...list].sort((a, b) => b.total - a.total);
                        const maxTotal = Math.max(...sorted.map((i) => i.total), 1);
                        listEl.innerHTML = sorted.map((item, i) => rowTemplate(item, i, maxTotal, grandTotal)).join('');
                    }

                    async function loadYear(year) {
                        const current = ++requestId;
                        const select = $('sdmYearFilter');
                        select.disabled = true;
                        listEl.classList.add('opacity-50');
                        show(errorEl, false);

                        try {
                            const res = await fetchDashboard({
                                sdm_year: year
                            });
                            if (current !== requestId) return;
                            render(res.sdmPerKabKota);
                        } catch (err) {
                            console.error('Gagal memuat data SDM:', err);
                            if (current === requestId) show(errorEl, true);
                        } finally {
                            if (current === requestId) {
                                select.disabled = false;
                                listEl.classList.remove('opacity-50');
                            }
                        }
                    }

                    return {
                        render,
                        loadYear
                    };
                })();

                // =========================================================
                // INIT
                // =========================================================
                renderRtk(toList(@json($rtkMasaAktifPerKabKota->values())));
                PendingApprovals.init(@json(collect($initialPendingApprovals)->values()));
                SdmLeaderboard.render(@json($sdmPerKabKota->values()));

                $('rtkYearFilter').addEventListener('change', (e) => loadRtk(e.target.value));
                $('sdmYearFilter').addEventListener('change', (e) => SdmLeaderboard.loadYear(e.target.value));
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>