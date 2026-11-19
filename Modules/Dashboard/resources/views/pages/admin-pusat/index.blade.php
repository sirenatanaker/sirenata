<x-dashboard::layouts.dashboard title="Dashboard Admin Pusat">
    @php
        // ---- Angka ringkasan RTK ----
        $rtkApproved = (int) $rtkStatusDistribution->get('approved', 0);
        $rtkPending  = (int) $rtkStatusDistribution->get('pending', 0);
        $rtkRejected = (int) $rtkStatusDistribution->get('rejected', 0);
        $rtkTotal    = $rtkApproved + $rtkPending + $rtkRejected;
        $rtkAktif    = (int) ($rtkMasaAktifPerProvinsi->sum('total') ?? 0);

        $kpis = [
            [
                'label' => 'RTK Disetujui',
                'value' => $rtkApproved,
                'hint' => 'Dokumen terverifikasi',
                'icon' => 'fa-check-double',
                'bg' => 'bg-emerald-600',
                'text' => 'text-emerald-600',
            ],
            [
                'label' => 'Menunggu Verifikasi',
                'value' => $rtkPending,
                'hint' => 'RTKD provinsi & kab/kota',
                'icon' => 'fa-hourglass-half',
                'bg' => 'bg-amber-500',
                'text' => 'text-amber-500',
            ],
            [
                'label' => 'RTK Ditolak',
                'value' => $rtkRejected,
                'hint' => 'Dikembalikan ke daerah',
                'icon' => 'fa-ban',
                'bg' => 'bg-red-500',
                'text' => 'text-red-500',
            ],
            [
                'label' => 'RTK Berlaku',
                'value' => $rtkAktif,
                'hint' => 'Dokumen acuan aktif',
                'icon' => 'fa-file-contract',
                'bg' => 'bg-[#13416B]',
                'text' => 'text-[#13416B]',
            ],
        ];
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6 bg-slate-50 min-h-screen">

        {{-- ===================================== --}}
        {{-- 1. KPI (Solid Colored Cards)          --}}
        {{-- ===================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            @foreach ($kpis as $kpi)
                <div
                    class="relative overflow-hidden {{ $kpi['bg'] }} rounded-md p-5 sm:p-6 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group z-0">
                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-white/85 text-xs font-bold uppercase mb-1 tracking-wider">{{ $kpi['label'] }}</p>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-white tabular-nums">
                                {{ number_format($kpi['value'], 0, ',', '.') }}
                            </h3>
                            <p class="text-[10px] sm:text-xs text-white/80 mt-1">{{ $kpi['hint'] }}</p>
                        </div>
                        <div class="shrink-0 text-white/80 group-hover:text-white transition-all duration-300 transform group-hover:scale-110">
                            <i class="fas {{ $kpi['icon'] }} text-2xl sm:text-3xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <!-- Efek Dekoratif Bubbles Geometris Profesional -->
                    <div class="absolute inset-0 pointer-events-none z-0">
                        <div class="absolute -top-12 -right-10 w-44 h-44 border-[16px] border-white/5 rounded-full group-hover:scale-105 transition-transform duration-500"></div>
                        <div class="absolute -bottom-10 right-10 w-28 h-28 bg-white/5 rounded-full"></div>
                        <div class="absolute top-3 right-24 w-12 h-12 border-[4px] border-white/15 rounded-full"></div>
                        <div class="absolute bottom-4 right-32 w-3 h-3 bg-white/20 rounded-full"></div>
                        <div class="absolute top-5 right-40 w-2 h-2 bg-white/10 rounded-full"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- 2. GRAFIK MASA BERLAKU RTK & PERSETUJUAN                  --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- KIRI: GRAFIK MASA BERLAKU RTK --}}
            <section
                class="lg:col-span-8 xl:col-span-8 bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <header
                    class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-slate-900">Komparasi Masa Berlaku RTK</h2>
                        <p class="text-sm text-slate-500">Tahun penyusunan dan masa berakhir dokumen per Provinsi</p>
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
                <div class="px-5 sm:px-6 pt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#13416B]"></span>Masih berlaku</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-500"></span>Segera berakhir (&le; 1 tahun)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-slate-400"></span>Sudah berakhir</span>
                    <span class="inline-flex items-center gap-1.5"><span class="inline-block w-4 border-t-2 border-dashed border-red-500"></span>Tahun ini ({{ now()->year }})</span>
                </div>

                <div class="p-5 sm:p-6 flex-1 flex flex-col">
                    <p id="rtkChartError" class="hidden mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                        Gagal memuat data RTK. Coba pilih ulang tahun atau muat ulang halaman.
                    </p>

                    <div class="relative flex-1 min-h-[420px] sm:min-h-[460px] flex flex-col">
                        {{-- Area scroll: label sumbu Y ikut scroll, sumbu X tetap menempel di bawah --}}
                        <div id="rtkChartScroll"
                            class="absolute inset-0 overflow-y-auto custom-scrollbar transition-opacity duration-200 {{ $rtkMasaAktifPerProvinsi->count() > 0 ? '' : 'hidden' }}">
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
                            class="w-full flex-1 min-h-[420px] sm:min-h-[460px] flex flex-col items-center justify-center py-16 px-6 sm:px-12 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200 transition-all {{ $rtkMasaAktifPerProvinsi->count() > 0 ? 'hidden' : '' }}">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white border border-slate-200/90 shadow-xs flex items-center justify-center mb-4 text-slate-300">
                                <i class="fas fa-file-contract text-2xl sm:text-3xl text-slate-300"></i>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-700 mb-1">Belum ada data RTK</h3>
                            <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                                Belum ada penyusunan dokumen RTK Provinsi yang tercatat.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- KANAN: PERLU PERSETUJUAN --}}
            <section id="pendingSection"
                class="lg:col-span-4 xl:col-span-4 bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col scroll-mt-6">
                <header class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">Perlu Persetujuan</h2>
                            <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-xs font-bold text-white tabular-nums shadow-2xs {{ $totalPendingApprovals > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}">
                                {{ number_format($totalPendingApprovals, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">RTKD (provinsi) &amp; Projek Daerah yang menunggu persetujuan</p>
                </header>

                {{-- Satu-satunya elemen yang scroll, dipakai untuk infinite scroll --}}
                <div id="pendingScrollContainer" class="p-4 flex-1 max-h-[500px] overflow-y-auto custom-scrollbar">
                    <div id="pendingApprovalList" class="space-y-3"></div>

                    <div id="pendingEmptyState" class="hidden flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-md bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 mb-3">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Tidak ada persetujuan tertunda</p>
                        <p class="text-xs text-slate-500 mt-1">Semua RTK dan projek telah diproses.</p>
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
                        Semua data persetujuan telah ditampilkan
                    </p>
                </div>
            </section>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. GANTT CHART PERIODE EVENT (LMS BY EVENT)               --}}
        {{-- ========================================================= --}}
        @php
            $lmsSummary = $lmsEventOverview['summary'] ?? [];
            $lmsHasEvents = !empty($lmsEventOverview['events']);
            $todayFormatted = $lmsEventOverview['today_label'] ?? now()->translatedFormat('d M Y');
        @endphp
        <section class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden">
            <header
                class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900">Gantt Chart Periode Event ({{ $selectedEventYear }})</h2>
                    <p class="text-sm text-slate-500">Linimasa jadwal pendaftaran, pelaksanaan Zoom, dan evaluasi seluruh gelombang pelatihan tahun ini</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <label for="eventStatusFilter" class="sr-only">Filter status event</label>
                    <select id="eventStatusFilter"
                        class="text-sm border-slate-200 rounded-md focus:ring-[#13416B] focus:border-[#13416B] cursor-pointer bg-white">
                        <option value="all">Semua Status</option>
                        <option value="ongoing">Sedang Berlangsung</option>
                        <option value="open_registration">Pendaftaran Buka</option>
                        <option value="completed">Selesai</option>
                    </select>

                    <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shadow-2xs">
                        <span>Kelola Periode Event</span>
                    </a>
                </div>
            </header>

            {{-- Legenda Fase --}}
            <div class="px-5 sm:px-6 py-3 border-b border-slate-100 bg-slate-50/50">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-xs bg-sky-300"></span>Pendaftaran
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-xs bg-[#2B6496]"></span>Pelaksanaan &amp; Live Zoom
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-xs bg-amber-400"></span>Evaluasi &amp; Pengajuan PPSDM
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-4 border-t-2 border-dashed border-red-500"></span>Hari Ini ({{ $todayFormatted }})
                    </span>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <p id="lmsEventError" class="hidden mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                    Gagal memuat data periode event LMS. Coba pilih ulang tahun atau muat ulang halaman.
                </p>

                {{-- Kontainer Gantt Chart --}}
                <div id="ganttWrapper"
                     class="border border-slate-200 rounded-md overflow-hidden bg-white {{ $lmsHasEvents ? '' : 'hidden' }}">
                    <div id="ganttScrollArea" class="overflow-x-auto overflow-y-auto max-h-[540px] custom-scrollbar relative">
                        <div id="ganttInner" class="min-w-[880px] relative">
                            {{-- Header Bulan & Tanggal (Sticky Top) --}}
                            <div id="ganttHeader" class="sticky top-0 z-30 bg-slate-50 border-b border-slate-200"></div>
                            {{-- Baris-Baris Event --}}
                            <div id="ganttRows" class="divide-y divide-slate-100 relative"></div>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="lmsEmptyState"
                    class="bg-slate-50 rounded-md p-10 text-center border border-dashed border-slate-200 {{ $lmsHasEvents ? 'hidden' : '' }}">
                    <div
                        class="w-12 h-12 bg-white border border-slate-200 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i class="fas fa-calendar-xmark text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Tidak Ada Periode Event</p>
                    <p class="text-xs text-slate-500 mt-1">Belum ada gelombang Bimtek yang sesuai dengan filter tahun atau status yang dipilih.</p>
                </div>
            </div>
        </section>

        {{-- Floating Tooltip Gantt Chart --}}
        <div id="ganttTooltip"
             class="fixed z-[100] hidden pointer-events-none w-72 bg-[#13416B] text-white rounded-md shadow-xl border border-white/15 p-3.5 text-xs space-y-2">
        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // =========================================================
                // CONFIG & HELPERS
                // =========================================================
                const DASHBOARD_URL = @json(route('admin-pusat.dashboard'));
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
                const RTK_MIN_ROW_HEIGHT = 54;
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
                    const rawLabels = rows.map((r) => r.province_name);
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
                                            size: 13,
                                            weight: '500',
                                            family: "'Inter', sans-serif"
                                        },
                                        color: '#334155',
                                        autoSkip: false
                                    },
                                    afterFit: (scaleInstance) => {
                                        scaleInstance.width = window.innerWidth >= 640 ? 185 : 135;
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
                        renderRtk(toList(res.rtkMasaAktifPerProvinsi));
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
                window.fetchRtkPusatData = loadRtk;

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
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-white ${tone.bg}">
                                    ${escapeHtml(label)}
                                </span>
                            </div>`;
                    };

                    const cardTemplate = (raw) => {
                        const item = normalize(raw);
                        const isRtk = item.type === 'rtk';
                        const statuses = isRtk ? `
                            <div class="mt-3 space-y-2">
                                ${statusRow('Verifikasi', item.verif)}
                                ${statusRow('Dokumen', item.doc)}
                            </div>` : '';

                        return `
                            <article class="p-4 rounded-md bg-slate-50 transition-colors duration-200 hover:bg-slate-100">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900 line-clamp-2" title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</h3>
                                    <span class="text-xs text-slate-500 shrink-0 whitespace-nowrap pt-0.5"><i class="far fa-clock mr-1"></i>${escapeHtml(item.date)}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 truncate">${escapeHtml(item.subtitle)} - <span class="font-semibold text-[#13416B]">${escapeHtml(item.category)}</span></p>
                                ${statuses}
                                <div class="flex justify-end mt-3 pt-3 border-t border-slate-200/60">
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
                            console.error('Gagal memuat persetujuan:', err);
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
                // 3. GANTT CHART PERIODE EVENT (LMS BY EVENT)
                // =========================================================
                const LmsGanttSection = (() => {
                    const wrapperEl = $('ganttWrapper');
                    const headerEl = $('ganttHeader');
                    const rowsEl = $('ganttRows');
                    const emptyEl = $('lmsEmptyState');
                    const errorEl = $('lmsEventError');
                    const tooltipEl = $('ganttTooltip');

                    const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const DAY_MS = 86400000;

                    let currentOverview = null;
                    let currentStatusFilter = 'all';
                    let requestId = 0;

                    const parseIsoDate = (iso) => {
                        if (!iso) return null;
                        const [y, m, d] = iso.split('-').map(Number);
                        return Date.UTC(y, m - 1, d);
                    };

                    const formatTick = (ms) => {
                        const dt = new Date(ms);
                        return `${dt.getUTCDate()} ${MONTH_SHORT[dt.getUTCMonth()]}`;
                    };

                    function computeDomain(events, todayMs) {
                        let minMs = Infinity;
                        let maxMs = -Infinity;

                        events.forEach((ev) => {
                            [ev.reg_start, ev.reg_end, ev.exec_start, ev.exec_end, ev.eval_start, ev.eval_end].forEach((d) => {
                                const ms = parseIsoDate(d);
                                if (ms !== null) {
                                    if (ms < minMs) minMs = ms;
                                    if (ms > maxMs) maxMs = ms;
                                }
                            });
                        });

                        if (!isFinite(minMs) || !isFinite(maxMs)) {
                            const y = currentOverview?.year || new Date().getFullYear();
                            return { minMs: Date.UTC(y, 0, 1), maxMs: Date.UTC(y, 11, 31) };
                        }

                        // Pastikan hari ini masuk rentang jika masih dekat dengan periode event
                        if (todayMs && todayMs >= minMs - 14 * DAY_MS && todayMs <= maxMs + 14 * DAY_MS) {
                            minMs = Math.min(minMs, todayMs);
                            maxMs = Math.max(maxMs, todayMs);
                        }

                        // Padding 2 hari kiri & kanan agar bar ujung tidak terpotong
                        minMs -= 2 * DAY_MS;
                        maxMs += 3 * DAY_MS;
                        if (maxMs <= minMs) maxMs = minMs + 14 * DAY_MS;

                        return { minMs, maxMs };
                    }

                    function buildTicks(minMs, maxMs) {
                        const totalDays = Math.max(1, Math.round((maxMs - minMs) / DAY_MS));
                        const ticks = [];

                        if (totalDays > 180) {
                            // Tick per awal bulan
                            const startDt = new Date(minMs);
                            let y = startDt.getUTCFullYear();
                            let m = startDt.getUTCMonth();
                            while (Date.UTC(y, m, 1) <= maxMs) {
                                const ms = Date.UTC(y, m, 1);
                                if (ms >= minMs) {
                                    ticks.push({ ms, label: `${MONTH_SHORT[m]} ${y}` });
                                }
                                m++;
                                if (m > 11) { m = 0; y++; }
                            }
                        } else {
                            const stepDays = totalDays <= 45 ? 5 : (totalDays <= 110 ? 7 : 14);
                            for (let ms = minMs; ms <= maxMs; ms += stepDays * DAY_MS) {
                                ticks.push({ ms, label: formatTick(ms) });
                            }
                        }

                        // Segment bulan untuk baris header atas
                        const months = [];
                        const sDt = new Date(minMs);
                        let y = sDt.getUTCFullYear();
                        let m = sDt.getUTCMonth();
                        while (Date.UTC(y, m, 1) <= maxMs) {
                            const mStart = Math.max(minMs, Date.UTC(y, m, 1));
                            const nextM = m === 11 ? Date.UTC(y + 1, 0, 1) : Date.UTC(y, m + 1, 1);
                            const mEnd = Math.min(maxMs, nextM);
                            if (mEnd > mStart) {
                                months.push({
                                    label: `${MONTH_SHORT[m]} ${y}`,
                                    leftPct: ((mStart - minMs) / (maxMs - minMs)) * 100,
                                    widthPct: ((mEnd - mStart) / (maxMs - minMs)) * 100,
                                });
                            }
                            m++;
                            if (m > 11) { m = 0; y++; }
                        }

                        return { ticks, months };
                    }

                    function calcSegment(startIso, endIso, minMs, spanMs) {
                        const s = parseIsoDate(startIso);
                        const e = parseIsoDate(endIso);
                        if (s === null || e === null) return null;
                        // Tambah 1 hari pada end agar tanggal akhir bersifat inklusif secara visual
                        const endInclusive = Math.max(s + DAY_MS, e + DAY_MS);
                        const clampedS = Math.max(minMs, s);
                        const clampedE = Math.min(minMs + spanMs, endInclusive);
                        if (clampedE <= clampedS) return null;

                        const left = ((clampedS - minMs) / spanMs) * 100;
                        const width = Math.max(1.2, ((clampedE - clampedS) / spanMs) * 100);
                        return { left, width: Math.min(width, 100 - left) };
                    }

                    function positionTooltip(clientX, clientY) {
                        if (!tooltipEl || tooltipEl.classList.contains('hidden')) return;
                        const pad = 14;
                        const rect = tooltipEl.getBoundingClientRect();
                        let x = clientX + pad;
                        let y = clientY + pad;
                        if (x + rect.width > window.innerWidth - 10) x = clientX - rect.width - pad;
                        if (y + rect.height > window.innerHeight - 10) y = clientY - rect.height - pad;
                        tooltipEl.style.left = `${Math.max(8, x)}px`;
                        tooltipEl.style.top = `${Math.max(8, y)}px`;
                    }

                    function showTooltip(ev, clientX, clientY) {
                        if (!tooltipEl) return;
                        const quotaText = ev.quota ? `${formatNumber(ev.registered)} / ${formatNumber(ev.quota)}` : formatNumber(ev.registered);
                        tooltipEl.innerHTML = `
                            <div class="border-b border-white/15 pb-2">
                                <p class="font-bold text-white leading-snug">${escapeHtml(ev.batch_name)}</p>
                                <p class="text-[11px] text-blue-200 mt-0.5">${escapeHtml(ev.course_name)}</p>
                            </div>
                            <div class="space-y-1 text-[11px]">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-sky-400"></span>Pendaftaran</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.reg_label)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-white"></span>Pelaksanaan</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.exec_label)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-blue-100"><span class="w-2 h-2 rounded-xs bg-amber-400"></span>Evaluasi &amp; PPSDM</span>
                                    <span class="font-semibold text-white tabular-nums">${escapeHtml(ev.eval_label)}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-white/15 flex items-center justify-between text-[11px] text-blue-100 tabular-nums">
                                <span>Peserta: <strong class="text-white">${quotaText}</strong></span>
                                <span>Tuntas: <strong class="text-emerald-300">${formatNumber(ev.completed)}</strong></span>
                                <span>Terbit: <strong class="text-amber-300">${formatNumber(ev.certified)}</strong></span>
                            </div>`;
                        tooltipEl.classList.remove('hidden');
                        positionTooltip(clientX, clientY);
                    }

                    function hideTooltip() {
                        if (tooltipEl) tooltipEl.classList.add('hidden');
                    }

                    function render(overview) {
                        currentOverview = overview || {};

                        const allEvents = toList(currentOverview.events);
                        const filtered = currentStatusFilter === 'all'
                            ? allEvents
                            : allEvents.filter((e) => e.status_key === currentStatusFilter);

                        const hasData = filtered.length > 0;
                        show(wrapperEl, hasData);
                        show(emptyEl, !hasData);
                        hideTooltip();

                        if (!hasData) return;

                        const todayMs = parseIsoDate(currentOverview.today);
                        const { minMs, maxMs } = computeDomain(filtered, todayMs);
                        const spanMs = maxMs - minMs;
                        const { ticks, months } = buildTicks(minMs, maxMs);

                        const todayInRange = todayMs !== null && todayMs >= minMs && todayMs <= maxMs;
                        const todayPct = todayInRange ? ((todayMs - minMs) / spanMs) * 100 : null;

                        // Render Sticky Header (Bulan + Ticks Tanggal)
                        const monthBandsHtml = months.map((m) => `
                            <div class="absolute top-0 bottom-0 flex items-center px-2 border-r border-slate-200/80 text-xs font-bold uppercase tracking-wider text-[#13416B] truncate"
                                 style="left:${m.leftPct}%;width:${m.widthPct}%">
                                ${escapeHtml(m.label)}
                            </div>`).join('');

                        const tickLabelsHtml = ticks.map((t) => {
                            const pct = ((t.ms - minMs) / spanMs) * 100;
                            return `
                                <span class="absolute top-1 text-xs font-medium text-slate-600 -translate-x-1/2 whitespace-nowrap"
                                      style="left:${pct}%">
                                    ${escapeHtml(t.label)}
                                </span>`;
                        }).join('');

                        const todayHeadBadge = todayInRange
                            ? `<div class="absolute top-0 bottom-0 border-l-2 border-dashed border-red-500 z-20 pointer-events-none" style="left:${todayPct}%">
                                   <span class="absolute -top-0.5 left-1 px-1.5 py-0.5 rounded bg-red-500 text-white text-[11px] font-bold whitespace-nowrap shadow-2xs">Hari Ini</span>
                               </div>`
                            : '';

                        headerEl.innerHTML = `
                            <div class="grid grid-cols-[280px_1fr] lg:grid-cols-[330px_1fr]">
                                <div class="px-4 py-3 border-r border-slate-200 flex items-center bg-slate-50">
                                    <span class="text-sm font-bold uppercase tracking-wider text-slate-700">Gelombang Event</span>
                                </div>
                                <div class="relative bg-slate-50">
                                    <div class="relative h-8 border-b border-slate-200/80 overflow-hidden">${monthBandsHtml}</div>
                                    <div class="relative h-7 overflow-hidden">${tickLabelsHtml}</div>
                                    ${todayHeadBadge}
                                </div>
                            </div>`;

                        // Gridlines & Garis Hari Ini yang konsisten di tiap baris
                        const gridLinesHtml = ticks.map((t) => {
                            const pct = ((t.ms - minMs) / spanMs) * 100;
                            return `<div class="absolute top-0 bottom-0 border-l border-slate-100 pointer-events-none" style="left:${pct}%"></div>`;
                        }).join('');

                        const todayLineHtml = todayInRange
                            ? `<div class="absolute top-0 bottom-0 border-l-2 border-dashed border-red-500/80 z-20 pointer-events-none" style="left:${todayPct}%"></div>`
                            : '';

                        // Render Baris Gantt per Event
                        rowsEl.innerHTML = filtered.map((ev, idx) => {
                            const regSeg = calcSegment(ev.reg_start, ev.reg_end, minMs, spanMs);
                            const execSeg = calcSegment(ev.exec_start, ev.exec_end, minMs, spanMs);
                            const evalSeg = calcSegment(ev.eval_start, ev.eval_end, minMs, spanMs);

                            const regBar = regSeg
                                ? `<a href="${escapeHtml(ev.url)}" class="absolute top-1/2 -translate-y-1/2 h-4 rounded-md bg-sky-300 hover:bg-sky-400 transition-colors z-10" style="left:${regSeg.left}%;width:${regSeg.width}%"></a>`
                                : '';
                            const evalBar = evalSeg
                                ? `<a href="${escapeHtml(ev.eval_url)}" class="absolute top-1/2 -translate-y-1/2 h-4 rounded-md bg-amber-400 hover:bg-amber-500 transition-colors z-10" style="left:${evalSeg.left}%;width:${evalSeg.width}%"></a>`
                                : '';
                            const execBar = execSeg
                                ? `<a href="${escapeHtml(ev.url)}" class="absolute top-1/2 -translate-y-1/2 h-7 rounded-md bg-[#2B6496] hover:bg-[#1B568A] shadow-2xs transition-colors z-15 flex items-center px-2 overflow-hidden" style="left:${execSeg.left}%;width:${execSeg.width}%">
                                       <span class="text-xs font-semibold text-white truncate">${execSeg.width >= 12 ? escapeHtml(ev.exec_label) : ''}</span>
                                   </a>`
                                : '';

                            const hasPendingEval = Number(ev.pending_eval || 0) > 0;
                            const hasReadyPpsdm = Number(ev.ready_ppsdm || 0) > 0;

                            return `
                                <div class="gantt-row grid grid-cols-[280px_1fr] lg:grid-cols-[330px_1fr] hover:bg-blue-50/35 transition-colors" data-idx="${idx}">
                                    <div class="px-4 py-3 border-r border-slate-200 min-w-0 flex flex-col justify-center gap-1.5">
                                        <a href="${escapeHtml(ev.url)}"
                                           class="text-sm sm:text-[15px] font-semibold text-slate-900 hover:text-[#13416B] line-clamp-2 leading-snug"
                                           title="${escapeHtml(ev.batch_name)}">
                                            ${escapeHtml(ev.batch_name)}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            <span class="inline-flex px-2 py-0.5 rounded font-bold ${escapeHtml(ev.status_class)}">${escapeHtml(ev.status_label)}</span>
                                            <span class="text-slate-600 font-semibold tabular-nums">${formatNumber(ev.registered)} Peserta</span>
                                            ${hasPendingEval ? `<a href="${escapeHtml(ev.eval_url)}" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 hover:bg-amber-200 font-bold tabular-nums">${formatNumber(ev.pending_eval)} Dinilai</a>` : ''}
                                            ${hasReadyPpsdm ? `<a href="${escapeHtml(ev.ppsdm_url)}" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 hover:bg-emerald-200 font-bold tabular-nums">${formatNumber(ev.ready_ppsdm)} PPSDM</a>` : ''}
                                        </div>
                                    </div>
                                    <div class="relative min-h-[68px] overflow-hidden">
                                        ${gridLinesHtml}
                                        ${todayLineHtml}
                                        ${regBar}
                                        ${evalBar}
                                        ${execBar}
                                    </div>
                                </div>`;
                        }).join('');

                        // Bind hover tooltip untuk setiap baris Gantt
                        rowsEl.querySelectorAll('.gantt-row').forEach((rowEl) => {
                            const ev = filtered[Number(rowEl.dataset.idx)];
                            if (!ev) return;
                            rowEl.addEventListener('mouseenter', (e) => showTooltip(ev, e.clientX, e.clientY));
                            rowEl.addEventListener('mousemove', (e) => positionTooltip(e.clientX, e.clientY));
                            rowEl.addEventListener('mouseleave', hideTooltip);
                        });
                    }

                    function setStatusFilter(status) {
                        currentStatusFilter = status;
                        if (currentOverview) render(currentOverview);
                    }

                    return { render, setStatusFilter };
                })();

                // =========================================================
                // INIT
                // =========================================================
                renderRtk(toList(@json($rtkMasaAktifPerProvinsi->values())));
                PendingApprovals.init(@json(collect($initialPendingApprovals)->values()));
                LmsGanttSection.render(@json($lmsEventOverview));

                $('rtkYearFilter').addEventListener('change', (e) => loadRtk(e.target.value));
                $('eventStatusFilter').addEventListener('change', (e) => LmsGanttSection.setStatusFilter(e.target.value));
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>