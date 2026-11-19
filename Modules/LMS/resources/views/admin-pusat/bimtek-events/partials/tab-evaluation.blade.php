{{-- TAB 3: PENILAIAN EVALUASI HARIAN & EVALUASI AKHIR (NON-PILGAN & CBT) --}}
<div class="space-y-5">
    @if (!$selectedPostTest)
        {{-- Daftar Timeline Evaluasi Harian + Evaluasi Akhir --}}
        @php
            $timelineTotal = $evalDailyAgendas->count() + ($evalFinalEvaluation->post_test ? 1 : 0);
        @endphp

        <div class="relative space-y-4 sm:space-y-5">
            @if ($timelineTotal > 1)
                <div class="absolute left-7 top-6 bottom-6 w-0.5 bg-slate-200 -translate-x-1/2 pointer-events-none z-0"></div>
            @endif

            @forelse ($evalDailyAgendas as $agenda)
                @php
                    $hasSubmitted = $agenda->submitted_count > 0;
                    $allGraded = $agenda->post_test && $hasSubmitted && $agenda->pending_count === 0;
                @endphp
                <div class="relative flex items-start gap-3.5 sm:gap-5">
                    <div class="relative z-10 w-14 h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
                        {{ $agenda->post_test ? ($hasSubmitted ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300') : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                        <span class="text-[10px] font-bold uppercase tracking-normal {{ $agenda->post_test && $hasSubmitted ? 'text-blue-200' : 'text-slate-400' }} leading-none">HARI</span>
                        <span class="text-lg sm:text-xl font-black leading-tight mt-1">{{ $agenda->day_number }}</span>
                        @if ($allGraded)
                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] ring-2 ring-white shadow-2xs"><i class="fas fa-check"></i></span>
                        @elseif ($agenda->pending_count > 0)
                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[9px] font-bold ring-2 ring-white shadow-2xs">!</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2 min-w-0 flex-1">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-[#13416B]">
                                    Evaluasi Hari ke-{{ $agenda->day_number }}{{ $agenda->day_date ? ' — ' . $agenda->day_date : '' }}
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                                    {{ $agenda->title }}
                                </h3>
                            </div>

                            @if ($agenda->post_test)
                                <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                    <span class="font-bold text-[#13416B]">Format {{ $agenda->format_label }}</span>
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    <span class="font-semibold">{{ $agenda->submitted_count }} / {{ $agenda->total_participants }} peserta mengumpulkan</span>
                                    @if ($agenda->is_manual && $agenda->pending_count > 0)
                                        <span class="text-[#13416B]/35 select-none">—</span>
                                        <span class="font-bold text-amber-600">{{ $agenda->pending_count }} perlu diperiksa / dipublikasikan</span>
                                    @elseif (!$agenda->is_manual)
                                        <span class="text-[#13416B]/35 select-none">—</span>
                                        <span class="font-bold text-emerald-600">Dinilai Otomatis (CBT)</span>
                                    @elseif ($hasSubmitted)
                                        <span class="text-[#13416B]/35 select-none">—</span>
                                        <span class="font-bold text-emerald-600">Semua Dipublikasikan</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs sm:text-sm italic text-slate-400">Belum ada instrumen evaluasi pada agenda ini</p>
                            @endif
                        </div>

                        @if ($agenda->post_test)
                            <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'evaluation', 'post_test_id' => $agenda->post_test->id]) }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold {{ $agenda->is_manual && $agenda->pending_count > 0 ? 'text-white bg-[#13416B] hover:bg-[#0f3354]' : 'text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white' }} transition-colors shrink-0">
                                <span>{{ $agenda->is_manual ? 'Periksa & Nilai' : 'Lihat Nilai CBT' }}</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-md border border-slate-200 p-10 text-center text-sm text-slate-500">
                    Belum ada agenda harian pada kursus ini.
                </div>
            @endforelse

            {{-- Evaluasi Akhir --}}
            @if ($evalFinalEvaluation->post_test)
                @php
                    $finalHasSubmitted = $evalFinalEvaluation->submitted_count > 0;
                    $finalAllGraded = $finalHasSubmitted && $evalFinalEvaluation->pending_count === 0;
                @endphp
                <div class="relative flex items-start gap-3.5 sm:gap-5">
                    <div class="relative z-10 w-12 h-12 sm:w-14 sm:h-14 rounded-md shrink-0 flex flex-col items-center justify-center border shadow-2xs
                        {{ $finalHasSubmitted ? 'bg-[#13416B] text-white border-[#13416B]' : 'bg-white text-[#13416B] border-slate-300' }}"
                         title="Evaluasi Akhir">
                        <i class="fas fa-flag-checkered text-lg sm:text-xl"></i>
                        @if ($finalAllGraded)
                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] ring-2 ring-white shadow-2xs"><i class="fas fa-check"></i></span>
                        @elseif ($evalFinalEvaluation->pending_count > 0)
                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[9px] font-bold ring-2 ring-white shadow-2xs">!</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 bg-white rounded-md border border-slate-200 p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2 min-w-0 flex-1">
                            <div>
                                <div class="text-xs sm:text-sm font-bold text-[#13416B]">Evaluasi Akhir Pelatihan</div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                                    {{ $evalFinalEvaluation->title }}
                                </h3>
                            </div>

                            <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                <span class="font-bold text-[#13416B]">Format {{ $evalFinalEvaluation->format_label }}</span>
                                <span class="text-[#13416B]/35 select-none">—</span>
                                <span class="font-semibold">{{ $evalFinalEvaluation->submitted_count }} / {{ $evalFinalEvaluation->total_participants }} peserta mengumpulkan</span>
                                @if ($evalFinalEvaluation->is_manual && $evalFinalEvaluation->pending_count > 0)
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    <span class="font-bold text-amber-600">{{ $evalFinalEvaluation->pending_count }} perlu diperiksa / dipublikasikan</span>
                                @elseif (!$evalFinalEvaluation->is_manual)
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    <span class="font-bold text-emerald-600">Dinilai Otomatis (CBT)</span>
                                @elseif ($finalHasSubmitted)
                                    <span class="text-[#13416B]/35 select-none">—</span>
                                    <span class="font-bold text-emerald-600">Semua Dipublikasikan</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'evaluation', 'post_test_id' => $evalFinalEvaluation->post_test->id]) }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold {{ $evalFinalEvaluation->is_manual && $evalFinalEvaluation->pending_count > 0 ? 'text-white bg-[#13416B] hover:bg-[#0f3354]' : 'text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white' }} transition-colors shrink-0">
                            <span>{{ $evalFinalEvaluation->is_manual ? 'Periksa & Nilai' : 'Lihat Nilai CBT' }}</span>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- Container Master Detail Evaluasi (Sub-Header + Filter + Kontrol Otomatis + Daftar Peserta + Paginasi) --}}
        <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
            {{-- 1. Sub-Header Evaluasi Terpilih & Tombol Kembali --}}
            <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#13416B]">
                        {{ $selectedPostTest->course_section_id ? 'Evaluasi Harian' : 'Evaluasi Akhir' }}
                    </p>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 wrap-break-word">
                        {{ $selectedPostTestHeaderTitle }}
                    </h2>
                </div>
                <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'evaluation']) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 border border-slate-200 transition-colors shrink-0">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali ke Daftar Evaluasi</span>
                </a>
            </div>

            {{-- 2. Bar Pencarian & Filter Peserta Evaluasi --}}
            <form action="{{ route('admin-pusat.management-course.bimtek-events.show', $event->id) }}" method="GET"
                  class="p-4 border-b border-slate-200 bg-white flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <input type="hidden" name="tab" value="evaluation">
                <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">

                <input type="text"
                       name="eval_search"
                       value="{{ $evalSearch }}"
                       placeholder="Cari nama, email, atau instansi peserta..."
                       class="flex-1 px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">

                @if ($isManualTest)
                    <select name="eval_status" onchange="this.form.submit()"
                            class="px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 bg-white focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">
                        <option value="all" @selected($evalStatus === 'all')>Semua Status ({{ $triageCounts['all'] }})</option>
                        <option value="pending" @selected(in_array($evalStatus, ['pending', 'submitted'], true))>Menunggu Dinilai ({{ $triageCounts['pending'] }})</option>
                        <option value="needs_review" @selected($evalStatus === 'needs_review')>Perlu Review Manual ({{ $triageCounts['needs_review'] }})</option>
                        <option value="failed" @selected($evalStatus === 'failed')>Gagal AI ({{ $triageCounts['failed'] }})</option>
                        <option value="draft_graded" @selected($evalStatus === 'draft_graded')>Siap Publikasi / Draf ({{ $triageCounts['draft_ready'] }})</option>
                        <option value="published" @selected(in_array($evalStatus, ['published', 'graded'], true))>Dipublikasikan ({{ $triageCounts['published'] }})</option>
                    </select>
                @endif

                <div class="flex items-center gap-2 shrink-0">
                    @if ($evalSearch !== '' || ($evalStatus !== 'all' && $evalStatus !== ''))
                        <a href="{{ route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $event->id, 'tab' => 'evaluation', 'post_test_id' => $selectedPostTest->id]) }}"
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

            {{-- 2b. Panel Kontrol Otomatis & Publikasi (Terintegrasi di dalam Container Master) --}}
            @if ($isManualTest)
                @php
                    $batchObj = $activeBatch ?? null;
                    $batchPollUrl = $batchObj
                        ? route('admin-pusat.management-course.post-test-submissions.batch.status', $batchObj->id)
                        : '';
                @endphp

                <div class="bg-[#1B568A] text-white px-4 sm:px-5 py-4 border-b border-[#13416B] space-y-3.5"
                     x-data="{
                         batchStatus: '{{ $batchObj?->status ?? '' }}',
                         processed: {{ (int) ($batchObj?->processed_count ?? 0) }},
                         failed: {{ (int) ($batchObj?->failed_count ?? 0) }},
                         total: {{ (int) ($batchObj?->total_targets ?? 0) }},
                         progress: {{ (int) ($batchObj?->progress_percent ?? 0) }},
                         pollUrl: '{{ $batchPollUrl }}',
                         timer: null,
                         init() {
                             if (this.batchStatus === 'RUNNING' && this.pollUrl) {
                                 this.timer = setInterval(() => this.fetchStatus(), 6000);
                             }
                         },
                         async fetchStatus() {
                             try {
                                 const res = await fetch(this.pollUrl, { headers: { 'Accept': 'application/json' } });
                                 if (!res.ok) return;
                                 const data = await res.json();
                                 this.batchStatus = data.status;
                                 this.processed = data.processed_count;
                                 this.failed = data.failed_count;
                                 this.total = data.total_targets;
                                 this.progress = data.progress_percent;
                                 if (data.status !== 'RUNNING' && this.timer) {
                                     clearInterval(this.timer);
                                     window.location.reload();
                                 }
                             } catch (e) {}
                         }
                     }">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-sm sm:text-base font-bold text-white">
                                @if ($triageCounts['pure_pending'] > 0)
                                    Tersedia {{ $triageCounts['pure_pending'] }} Jawaban Belum Dinilai
                                @elseif ($triageCounts['draft_ready'] > 0)
                                    {{ $triageCounts['draft_ready'] }} Draf Nilai Siap Ditinjau &amp; Dipublikasikan
                                @else
                                    Kontrol Penilaian Otomatis &amp; Publikasi
                                @endif
                            </p>
                            <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                                <span>
                                    {{ $selectedPostTest->course_section_id ? 'Evaluasi Harian' : 'Evaluasi Akhir' }}
                                    — Format {{ $selectedPostTest->is_essay ? 'Uraian / Esai' : 'Upload Berkas' }}
                                </span>
                                @if ($triageCounts['needs_review'] > 0)
                                    <span class="text-white/40 mx-1.5 select-none">—</span>
                                    <span class="font-semibold text-amber-200">{{ $triageCounts['needs_review'] }} Perlu Review Manual</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if ($selectedPostTest->has_auto_grading && $triageCounts['pure_pending'] > 0)
                                <form action="{{ route('admin-pusat.management-course.post-test-submissions.batch.start') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="bimtek_event_id" value="{{ $event->id }}">
                                    <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors shadow-2xs cursor-pointer">
                                        <i class="fas fa-wand-magic-sparkles"></i>
                                        <span>Mulai Penilaian Otomatis ({{ $triageCounts['pure_pending'] }})</span>
                                    </button>
                                </form>
                            @endif

                            @if ($batchObj && $batchObj->status === 'PAUSED_QUOTA')
                                <form action="{{ route('admin-pusat.management-course.post-test-submissions.batch.resume', $batchObj->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-colors shadow-2xs cursor-pointer">
                                        <i class="fas fa-play"></i>
                                        <span>Lanjutkan Batch</span>
                                    </button>
                                </form>
                            @endif

                            @if ($batchObj && $triageCounts['failed'] > 0)
                                <form action="{{ route('admin-pusat.management-course.post-test-submissions.batch.retry-failed', $batchObj->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-white bg-rose-500 hover:bg-rose-600 transition-colors shadow-2xs cursor-pointer">
                                        <i class="fas fa-rotate-right"></i>
                                        <span>Ulangi yang Gagal ({{ $triageCounts['failed'] }})</span>
                                    </button>
                                </form>
                            @endif

                            @if ($triageCounts['draft_ready'] > 0)
                                <button type="button"
                                        @click="$dispatch('open-modal', 'confirm-publish-grades')"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-blue-50 transition-colors shadow-2xs cursor-pointer">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Publikasikan Semua Nilai ({{ $triageCounts['draft_ready'] }})</span>
                                </button>
                            @endif

                            <form action="{{ route('admin-pusat.management-course.post-test-submissions.toggle-lock') }}" method="POST">
                                @csrf
                                <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-md text-xs sm:text-sm font-semibold text-white bg-white/15 hover:bg-white/25 border border-white/25 transition-colors cursor-pointer">
                                    <i class="fas {{ $selectedPostTest->is_submission_locked ? 'fa-lock-open' : 'fa-lock' }} text-xs"></i>
                                    <span>{{ $selectedPostTest->is_submission_locked ? 'Buka Kunci' : 'Kunci Pengumpulan' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($batchObj && in_array($batchObj->status, ['RUNNING', 'PAUSED_QUOTA'], true))
                        <div class="pt-3 border-t border-white/15 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs sm:text-sm">
                                <span class="font-semibold text-blue-100">
                                    @if ($batchObj->status === 'PAUSED_QUOTA')
                                        <span class="text-amber-200 font-semibold">Dijeda sementara — Kuota harian/RPM tercapai, tekan Lanjutkan Batch saat siap</span>
                                    @else
                                        <span>Sedang memproses penilaian otomatis...</span>
                                    @endif
                                </span>
                                <span class="font-bold text-white tabular-nums">
                                    <span x-text="processed"></span> / <span x-text="total"></span> selesai
                                    <template x-if="failed > 0">
                                        <span class="text-rose-200 ml-1">(<span x-text="failed"></span> gagal)</span>
                                    </template>
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-white/20 overflow-hidden">
                                <div class="h-full bg-emerald-400 transition-all duration-300" :style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 3. Daftar Jawaban Peserta Bernomor --}}
            @php
                $resolveFileIcon = function (?string $filename): array {
                    $ext = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
                    return match ($ext) {
                        'xlsx', 'xls', 'csv' => ['icon' => 'fa-file-excel', 'color' => 'text-emerald-600'],
                        'pdf'                => ['icon' => 'fa-file-pdf', 'color' => 'text-rose-600'],
                        'doc', 'docx'        => ['icon' => 'fa-file-word', 'color' => 'text-blue-600'],
                        'ppt', 'pptx'        => ['icon' => 'fa-file-powerpoint', 'color' => 'text-orange-500'],
                        'zip', 'rar', '7z'   => ['icon' => 'fa-file-archive', 'color' => 'text-amber-600'],
                        default              => ['icon' => 'fa-file-alt', 'color' => 'text-slate-500'],
                    };
                };
            @endphp

            <div class="divide-y divide-slate-200">
            @forelse ($evalSubmissions as $sub)
                @php
                    $isEssay = (bool) $selectedPostTest->is_essay;
                    $isUpload = (bool) $selectedPostTest->is_file_upload;
                    $gStatus = (string) ($sub->grading_status ?? 'PENDING');
                    $isPublished = $gStatus === 'PUBLISHED';
                    $isDraft = in_array($gStatus, ['DRAFT_GRADED', 'MANUAL_REVIEW'], true) && (!is_null($sub->final_score) || !is_null($sub->ai_score));
                    $hasScoreValue = !is_null($sub->final_score) || !is_null($sub->ai_score) || $isPublished;
                    $displayScore = $sub->effective_score;
                    $passingScore = (float) $selectedPostTest->effectivePassingGrade();
                    $isPassedNow = $hasScoreValue && !is_null($displayScore) ? ($displayScore >= $passingScore) : (bool) $sub->is_passed;
                    $studentName = $sub->user?->profile?->full_name ?? $sub->user?->name ?? 'Peserta';
                    $studentInstansi = $sub->user?->profile?->instansi;
                    $submittedAt = $sub->completed_at ?? $sub->updated_at;
                    $rowNumber = ($evalSubmissions->firstItem() ?? 1) + $loop->index;
                    $feedbackData = is_array($sub->ai_feedback_json) ? $sub->ai_feedback_json : [];
                    $triageReasons = is_array($feedbackData['triage_reasons'] ?? null) ? $feedbackData['triage_reasons'] : [];
                @endphp

                @if ($isManualTest)
                    <div x-data="{ expanded: false, scoreVal: {{ $hasScoreValue && !is_null($displayScore) ? (float) $displayScore : "''" }}, isPassedVal: '{{ $isPassedNow ? '1' : '0' }}' }">
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

                                    <div class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 px-3.5 py-2 rounded-md bg-[#DCEBF8] border border-[#BBD6EE] text-xs sm:text-sm text-[#1E4468]">
                                        @if ($isPublished)
                                            <span class="font-extrabold {{ $isPassedNow ? 'text-emerald-600' : 'text-rose-600' }}">
                                                Nilai {{ rtrim(rtrim(number_format((float) $displayScore, 2, '.', ''), '0'), '.') }}
                                            </span>
                                            <span class="text-[#13416B]/35 select-none">—</span>
                                            <span class="font-bold text-emerald-600">Dipublikasikan</span>
                                        @elseif ($isDraft)
                                            <span class="font-extrabold {{ $isPassedNow ? 'text-emerald-600' : 'text-rose-600' }}">
                                                Nilai {{ rtrim(rtrim(number_format((float) $displayScore, 2, '.', ''), '0'), '.') }}
                                            </span>
                                            <span class="text-[#13416B]/35 select-none">—</span>
                                            @if ($sub->needs_manual_review)
                                                <span class="font-bold text-amber-600">Draf &amp; Perlu Direview Manual</span>
                                            @else
                                                <span class="font-bold text-[#13416B]">Draf</span>
                                            @endif
                                        @elseif ($gStatus === 'FAILED')
                                            <span class="font-bold text-rose-600">Gagal AI</span>
                                        @elseif ($gStatus === 'MANUAL_REVIEW')
                                            <span class="font-bold text-amber-600">Perlu Review Manual</span>
                                        @elseif (in_array($gStatus, ['QUEUED', 'PROCESSING'], true))
                                            <span class="font-bold text-[#13416B]">{{ $gStatus === 'PROCESSING' ? 'Sedang Dinilai' : 'Dalam Antrean' }}</span>
                                        @else
                                            <span class="font-bold text-amber-600">Menunggu Penilaian</span>
                                        @endif

                                        @if ($submittedAt)
                                            <span class="text-[#13416B]/35 select-none">—</span>
                                            <span class="font-semibold text-[#1E4468]">Dikumpulkan {{ $submittedAt->translatedFormat('d M Y, H:i') }} WIB</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs sm:text-sm font-semibold text-[#13416B] bg-slate-100 hover:bg-slate-200 transition-colors">
                                    <span x-text="expanded ? 'Tutup' : 'Periksa & Nilai'"></span>
                                    <i class="fas fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                                </span>
                            </div>
                        </div>

                        <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                                {{-- Kolom Kiri: Jawaban Peserta --}}
                                <div class="lg:col-span-7 space-y-4">
                                    @if ($gStatus === 'FAILED' && !empty($sub->last_error_message))
                                        <div class="p-3.5 rounded-md bg-rose-50 border border-rose-200 text-xs sm:text-sm text-rose-800 space-y-1">
                                            <p class="font-bold">Gagal Diproses Otomatis:</p>
                                            <p>{{ $sub->last_error_message }}</p>
                                        </div>
                                    @endif

                                    @if ($sub->needs_manual_review && !empty($triageReasons))
                                        <div class="p-3.5 rounded-md bg-amber-50 border border-amber-200 text-xs sm:text-sm text-amber-900 space-y-1.5">
                                            <p class="font-bold text-amber-800">Ditandai Perlu Review Manual:</p>
                                            @if (count($triageReasons) === 1)
                                                <p>{{ $triageReasons[0] }}</p>
                                            @else
                                                <ul class="list-disc list-inside space-y-0.5">
                                                    @foreach ($triageReasons as $reason)
                                                        <li>{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($isEssay)
                                        @php
                                            $questions = $selectedPostTest->questions->values();
                                            $answers = is_array($sub->answers) ? $sub->answers : [];
                                            $aiAnswersByQ = collect($feedbackData['answers'] ?? [])->keyBy(fn($a) => (string) ($a['question_id'] ?? ''));
                                        @endphp

                                        @forelse ($questions as $qIdx => $question)
                                            @php
                                                $ansText = (string) ($answers[$qIdx] ?? ($answers[(string) $question->id] ?? ''));
                                                $wordCount = trim($ansText) !== '' ? count(preg_split('/\s+/u', trim($ansText))) : 0;
                                                $aiAnsItem = $aiAnswersByQ->get((string) $question->id);
                                            @endphp
                                            <div class="bg-white rounded-md border border-slate-200 p-4 sm:p-5 space-y-3 shadow-2xs">
                                                <div class="flex items-start justify-between gap-2">
                                                    <p class="text-sm sm:text-base font-bold text-slate-800 leading-snug wrap-break-word">
                                                        {{ $qIdx + 1 }}. {{ strip_tags($question->question) }}
                                                    </p>
                                                    @if ($aiAnsItem)
                                                        <span class="px-2.5 py-0.5 rounded bg-blue-50 border border-blue-200 text-xs font-bold text-[#13416B] shrink-0">
                                                            {{ $aiAnsItem['awarded_score'] }} / {{ $aiAnsItem['max_score'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="p-3.5 sm:p-4 rounded bg-slate-50 border border-slate-200 text-sm sm:text-[15px] text-slate-800 leading-relaxed whitespace-pre-wrap wrap-break-word">{{ $ansText !== '' ? $ansText : 'Peserta tidak mengisi jawaban pada nomor ini.' }}</div>
                                                @if ($aiAnsItem && !empty($aiAnsItem['reason']))
                                                    <div class="p-3 rounded bg-blue-50/60 border border-blue-200/80 text-xs sm:text-sm text-slate-700">
                                                        <span class="font-bold text-[#13416B]">Alasan Penilaian:</span>
                                                        <span>{{ $aiAnsItem['reason'] }}</span>
                                                    </div>
                                                @endif
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
                                                    <div class="p-3.5 rounded bg-slate-50 border border-slate-200 text-sm text-slate-800 whitespace-pre-wrap">{{ $aVal }}</div>
                                                </div>
                                            @endforeach
                                        @endforelse

                                        @if (!$isPublished && !empty($sub->notes))
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

                                            @if (!$isPublished && !empty($sub->notes))
                                                <div class="p-3.5 rounded bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
                                                    <span class="font-bold text-slate-600">Catatan Peserta:</span>
                                                    <span class="italic">“{{ $sub->notes }}”</span>
                                                </div>
                                            @endif

                                            @if (!empty($feedbackData['criteria_breakdown']) && is_array($feedbackData['criteria_breakdown']))
                                                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                                                    <p class="text-xs sm:text-sm font-bold text-slate-700">Rincian Penilaian Otomatis</p>
                                                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                                        @foreach ($feedbackData['criteria_breakdown'] as $cb)
                                                            <div class="p-3 rounded bg-slate-50 border border-slate-200 text-xs sm:text-sm space-y-1">
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <span class="font-bold text-slate-800">{{ $cb['criterion_name'] ?? '-' }}</span>
                                                                    <span class="font-bold text-[#13416B] tabular-nums">{{ $cb['awarded_score'] ?? 0 }} / {{ $cb['max_score'] ?? 0 }}</span>
                                                                </div>
                                                                @if (!empty($cb['evidence_quote']))
                                                                    <p class="text-slate-600 italic">Bukti: “{{ $cb['evidence_quote'] }}”</p>
                                                                @endif
                                                                @if (!empty($cb['reason']))
                                                                    <p class="text-slate-600">{{ $cb['reason'] }}</p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($selectedPostTest->admin_file_url)
                                                @php $adminIcon = $resolveFileIcon($selectedPostTest->admin_file_name ?: $selectedPostTest->admin_file_path); @endphp
                                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs sm:text-sm text-slate-600">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <i class="fas {{ $adminIcon['icon'] }} {{ $adminIcon['color'] }} text-sm shrink-0"></i>
                                                        <span class="truncate">
                                                            <span class="font-semibold text-slate-700">Soal :</span>
                                                            {{ $selectedPostTest->admin_file_name ?? 'Dokumen Soal' }}
                                                        </span>
                                                    </div>
                                                    <a href="{{ $selectedPostTest->admin_file_url }}" target="_blank" class="font-bold text-[#13416B] hover:underline shrink-0">Lihat Soal</a>
                                                </div>
                                            @endif

                                            @if ($selectedPostTest->answer_key_file_url)
                                                @php $keyIcon = $resolveFileIcon($selectedPostTest->answer_key_file_name ?: $selectedPostTest->answer_key_file_path); @endphp
                                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs sm:text-sm text-emerald-700">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <i class="fas {{ $keyIcon['icon'] }} {{ $keyIcon['color'] }} text-sm shrink-0"></i>
                                                        <span class="truncate font-medium">
                                                            <span class="font-semibold text-emerald-800">Kunci Jawaban :</span>
                                                            {{ $selectedPostTest->answer_key_file_name ?? 'Berkas Kunci' }}
                                                        </span>
                                                    </div>
                                                    <a href="{{ $selectedPostTest->answer_key_file_url }}" target="_blank" class="font-bold text-emerald-700 hover:underline shrink-0">Lihat Kunci</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Kolom Kanan: Form Penilaian --}}
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
                                                Nilai Akhir (0 - 100) <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="number" name="score" min="0" max="100" step="0.01" required
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
                                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Umpan Balik untuk Peserta</label>
                                            <textarea name="notes" rows="3"
                                                      placeholder="Tuliskan catatan atau umpan balik penilaian yang akan dibaca oleh peserta..."
                                                      class="w-full px-3.5 py-2.5 text-sm sm:text-[15px] rounded-md border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none">{{ $isPublished ? ($sub->notes ?: ($feedbackData['overall_feedback'] ?? '')) : ($isDraft ? ($feedbackData['overall_feedback'] ?? '') : '') }}</textarea>
                                        </div>

                                        <label class="flex items-center gap-2 text-xs sm:text-sm text-slate-700 cursor-pointer">
                                            <input type="checkbox" name="publish_now" value="1" checked class="rounded text-[#13416B] focus:ring-[#13416B]">
                                            <span>Publikasikan langsung ke peserta (jika tidak dicentang, disimpan sebagai Draf)</span>
                                        </label>

                                        <button type="submit"
                                                class="w-full px-4 py-2.5 rounded-md text-sm sm:text-[15px] font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors cursor-pointer">
                                            {{ $isPublished ? 'Perbarui Penilaian' : 'Simpan Penilaian' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-start gap-3 sm:gap-4 min-w-0 flex-1">
                            <span class="text-base sm:text-lg font-bold text-slate-500 tabular-nums shrink-0 leading-snug w-7 text-right">{{ $rowNumber }}.</span>
                            <div class="space-y-1 min-w-0 flex-1">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900">{{ $studentName }}</h3>
                                <p class="text-xs sm:text-sm text-slate-500">{{ $sub->user?->email ?? '-' }} {{ $submittedAt ? '— Selesai ' . $submittedAt->translatedFormat('d M Y, H:i') . ' WIB' : '' }}</p>
                            </div>
                        </div>
                        <div class="text-sm sm:text-base font-bold {{ $sub->is_passed ? 'text-emerald-600' : 'text-rose-600' }} shrink-0">
                            Skor CBT: {{ $sub->score ?? 0 }} — {{ $sub->is_passed ? 'Lulus' : 'Belum Lulus' }}
                        </div>
                    </div>
                @endif
                @empty
                    <div class="p-12 text-center text-slate-500 space-y-1">
                        <p class="text-base font-bold text-slate-700">Belum ada hasil evaluasi dari peserta gelombang ini</p>
                        <p class="text-sm text-slate-400">Jawaban peserta akan muncul di sini setelah mereka menyelesaikan evaluasi terkait.</p>
                    </div>
                @endforelse
            </div>

            @if ($evalSubmissions && $evalSubmissions->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/40">
                    {{ $evalSubmissions->links() }}
                </div>
            @endif
        </div>

        @if ($isManualTest && ($triageCounts['draft_ready'] ?? 0) > 0)
            <x-modal name="confirm-publish-grades" title="Konfirmasi Publikasi Nilai" maxWidth="sm:max-w-md">
                <form action="{{ route('admin-pusat.management-course.post-test-submissions.publish') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="bimtek_event_id" value="{{ $event->id }}">
                    <input type="hidden" name="post_test_id" value="{{ $selectedPostTest->id }}">

                    <div class="flex items-start gap-3.5 py-1">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[#13416B]">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </div>
                        <div class="flex-1 pt-0.5 space-y-1">
                            <p class="text-sm font-semibold text-slate-800 leading-relaxed">
                                Publikasikan {{ $triageCounts['draft_ready'] }} draf nilai ke peserta?
                            </p>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Nilai akhir akan tampil di halaman masing-masing peserta dan otomatis memperbarui progres kelulusan pelatihan.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button"
                                @click="$dispatch('close-modal', 'confirm-publish-grades')"
                                class="px-4 py-2 rounded-md text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors cursor-pointer">
                            <i class="fas fa-paper-plane text-xs"></i>
                            <span>Ya, Publikasikan</span>
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif
    @endif
</div>
