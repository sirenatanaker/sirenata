{{--
    ==========================================================================
    LONCENG NOTIFIKASI — Event-Driven Push
    Khusus: Admin Provinsi & Admin Kab/Kota
    --------------------------------------------------------------------------
    - Notifikasi dibuat oleh listener SAAT kejadian terjadi
      (proyek disetujui / RTKD diverifikasi-disetujui-ditolak).
    - Navbar menyajikannya (render server) lalu menyegarkan secara ringan
      dengan polling `poll_interval` — tanpa koneksi SSE.
    - Desain: kalem & flat (tanpa kartu), angka notifikasi di
      kanan atas lonceng, dan tombol "X" untuk membuang tiap notifikasi.
    ==========================================================================
--}}
@php
    $notifUser = auth()->user();
    $notifBase = \Modules\Dashboard\Models\Notification::query()->forUser($notifUser->id);
    $notifItems = (clone $notifBase)->orderByDesc('created_at')->orderByDesc('id')
        ->limit(max(5, (int) config('dashboard.notifications.per_page', 15)))->get();
    $notifUnread = (clone $notifBase)->unread()->count();
@endphp

<style>
    @keyframes notif-fade-in {
        from {
            opacity: 0;
            transform: translateY(-4px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .notif-fade {
        animation: notif-fade-in .25s ease-out both;
    }

    .notif-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .notif-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .notif-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 999px;
    }
</style>

<div class="relative" x-data="notificationBell()" x-init="boot()"
    @keydown.escape.window="open = false" @click.outside="open = false">

    <!-- ===================== LONCENG ===================== -->
    <button type="button" @click="toggle()" :aria-expanded="open" aria-haspopup="true" aria-label="Notifikasi"
        class="relative inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full text-slate-600 dark:text-slate-300 transition-colors duration-150 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30 cursor-pointer">

        <!-- Outline Bell Icon (Ukuran diperkecil di mobile) -->
        <svg class="w-4.5 h-4.5 sm:w-5 sm:h-5 shrink-0 transition-transform duration-150 hover:scale-105" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>

        <!-- Angka notifikasi (kanan atas) -->
        <span x-show="unreadCount > 0" x-cloak
            class="absolute -top-0.5 -right-0.5 grid h-4 min-w-[16px] sm:h-[18px] sm:min-w-[18px] place-items-center rounded-full bg-rose-600 px-0.5 sm:px-1 text-[8px] sm:text-[10px] font-bold leading-none text-white ring-1.5 sm:ring-2 ring-white dark:ring-slate-800"
            x-text="badgeLabel"></span>
    </button>

    <!-- Overlay Transparan di Mobile saat Notifikasi Terbuka (Deteksi Klik Luar Tanpa Efek Blank/Gelap di Layar) -->
    <div x-show="open" @click="open = false" x-cloak
        class="fixed inset-0 z-40 bg-transparent sm:hidden" aria-hidden="true">
    </div>

    <!-- ===================== DAFTAR (RESPONSIF MOBILE & DESKTOP) ===================== -->
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95" x-cloak
        class="fixed sm:absolute inset-x-3 sm:inset-x-auto sm:right-0 top-[68px] sm:top-full z-50 sm:mt-2 w-auto sm:w-[25rem] md:w-[27rem] max-w-full sm:max-w-[calc(100vw-2rem)] origin-top-right overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl">

        <!-- Kepala: YouTube Style -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-700/60 px-4 py-3 bg-white dark:bg-slate-800">
            <div class="flex items-baseline gap-2">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Notifikasi</h3>
                <span class="text-[11px] font-medium text-slate-400 dark:text-slate-400">
                    <span x-text="unreadCount"></span> belum dibaca
                </span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="markAll" :disabled="unreadCount === 0 || busy"
                    class="cursor-pointer text-[11px] font-semibold text-slate-500 dark:text-slate-400 transition-colors hover:text-[#13416B] dark:hover:text-white disabled:cursor-not-allowed disabled:opacity-40">
                    Tandai semua dibaca
                </button>

                {{-- Tombol buang semua (X) --}}
                <button type="button" @click="clearAll" :disabled="items.length === 0 || busy" aria-label="Buang semua notifikasi"
                    class="grid h-6 w-6 cursor-pointer place-items-center rounded-md text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-white transition-colors disabled:cursor-not-allowed disabled:opacity-40">
                    <i class="fas fa-times text-xs" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        @if (auth()->user()->hasRole('user'))
            <!-- Section Filter Tabs (YouTube Style: Semua, Katalog, Perpustakaan) -->
            <div class="flex items-center gap-1.5 px-4 py-2 bg-slate-50/60 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-700/60">
                <button type="button" @click="activeTab = 'semua'"
                    class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
                    :class="activeTab === 'semua' ? 'bg-[#13416B] text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-600'">
                    Semua
                </button>
                <button type="button" @click="activeTab = 'katalog'"
                    class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
                    :class="activeTab === 'katalog' ? 'bg-[#13416B] text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-600'">
                    Katalog
                </button>
                <button type="button" @click="activeTab = 'perpustakaan'"
                    class="px-3 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer"
                    :class="activeTab === 'perpustakaan' ? 'bg-[#13416B] text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-600'">
                    Perpustakaan
                </button>
            </div>
        @endif

        <!-- Isi -->
        <div class="notif-scroll max-h-[60vh] sm:max-h-[24rem] overflow-y-auto">

            <!-- Kosong -->
            <div x-show="filteredItems.length === 0" x-cloak class="px-6 py-10 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-300 mb-3">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <p class="text-[13px] font-semibold text-slate-700 dark:text-slate-200"
                   x-text="activeTab === 'semua' ? 'Belum ada notifikasi' : (activeTab === 'katalog' ? 'Belum ada notifikasi katalog' : 'Belum ada notifikasi perpustakaan')"></p>
                <p class="mx-auto mt-1 max-w-[14rem] text-[11px] leading-relaxed text-slate-400 dark:text-slate-400">
                    {{ auth()->user()->hasRole('user') ? 'Pemberitahuan katalog kursus baru dan koleksi perpustakaan akan muncul di sini.' : 'Pemberitahuan persetujuan proyek dan status RTKD akan muncul di sini.' }}
                </p>
            </div>

            <!-- Baris notifikasi (YouTube Style: Thumbnail rounded-md, Judul, Waktu) -->
            <template x-for="(item, index) in filteredItems" :key="item.id">
                <div>
                    <!-- Pemisah Garis Bagian Koleksi Perpustakaan -->
                    <template x-if="isFirstOfSection(item, index)">
                        <div class="flex items-center gap-2.5 px-3.5 py-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-400"
                             :class="!item.read ? 'bg-slate-50/60 dark:bg-slate-900/30' : 'bg-white dark:bg-slate-800'">
                            <span>Koleksi Perpustakaan</span>
                            <div class="flex-1 h-px bg-slate-200/70 dark:bg-slate-700/60"></div>
                        </div>
                    </template>

                    <div class="notif-fade group relative flex items-start gap-3 px-3.5 py-3 hover:bg-slate-50/90 dark:hover:bg-slate-700/50 transition-colors cursor-pointer"
                         :class="!item.read ? 'bg-slate-50/60 dark:bg-slate-900/40' : 'bg-white dark:bg-slate-800'"
                         @click="openItem(item)">

                        <!-- 1. THUMBNAIL KURSUS: Persegi Panjang Horizontal 16:9 (rounded-md) -->
                        <template x-if="item.section !== 'perpustakaan' && item.type !== 'library.published'">
                            <div class="shrink-0">
                                <template x-if="item.thumbnail">
                                    <div class="relative w-20 sm:w-28 aspect-video rounded-md overflow-hidden bg-slate-100 dark:bg-slate-700 shadow-xs">
                                        <img :src="item.thumbnail" :alt="item.title" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!item.thumbnail">
                                    <div class="w-10 sm:w-12 h-9 sm:h-10 shrink-0 rounded-md bg-slate-50 dark:bg-slate-700 text-slate-400 dark:text-slate-300 flex items-center justify-center">
                                        <i class="fa-regular fa-bell text-sm sm:text-base" aria-hidden="true"></i>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- 2. THUMBNAIL KOLEKSI: Persegi Panjang Vertikal 3:4 (rounded-md) -->
                        <template x-if="item.section === 'perpustakaan' || item.type === 'library.published'">
                            <div class="shrink-0">
                                <template x-if="item.thumbnail">
                                    <div class="relative w-12 sm:w-16 aspect-[3/4] rounded-md overflow-hidden bg-slate-100 dark:bg-slate-700 shadow-xs">
                                        <img :src="item.thumbnail" :alt="item.title" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!item.thumbnail">
                                    <div class="w-12 sm:w-16 aspect-[3/4] shrink-0 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 flex items-center justify-center">
                                        <i class="fa-regular fa-bookmark text-sm sm:text-base" aria-hidden="true"></i>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Isi: Judul, Deskripsi & Waktu -->
                        <div class="min-w-0 flex-1 pr-1">
                            <p class="text-xs sm:text-[13px] leading-snug line-clamp-2 break-words text-slate-800 dark:text-white group-hover:text-[#13416B] dark:group-hover:text-slate-200 transition-colors"
                               :class="!item.read ? 'font-bold' : 'font-medium'"
                               x-text="item.title"></p>

                            <template x-if="item.description || item.message">
                                <p class="mt-1 text-[11px] sm:text-xs leading-relaxed text-slate-500 dark:text-slate-300 line-clamp-2 break-words"
                                   x-text="item.description || item.message"></p>
                            </template>
                            
                            <p class="mt-1.5 text-[11px] font-medium text-slate-400 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="far fa-clock text-[10px]" aria-hidden="true"></i>
                                <span x-text="item.time"></span>
                            </p>
                        </div>

                        <!-- Tombol Hapus (X) -->
                        <button type="button" @click.stop="dismiss(item)" :disabled="busy" aria-label="Hapus notifikasi"
                            class="grid h-6 w-6 shrink-0 cursor-pointer place-items-center rounded-md text-slate-300 dark:text-slate-500 opacity-70 sm:opacity-0 transition-all hover:bg-slate-200/60 dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-white group-hover:opacity-100 disabled:cursor-not-allowed">
                            <i class="fas fa-times text-xs" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Kaki -->
        <a href="{{ route('notifications.index') }}"
            class="block border-t border-slate-100 dark:border-slate-700/60 px-4 py-2.5 text-center text-[12px] font-semibold text-slate-500 dark:text-slate-400 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/40 hover:text-[#13416B] dark:hover:text-white">
            Lihat semua notifikasi
        </a>
    </div>
</div>

<script>
    /* Seed (render server) + konfigurasi — semuanya di konteks JS. */
    const NOTIF_SEED = {!! json_encode($notifItems->map->toPayload()->values()->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    const NOTIF_UNREAD = {{ (int) $notifUnread }};
    const NOTIF_POLL_MS = {{ max(10, (int) config('dashboard.notifications.poll_interval', 30)) }} * 1000;
    const NOTIF_ROUTES = {
        latest: @js(route('notifications.latest')),
        readAll: @js(route('notifications.read-all')),
        read: @js(route('notifications.read', ['notification' => '__ID__'])),
        destroy: @js(route('notifications.destroy', ['notification' => '__ID__'])),
    };

    function notificationBell() {
        return {
            open: false,
            busy: false,
            activeTab: 'semua',
            items: Array.isArray(NOTIF_SEED) ? NOTIF_SEED : [],
            unreadCount: parseInt(NOTIF_UNREAD, 10) || 0,
            timer: null,

            get filteredItems() {
                if (this.activeTab === 'katalog') {
                    return this.items.filter(i => (i.section === 'katalog' || i.type === 'course.published'));
                }
                if (this.activeTab === 'perpustakaan') {
                    return this.items.filter(i => (i.section === 'perpustakaan' || i.type === 'library.published'));
                }

                // Di tab 'semua': urutan section katalog dulu, baru koleksi perpustakaan
                const katalogItems = this.items.filter(i => (i.section === 'katalog' || i.type === 'course.published'));
                const perpustakaanItems = this.items.filter(i => (i.section === 'perpustakaan' || i.type === 'library.published'));
                const otherItems = this.items.filter(i => 
                    i.section !== 'katalog' && i.type !== 'course.published' &&
                    i.section !== 'perpustakaan' && i.type !== 'library.published'
                );
                return [...katalogItems, ...perpustakaanItems, ...otherItems];
            },

            isFirstOfSection(item, index) {
                if (this.activeTab !== 'semua') return false;
                const isLib = item.section === 'perpustakaan' || item.type === 'library.published';
                if (!isLib) return false;
                if (index === 0) return true;
                const prev = this.filteredItems[index - 1];
                return prev && prev.section !== 'perpustakaan' && prev.type !== 'library.published';
            },

            boot() {
                // Penyegar ringan: notifikasi sudah tersimpan saat kejadian
                // terjadi; polling ini hanya menarik tampilan terbaru.
                this.timer = setInterval(() => this.refresh(), NOTIF_POLL_MS);

                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') this.refresh();
                });
            },

            get badgeLabel() {
                return this.unreadCount > 99 ? '99+' : String(this.unreadCount);
            },

            toggle() {
                this.open = !this.open;
                if (this.open) this.refresh();
            },

            token() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            },

            async send(url, method = 'POST') {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.token(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });

                return response.json();
            },

            async refresh() {
                try {
                    const response = await fetch(NOTIF_ROUTES.latest, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) return;

                    const payload = await response.json();
                    if (!payload || !Array.isArray(payload.items)) return;

                    this.items = payload.items;
                    this.unreadCount = parseInt(payload.unread_count, 10) || 0;
                } catch (error) {
                    /* biarkan tampilan terakhir yang tampil */
                }
            },

            async markAll() {
                if (this.busy || this.unreadCount === 0) return;

                this.busy = true;
                try {
                    await this.send(NOTIF_ROUTES.readAll);
                    this.items = this.items.map(item => ({ ...item, read: true }));
                    this.unreadCount = 0;
                } finally {
                    this.busy = false;
                }
            },

            async openItem(item) {
                if (!item.read) {
                    item.read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    this.send(NOTIF_ROUTES.read.replace('__ID__', item.id)).catch(() => {});
                }

                this.open = false;

                if (item.link) window.location.href = item.link;
            },

            /* Buang satu notifikasi lewat tombol X */
            async dismiss(item) {
                const index = this.items.findIndex(i => i.id === item.id);
                if (index === -1) return;

                // Hilangkan dari tampilan (optimistis), lalu simpan ke server.
                this.items.splice(index, 1);
                if (!item.read) {
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }

                try {
                    const result = await this.send(NOTIF_ROUTES.destroy.replace('__ID__', item.id), 'DELETE');
                    if (typeof result.unread_count === 'number') {
                        this.unreadCount = result.unread_count;
                    }
                } catch (error) {
                    // Gagal simpan → kembalikan ke tampilan.
                    await this.refresh();
                }
            },

            /* Buang semua notifikasi yang terlihat (tombol X di kepala panel) */
            async clearAll() {
                if (this.busy || this.items.length === 0) return;

                this.busy = true;
                const snapshot = [...this.items];

                try {
                    await Promise.allSettled(
                        snapshot.map(item => this.send(NOTIF_ROUTES.destroy.replace('__ID__', item.id), 'DELETE'))
                    );
                    this.items = [];
                    this.unreadCount = 0;
                } finally {
                    this.busy = false;
                }
            },
        };
    }
</script>
