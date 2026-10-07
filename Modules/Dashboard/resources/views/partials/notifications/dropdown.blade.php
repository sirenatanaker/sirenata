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
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition-colors duration-150 hover:bg-slate-100 hover:text-[#13416B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30">

        <i class="far fa-bell text-[17px]" aria-hidden="true"></i>

        <!-- Angka notifikasi (kanan atas) -->
        <span x-show="unreadCount > 0" x-cloak
            class="absolute -right-1 -top-1 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white"
            x-text="badgeLabel"></span>
    </button>

    <!-- ===================== DAFTAR ===================== -->
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" x-cloak
        class="absolute right-0 top-full z-50 mt-2 w-[24rem] max-w-[calc(100vw-1.5rem)] origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">

        <!-- Kepala: ringkas, tanpa kartu/kilau -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
            <div class="flex items-baseline gap-2">
                <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                <span class="text-[11px] font-medium text-slate-400">
                    <span x-text="unreadCount"></span> belum dibaca
                </span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="markAll" :disabled="unreadCount === 0 || busy"
                    class="cursor-pointer text-[11px] font-semibold text-slate-400 transition-colors hover:text-[#13416B] disabled:cursor-not-allowed disabled:opacity-40">
                    Tandai semua dibaca
                </button>

                {{-- Tombol buang semua (X) --}}
                <button type="button" @click="clearAll" :disabled="items.length === 0 || busy" aria-label="Buang semua notifikasi"
                    class="grid h-6 w-6 cursor-pointer place-items-center rounded-md text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500 disabled:cursor-not-allowed disabled:opacity-40">
                    <i class="fas fa-times text-xs" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <!-- Isi -->
        <div class="notif-scroll max-h-[22rem] overflow-y-auto">

            <!-- Kosong -->
            <div x-show="items.length === 0" x-cloak class="px-6 py-9 text-center">
                <i class="far fa-bell text-2xl text-slate-300" aria-hidden="true"></i>
                <p class="mt-2 text-[13px] font-medium text-slate-500">Belum ada notifikasi</p>
                <p class="mx-auto mt-1 max-w-[14rem] text-[11px] leading-relaxed text-slate-400">
                    Pemberitahuan persetujuan proyek dan status RTKD akan muncul di sini.
                </p>
            </div>

            <!-- Baris notifikasi (flat, tanpa kartu) -->
            <template x-for="item in items" :key="item.id">
                <div class="notif-fade group relative flex items-start gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0 hover:bg-slate-50/80">

                    <!-- Ikon kecil warna kalem -->
                    <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center">
                        <i :class="item.icon" class="text-[13px]" aria-hidden="true"></i>
                    </span>

                    <!-- Teks -->
                    <a :href="item.link || '#'" @click.prevent="openItem(item)"
                        class="min-w-0 flex-1 cursor-pointer">
                        <p class="flex items-center gap-1.5">
                            <span class="truncate text-[13px] font-semibold text-slate-700" x-text="item.title"></span>
                            <span x-show="!item.read" class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#13416B]"></span>
                        </p>
                        <p class="mt-0.5 line-clamp-2 text-[11.5px] leading-relaxed text-slate-500"
                            x-text="item.message"></p>
                        <p class="mt-1 text-[10.5px] font-medium text-slate-400" x-text="item.time"></p>
                    </a>

                    <!-- Tombol buang (X) -->
                    <button type="button" @click.stop="dismiss(item)" aria-label="Hapus notifikasi"
                        class="mt-1 grid h-6 w-6 shrink-0 cursor-pointer place-items-center rounded-md text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30">
                        <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </template>
        </div>

        <!-- Kaki -->
        <a href="{{ route('notifications.index') }}"
            class="block border-t border-slate-100 px-4 py-2.5 text-center text-[12px] font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-[#13416B]">
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
            items: Array.isArray(NOTIF_SEED) ? NOTIF_SEED : [],
            unreadCount: parseInt(NOTIF_UNREAD, 10) || 0,
            timer: null,

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
