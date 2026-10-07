{{--
    Baris notifikasi untuk halaman "Lihat Semua Notifikasi".
    ------------------------------------------------------------------
    - Flat (tanpa kartu), ikon per jenis kejadian + ring warna kalem
    - Label grup (mis. "Persetujuan RTKD") sebagai chip kecil
    - Responsif: header & aksi turun ke bawah di layar sempit
    - Tombol "X" untuk membuang notifikasi

    $notification — Modules\Dashboard\Models\Notification
--}}
@php($notifMeta = \Modules\Dashboard\Models\Notification::typeMeta($notification->type))
@php($notifGroup = $notification->meta['group'] ?? $notifMeta['label'])

<div data-notif-id="{{ $notification->id }}"
    class="group relative flex items-start gap-3 border-b border-slate-100 px-3 py-3.5 transition-colors last:border-b-0 sm:gap-4 sm:px-5 sm:py-4 {{ $notification->read_at ? 'bg-white hover:bg-slate-50/70' : 'bg-slate-50/60 hover:bg-slate-100/60' }}">

    <!-- Ikon kejadian (hijau=centang, merah=silang, dst.) -->
    <span
        class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl ring-1 ring-inset {{ $notification->meta['tile'] ?? $notifMeta['tile'] }}">
        @switch($notifMeta['icon'])
            @case('workflow')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="6" height="6" rx="1" /><rect x="15" y="15" width="6" height="6" rx="1" /><path d="M9 6h3a3 3 0 0 1 3 3v6m-6 3H6a3 3 0 0 1-3-3v-3" /></svg>
                @break
            @case('flag')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22V4m0 1c5-4 11 4 16 0v12c-5 4-11-4-16 0" /></svg>
                @break
            @case('list-checks')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 17 2 2 4-4m-6-8 2 2 4-4m4 2h8m-8 8h8" /></svg>
                @break
            @case('circle-check')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path d="m9 12 2 2 4-4" /></svg>
                @break
            @case('circle-x')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path d="m15 9-6 6m0-6 6 6" /></svg>
                @break
            @default
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9m-8.27 13a2 2 0 0 0 3.54 0" /></svg>
        @endswitch
    </span>

    <!-- Konten -->
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <h3 class="max-w-full break-words text-[13.5px] font-semibold leading-snug text-slate-700 sm:text-sm">
                {{ $notification->title }}
            </h3>

            {{-- Chip jenis kejadian --}}
            <span
                class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                {{ $notifGroup }}
            </span>

            @if (!$notification->read_at)
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#13416B]" title="Belum dibaca"></span>
            @endif
        </div>

        <p class="mt-1 text-[12.5px] leading-relaxed text-slate-500 break-words">
            {{ $notification->message }}
        </p>

        <!-- Aksi: menumpuk di bawah pada layar sempit -->
        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5">
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400"
                title="{{ $notification->created_at?->format('d M Y, H:i') }}">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>{{ $notification->time_ago }}
            </span>

            @if (!$notification->read_at)
                <button type="button" data-notif-read="{{ $notification->id }}"
                    class="cursor-pointer text-[11px] font-semibold text-[#13416B] hover:underline">
                    Tandai dibaca
                </button>
            @endif

            @if ($notification->link)
                <a href="{{ $notification->link }}" data-notif-open="{{ $notification->id }}"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 transition-colors hover:text-[#13416B] hover:underline">
                    Buka tabel <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6m-11 5L21 3M19 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6" /></svg>
                </a>
            @endif
        </div>
    </div>

    <!-- Tombol buang (X) -->
    <button type="button" data-notif-dismiss="{{ $notification->id }}" aria-label="Hapus notifikasi"
        class="mt-1 grid h-7 w-7 shrink-0 cursor-pointer place-items-center rounded-md text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            viewBox="0 0 24 24" aria-hidden="true">
            <path d="M18 6 6 18M6 6l12 12" />
        </svg>
    </button>
</div>
