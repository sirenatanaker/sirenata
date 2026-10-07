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
@php($notifIconColor = collect(explode(' ', $notification->meta['tile'] ?? $notifMeta['tile']))->first(fn ($class) => str_starts_with($class, 'text-'), 'text-slate-600'))

<div data-notif-id="{{ $notification->id }}"
    class="group relative flex items-start gap-3 border-b border-slate-100 px-3 py-3.5 transition-colors last:border-b-0 sm:gap-4 sm:px-5 sm:py-4 {{ $notification->read_at ? 'bg-white hover:bg-slate-50/70' : 'bg-slate-50/60 hover:bg-slate-100/60' }}">

    <!-- Ikon kejadian (hijau=centang, merah=silang, dst.) -->
    <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center {{ $notifIconColor }}">
        <i class="{{ $notification->meta['icon'] ?? $notifMeta['icon'] }} text-[15px]" aria-hidden="true"></i>
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
                <i class="far fa-clock" aria-hidden="true"></i>{{ $notification->time_ago }}
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
                    Buka tabel <i class="fas fa-external-link-alt text-[9px]" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Tombol buang (X) -->
    <button type="button" data-notif-dismiss="{{ $notification->id }}" aria-label="Hapus notifikasi"
        class="mt-1 grid h-7 w-7 shrink-0 cursor-pointer place-items-center rounded-md text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30">
        <i class="fas fa-times text-xs" aria-hidden="true"></i>
    </button>
</div>
