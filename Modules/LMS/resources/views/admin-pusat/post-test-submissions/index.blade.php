<x-dashboard::layouts.dashboard title="Jawaban Evaluasi Peserta - Admin Pusat">
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

        {{-- Header Banner Terpadu dengan Guide Timeline Panah 3 Tahap --}}
        @php
            $stepNumber = match ($step) {
                'agenda'  => 2,
                'results' => 3,
                default   => 1,
            };
            $eventTitleShort = $selectedEvent ? preg_replace('/\bbatch\b/i', 'Gelombang', $selectedEvent->batch_name) : null;

            $step2Subtitle = 'Agenda Evaluasi';
            if ($selectedPostTest) {
                if ($selectedPostTest->course_section_id) {
                    $eventSections = $selectedEvent?->course?->sections?->values() ?? collect();
                    $secIdx = $eventSections->search(fn($s) => (string) $s->id === (string) $selectedPostTest->course_section_id);
                    $rawAgendaTitle = $selectedPostTest->section?->name ?? $selectedPostTest->title ?? '';
                    $dayNum = $secIdx !== false
                        ? ($secIdx + 1)
                        : (preg_match('/(?:modul|hari)\s*(\d+)/i', $rawAgendaTitle, $m) ? (int) $m[1] : 1);
                    $step2Subtitle = "Agenda Hari-{$dayNum}";
                } else {
                    $step2Subtitle = 'Evaluasi Akhir';
                }
            }
        @endphp

        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm space-y-5">
            {{-- Baris Atas: Kondisi Saat Ini & Tombol Kembali --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5 min-w-0">
                    @if ($step === 'events')
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Jawaban Evaluasi Peserta</h1>
                        <p class="text-sm sm:text-base text-slate-200 max-w-2xl leading-relaxed">
                            Pilih event pelatihan untuk meninjau agenda harian serta hasil evaluasi peserta.
                        </p>
                    @elseif ($step === 'agenda')
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                            {{ $eventTitleShort }}
                        </h1>
                        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                            {{ $selectedEvent->course?->name ?? '-' }} — {{ $selectedEvent->participants_count }} Peserta Terdaftar
                        </p>
                    @else
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight wrap-break-word">
                            {{ $selectedPostTest?->title ?? 'Hasil Evaluasi' }}
                        </h1>
                        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                            {{ $eventTitleShort }}
                            — {{ $selectedPostTest?->course_section_id ? 'Evaluasi Harian' : 'Evaluasi Akhir' }}
                            — Format: {{ $selectedPostTest?->is_essay ? 'Uraian / Esai' : ($selectedPostTest?->is_file_upload ? 'Upload Berkas' : 'Pilihan Ganda (CBT)') }}
                        </p>
                    @endif
                </div>

                @if ($step === 'agenda')
                    <a href="{{ route('admin-pusat.management-course.post-test-submissions.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 transition-colors shrink-0">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Ganti Event</span>
                    </a>
                @elseif ($step === 'results' && $selectedEvent)
                    <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $selectedEvent->id]) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 transition-colors shrink-0">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Kembali ke Agenda</span>
                    </a>
                @endif
            </div>

            {{-- Baris Bawah: Guide Timeline Panah 3 Langkah --}}
            <div class="border-t border-white/15 pt-4 grid grid-cols-1 md:grid-cols-3 gap-2.5 md:gap-1.5">
                {{-- Panah Tahap 1: Pilih Event --}}
                @if ($stepNumber > 1)
                    <a href="{{ route('admin-pusat.management-course.post-test-submissions.index') }}"
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
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">
                            1
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 1 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">Pilih Event Pelatihan</div>
                        </div>
                    </div>
                @endif

                {{-- Panah Tahap 2: Pilih Agenda Harian / Evaluasi Akhir --}}
                @if ($stepNumber === 3 && $selectedEvent)
                    <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $selectedEvent->id]) }}"
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
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">
                            2
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 2 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">Pilih Agenda / Evaluasi Akhir</div>
                        </div>
                    </div>
                @else
                    <div class="timeline-arrow-mid flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none bg-white/10 text-white/70">
                        <span class="w-8 h-8 rounded-full bg-white/15 text-white/80 text-sm font-bold flex items-center justify-center shrink-0">
                            2
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-white/60 leading-tight">Langkah 2 — Berikutnya</div>
                            <div class="text-sm sm:text-[15px] font-semibold truncate mt-0.5">Pilih Agenda / Evaluasi Akhir</div>
                        </div>
                    </div>
                @endif

                {{-- Panah Tahap 3: Periksa & Nilai Jawaban --}}
                @if ($stepNumber === 3)
                    <div class="timeline-arrow-end flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-r-md bg-white text-slate-900 shadow-xs">
                        <span class="w-8 h-8 rounded-full bg-[#13416B] text-white text-sm font-bold flex items-center justify-center shrink-0">
                            3
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#13416B] leading-tight">Langkah 3 — Aktif</div>
                            <div class="text-sm sm:text-[15px] font-bold text-slate-900 truncate mt-0.5">
                                Periksa & Nilai ({{ $submissions ? number_format($submissions->total()) : 0 }})
                            </div>
                        </div>
                    </div>
                @else
                    <div class="timeline-arrow-end flex items-center gap-3 py-3 px-4 rounded-md md:rounded-none md:rounded-r-md bg-white/10 text-white/70">
                        <span class="w-8 h-8 rounded-full bg-white/15 text-white/80 text-sm font-bold flex items-center justify-center shrink-0">
                            3
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-white/60 leading-tight">Langkah 3 — Berikutnya</div>
                            <div class="text-sm sm:text-[15px] font-semibold truncate mt-0.5">Periksa & Nilai Jawaban</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- LANGKAH 1: PILIH EVENT BIMTEK                                          --}}
        {{-- ===================================================================== --}}
        @if ($step === 'events')
            <form action="{{ route('admin-pusat.management-course.post-test-submissions.index') }}" method="GET"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-white p-3.5 rounded-md border border-slate-200 shadow-2xs">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama event, kode gelombang, atau kursus..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                <div class="flex items-center gap-2 shrink-0">
                    @if ($search !== '')
                        <a href="{{ route('admin-pusat.management-course.post-test-submissions.index') }}"
                           class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md border border-slate-200">
                            Reset
                        </a>
                    @endif
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors cursor-pointer">
                        Cari Event
                    </button>
                </div>
            </form>

            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden divide-y divide-slate-100">
                @forelse ($events as $ev)
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="space-y-2 min-w-0 flex-1">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                    {{ preg_replace('/\bbatch\b/i', 'Gelombang', $ev->batch_name) }}
                                </h2>
                                <p class="text-sm sm:text-[15px] font-semibold text-slate-600 mt-0.5">
                                    {{ $ev->course?->name ?? '-' }}
                                </p>
                            </div>
                            <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-600">
                                <span class="font-semibold text-slate-800">
                                    {{ $ev->start_date ? $ev->start_date->translatedFormat('d M') : '-' }} - {{ $ev->end_date ? $ev->end_date->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-slate-300 select-none">—</span>
                                <span class="font-medium">{{ number_format($ev->participants_count) }} Peserta</span>
                                <span class="text-slate-300 select-none">—</span>
                                <span class="font-medium">{{ number_format($ev->submissions_count) }} Jawaban Masuk</span>
                                @if ($ev->pending_count > 0)
                                    <span class="text-slate-300 select-none">—</span>
                                    <span class="font-bold text-amber-600">{{ $ev->pending_count }} perlu dinilai</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $ev->id]) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shrink-0">
                            <span>Lihat Agenda</span>
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
        {{-- LANGKAH 2: TIMELINE AGENDA HARIAN & EVALUASI AKHIR                    --}}
        {{-- ===================================================================== --}}
        @elseif ($step === 'agenda')
            @php
                $timelineTotalCount = $dailyAgendas->count() + ($finalEvaluation ? 1 : 0);
            @endphp

            <div class="relative space-y-4 sm:space-y-5">
                @if ($timelineTotalCount > 1)
                    <div class="absolute left-6 sm:left-7 top-6 bottom-6 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
                @endif

                {{-- Card Timeline Agenda Harian --}}
                @forelse ($dailyAgendas as $agenda)
                    @php
                        $hasSubmitted = $agenda->submitted_count > 0;
                        $allGraded = $agenda->post_test && $hasSubmitted && $agenda->pending_count === 0;
                    @endphp
                    <div class="relative flex items-start gap-3.5 sm:gap-5">
                        {{-- Node Timeline Hari --}}
                        <div class="relative z-10 w-12 h-12 sm:w-14 sm:h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
                            {{ $agenda->post_test ? ($hasSubmitted ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $agenda->post_test && $hasSubmitted ? 'text-blue-200' : 'text-slate-400' }} leading-none">
                                HARI
                            </span>
                            <span class="text-lg sm:text-xl font-black leading-tight mt-0.5">
                                {{ $agenda->day_number }}
                            </span>
                            @if ($allGraded)
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] ring-2 ring-white shadow-2xs" title="Semua jawaban telah dinilai">
                                    <i class="fas fa-check"></i>
                                </span>
                            @elseif ($agenda->pending_count > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[9px] font-bold ring-2 ring-white shadow-2xs" title="{{ $agenda->pending_count }} menunggu penilaian">
                                    !
                                </span>
                            @endif
                        </div>

                        {{-- Card Agenda Timeline --}}
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

                                @if ($agenda->post_test)
                                    <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-600">
                                        <span class="font-semibold text-slate-800">Format {{ $agenda->format_label }}</span>
                                        <span class="text-slate-300 select-none">—</span>
                                        <span class="font-medium">{{ $agenda->submitted_count }} dari {{ $agenda->total_participants }} peserta mengumpulkan</span>
                                        @if ($agenda->pending_count > 0)
                                            <span class="text-slate-300 select-none">—</span>
                                            <span class="font-bold text-amber-600">{{ $agenda->pending_count }} menunggu penilaian</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs sm:text-sm italic text-slate-400">Belum ada instrumen evaluasi pada agenda ini</p>
                                @endif
                            </div>

                            @if ($agenda->post_test)
                                <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $selectedEvent->id, 'post_test_id' => $agenda->post_test->id]) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white transition-colors shrink-0">
                                    <span>Lihat Hasil Evaluasi</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-md border border-slate-200 p-10 text-center text-sm text-slate-500">
                        Belum ada agenda pembelajaran pada kursus ini.
                    </div>
                @endforelse

                {{-- Node & Card Timeline Evaluasi Akhir (Ikon Bendera Finish) --}}
                @if ($finalEvaluation)
                    @php
                        $finalHasSubmitted = $finalEvaluation->submitted_count > 0;
                        $finalAllGraded = $finalEvaluation->post_test && $finalHasSubmitted && $finalEvaluation->pending_count === 0;
                    @endphp
                    <div class="relative flex items-start gap-3.5 sm:gap-5">
                        {{-- Node Timeline Finish (Hanya Ikon Bendera) --}}
                        <div class="relative z-10 w-12 h-12 sm:w-14 sm:h-14 rounded-md shrink-0 flex items-center justify-center border shadow-2xs
                            {{ $finalEvaluation->post_test ? ($finalHasSubmitted ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') : 'bg-slate-100 text-slate-400 border-slate-200' }}"
                             title="Evaluasi Akhir">
                            <i class="fas fa-flag-checkered text-lg sm:text-xl"></i>
                            @if ($finalAllGraded)
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] ring-2 ring-white shadow-2xs" title="Semua jawaban telah dinilai">
                                    <i class="fas fa-check"></i>
                                </span>
                            @elseif ($finalEvaluation->pending_count > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[9px] font-bold ring-2 ring-white shadow-2xs" title="{{ $finalEvaluation->pending_count }} menunggu penilaian">
                                    !
                                </span>
                            @endif
                        </div>

                        {{-- Card Evaluasi Akhir --}}
                        <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-2 min-w-0 flex-1">
                                <div>
                                    <div class="text-xs sm:text-sm font-bold text-[#13416B]">
                                        Evaluasi Akhir{{ $selectedEvent->end_date ? ' — ' . $selectedEvent->end_date->translatedFormat('d M Y') : '' }}
                                    </div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                                        {{ $finalEvaluation->title }}
                                    </h3>
                                </div>

                                @if ($finalEvaluation->post_test)
                                    <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-600">
                                        <span class="font-semibold text-slate-800">Format {{ $finalEvaluation->format_label }}</span>
                                        <span class="text-slate-300 select-none">—</span>
                                        <span class="font-medium">{{ $finalEvaluation->submitted_count }} dari {{ $finalEvaluation->total_participants }} peserta mengumpulkan</span>
                                        @if ($finalEvaluation->pending_count > 0)
                                            <span class="text-slate-300 select-none">—</span>
                                            <span class="font-bold text-amber-600">{{ $finalEvaluation->pending_count }} menunggu penilaian</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs sm:text-sm italic text-slate-400">Belum ada instrumen evaluasi akhir pada kursus ini</p>
                                @endif
                            </div>

                            @if ($finalEvaluation->post_test)
                                <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $selectedEvent->id, 'post_test_id' => $finalEvaluation->post_test->id]) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white transition-colors shrink-0">
                                    <span>Lihat Hasil Evaluasi</span>
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

        {{-- ===================================================================== --}}
        {{-- LANGKAH 3: HASIL & PENILAIAN EVALUASI PESERTA                          --}}
        {{-- ===================================================================== --}}
        @else
            <form action="{{ route('admin-pusat.management-course.post-test-submissions.index') }}" method="GET"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 bg-white p-3.5 rounded-md border border-slate-200 shadow-2xs">
                <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">
                <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">

                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama, email, atau instansi peserta..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">

                @if ($isManualTest)
                    <select name="status" onchange="this.form.submit()"
                            class="px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                        <option value="all" @selected($status === 'all')>Semua Status</option>
                        <option value="submitted" @selected($status === 'submitted')>Menunggu Penilaian</option>
                        <option value="graded" @selected($status === 'graded')>Sudah Dinilai</option>
                    </select>
                @endif

                <div class="flex items-center gap-2 shrink-0">
                    @if ($search !== '' || ($status !== 'all' && $status !== ''))
                        <a href="{{ route('admin-pusat.management-course.post-test-submissions.index', ['bimtek_event_id' => $selectedEvent->id, 'post_test_id' => $selectedPostTest->id]) }}"
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

            @if ($isManualTest && $selectedPostTest->has_auto_grading && ($pendingCount ?? 0) > 0)
                <div class="bg-emerald-50/80 border border-emerald-200 rounded-md p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="space-y-0.5">
                        <p class="text-sm sm:text-base font-bold text-emerald-800">
                            Tersedia {{ $pendingCount }} Jawaban Belum Dinilai
                        </p>
                        <p class="text-xs sm:text-sm text-slate-600">
                            Anda dapat menjalankan koreksi otomatis terhadap kunci jawaban untuk seluruh peserta yang belum dinilai, atau menguji satu per satu di bawah ini.
                        </p>
                    </div>
                    <form action="{{ route('admin-pusat.management-course.post-test-submissions.auto-grade-bulk') }}" method="POST" class="shrink-0">
                        @csrf
                        <input type="hidden" name="bimtek_event_id" value="{{ $selectedEvent->id }}">
                        <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-2xs cursor-pointer">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            <span>Koreksi Otomatis Semua ({{ $pendingCount }} Belum Dinilai)</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden divide-y divide-slate-200">
                @forelse ($submissions as $sub)
                    @php
                        $isEssay = (bool) $selectedPostTest->is_essay;
                        $isUpload = (bool) $selectedPostTest->is_file_upload;
                        $isGraded = ($sub->status ?? '') === 'graded';
                        $passingScore = (int) ($selectedPostTest->passing_score ?? 70);
                        $defaultScore = $isGraded ? (int) ($sub->score ?? 0) : '';
                        $studentName = $sub->user?->profile?->full_name ?? $sub->user?->name ?? 'Peserta';
                        $studentInstansi = $sub->user?->profile?->instansi;
                        $submittedAt = $sub->completed_at ?? $sub->updated_at;
                        $rowNumber = ($submissions->firstItem() ?? 1) + $loop->index;
                    @endphp

                    @if ($isManualTest)
                        <div x-data="{ expanded: false, scoreVal: {{ $isGraded ? (int) ($sub->score ?? 0) : "''" }}, isPassedVal: '{{ $sub->is_passed ? '1' : '0' }}' }">
                            <div @click="expanded = !expanded"
                                 class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:bg-slate-50/70 transition-colors select-none">
                                <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                                    <span class="text-base sm:text-lg font-bold text-slate-500 tabular-nums shrink-0 leading-snug w-7 text-right">
                                        {{ $rowNumber }}.
                                    </span>

                                    <div class="space-y-2 min-w-0 flex-1">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $studentName }}</h3>
                                                @if ($studentInstansi)
                                                    <span class="text-xs sm:text-sm text-slate-500">({{ $studentInstansi }})</span>
                                                @endif
                                            </div>
                                            @if ($sub->user?->email)
                                                <p class="text-sm sm:text-[15px] font-medium text-slate-600 mt-0.5">{{ $sub->user->email }}</p>
                                            @endif
                                        </div>

                                        {{-- Badge Kotak Info Sejajar Step 1 --}}
                                        <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-600">
                                            @if ($isGraded)
                                                <span class="font-bold {{ $sub->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    Nilai {{ $sub->score ?? 0 }}
                                                </span>
                                            @else
                                                <span class="font-bold text-amber-600">Menunggu Penilaian</span>
                                            @endif

                                            @if ($submittedAt)
                                                <span class="text-slate-300 select-none">—</span>
                                                <span class="font-medium">Dikumpulkan {{ $submittedAt->translatedFormat('d M Y, H:i') }} WIB</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2.5 flex-wrap shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors">
                                        <span x-text="expanded ? 'Tutup' : 'Periksa & Nilai'"></span>
                                        <i class="fas fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                                    </span>
                                </div>
                            </div>

                            <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-6">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                                    {{-- Jawaban Peserta --}}
                                    <div class="lg:col-span-7 space-y-4">
                                        @if ($isEssay)
                                            @php
                                                $questions = $selectedPostTest->questions->values();
                                                $answers = is_array($sub->answers) ? $sub->answers : [];
                                            @endphp

                                            @forelse ($questions as $qIdx => $question)
                                                @php
                                                    $ansText = (string) ($answers[$qIdx] ?? ($answers[(string) $question->id] ?? ''));
                                                    $wordCount = trim($ansText) !== '' ? count(preg_split('/\s+/u', trim($ansText))) : 0;
                                                @endphp
                                                <div class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-3 shadow-2xs">
                                                    <p class="text-sm sm:text-base font-bold text-slate-800 leading-snug wrap-break-word">
                                                        {{ $qIdx + 1 }}. {{ strip_tags($question->question) }}
                                                    </p>
                                                    <div class="p-3.5 sm:p-4 rounded bg-slate-50 border border-slate-200 text-sm sm:text-[15px] text-slate-800 leading-relaxed whitespace-pre-wrap wrap-break-word">{{ $ansText !== '' ? $ansText : 'Peserta tidak mengisi jawaban pada nomor ini.' }}</div>
                                                    @if (!empty($question->answer_key))
                                                        <div class="p-3 rounded bg-emerald-50/70 border border-emerald-200 text-xs sm:text-sm text-emerald-900">
                                                            <span class="font-bold text-emerald-700">Kunci Jawaban / Poin Kunci:</span>
                                                            <span>{{ $question->answer_key }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="text-xs text-slate-400 text-right">{{ $wordCount }} kata</div>
                                                </div>
                                            @empty
                                                @foreach ($answers as $aKey => $aVal)
                                                    <div class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-2">
                                                        <p class="text-sm font-bold text-slate-700">Soal {{ is_numeric($aKey) ? ((int) $aKey + 1) : $aKey }}</p>
                                                        <div class="p-3.5 rounded bg-slate-50 border border-slate-200 text-sm sm:text-[15px] text-slate-800 whitespace-pre-wrap">{{ $aVal }}</div>
                                                    </div>
                                                @endforeach
                                            @endforelse

                                            @if (!$isGraded && !empty($sub->notes))
                                                <div class="p-3.5 rounded bg-white border border-slate-200 text-xs sm:text-sm text-slate-700 shadow-2xs">
                                                    <span class="font-bold text-slate-600">Catatan Peserta:</span>
                                                    <span class="italic">“{{ $sub->notes }}”</span>
                                                </div>
                                            @endif
                                        @elseif ($isUpload)
                                            <div class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-4 shadow-2xs">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="text-xs sm:text-sm text-slate-500">Berkas Jawaban Diunggah</p>
                                                        <p class="text-sm sm:text-base font-bold text-slate-800 wrap-break-word mt-0.5">
                                                            {{ $sub->file_name ?? 'Berkas_Tugas_Peserta.pdf' }}
                                                        </p>
                                                    </div>
                                                    @if ($sub->file_url)
                                                        <a href="{{ $sub->file_url }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shrink-0">
                                                            <i class="fas fa-external-link-alt text-xs"></i>
                                                            <span>Buka Berkas</span>
                                                        </a>
                                                    @endif
                                                </div>

                                                @if (!$isGraded && !empty($sub->notes))
                                                    <div class="p-3.5 rounded bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
                                                        <span class="font-bold text-slate-600">Catatan Peserta:</span>
                                                        <span class="italic">“{{ $sub->notes }}”</span>
                                                    </div>
                                                @endif

                                                @if ($selectedPostTest->admin_file_url)
                                                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs sm:text-sm text-slate-500">
                                                        <span class="truncate">Soal Admin: {{ $selectedPostTest->admin_file_name ?? 'Dokumen Soal' }}</span>
                                                        <a href="{{ $selectedPostTest->admin_file_url }}" target="_blank" class="font-bold text-[#13416B] hover:underline shrink-0">Lihat Soal</a>
                                                    </div>
                                                @endif

                                                @if ($selectedPostTest->answer_key_file_url)
                                                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs sm:text-sm text-emerald-700">
                                                        <span class="truncate font-semibold">Kunci Jawaban Otomatis: {{ $selectedPostTest->answer_key_file_name ?? 'Berkas Kunci' }}</span>
                                                        <a href="{{ $selectedPostTest->answer_key_file_url }}" target="_blank" class="font-bold text-emerald-700 hover:underline shrink-0">Lihat Kunci</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Form Penilaian --}}
                                    <div class="lg:col-span-5">
                                        @if ($selectedPostTest->has_auto_grading)
                                            <form id="auto-grade-form-{{ $sub->id }}"
                                                  action="{{ route('admin-pusat.management-course.post-test-submissions.auto-grade', $sub->id) }}"
                                                  method="POST"
                                                  class="hidden">
                                                @csrf
                                            </form>
                                        @endif

                                        <form action="{{ route('admin-pusat.management-course.post-test-submissions.grade', $sub->id) }}"
                                              method="POST"
                                              class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-4 shadow-2xs">
                                            @csrf

                                            <div class="pb-2.5 border-b border-slate-100 flex items-center justify-between gap-2 flex-wrap">
                                                <div>
                                                    <h4 class="text-sm sm:text-base font-bold text-slate-800">Penilaian Instruktur</h4>
                                                    @if ($passingScore > 0)
                                                        <span class="text-xs sm:text-sm text-slate-500">KKM: {{ $passingScore }}</span>
                                                    @endif
                                                </div>
                                                @if ($selectedPostTest->has_auto_grading)
                                                    <button type="submit"
                                                            form="auto-grade-form-{{ $sub->id }}"
                                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer">
                                                        <i class="fas fa-robot text-xs"></i>
                                                        <span>Koreksi Otomatis</span>
                                                    </button>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                                                    Nilai (0 - 100) <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number"
                                                       name="score"
                                                       min="0"
                                                       max="100"
                                                       required
                                                       x-model.number="scoreVal"
                                                       @if ($passingScore > 0)
                                                           @input="isPassedVal = (scoreVal !== '' && Number(scoreVal) >= {{ $passingScore }}) ? '1' : '0'"
                                                       @endif
                                                       class="w-full px-3.5 py-2.5 text-sm sm:text-base font-bold text-slate-800 rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                                            </div>

                                            <div>
                                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                                                    Keputusan Kelulusan <span class="text-rose-500">*</span>
                                                </label>
                                                <div class="grid grid-cols-2 gap-2.5">
                                                    <label class="flex items-center gap-2 px-3.5 py-2.5 rounded-md border text-xs sm:text-sm font-semibold cursor-pointer transition-colors"
                                                           :class="isPassedVal === '1' ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-white text-slate-600'">
                                                        <input type="radio" name="is_passed" value="1" x-model="isPassedVal" class="text-emerald-600 focus:ring-emerald-500">
                                                        <span>Lulus</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 px-3.5 py-2.5 rounded-md border text-xs sm:text-sm font-semibold cursor-pointer transition-colors"
                                                           :class="isPassedVal === '0' ? 'border-rose-500 bg-rose-50 text-rose-800' : 'border-slate-200 bg-white text-slate-600'">
                                                        <input type="radio" name="is_passed" value="0" x-model="isPassedVal" class="text-rose-600 focus:ring-rose-500">
                                                        <span>Perlu Revisi</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Catatan / Umpan Balik</label>
                                                <textarea name="notes"
                                                          rows="3"
                                                          placeholder="Tuliskan catatan atau umpan balik penilaian untuk peserta..."
                                                          class="w-full px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">{{ $isGraded ? ($sub->notes ?? '') : '' }}</textarea>
                                            </div>

                                            <button type="submit"
                                                    class="w-full px-4 py-2.5 rounded-md text-sm sm:text-[15px] font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors cursor-pointer">
                                                {{ $isGraded ? 'Perbarui Penilaian' : 'Simpan Penilaian' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Tampilan Ringkas Hasil Pilihan Ganda (CBT) --}}
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                                <span class="text-base sm:text-lg font-bold text-slate-500 tabular-nums shrink-0 leading-snug w-7 text-right">
                                    {{ $rowNumber }}.
                                </span>

                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-900">{{ $studentName }}</h3>
                                        @if ($studentInstansi)
                                            <span class="text-xs sm:text-sm text-slate-500">({{ $studentInstansi }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-500">
                                        {{ $sub->user?->email ?? '-' }}
                                        @if ($submittedAt)
                                            — Selesai {{ $submittedAt->translatedFormat('d M Y, H:i') }} WIB
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm sm:text-base font-bold {{ $sub->is_passed ? 'text-emerald-600' : 'text-rose-600' }} shrink-0">
                                Skor CBT: {{ $sub->score ?? 0 }} — {{ $sub->is_passed ? 'Lulus' : 'Belum Lulus' }}
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="p-12 text-center text-slate-500 space-y-1">
                        <p class="text-base font-bold text-slate-700">Belum ada hasil evaluasi dari peserta event ini</p>
                        <p class="text-sm text-slate-400">Jawaban peserta akan muncul di sini setelah mereka menyelesaikan evaluasi terkait.</p>
                    </div>
                @endforelse
            </div>

            @if ($submissions->hasPages())
                <div>{{ $submissions->links() }}</div>
            @endif
        @endif

        {{-- Pop-up Notifikasi Berhasil Menilai (Otomatis / Manual) --}}
        @if (session('grading_popup'))
            @php $popup = session('grading_popup'); @endphp
            <div x-data="{ open: true }"
                 x-show="open"
                 x-cloak
                 @keydown.escape.window="open = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div class="fixed inset-0" @click="open = false"></div>

                <div class="relative z-10 w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                    {{ $popup['method'] ?? 'Penilaian Selesai' }}
                                </p>
                                <h3 class="text-lg font-extrabold text-slate-900 mt-0.5 leading-snug">
                                    {{ $popup['title'] ?? 'Berhasil Menilai' }}
                                </h3>
                            </div>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-1.5 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-slate-500">Peserta</span>
                                <span class="font-bold text-slate-900">{{ $popup['student_name'] ?? '-' }}</span>
                            </div>
                            @if (!is_null($popup['score'] ?? null))
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-slate-500">Nilai Akhir</span>
                                    <span class="font-extrabold {{ !empty($popup['is_passed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $popup['score'] }} ({{ !empty($popup['is_passed']) ? 'Lulus' : 'Perlu Revisi' }})
                                    </span>
                                </div>
                            @elseif (!empty($popup['summary']))
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-slate-500">Ringkasan</span>
                                    <span class="font-bold text-slate-800">{{ $popup['summary'] }}</span>
                                </div>
                            @endif
                        </div>

                        @if (!empty($popup['message']))
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-emerald-50/60 border border-emerald-200/80 rounded-lg p-3">
                                {{ $popup['message'] }}
                            </p>
                        @endif

                        <div class="pt-1 flex justify-end">
                            <button type="button"
                                    @click="open = false"
                                    class="px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-2xs cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-dashboard::layouts.dashboard>
