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

    @if ($notification->thumbnail_url)
        @if ($notification->type === 'library.published')
            <!-- Thumbnail Persegi Panjang Vertikal 3:4 (Koleksi Perpustakaan) -->
            <div class="relative w-14 sm:w-16 aspect-[3/4] rounded-md overflow-hidden bg-slate-100 shrink-0 shadow-xs">
                <img src="{{ $notification->thumbnail_url }}" alt="{{ $notification->title }}" class="w-full h-full object-cover">
            </div>
        @else
            <!-- Thumbnail Persegi Panjang Horizontal 16:9 (Katalog Kursus) -->
            <div class="relative w-24 sm:w-28 aspect-video rounded-md overflow-hidden bg-slate-100 shrink-0 shadow-xs">
                <img src="{{ $notification->thumbnail_url }}" alt="{{ $notification->title }}" class="w-full h-full object-cover">
            </div>
        @endif
    @else
        <!-- Ikon kejadian -->
        <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl ring-1 ring-inset {{ $notification->meta['tile'] ?? $notifMeta['tile'] }}">
            <i class="{{ $notification->meta['icon'] ?? $notifMeta['icon'] }} text-[15px]" aria-hidden="true"></i>
        </span>
    @endif

    <!-- Konten -->
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <h3 class="w-full sm:w-auto max-w-full break-words text-[13.5px] font-semibold leading-snug text-slate-700 sm:text-sm line-clamp-2 sm:line-clamp-none">
                {{ preg_replace('/^(Katalog|Koleksi) (Baru|Kursus Baru|Perpustakaan Baru):\s*/i', '', $notification->title) }}
            </h3>

            @if (!in_array($notification->type, ['course.published', 'library.published']))
                {{-- Chip jenis kejadian (hanya untuk notifikasi sistem/proyek/RTKD) --}}
                <span
                    class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                    {{ $notifGroup }}
                </span>
            @endif
        </div>

        <p class="mt-1 text-[12.5px] leading-relaxed text-slate-500 break-words line-clamp-2">
            {{ $notification->description ?? $notification->message }}
        </p>

        <!-- Aksi: menumpuk di bawah pada layar sempit -->
        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5">
            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-400"
                title="{{ $notification->created_at?->format('d M Y, H:i') }}">
                <i class="far fa-clock text-[10px]" aria-hidden="true"></i> {{ $notification->time_ago }}
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
