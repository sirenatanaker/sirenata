<x-dashboard::layouts.dashboard title="Absensi & Bukti Zoom Peserta - Admin Pusat">
    @push('styles')
        <style>
            @media (min-width: 768px) {
                .timeline-arrow-start {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%);
                    padding-left: 1rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-mid {
                    clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1.75rem;
                }
                .timeline-arrow-end {
                    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%);
                    padding-left: 1.85rem;
                    padding-right: 1rem;
                }
            }
        </style>
    @endpush

    <div class="p-3 sm:p-6 max-w-400 mx-auto space-y-5 sm:space-y-6">

        @php
            $stepNumber = match ($step) {
                'agenda'       => 2,
                'participants' => 3,
                default        => 1,
            };
            $eventTitleShort = $selectedEvent
                ? preg_replace('/\bbatch\b/i', 'Gelombang', $selectedEvent->batch_name)
                : null;
            $step2Subtitle = "Agenda Hari-{$dayNumber}";
            $step3HeaderTitle = "Absensi Hari ke-{$dayNumber}" . ($cleanTopicTitle !== '' ? ": {$cleanTopicTitle}" : '');
        @endphp

        {{-- Header Banner Terpadu dengan Guide Timeline Panah 3 Tahap --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5 min-w-0">
                    @if ($step === 'events')
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Absensi & Bukti Zoom Peserta</h1>
                        <p class="text-sm sm:text-base text-slate-200 max-w-2xl leading-relaxed">
                            Pilih event pelatihan untuk mengatur tautan Google Drive pengumpulan bukti Zoom serta memantau daftar kehadiran peserta.
                        </p>
                    @elseif ($step === 'agenda')
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                            {{ $eventTitleShort }}
                        </h1>
                        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                            {{ $selectedEvent->course?->name ?? '-' }} — {{ $summaryCounts['total_participants'] ?? $selectedEvent->participants_count }} Peserta Terdaftar
                        </p>
                    @else
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight wrap-break-word">
                            {{ $step3HeaderTitle }}
                        </h1>
                        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                            {{ $eventTitleShort }}
                        </p>
                    @endif
                </div>

                @if ($step === 'agenda')
                    <a href="{{ route('admin-pusat.management-course.attendances.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 transition-colors shrink-0">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Ganti Event</span>
                    </a>
                @elseif ($step === 'participants' && $selectedEvent)
                    <a href="{{ route('admin-pusat.management-course.attendances.index', ['bimtek_event_id' => $selectedEvent->id]) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 transition-colors shrink-0">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Kembali ke Agenda</span>
                    </a>
                @endif
            </div>

            {{-- Guide Timeline Panah 3 Langkah --}}
            <div class="border-t border-white/15 pt-4 grid grid-cols-1 md:grid-cols-3 gap-2.5 md:gap-1.5">
                {{-- Panah Tahap 1 --}}
                @if ($stepNumber > 1)
                    <a href="{{ route('admin-pusat.management-course.attendances.index') }}"
                       class="timeline-arrow-start flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-l-md bg-white/15 hover:bg-white/20 transition-colors group">
                        <span class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-emerald-300 leading-tight">Langkah 1 — Selesai</div>
                            <div class="text-sm sm:text-[15px] font-bold text-white truncate group-hover:underline mt-0.5">{{ $eventTitleShort }}</div>
                        </div>
                    </a>
                @else
                    <div class="timeline-arrow-start flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-l-md bg-white text-slate-900 shadow-xs">
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 1 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">Pilih Event Pelatihan</div>
                        </div>
                    </div>
                @endif

                {{-- Panah Tahap 2 --}}
                @if ($stepNumber === 3 && $selectedEvent)
                    <a href="{{ route('admin-pusat.management-course.attendances.index', ['bimtek_event_id' => $selectedEvent->id]) }}"
                       class="timeline-arrow-mid flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none bg-white/15 hover:bg-white/20 transition-colors group">
                        <span class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-emerald-300 leading-tight">Langkah 2 — Selesai</div>
                            <div class="text-sm sm:text-[15px] font-bold text-white truncate group-hover:underline mt-0.5">{{ $step2Subtitle }}</div>
                        </div>
                    </a>
                @elseif ($stepNumber === 2)
                    <div class="timeline-arrow-mid flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none bg-white text-slate-900 shadow-xs">
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 2 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">Atur Drive & Pilih Agenda</div>
                        </div>
                    </div>
                @else
                    <div class="timeline-arrow-mid flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none bg-white/10 text-white/70">
                        <span class="w-8 h-8 rounded-full bg-white/15 text-white/80 text-sm font-bold flex items-center justify-center shrink-0">2</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-white/60 leading-tight">Langkah 2 — Berikutnya</div>
                            <div class="text-sm sm:text-[15px] font-semibold truncate mt-0.5">Atur Drive & Pilih Agenda</div>
                        </div>
                    </div>
                @endif

                {{-- Panah Tahap 3 --}}
                @if ($stepNumber === 3)
                    <div class="timeline-arrow-end flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-r-md bg-white text-slate-900 shadow-xs">
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 3 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">Daftar Absensi & Bukti</div>
                        </div>
                    </div>
                @else
                    <div class="timeline-arrow-end flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-r-md bg-white/10 text-white/70">
                        <span class="w-8 h-8 rounded-full bg-white/15 text-white/80 text-sm font-bold flex items-center justify-center shrink-0">3</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-white/60 leading-tight">Langkah 3 — Akhir</div>
                            <div class="text-sm sm:text-[15px] font-semibold truncate mt-0.5">Daftar Absensi & Bukti</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- LANGKAH 1: PILIH EVENT PELATIHAN                                      --}}
        {{-- ===================================================================== --}}
        @if ($step === 'events')
            <form action="{{ route('admin-pusat.management-course.attendances.index') }}" method="GET"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-white p-3.5 rounded-md border border-slate-200 shadow-2xs">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama event, kode gelombang, atau nama pelatihan..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                <div class="flex items-center gap-2 shrink-0">
                    @if ($search !== '')
                        <a href="{{ route('admin-pusat.management-course.attendances.index') }}"
                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md border border-slate-200">
                            Reset
                        </a>
                    @endif
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors cursor-pointer">
                        Cari
                    </button>
                </div>
            </form>

            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden divide-y divide-slate-200">
                @forelse ($events as $ev)
                    @php
                        $badge = $ev->status_badge;
                        $displayBatchName = preg_replace('/\bbatch\b/i', 'Gelombang', $ev->batch_name);
                    @endphp
                    <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2 min-w-0 flex-1">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                        {{ $displayBatchName }}
                                    </h3>
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                <p class="text-sm sm:text-[15px] font-medium text-slate-600 mt-1">
                                    {{ $ev->course?->name ?? 'Pelatihan tidak ditemukan' }}
                                </p>
                            </div>

                            <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                <span class="font-semibold">{{ $ev->participants_count }} Peserta</span>
                                <span class="text-[#13416B]/35 select-none">—</span>
                                <span class="font-bold text-emerald-600">{{ $ev->attended_users_count }} Sudah Presensi</span>
                                <span class="text-[#13416B]/35 select-none">—</span>
                                <span class="font-bold text-[#13416B]">{{ $ev->proof_users_count }} Kumpul Bukti Zoom</span>
                                <span class="text-[#13416B]/35 select-none">—</span>
                                @if ($ev->has_drive_link)
                                    <span class="font-bold text-emerald-600">Link Drive Tersedia</span>
                                @else
                                    <span class="font-bold text-amber-600">Link Drive Belum Diatur</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin-pusat.management-course.attendances.index', ['bimtek_event_id' => $ev->id]) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shrink-0">
                            <span>Kelola Absensi</span>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-500 space-y-1">
                        <p class="text-base font-bold text-slate-700">Event pelatihan tidak ditemukan</p>
                        <p class="text-sm text-slate-400">Belum ada gelombang event yang sesuai dengan pencarian Anda.</p>
                    </div>
                @endforelse
            </div>

            @if ($events->hasPages())
                <div>{{ $events->links() }}</div>
            @endif

        {{-- ===================================================================== --}}
        {{-- LANGKAH 2: PENGATURAN LINK GOOGLE DRIVE EVENT & TIMELINE HARIAN       --}}
        {{-- ===================================================================== --}}
        @elseif ($step === 'agenda')
            {{-- Card Pengaturan Link Google Drive Event --}}
            <div class="bg-[#1B568A] text-white rounded-md p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h2 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                            <i class="fab fa-google-drive text-base text-sky-200"></i>
                            <span>Tautan Folder Google Drive Bukti Mengikuti Zoom (Utama Event)</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                            Tautan ini otomatis tampil pada menu Bukti Pendukung Presensi peserta di seluruh hari pelaksanaan event ini. Anda juga dapat mengatur tautan berbeda untuk masing-masing hari.
                        </p>
                    </div>
                    @if (!empty($selectedEvent->submission_drive_link))
                        <a href="{{ $selectedEvent->submission_drive_link }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs shrink-0">
                            <i class="fab fa-google-drive"></i>
                            <span>Buka Folder Drive</span>
                            <i class="fas fa-external-link-alt text-[11px] opacity-75"></i>
                        </a>
                    @endif
                </div>

                <form action="{{ route('admin-pusat.management-course.attendances.event-drive-link', $selectedEvent->id) }}"
                      method="POST"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    @csrf
                    <input type="url"
                           name="submission_drive_link"
                           value="{{ old('submission_drive_link', $selectedEvent->submission_drive_link) }}"
                           placeholder="https://drive.google.com/drive/folders/..."
                           class="flex-1 px-3.5 py-2.5 text-sm rounded-md bg-white text-slate-900 placeholder:text-slate-400 border border-white/20 focus:ring-2 focus:ring-white/60 outline-none">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors shadow-2xs cursor-pointer shrink-0">
                        <i class="fas fa-save text-xs"></i>
                        <span>Simpan Tautan Drive</span>
                    </button>
                </form>
                @error('submission_drive_link')
                    <p class="text-xs font-semibold text-amber-200">{{ $message }}</p>
                @enderror
            </div>

            {{-- Timeline Agenda Harian --}}
            <div class="relative space-y-4 sm:space-y-5">
                @if ($dailyAgendas->count() > 1)
                    <div class="absolute left-6 sm:left-7 top-6 bottom-6 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
                @endif

                @forelse ($dailyAgendas as $agenda)
                    @php
                        $hasActivity = $agenda->attended_count > 0 || $agenda->proof_count > 0;
                    @endphp
                    <div class="relative flex items-start gap-3.5 sm:gap-5">
                        <div class="relative z-10 w-12 h-12 sm:w-14 sm:h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
                            {{ $hasActivity ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $hasActivity ? 'text-blue-200' : 'text-slate-400' }} leading-none">
                                HARI
                            </span>
                            <span class="text-lg sm:text-xl font-black leading-tight mt-0.5">
                                {{ $agenda->day_number }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-2 min-w-0 flex-1">
                                <div>
                                    <div class="text-xs sm:text-sm font-bold text-[#13416B]">
                                        Agenda Hari ke-{{ $agenda->day_number }}{{ $agenda->day_date ? ' — ' . $agenda->day_date : '' }}
                                    </div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                                        {{ $agenda->title }}
                                    </h3>
                                </div>

                                <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                    <span class="font-bold text-emerald-600">{{ $agenda->attended_count }} / {{ $agenda->total_participants }} Sudah Presensi</span>
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    <span class="font-bold text-[#13416B]">{{ $agenda->proof_count }} / {{ $agenda->total_participants }} Kumpul Bukti Zoom</span>
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    @if ($agenda->drive_source === 'custom')
                                        <span class="font-bold text-emerald-600">Link Drive Khusus Hari Ini</span>
                                    @elseif ($agenda->drive_source === 'event')
                                        <span class="font-semibold text-[#1E4468]">Menggunakan Link Drive Event</span>
                                    @else
                                        <span class="font-bold text-amber-600">Link Drive Belum Diatur</span>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('admin-pusat.management-course.attendances.index', ['bimtek_event_id' => $selectedEvent->id, 'section_id' => $agenda->section->id]) }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white transition-colors shrink-0">
                                <span>Lihat Daftar Peserta</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-md border border-slate-200 p-10 text-center text-sm text-slate-500">
                        Belum ada agenda harian pada kursus ini.
                    </div>
                @endforelse
            </div>

        {{-- ===================================================================== --}}
        {{-- LANGKAH 3: DAFTAR PESERTA ABSENSI & BUKTI MENGIKUTI ZOOM              --}}
        {{-- ===================================================================== --}}
        @else
            {{-- Filter & Pencarian Peserta --}}
            <form action="{{ route('admin-pusat.management-course.attendances.index') }}" method="GET"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-white p-3.5 rounded-md border border-slate-200 shadow-2xs">
                <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">
                <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">

                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama, email, atau instansi peserta..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">

                <select name="filter" onchange="this.form.submit()"
                        class="px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                    <option value="all" @selected($filter === 'all')>Semua Peserta ({{ $summaryCounts['all'] ?? 0 }})</option>
                    <option value="complete" @selected($filter === 'complete')>Lengkap: Presensi &amp; Bukti ({{ $summaryCounts['complete'] ?? 0 }})</option>
                    <option value="attended" @selected($filter === 'attended')>Sudah Presensi Zoom ({{ $summaryCounts['attended'] ?? 0 }})</option>
                    <option value="not_attended" @selected($filter === 'not_attended')>Belum Presensi Zoom ({{ $summaryCounts['not_attended'] ?? 0 }})</option>
                    <option value="proof_submitted" @selected($filter === 'proof_submitted')>Sudah Kumpul Bukti ({{ $summaryCounts['proof_submitted'] ?? 0 }})</option>
                    <option value="proof_verified" @selected($filter === 'proof_verified')>Bukti Terverifikasi ({{ $summaryCounts['proof_verified'] ?? 0 }})</option>
                    <option value="proof_missing" @selected($filter === 'proof_missing')>Belum Kumpul Bukti ({{ $summaryCounts['proof_missing'] ?? 0 }})</option>
                </select>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($search !== '' || ($filter !== 'all' && $filter !== ''))
                        <a href="{{ route('admin-pusat.management-course.attendances.index', ['bimtek_event_id' => $selectedEvent->id, 'section_id' => $selectedSection->id]) }}"
                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md border border-slate-200">
                            Reset
                        </a>
                    @endif
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </form>

            {{-- Panel Ringkasan & Pengaturan Link Google Drive Hari Ini --}}
            <div x-data="{ editDrive: {{ empty($effectiveDrive) ? 'true' : 'false' }} }"
                 class="bg-[#1B568A] text-white rounded-md p-4 sm:p-5 space-y-3.5 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-sm sm:text-base font-bold text-white">
                            {{ $summaryCounts['attended'] ?? 0 }} dari {{ $summaryCounts['all'] ?? 0 }} Peserta Sudah Presensi
                            — {{ $summaryCounts['proof_submitted'] ?? 0 }} Sudah Mengumpulkan Bukti Zoom
                        </p>
                        <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                            <span>Agenda Hari ke-{{ $dayNumber }}</span>
                            <span class="text-white/40 mx-1.5 select-none">—</span>
                            @if (!empty($selectedSession?->proof_drive_link))
                                <span class="font-semibold text-emerald-200">Link Google Drive Khusus Hari Ini Aktif</span>
                            @elseif (!empty($selectedEvent->submission_drive_link))
                                <span class="text-blue-100">Menggunakan Link Google Drive Utama Event</span>
                            @else
                                <span class="font-semibold text-amber-200">Link Google Drive Bukti Zoom Belum Diatur</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if ($effectiveDrive)
                            <a href="{{ $effectiveDrive }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs">
                                <i class="fab fa-google-drive"></i>
                                <span>Buka Folder Drive</span>
                                <i class="fas fa-external-link-alt text-[11px] opacity-75"></i>
                            </a>
                        @endif

                        <button type="button" @click="editDrive = !editDrive"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-white/15 hover:bg-white/25 border border-white/25 transition-colors cursor-pointer">
                            <i class="fas fa-link text-xs"></i>
                            <span x-text="editDrive ? 'Tutup Form Drive' : 'Atur Link Drive Hari Ini'"></span>
                        </button>
                    </div>
                </div>

                <div x-show="editDrive" x-collapse x-cloak class="pt-3 border-t border-white/15 space-y-2">
                    <form action="{{ route('admin-pusat.management-course.attendances.section-drive-link', ['eventId' => $selectedEvent->id, 'sectionId' => $selectedSection->id]) }}"
                          method="POST"
                          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                        @csrf
                        <input type="url"
                               name="proof_drive_link"
                               value="{{ old('proof_drive_link', $selectedSession?->proof_drive_link ?: $selectedEvent->submission_drive_link) }}"
                               placeholder="https://drive.google.com/drive/folders/..."
                               class="flex-1 px-3.5 py-2.5 text-sm rounded-md bg-white text-slate-900 placeholder:text-slate-400 border border-white/20 focus:ring-2 focus:ring-white/60 outline-none">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors shadow-2xs cursor-pointer shrink-0">
                            <i class="fas fa-save text-xs"></i>
                            <span>Simpan Link Hari ke-{{ $dayNumber }}</span>
                        </button>
                    </form>
                    <p class="text-[11px] sm:text-xs text-blue-100/90">
                        Kosongkan lalu simpan jika ingin menggunakan kembali tautan Google Drive utama milik event.
                    </p>
                    @error('proof_drive_link')
                        <p class="text-xs font-semibold text-amber-200">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Daftar Peserta Bernomor --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden divide-y divide-slate-200">
                @forelse ($participants as $row)
                    @php
                        $rowNumber = ($participants->firstItem() ?? 1) + $loop->index;
                        $proof = $row->proof;
                    @endphp
                    <div x-data="{ expanded: false }">
                        <div @click="expanded = !expanded"
                             class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:bg-slate-50/70 transition-colors select-none">
                            <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                                <span class="text-base sm:text-lg font-bold text-slate-500 tabular-nums shrink-0 leading-snug w-7 text-right">
                                    {{ $rowNumber }}.
                                </span>

                                <div class="space-y-2 min-w-0 flex-1">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $row->name }}</h3>
                                            @if ($row->instansi)
                                                <span class="text-xs sm:text-sm text-slate-500">({{ $row->instansi }})</span>
                                            @endif
                                        </div>
                                        <p class="text-sm sm:text-[15px] font-medium text-slate-600 mt-0.5">{{ $row->email }}</p>
                                    </div>

                                    {{-- Kotak Status Presensi & Bukti Zoom (Solid Biru Cerah Serasi) --}}
                                    <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                        @if ($row->is_attended)
                                            <span class="font-bold text-emerald-600">
                                                Sudah Presensi{{ $row->attended_at ? ' (' . $row->attended_at->translatedFormat('d M Y, H:i') . ' WIB)' : '' }}
                                            </span>
                                        @else
                                            <span class="font-bold text-amber-600">Belum Presensi</span>
                                        @endif

                                        <span class="text-[#13416B]/35 select-none">—</span>

                                        @if ($row->proof_status === 'verified')
                                            <span class="font-bold text-emerald-600">Bukti Zoom Terverifikasi</span>
                                        @elseif ($row->proof_status === 'submitted')
                                            <span class="font-bold text-[#13416B]">Bukti Zoom Terkumpul</span>
                                        @elseif ($row->proof_status === 'rejected')
                                            <span class="font-bold text-rose-600">Bukti Perlu Perbaikan</span>
                                        @else
                                            <span class="font-bold text-amber-600">Belum Kumpul Bukti</span>
                                        @endif

                                        @if ($proof?->submitted_at)
                                            <span class="text-[#13416B]/35 select-none">—</span>
                                            <span class="font-semibold text-[#1E4468]">Diunggah {{ $proof->submitted_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors">
                                    <span x-text="expanded ? 'Tutup' : 'Detail & Aksi'"></span>
                                    <i class="fas fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                                </span>
                            </div>
                        </div>

                        <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/60 p-4 sm:p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                                {{-- Kolom Kiri: Detail Presensi & Bukti Peserta --}}
                                <div class="lg:col-span-7 space-y-4">
                                    <div class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-3 shadow-2xs">
                                        <h4 class="text-sm sm:text-base font-bold text-slate-800 pb-2 border-b border-slate-100">
                                            Riwayat Presensi &amp; Bukti Mengikuti Zoom
                                        </h4>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                                            <div class="p-3 rounded-md bg-slate-50 border border-slate-200">
                                                <p class="text-slate-500">Status Presensi Zoom</p>
                                                <p class="font-bold mt-0.5 {{ $row->is_attended ? 'text-emerald-600' : 'text-amber-600' }}">
                                                    {{ $row->is_attended ? 'Sudah Melakukan Presensi' : 'Belum Melakukan Presensi' }}
                                                </p>
                                                @if ($row->attended_at)
                                                    <p class="text-xs text-slate-500 mt-0.5">{{ $row->attended_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                                @elseif ($row->joined_at)
                                                    <p class="text-xs text-slate-500 mt-0.5">Klik Zoom: {{ $row->joined_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                                @endif
                                            </div>

                                            <div class="p-3 rounded-md bg-slate-50 border border-slate-200">
                                                <p class="text-slate-500">Status Pengumpulan Bukti</p>
                                                <p class="font-bold mt-0.5 {{ $row->proof_status === 'verified' ? 'text-emerald-600' : ($row->proof_status === 'submitted' ? 'text-[#13416B]' : ($row->proof_status === 'rejected' ? 'text-rose-600' : 'text-amber-600')) }}">
                                                    {{ match ($row->proof_status) {
                                                        'verified'  => 'Terverifikasi Admin',
                                                        'submitted' => 'Sudah Dikumpulkan',
                                                        'rejected'  => 'Perlu Perbaikan',
                                                        default     => 'Belum Mengumpulkan',
                                                    } }}
                                                </p>
                                                @if ($proof?->submitted_at)
                                                    <p class="text-xs text-slate-500 mt-0.5">{{ $proof->submitted_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($proof && ($proof->drive_link || $proof->file_url))
                                            <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2">
                                                @if ($proof->drive_link)
                                                    <a href="{{ $proof->drive_link }}" target="_blank" rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors">
                                                        <i class="fab fa-google-drive"></i>
                                                        <span>Buka Tautan Bukti Peserta</span>
                                                        <i class="fas fa-external-link-alt text-[11px] opacity-80"></i>
                                                    </a>
                                                @endif
                                                @if ($proof->file_url)
                                                    <a href="{{ $proof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors">
                                                        <i class="fas fa-image"></i>
                                                        <span>Lihat Berkas Screenshot</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($proof?->notes)
                                            <div class="p-3 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
                                                <span class="font-bold text-slate-600">Keterangan:</span>
                                                <span>{{ $proof->notes }}</span>
                                            </div>
                                        @elseif (!$proof)
                                            <p class="text-xs sm:text-sm text-slate-500">
                                                Peserta belum mengonfirmasi pengumpulan bukti Zoom di LMS. Jika bukti sudah masuk di folder Google Drive panitia, Anda tetap dapat langsung memverifikasinya di panel kanan.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Aksi Admin --}}
                                <div class="lg:col-span-5 space-y-3.5">
                                    {{-- Aksi Presensi --}}
                                    <div class="bg-white rounded-md border border-slate-200 p-4 space-y-3 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-800">Presensi Kehadiran Zoom</h4>
                                                <p class="text-xs text-slate-500">Konfirmasi atau ubah status kehadiran peserta secara manual.</p>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin-pusat.management-course.attendances.toggle-presence') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">
                                            <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                                            <input type="hidden" name="user_id" value="{{ $row->user->id }}">

                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold transition-colors cursor-pointer {{ $row->is_attended ? 'text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200' : 'text-white bg-emerald-600 hover:bg-emerald-700' }}">
                                                <i class="fas {{ $row->is_attended ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                                <span>{{ $row->is_attended ? 'Batalkan Status Presensi' : 'Tandai Sudah Presensi' }}</span>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Aksi Verifikasi Bukti Zoom --}}
                                    <div class="bg-white rounded-md border border-slate-200 p-4 space-y-3 shadow-2xs">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800">Verifikasi Bukti Mengikuti Zoom</h4>
                                            <p class="text-xs text-slate-500">Perbarui status bukti peserta setelah mengecek folder Google Drive.</p>
                                        </div>

                                        <form action="{{ route('admin-pusat.management-course.attendances.proof-status') }}" method="POST" class="space-y-2.5">
                                            @csrf
                                            <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                                            <input type="hidden" name="user_id" value="{{ $row->user->id }}">

                                            <input type="text"
                                                   name="notes"
                                                   value="{{ $proof?->notes ?? '' }}"
                                                   placeholder="Catatan verifikasi (opsional)..."
                                                   class="w-full px-3 py-2 text-xs sm:text-sm rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">

                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="submit" name="status" value="verified"
                                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors cursor-pointer">
                                                    <i class="fas fa-check text-xs"></i>
                                                    <span>Verifikasi</span>
                                                </button>
                                                <button type="submit" name="status" value="rejected"
                                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                                    <i class="fas fa-exclamation-triangle text-xs"></i>
                                                    <span>Perlu Perbaikan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-500 space-y-1">
                        <p class="text-base font-bold text-slate-700">Peserta tidak ditemukan</p>
                        <p class="text-sm text-slate-400">Tidak ada peserta yang sesuai dengan filter atau pencarian Anda.</p>
                    </div>
                @endforelse
            </div>

            @if ($participants->hasPages())
                <div>{{ $participants->links() }}</div>
            @endif
        @endif

    </div>
</x-dashboard::layouts.dashboard>
