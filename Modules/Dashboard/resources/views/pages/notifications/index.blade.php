<x-dashboard::layouts.dashboard title="Notifikasi">
    {{-- w-full max-w-full → mengikuti lebar layar di semua perangkat --}}
    <div class="mx-auto w-full max-w-full space-y-4 px-3 pb-10 pt-4 sm:px-5 sm:pt-6 lg:px-6"
        x-data="notificationPage()" x-init="boot()">

        <!-- Header -->
        <header
            class="flex flex-col gap-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center text-[#13416B]">
                    <i class="far fa-bell text-xl" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-800 sm:text-xl">Notifikasi</h1>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500 sm:text-sm">
                        Pemberitahuan persetujuan proyek serta status verifikasi &amp; persetujuan RTKD wilayah Anda.
                    </p>
                </div>
            </div>

            <!-- Aksi -->
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-semibold text-slate-500">
                    <i class="fas fa-sync-alt text-[10px]"></i> Diperbarui otomatis
                </span>

                <span data-unread-count="{{ (int) $unreadCount }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1.5 text-[11px] font-bold text-rose-500 ring-1 ring-inset ring-rose-100"
                    @if (($unreadCount ?? 0) === 0) style="display: none" @endif>
                    <span>{{ (int) $unreadCount }} Belum Dibaca</span>
                </span>

                <button type="button" data-notif-read-all :disabled="unread <= 0 || busy"
                    class="cursor-pointer rounded-lg bg-[#13416B] px-3.5 py-2 text-xs font-bold text-white transition-colors hover:bg-[#0d3457] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/40 disabled:cursor-not-allowed disabled:opacity-40">
                    Tandai semua dibaca
                </button>
            </div>
        </header>

        <!-- Chip pembaruan: ada baris baru yang belum tampil -->
        <div x-show="pending > 0" x-cloak x-transition
            class="sticky top-20 z-30 flex flex-col gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 sm:top-24 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-bold text-indigo-800">
                <span x-text="pending"></span> notifikasi baru masuk.
            </p>
            <button type="button" @click="reload"
                class="cursor-pointer self-start rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 sm:self-auto">
                Segarkan
            </button>
        </div>

        <!-- Tab filter (bisa digeser di layar sempit) -->
        <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Filter notifikasi">
            <a href="{{ route('notifications.index') }}"
                class="shrink-0 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-bold transition-colors {{ ($filter ?? '') !== 'unread' ? 'bg-[#13416B] text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-[#13416B]/40' }}">
                Semua
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                class="inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-bold transition-colors {{ ($filter ?? '') === 'unread' ? 'bg-[#13416B] text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-[#13416B]/40' }}">
                Belum dibaca
                @if (($unreadCount ?? 0) > 0)
                    <span
                        class="grid h-5 min-w-[20px] place-items-center rounded-full bg-rose-500 px-1.5 text-[10px] font-extrabold text-white">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </a>

            <!-- Keterangan ikon (layar sedang ke atas) -->
            <div class="ml-auto hidden shrink-0 items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-500 lg:flex">
                <span class="inline-flex items-center gap-1.5"><i
                        class="far fa-circle-check text-emerald-500"></i> Disetujui</span>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-check-double text-blue-500"></i>
                    Diverifikasi</span>
                <span class="inline-flex items-center gap-1.5"><i class="far fa-circle-xmark text-rose-500"></i>
                    Ditolak</span>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-project-diagram text-indigo-500"></i>
                    Proyek</span>
            </div>
        </nav>

        <!-- Daftar notifikasi -->
        <div id="notif-list" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @forelse ($notifications as $notification)
                @include('dashboard::partials.notifications.item', ['notification' => $notification])
            @empty
                <div class="px-6 py-14 text-center sm:py-20">
                    <span class="mx-auto grid h-14 w-14 place-items-center text-slate-300">
                        <i class="far fa-bell-slash text-2xl"></i>
                    </span>
                    <p class="mt-4 text-sm font-bold text-slate-700">
                        {{ ($filter ?? '') === 'unread' ? 'Tidak ada notifikasi yang belum dibaca' : 'Belum ada notifikasi' }}
                    </p>
                    <p class="mx-auto mt-1 max-w-sm text-xs leading-relaxed text-slate-400">
                        Pemberitahuan persetujuan proyek dan status RTKD akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($notifications->hasPages())
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            const NOTIF_PAGE_ROUTES = {
                read: @js(route('notifications.read', ['notification' => '__ID__'])),
                readAll: @js(route('notifications.read-all')),
                destroy: @js(route('notifications.destroy', ['notification' => '__ID__'])),
                latest: @js(route('notifications.latest')),
            };
            const NOTIF_PAGE_POLL_MS = {{ max(10, (int) config('dashboard.notifications.poll_interval', 30)) }} * 1000;

            function notificationPage() {
                return {
                    busy: false,
                    pending: 0,
                    unread: {{ (int) $unreadCount }},
                    knownIds: null,

                    boot() {
                        this.knownIds = new Set(
                            [...document.querySelectorAll('[data-notif-id]')]
                                .map((row) => row.dataset.notifId)
                        );

                        // Notifikasi sudah tersimpan di database saat kejadian
                        // terjadi; polling ini hanya menarik baris terbaru.
                        setInterval(() => this.check(), NOTIF_PAGE_POLL_MS);

                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') this.check();
                        });
                    },

                    async post(url, method = 'POST') {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch(url, {
                            method,
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({})
                        });
                        return response.json();
                    },

                    /** Deteksi baris baru → tampilkan chip "Segarkan". */
                    async check() {
                        try {
                            const response = await fetch(NOTIF_PAGE_ROUTES.latest, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });
                            if (!response.ok) return;

                            const payload = await response.json();
                            if (!payload || !Array.isArray(payload.items)) return;

                            this.pending = payload.items.filter((item) => !this.knownIds.has(String(item.id))).length;
                        } catch (error) {
                            /* abaikan — biarkan isi halaman yang tampil */
                        }
                    },

                    async markAll() {
                        if (this.busy || this.unread <= 0) return;

                        this.busy = true;
                        try {
                            const result = await this.post(NOTIF_PAGE_ROUTES.readAll);
                            this.unread = result.unread_count || 0;
                            this.pending = 0;
                            this.reload();
                        } finally {
                            this.busy = false;
                        }
                    },

                    reload() {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('live');
                        window.location.href = url.toString();
                    },
                };
            }

            /* Aksi per-item: buang (X), tandai dibaca, buka tabel. */
            document.addEventListener('click', async (event) => {
                // 1) Tombol buang (X)
                const dismissButton = event.target.closest('[data-notif-dismiss]');
                if (dismissButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    dismissButton.disabled = true;

                    const row = dismissButton.closest('[data-notif-id]');
                    const wasUnread = row && row.classList.contains('bg-slate-50/60');

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch(
                            NOTIF_PAGE_ROUTES.destroy.replace('__ID__', dismissButton.dataset.notifDismiss),
                            {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({})
                            }
                        );
                        const result = await response.json();

                        if (wasUnread && typeof result.unread_count === 'number') {
                            const chip = document.querySelector('[data-unread-count]');
                            if (chip) {
                                chip.style.display = result.unread_count === 0 ? 'none' : '';
                                chip.querySelector('span').textContent = result.unread_count + ' Belum Dibaca';
                            }
                        }
                    } catch (error) {
                        dismissButton.disabled = false;
                        return;
                    }

                    if (row) {
                        row.style.transition = 'opacity .18s ease';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            if (!document.querySelector('[data-notif-id]')) {
                                location.reload();
                            }
                        }, 180);
                    }

                    return;
                }

                // 2) Tandai dibaca / buka tabel
                const readButton = event.target.closest('[data-notif-read]');
                const openButton = event.target.closest('[data-notif-open]');

                if (!readButton && !openButton) return;

                event.preventDefault();

                const id = readButton?.dataset.notifRead || openButton?.dataset.notifOpen;
                if (!id) return;

                try {
                    await fetch(NOTIF_PAGE_ROUTES.read.replace('__ID__', id), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({})
                    });
                } catch (error) {
                    /* tetap lanjut */
                }

                if (openButton?.getAttribute('href')) {
                    window.location.href = openButton.getAttribute('href');
                } else {
                    window.location.reload();
                }
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
