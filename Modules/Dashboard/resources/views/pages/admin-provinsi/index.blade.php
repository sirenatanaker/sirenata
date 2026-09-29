<x-dashboard::layouts.dashboard title="Dashboard Admin Provinsi">
    <div class="p-4 sm:p-6 lg:p-6 max-w-full mx-auto space-y-6 bg-slate-50 min-h-screen">

        <!-- Header & Greeting -->
        <div class="flex flex-col gap-4">


            <!-- Peringatan Wilayah Belum Ditetapkan -->
            @if (!$user->hasCompleteScope())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 sm:p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
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
        </div>

        <!-- ===================================== -->
        <!-- 1. STATS GRID (4 Core Color Palette)  -->
        <!-- ===================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

            <!-- RTK Disetujui (Navy #13416B) -->
            <div class="relative overflow-hidden bg-[#13416B] text-white rounded-md p-5 sm:p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-1 group z-0">
                <div class="absolute -right-6 -bottom-6 text-white opacity-[0.05] group-hover:opacity-[0.1] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-check-double text-[130px]"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-white text-sm font-semibold uppercase tracking-wider mb-1">RTK Disetujui</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $rtkStatusDistribution->get('approved', 0) }}</h3>
                    <p class="text-[10px] text-white mt-1">Verifikasi disetujui</p>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#13416B] flex items-center justify-center shrink-0 shadow-sm relative z-10 transition-transform duration-300 group-hover:scale-105">
                    <i class="fas fa-check-double text-xl sm:text-2xl"></i>
                </div>
            </div>

            <!-- RTK Menunggu Verifikasi (Slate Blue #547996) -->
            <div class="relative overflow-hidden bg-[#547996] text-white rounded-md p-5 sm:p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-1 group z-0">
                <div class="absolute -right-6 -bottom-6 text-white opacity-[0.05] group-hover:opacity-[0.1] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-hourglass-half text-[130px]"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-white text-sm font-semibold uppercase tracking-wider mb-1">Status Menunggu</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $rtkStatusDistribution->get('pending', 0) }}</h3>
                    <p class="text-[10px] text-white mt-1">RTK kab/kota menunggu verifikasi</p>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#547996] flex items-center justify-center shrink-0 shadow-sm relative z-10 transition-transform duration-300 group-hover:scale-105">
                    <i class="fas fa-hourglass-half text-xl sm:text-2xl"></i>
                </div>
            </div>

            <!-- RTK Ditolak (Light Blue #8BB1CC) -->
            <div class="relative overflow-hidden bg-[#8BB1CC] text-white rounded-md p-5 sm:p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-1 group z-0">
                <div class="absolute -right-6 -bottom-6 text-white opacity-[0.1] group-hover:opacity-[0.15] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-ban text-[130px]"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-white text-sm font-semibold uppercase tracking-wider mb-1">RTK Ditolak</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $rtkStatusDistribution->get('rejected', 0) }}</h3>
                    <p class="text-[10px] text-white mt-1">Dikembalikan ke daerah</p>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#8BB1CC] flex items-center justify-center shrink-0 shadow-sm relative z-10 transition-transform duration-300 group-hover:scale-105">
                    <i class="fas fa-ban text-xl sm:text-2xl"></i>
                </div>
            </div>

            <!-- RTK Aktif / Berlaku (Muted Green #79A736) -->
            <div class="relative overflow-hidden bg-[#79A736] text-white rounded-md p-5 sm:p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-1 group z-0">
                <div class="absolute -right-6 -bottom-6 text-white opacity-[0.05] group-hover:opacity-[0.1] transition-all duration-500 pointer-events-none transform group-hover:scale-110 z-0">
                    <i class="fas fa-file-contract text-[130px]"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-white text-sm font-semibold uppercase tracking-wider mb-1">RTK Aktif</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $rtkMasaAktifPerKabKota->count() ?? 0 }}</h3>
                    <p class="text-[10px] text-white mt-1">Kabupaten/Kota aktif</p>
                </div>
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white text-[#79A736] flex items-center justify-center shrink-0 shadow-sm relative z-10 transition-transform duration-300 group-hover:scale-105">
                    <i class="fas fa-file-contract text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 2. GRAFIK KOMPARASI RTK & PERSETUJUAN                     --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT: GRAFIK KOMPARASI RTK --}}
            <section
                class="lg:col-span-7 xl:col-span-8 bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col">
                <header
                    class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Komparasi Masa Berlaku RTK</h2>
                            <p class="text-sm text-slate-500">Tahun penyusunan dan masa berakhir dokumen per Kabupaten/Kota</p>
                        </div>
                    </div>

                    <select id="rtkYearFilter" onchange="fetchRtkProvinsiData(this.value)"
                        class="w-full sm:w-auto sm:max-w-xs shrink-0 text-sm border-slate-200 rounded-lg focus:ring-[#13416B] focus:border-[#13416B] text-ellipsis overflow-hidden cursor-pointer bg-white">
                        <option value="all" {{ $selectedRtkYear === 'all' ? 'selected' : '' }}>Semua Tahun (Default)
                        </option>
                        @foreach ($rtkYearsOptions as $y)
                            <option value="{{ $y }}"
                                {{ (string) $y === (string) $selectedRtkYear ? 'selected' : '' }}>
                                Mulai {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </header>

                <div class="p-5 sm:p-6 flex-1 flex flex-col">
                    <div class="relative flex-1 min-h-[400px]">
                        {{-- Area scroll: label sumbu Y ikut scroll, sumbu X tetap menempel di bawah --}}
                        <div id="rtkChartScroll"
                            class="absolute inset-0 overflow-y-auto custom-scrollbar {{ $rtkMasaAktifPerKabKota->count() > 0 ? '' : 'hidden' }}">
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
                        class="bg-slate-50 rounded-xl p-10 text-center border border-dashed border-slate-200 {{ $rtkMasaAktifPerKabKota->count() > 0 ? 'hidden' : '' }}">
                        <div
                            class="w-12 h-12 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <i class="fas fa-chart-area text-lg"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Belum ada data RTK</p>
                        <p class="text-xs text-slate-500 mt-1">Belum ada penyusunan dokumen RTK Kab/Kota yang
                            tercatat.</p>
                    </div>
                    </div>
                </div>
            </section>

            {{-- RIGHT: PERLU PERSETUJUAN --}}
            <section
                class="lg:col-span-5 xl:col-span-4 bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col">
                <header class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            
                            <h2 class="text-lg font-semibold text-slate-900">Perlu Tindakan</h2>
                        </div>
                        <span
                            class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-full bg-[#13416B] text-white text-sm font-semibold tabular-nums">
                            {{ $totalPendingApprovals }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1 max-w-[80%]">RTK yang masih memerlukan proses verifikasi atau dokumen</p>
                </header>

                {{-- Satu-satunya elemen yang scroll, dipakai untuk infinite scroll --}}
                <div id="pendingScrollContainer" class="p-4 flex-1 max-h-[500px] overflow-y-auto custom-scrollbar">
                    <div id="pendingApprovalList" class="space-y-3"></div>

                    <div id="pendingEmptyState" class="hidden flex-col items-center justify-center py-10 text-center">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Semua Terproses</p>
                        <p class="text-xs text-slate-500 mt-1">Tidak ada RTK yang memerlukan tindakan.</p>
                    </div>

                    <div id="pendingLoadingSpinner" class="hidden py-3 text-center">
                        <i class="fas fa-spinner fa-spin text-[#13416B]"></i>
                        <span class="text-xs text-slate-500 ml-2">Memuat data berikutnya...</span>
                    </div>

                    <p id="pendingEndMessage"
                        class="hidden pt-3 mt-3 text-center text-xs text-slate-500 border-t border-slate-100">
                        Semua data tindakan telah ditampilkan
                    </p>
                </div>
            </section>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. DISTRIBUSI E-LEARNING (LEADERBOARD)                    --}}
        {{-- ========================================================= --}}
        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <header
                class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-md bg-[#547996] text-white flex items-center justify-center shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Distribusi Pendaftar E-Learning</h2>
                        <p class="text-sm text-slate-500">Jumlah pengguna terdaftar berdasarkan Kabupaten/Kota</p>
                    </div>
                </div>

                <select id="sdmYearFilter"
                    class="w-full sm:w-auto sm:max-w-xs shrink-0 text-sm border-slate-200 rounded-lg focus:ring-[#13416B] focus:border-[#13416B] text-ellipsis overflow-hidden cursor-pointer bg-white">
                    @foreach ($sdmYears as $year)
                        <option value="{{ $year }}" {{ $selectedSdmYear == $year ? 'selected' : '' }}>
                            Tahun Registrasi {{ $year }}
                        </option>
                    @endforeach
                </select>
            </header>

            <div class="p-5 sm:p-6">
                {{-- Legend --}}
                <div class="flex items-center gap-4 mb-4 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-sm bg-[#13416B]"></span>Laki-laki</span>
                    <span class="inline-flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-sm bg-[#547996]"></span>Perempuan</span>
                </div>

                <div id="sdmListContainer"
                    class="space-y-1 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar {{ $sdmPerKabKota->count() > 0 ? '' : 'hidden' }}">
                </div>

                <div id="sdmEmptyState"
                    class="bg-slate-50 rounded-xl p-10 text-center border border-dashed border-slate-200 {{ $sdmPerKabKota->count() > 0 ? 'hidden' : '' }}">
                    <div
                        class="w-12 h-12 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
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
                const PALETTE = ['#13416B', '#547996', '#8BB1CC'];
                const DEFAULT_BADGE = 'bg-[#13416B] text-white border-transparent';

                const $ = (id) => document.getElementById(id);
                const show = (el, visible = true) => el.classList.toggle('hidden', !visible);

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
                    let lines = [];
                    let currentLine = '';

                    words.forEach(word => {
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
                // 1. GRAFIK KOMPARASI RTK (HORIZONTAL FLOATING BAR)
                //    Sumbu X berupa elemen HTML sticky di bawah area scroll,
                //    jadi tetap terlihat saat label sumbu Y di-scroll.
                // =========================================================
                const RTK_MIN_ROW_HEIGHT = 50;
                const RTK_AXIS_HEIGHT = 28;
                const rtkChartColorPalette = ['#13416B', '#547996', '#8BB1CC'];

                // Data sedikit: baris melebar mengisi area (tanpa ruang kosong).
                // Data banyak: 50px per baris, sisanya di-scroll.
                function fitRtkChartHeight(rowCount) {
                    const scrollEl = document.getElementById('rtkChartScroll');
                    if (!scrollEl.clientHeight || !rowCount) return;

                    const available = scrollEl.clientHeight - RTK_AXIS_HEIGHT;
                    const rowHeight = Math.max(RTK_MIN_ROW_HEIGHT, Math.floor(available / rowCount));
                    document.getElementById('rtkCombinedChartContainer').style.height = (rowHeight * rowCount) + 'px';
                }

                // Label tahun sumbu X (HTML), sejajar dengan area plot chart
                function renderRtkAxis(chart) {
                    const xScale = chart.scales.x;
                    let html = '';

                    for (let year = Math.ceil(xScale.min); year <= Math.floor(xScale.max); year++) {
                        html += `<span class="absolute text-xs text-slate-500" style="top:6px;left:${xScale.getPixelForValue(year)}px;transform:translateX(-50%)">${year}</span>`;
                    }
                    document.getElementById('rtkAxisX').innerHTML = html;
                }

                @if ($rtkMasaAktifPerKabKota->count() > 0)
                    const rtkLabelsRaw = @json($rtkMasaAktifPerKabKota->pluck('regency_name'));
                    const rtkLabels = rtkLabelsRaw.map(label => formatMultilineLabel(label));

                    const rtkStartData = @json($rtkMasaAktifPerKabKota->pluck('start_date'));
                    const rtkEndData = @json($rtkMasaAktifPerKabKota->pluck('end_date'));

                    const floatingData = rtkStartData.map((start, index) => [start, rtkEndData[index]]);
                    const barColors = floatingData.map((_, i) => rtkChartColorPalette[i % rtkChartColorPalette.length]);

                    fitRtkChartHeight(rtkLabelsRaw.length);

                    window.rtkCombinedChartInstance = new Chart(document.getElementById('rtkCombinedBarChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: rtkLabels,
                            datasets: [{
                                label: 'Periode Aktif',
                                data: floatingData,
                                backgroundColor: barColors,
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
                            layout: { padding: { right: 16 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(19, 65, 107, 0.95)',
                                    padding: 12,
                                    cornerRadius: 6,
                                    titleFont: { size: 13, weight: 'bold' },
                                    callbacks: {
                                        title: function(context) {
                                            return Array.isArray(context[0].label) ? context[0].label.join(' ') : context[0].label;
                                        },
                                        label: function(context) {
                                            const startYear = context.raw[0];
                                            const endYear = context.raw[1];
                                            return ` Masa Berlaku: ${startYear} s.d. ${endYear}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    min: Math.min(...rtkStartData) - 1,
                                    max: Math.max(...rtkEndData) + 1,
                                    border: { display: false },
                                    grid: { color: '#f1f5f9', drawTicks: false },
                                    // Label tahun digambar di #rtkAxisX (sticky), bukan di canvas
                                    ticks: { display: false, stepSize: 1 }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11, family: "'Inter', sans-serif" }, autoSkip: false },
                                    afterFit: function(scaleInstance) {
                                        scaleInstance.width = window.innerWidth >= 640 ? 160 : 120;
                                    }
                                }
                            }
                        },
                        plugins: [{
                            id: 'rtkAxisSync',
                            afterLayout: (chart) => renderRtkAxis(chart)
                        }]
                    });
                @endif

                // Sesuaikan tinggi chart ketika ukuran area berubah (resize / kartu sebelah selesai dirender)
                new ResizeObserver(() => {
                    if (window.rtkCombinedChartInstance) {
                        fitRtkChartHeight(window.rtkCombinedChartInstance.data.labels.length);
                    }
                }).observe(document.getElementById('rtkChartScroll'));

                window.fetchRtkProvinsiData = function(year) {
                    fetch(`{{ route('admin-province.dashboard') }}?rtk_year=${year}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => response.json())
                        .then(data => {
                            const rtkData = data.rtkMasaAktifPerKabKota;
                            const containerScroll = document.getElementById('rtkChartScroll');
                            const emptyState = document.getElementById('rtkCombinedEmptyState');

                            if (!rtkData || rtkData.length === 0) {
                                containerScroll.classList.add('hidden');
                                emptyState.classList.remove('hidden');
                                return;
                            }

                            containerScroll.classList.remove('hidden');
                            emptyState.classList.add('hidden');

                            const rawLabels = rtkData.map(item => item.regency_name);
                            const labels = rawLabels.map(label => formatMultilineLabel(label));
                            const start = rtkData.map(item => item.start_date);
                            const end = rtkData.map(item => item.end_date);

                            const newFloatingData = start.map((s, index) => [s, end[index]]);
                            const newBarColors = newFloatingData.map((_, i) => rtkChartColorPalette[i % rtkChartColorPalette.length]);

                            if (window.rtkCombinedChartInstance) {
                                const chart = window.rtkCombinedChartInstance;

                                fitRtkChartHeight(rawLabels.length);

                                chart.data.labels = labels;
                                chart.data.datasets[0].data = newFloatingData;
                                chart.data.datasets[0].backgroundColor = newBarColors;

                                const minYear = Math.min(...start) - 1;
                                const maxYear = Math.max(...end) + 1;
                                chart.options.scales.x.min = isFinite(minYear) ? minYear : 2020;
                                chart.options.scales.x.max = isFinite(maxYear) ? maxYear : 2030;

                                chart.update();
                                containerScroll.scrollTop = 0;
                            }
                        });
                };

                // =========================================================
                // 2. PERSETUJUAN (INFINITE SCROLL)
                // =========================================================
                const PendingApprovals = (() => {
                    const scrollEl = $('pendingScrollContainer');
                    const listEl = $('pendingApprovalList');
                    const spinnerEl = $('pendingLoadingSpinner');
                    const endEl = $('pendingEndMessage');
                    const emptyEl = $('pendingEmptyState');
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
                    const STATUS_TONES = [
                        { match: /green|emerald|teal|lime/, bg: 'bg-emerald-500', icon: 'fa-check-circle' },
                        { match: /red|rose|pink/, bg: 'bg-red-500', icon: 'fa-times-circle' },
                        { match: /amber|yellow|orange/, bg: 'bg-amber-500', icon: 'fa-clock' },
                        { match: /blue|sky|indigo|cyan/, bg: 'bg-[#13416B]', icon: 'fa-info-circle' },
                    ];
                    const FALLBACK_TONE = { bg: 'bg-slate-500', icon: 'fa-info-circle' };

                    const statusTone = (cls) => STATUS_TONES.find((t) => t.match.test(cls)) || FALLBACK_TONE;

                    const statusRow = (caption, { label, cls }) => {
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
                        const statuses = `
                            <div class="mt-3 space-y-2">
                                ${statusRow('Verifikasi', item.verif)}
                                ${statusRow('Dokumen', item.doc)}
                            </div>`;

                        return `
                            <article class="p-4 rounded-lg border border-slate-200 bg-white transition-colors duration-200 hover:bg-slate-50">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md border text-xs font-medium ${item.badge}">${escapeHtml(item.category)}</span>
                                    <span class="text-xs text-slate-500 shrink-0"><i class="far fa-clock mr-1"></i>${escapeHtml(item.date)}</span>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-900 line-clamp-1" title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</h3>
                                <p class="mt-0.5 text-xs text-slate-500 truncate">${escapeHtml(item.subtitle)}</p>
                                ${statuses}
                                <div class="flex justify-end mt-3 pt-3 border-t border-slate-100">
                                    <a href="${escapeHtml(item.url)}" class="inline-flex items-center text-sm font-medium text-[#13416B] hover:text-[#547996] transition-colors">
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

                        try {
                            const res = await fetchDashboard({
                                pending_page: state.page + 1
                            });
                            state.page = res.page;
                            state.hasMore = res.has_more;
                            append(res.data);
                        } catch (err) {
                            console.error('Gagal memuat data tindakan:', err);
                        } finally {
                            state.loading = false;
                            show(spinnerEl, false);
                            syncStatus();
                        }
                    }

                    function init(initialItems) {
                        append(initialItems);
                        scrollEl.addEventListener('scroll', () => {
                            const nearBottom = scrollEl.scrollTop + scrollEl.clientHeight >=
                                scrollEl.scrollHeight - SCROLL_BUFFER;
                            if (nearBottom) loadMore();
                        });
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

                    const rowTemplate = (item, index, maxTotal) => {
                        const malePct = (item.male / maxTotal) * 100;
                        const femalePct = (item.female / maxTotal) * 100;

                        return `
                            <div class="flex items-start gap-4 px-2 py-3 rounded-lg transition-colors duration-200 hover:bg-slate-50">
                                <span class="w-6 pt-0.5 text-sm font-semibold text-slate-500 text-right tabular-nums shrink-0">${index + 1}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="text-sm font-semibold text-slate-900 truncate">${escapeHtml(item.regency_name)}</span>
                                        <span class="shrink-0 text-sm text-slate-500"><strong class="text-base font-bold text-[#13416B] tabular-nums">${item.total}</strong> peserta</span>
                                    </div>
                                    <div class="mt-2 w-full h-2 rounded-full bg-slate-100 overflow-hidden flex">
                                        <div class="bg-[#13416B] h-full transition-all duration-500 ease-out" style="width: ${malePct}%" title="Laki-laki: ${item.male}"></div>
                                        <div class="bg-[#547996] h-full transition-all duration-500 ease-out" style="width: ${femalePct}%" title="Perempuan: ${item.female}"></div>
                                    </div>
                                    <div class="mt-1.5 flex gap-4 text-xs text-slate-500">
                                        <span><i class="fas fa-male text-[#13416B] mr-1"></i>${item.male}</span>
                                        <span><i class="fas fa-female text-[#547996] mr-1"></i>${item.female}</span>
                                    </div>
                                </div>
                            </div>`;
                    };

                    function render(data) {
                        const hasData = Array.isArray(data) && data.length > 0;
                        show(listEl, hasData);
                        show(emptyEl, !hasData);
                        if (!hasData) return;

                        const sorted = [...data].sort((a, b) => b.total - a.total);
                        const maxTotal = Math.max(...sorted.map((i) => i.total), 1);
                        listEl.innerHTML = sorted.map((item, i) => rowTemplate(item, i, maxTotal)).join('');
                    }

                    async function loadYear(year) {
                        try {
                            const res = await fetchDashboard({
                                sdm_year: year
                            });
                            render(res.sdmPerKabKota);
                        } catch (err) {
                            console.error('Gagal memuat data SDM:', err);
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
                PendingApprovals.init(@json(collect($initialPendingApprovals)->values()));
                SdmLeaderboard.render(@json($sdmPerKabKota->values()));

                $('sdmYearFilter').addEventListener('change', (e) => SdmLeaderboard.loadYear(e.target.value));
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>