<x-dashboard::layouts.dashboard title="Kuesioner Evaluasi Bimtek - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- Header Banner --}}
        <div class="p-5 sm:p-6 rounded-md bg-[#13416B] text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-md bg-white/10 flex items-center justify-center text-sm">
                        <i class="fas fa-poll-h"></i>
                    </span>
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight">Kuesioner Evaluasi Bimtek</h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-200 max-w-2xl leading-relaxed">
                    Kelola format instrumen evaluasi narasumber & sarana bimtek bergaya kuesioner mandiri, serta pantau rekapitulasi masukan objektif dari seluruh peserta pelatihan.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin-pusat.management-course.evaluation-questions.create') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-bold text-[#13416B] bg-white hover:bg-slate-100 rounded-md transition-colors shadow-2xs">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Tambah Pertanyaan Baru</span>
                </a>
            </div>
        </div>

        {{-- Navigasi Tab (Pertanyaan vs Rekap Respon) --}}
        <div class="flex border-b border-slate-200 gap-2">
            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'questions']) }}"
               class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2
               {{ $tab === 'questions' ? 'border-[#13416B] text-[#13416B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i class="fas fa-list-ul"></i>
                <span>Instrumen Pertanyaan ({{ $questions->count() }})</span>
            </a>
            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'responses', 'bimtek_event_id' => $selectedEventId]) }}"
               class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2
               {{ $tab === 'responses' ? 'border-[#13416B] text-[#13416B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Rekapitulasi Respon Kuesioner ({{ $totalEvaluationsCount }})</span>
            </a>
        </div>

        @if ($tab === 'questions')
            {{-- TAB 1: DAFTAR INSTRUMEN PERTANYAAN (FORM BUILDER STYLE) --}}
            <div class="space-y-6">
                @php
                    $groupedQuestions = $questions->groupBy('section');
                @endphp

                @forelse ($groupedQuestions as $sectionName => $sectionQuestions)
                    <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                        {{-- Header Section --}}
                        <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#13416B]"></span>
                                <h3 class="text-sm font-bold text-slate-800">{{ $sectionName ?: 'Umum' }}</h3>
                                <span class="text-xs text-slate-400">({{ $sectionQuestions->count() }} Pertanyaan)</span>
                            </div>
                        </div>

                        {{-- Item Pertanyaan --}}
                        <div class="divide-y divide-slate-100">
                            @foreach ($sectionQuestions as $q)
                                <div class="p-4 sm:p-5 hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
                                        {{-- Badge Nomor Urut --}}
                                        <div class="w-7 h-7 rounded-md bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200 mt-0.5">
                                            {{ $q->order }}
                                        </div>

                                        <div class="space-y-1.5 flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                                    {{ $q->question }}
                                                    @if ($q->is_required)
                                                        <span class="text-rose-500 font-bold" title="Wajib Diisi">*</span>
                                                    @endif
                                                </h4>
                                                
                                                {{-- Badge Tipe Pertanyaan --}}
                                                @if ($q->type === 'scale')
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                                        <i class="fas fa-star text-[10px] text-amber-500"></i> Skala 1 - {{ $q->scale_max }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded">
                                                        <i class="fas fa-align-left text-[10px]"></i> Teks Uraian / Masukan
                                                    </span>
                                                @endif

                                                {{-- Badge Wajib / Opsional --}}
                                                @if ($q->is_required)
                                                    <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">Wajib</span>
                                                @else
                                                    <span class="text-[10px] font-medium text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">Opsional</span>
                                                @endif

                                                {{-- Badge Status --}}
                                                @if ($q->is_active)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                                                        <i class="fas fa-check-circle text-[9px]"></i> Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                                        Non-Aktif
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($q->description)
                                                <p class="text-xs text-slate-500 leading-relaxed">
                                                    {{ $q->description }}
                                                </p>
                                            @endif

                                            @if ($q->type === 'scale')
                                                <div class="flex items-center gap-3 text-[11px] text-slate-400 pt-0.5">
                                                    <span>1 = {{ $q->min_label ?: 'Sangat Kurang' }}</span>
                                                    <span>•</span>
                                                    <span>{{ $q->scale_max }} = {{ $q->max_label ?: 'Sangat Baik' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Aksi Edit / Hapus / Toggle Status --}}
                                    <div class="flex items-center gap-2 self-end sm:self-start shrink-0">
                                        {{-- Toggle Status --}}
                                        <form action="{{ route('admin-pusat.management-course.evaluation-questions.toggle-status', $q->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    title="{{ $q->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}"
                                                    class="px-2.5 py-1 text-xs rounded border transition-colors
                                                    {{ $q->is_active ? 'text-slate-600 hover:bg-slate-100 border-slate-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200 font-semibold' }}">
                                                {{ $q->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        {{-- Edit --}}
                                        <a href="{{ route('admin-pusat.management-course.evaluation-questions.edit', $q->id) }}"
                                           class="p-1.5 text-slate-500 hover:text-[#13416B] hover:bg-[#13416B]/10 rounded transition-colors"
                                           title="Edit Pertanyaan">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>

                                        {{-- Hapus --}}
                                        <form action="{{ route('admin-pusat.management-course.evaluation-questions.destroy', $q->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini? Data jawaban terdahulu yang terkait mungkin ikut terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition-colors"
                                                    title="Hapus Pertanyaan">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center bg-white rounded-md border border-slate-200 text-slate-400 space-y-3">
                        <i class="fas fa-clipboard-list text-3xl text-slate-300"></i>
                        <p class="text-sm">Belum ada pertanyaan kuesioner yang dibuat.</p>
                        <a href="{{ route('admin-pusat.management-course.evaluation-questions.create') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-md transition-colors">
                            <i class="fas fa-plus"></i> Tambah Pertanyaan Pertama
                        </a>
                    </div>
                @endforelse
            </div>

        @else
            {{-- TAB 2: REKAPITULASI RESPON PESERTA --}}
            <div class="space-y-6">
                {{-- Filter Event Bimtek --}}
                <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <form action="{{ route('admin-pusat.management-course.evaluation-questions.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                        <input type="hidden" name="tab" value="responses">
                        <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Filter Event Bimtek:</label>
                        <select name="bimtek_event_id" onchange="this.form.submit()"
                                class="px-3 py-2 rounded-md border border-slate-300 text-xs sm:text-sm bg-white min-w-[280px]">
                            <option value="">Semua Event Bimtek</option>
                            @foreach ($bimtekEvents as $event)
                                <option value="{{ $event->id }}" @selected($selectedEventId == $event->id)>
                                    {{ $event->batch_name }} ({{ $event->batch_code }})
                                </option>
                            @endforeach
                        </select>
                        @if ($selectedEventId)
                            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'responses']) }}"
                               class="text-xs text-rose-600 hover:underline">Reset Filter</a>
                        @endif
                    </form>

                    <div class="text-xs text-slate-500 font-medium">
                        Total Responden: <span class="font-bold text-slate-800">{{ $totalEvaluationsCount }} Peserta</span>
                    </div>
                </div>

                {{-- Metrik Ringkasan Skor Rata-rata per Pertanyaan Skala --}}
                <div class="bg-white rounded-md border border-slate-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-star text-amber-400"></i>
                            <span>Rata-Rata Skor per Aspek Penilaian</span>
                        </h3>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Overall: {{ number_format($overallAvg, 1) }} / 5.0
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($questions->where('type', 'scale') as $sq)
                            @php
                                $stat = $scaleStats[$sq->id] ?? ['count' => 0, 'avg' => 0.0];
                            @endphp
                            <div class="p-3.5 rounded-md border border-slate-200 bg-slate-50/50 space-y-1.5">
                                <span class="text-[11px] font-semibold text-slate-500 line-clamp-1" title="{{ $sq->question }}">
                                    {{ $sq->question }}
                                </span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-black text-slate-800">{{ number_format($stat['avg'], 1) }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $sq->scale_max }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-amber-400 text-[10px]">
                                    @for ($i = 1; $i <= $sq->scale_max; $i++)
                                        <i class="fas fa-star {{ $i <= round($stat['avg']) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                    <span class="text-slate-400 ml-1 text-[10px]">({{ $stat['count'] }} isi)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tabel / Riwayat Masukan Kuesioner Peserta --}}
                <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800">Riwayat Tanggapan Peserta (Anonim)</h3>
                        <span class="text-xs text-slate-400">Halaman {{ $responses->currentPage() }} dari {{ $responses->lastPage() }}</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($responses as $resp)
                            <div class="p-4 sm:p-5 hover:bg-slate-50/40 transition-colors space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold">
                                            <i class="fas fa-user-secret"></i>
                                        </span>
                                        <div>
                                            <span class="text-xs font-bold text-slate-800">Peserta Bimtek (Anonim)</span>
                                            <span class="text-[11px] text-slate-400 ml-1.5">• {{ $resp->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                            <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded ml-2">
                                                {{ $resp->bimtekEvent?->batch_name ?? $resp->course?->name }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-star text-amber-400 text-[10px]"></i>
                                        <span>Rata-Rata: {{ number_format($resp->average_rating, 1) }} / 5.0</span>
                                    </span>
                                </div>

                                {{-- Rincian Jawaban per Pertanyaan --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-1 text-xs">
                                    @if ($resp->answers->isNotEmpty())
                                        @foreach ($resp->answers as $ans)
                                            <div class="p-2.5 rounded bg-slate-50 border border-slate-150">
                                                <p class="font-semibold text-slate-700 mb-1 leading-snug">
                                                    {{ $ans->question?->question ?? 'Pertanyaan' }}
                                                </p>
                                                @if (!is_null($ans->rating))
                                                    <div class="flex items-center gap-1.5 text-amber-500 font-bold">
                                                        <span>{{ $ans->rating }}★</span>
                                                        <span class="text-[10px] text-slate-400 font-normal">
                                                            ({{ $ans->rating == 5 ? 'Sangat Baik' : ($ans->rating >= 4 ? 'Baik' : ($ans->rating >= 3 ? 'Cukup' : 'Kurang')) }})
                                                        </span>
                                                    </div>
                                                @else
                                                    <p class="text-slate-600 italic">
                                                        {{ $ans->text_answer ?: 'Tidak ada catatan.' }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        {{-- Legacy Data Fallback --}}
                                        <div class="p-2 rounded bg-slate-50 border border-slate-150">
                                            <span class="text-slate-500">Materi:</span> <strong class="text-amber-500">{{ $resp->rating_materi }}★</strong>
                                            <span class="mx-2">•</span>
                                            <span class="text-slate-500">Penyampaian:</span> <strong class="text-amber-500">{{ $resp->rating_penyampaian }}★</strong>
                                            <span class="mx-2">•</span>
                                            <span class="text-slate-500">Fasilitasi:</span> <strong class="text-amber-500">{{ $resp->rating_fasilitasi }}★</strong>
                                        </div>
                                        @if ($resp->feedback)
                                            <div class="p-2 rounded bg-slate-50 border border-slate-150 col-span-full">
                                                <span class="text-slate-500 font-semibold block mb-0.5">Catatan Masukan:</span>
                                                <p class="text-slate-700 italic">“{{ $resp->feedback }}”</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center text-slate-400 text-xs">
                                Belum ada respon kuesioner yang tercatat.
                            </div>
                        @endforelse
                    </div>

                    @if ($responses->hasPages())
                        <div class="p-4 border-t border-slate-100">
                            {{ $responses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-dashboard::layouts.dashboard>
