<x-dashboard::layouts.dashboard title="Kuesioner Evaluasi Bimtek - Admin Pusat">
    <div class="w-full p-0 sm:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- Navigasi Tab (Pertanyaan vs Rekap Respon) --}}
        <div class="flex border-b border-slate-200 gap-2">
            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'questions']) }}"
               class="px-4 py-2.5 text-sm sm:text-base font-bold border-b-2 transition-colors flex items-center
               {{ $tab === 'questions' ? 'border-[#13416B] text-[#13416B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <span>Instrumen Pertanyaan ({{ $questions->count() }})</span>
            </a>
            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'responses', 'bimtek_event_id' => $selectedEventId]) }}"
               class="px-4 py-2.5 text-sm sm:text-base font-bold border-b-2 transition-colors flex items-center
               {{ $tab === 'responses' ? 'border-[#13416B] text-[#13416B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <span>Rekapitulasi Respon Kuesioner ({{ $totalEvaluationsCount }})</span>
            </a>
        </div>

        @if ($tab === 'questions')
            {{-- TAB 1: DAFTAR INSTRUMEN PERTANYAAN --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">Daftar Pertanyaan Kuesioner</h3>
                        <span class="text-xs sm:text-sm text-slate-500 font-medium">({{ $questions->count() }} Pertanyaan)</span>
                    </div>
                    <a href="{{ route('admin-pusat.management-course.evaluation-questions.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shadow-2xs shrink-0">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Tambah Pertanyaan Baru</span>
                    </a>
                </div>

                @if ($questions->isEmpty())
                    <div class="p-12 text-center text-slate-400 space-y-3">
                        <i class="fas fa-clipboard-list text-3xl text-slate-300"></i>
                        <p class="text-base">Belum ada pertanyaan kuesioner yang dibuat.</p>
                        <a href="{{ route('admin-pusat.management-course.evaluation-questions.create') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-md transition-colors">
                            <i class="fas fa-plus"></i> Tambah Pertanyaan Pertama
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($questions as $q)
                            <div class="p-4 sm:p-5 hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
                                    {{-- Badge Nomor Urut --}}
                                    <div class="w-8 h-8 rounded-md bg-slate-100 text-slate-600 font-bold text-xs sm:text-sm flex items-center justify-center shrink-0 border border-slate-200 mt-0.5">
                                        {{ $q->order }}
                                    </div>

                                    <div class="space-y-1.5 flex-1 min-w-0">
                                        {{-- Judul Pertanyaan Bersih --}}
                                        <h4 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                            {{ $q->question }}
                                            @if ($q->is_required)
                                                <span class="text-rose-500 font-bold" title="Wajib Diisi">*</span>
                                            @endif
                                        </h4>

                                        @if ($q->description)
                                            <p class="text-sm sm:text-[15px] text-slate-500 leading-relaxed">
                                                {{ $q->description }}
                                            </p>
                                        @endif

                                        {{-- Metadata Mengalir di Bawah Title (Tanpa Badge Kotak) --}}
                                        <div class="flex flex-wrap items-center gap-x-2.5 sm:gap-x-3 gap-y-1.5 text-xs sm:text-sm text-slate-500 pt-1">
                                            <span>
                                                @if ($q->type === 'scale')
                                                    <span class="font-semibold text-slate-700">Skala 1 - {{ $q->scale_max }}</span>
                                                    <span class="text-slate-500 font-medium ml-1">(1 = {{ $q->min_label ?: 'Sangat Kurang' }}, {{ $q->scale_max }} = {{ $q->max_label ?: 'Sangat Baik' }})</span>
                                                @else
                                                    <span class="font-semibold text-slate-700">Teks Uraian / Masukan</span>
                                                @endif
                                            </span>
                                            <span class="text-slate-300 select-none">—</span>
                                            <span class="font-semibold {{ $q->is_required ? 'text-slate-700' : 'text-slate-500' }}">
                                                {{ $q->is_required ? 'Wajib Diisi' : 'Opsional' }}
                                            </span>
                                            <span class="text-slate-300 select-none">—</span>
                                            @if ($q->is_active)
                                                <span class="text-emerald-600 font-bold">Aktif</span>
                                            @else
                                                <span class="text-slate-400 font-semibold">Non-Aktif</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Aksi Edit / Hapus / Toggle Status --}}
                                <div class="flex items-center gap-2 self-end sm:self-start shrink-0">
                                    {{-- Toggle Status --}}
                                    <form action="{{ route('admin-pusat.management-course.evaluation-questions.toggle-status', $q->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                title="{{ $q->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}"
                                                class="px-2.5 py-1 text-xs sm:text-sm rounded border transition-colors
                                                {{ $q->is_active ? 'text-slate-600 hover:bg-slate-100 border-slate-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200 font-semibold' }}">
                                            {{ $q->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin-pusat.management-course.evaluation-questions.edit', $q->id) }}"
                                       class="p-1.5 text-slate-500 hover:text-[#13416B] hover:bg-[#13416B]/10 rounded transition-colors"
                                       title="Edit Pertanyaan">
                                        <i class="fas fa-pen text-xs sm:text-sm"></i>
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
                                            <i class="fas fa-trash-alt text-xs sm:text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        @else
            {{-- TAB 2: REKAPITULASI RESPON PESERTA --}}
            <div class="space-y-6">
                {{-- Filter Event Bimtek --}}
                <div class="p-4 rounded-md bg-white border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <form action="{{ route('admin-pusat.management-course.evaluation-questions.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                        <input type="hidden" name="tab" value="responses">
                        <label class="text-xs sm:text-sm font-bold text-slate-700 whitespace-nowrap">Filter Acara:</label>
                        <select name="bimtek_event_id" onchange="this.form.submit()"
                                class="px-3 py-2 rounded-md border border-slate-300 text-sm sm:text-[15px] bg-white min-w-[280px]">
                            <option value="">Semua Acara</option>
                            @foreach ($bimtekEvents as $event)
                                <option value="{{ $event->id }}" @selected($selectedEventId == $event->id)>
                                    {{ $event->batch_name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($selectedEventId)
                            <a href="{{ route('admin-pusat.management-course.evaluation-questions.index', ['tab' => 'responses']) }}"
                               class="text-xs sm:text-sm text-rose-600 hover:underline">Reset Filter</a>
                        @endif
                    </form>

                    <div class="text-xs sm:text-sm text-slate-500 font-medium">
                        Total Responden: <span class="font-bold text-slate-800">{{ $totalEvaluationsCount }} Peserta</span>
                    </div>
                </div>

                {{-- Metrik Ringkasan Skor Rata-rata per Pertanyaan Skala --}}
                <div class="bg-white rounded-md border border-slate-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">
                            Rata-Rata Skor per Aspek Penilaian
                        </h3>
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs sm:text-sm font-bold bg-amber-500 text-white">
                            Overall: {{ number_format($overallAvg, 1) }} / 5.0
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($questions->where('type', 'scale') as $sq)
                            @php
                                $stat = $scaleStats[$sq->id] ?? ['count' => 0, 'avg' => 0.0];
                            @endphp
                            <div class="p-3.5 rounded-md border border-slate-200 bg-slate-50/50 space-y-1.5">
                                <span class="text-xs sm:text-sm font-semibold text-slate-600 line-clamp-2 min-h-[2.5rem] leading-snug" title="{{ $sq->question }}">
                                    {{ $sq->question }}
                                </span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-black text-slate-800">{{ number_format($stat['avg'], 1) }}</span>
                                    <span class="text-xs sm:text-sm text-slate-400">/ {{ $sq->scale_max }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-amber-400 text-xs">
                                    @for ($i = 1; $i <= $sq->scale_max; $i++)
                                        <i class="fas fa-star {{ $i <= round($stat['avg']) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                    <span class="text-slate-400 ml-1 text-xs">({{ $stat['count'] }} isi)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tabel / Riwayat Masukan Kuesioner Peserta --}}
                <div class="bg-white rounded-md border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">Riwayat Tanggapan Peserta</h3>
                        <span class="text-xs sm:text-sm text-slate-400">Halaman {{ $responses->currentPage() }} dari {{ $responses->lastPage() }}</span>
                    </div>

                    <div class="p-4 sm:p-6 space-y-6">
                        @php
                            $avatarPalette = [
                                'bg-[#13416B] text-white',
                                'bg-emerald-600 text-white',
                                'bg-amber-500 text-white',
                                'bg-blue-600 text-white',
                                'bg-indigo-600 text-white',
                                'bg-slate-600 text-white',
                            ];
                        @endphp

                        @forelse ($responses as $resp)
                            @php
                                $textAnswers = $resp->answers->filter(fn ($a) => is_null($a->rating) && trim((string) $a->text_answer) !== '')->values();
                                $fallbackFeedback = trim((string) ($resp->feedback ?? ''));
                                $avatarClass = $avatarPalette[$loop->index % count($avatarPalette)];
                                $avgRating = (float) $resp->average_rating;
                            @endphp
                            <div class="flex items-start gap-3 sm:gap-4 {{ !$loop->first ? 'pt-5 border-t border-slate-100' : '' }}">
                                {{-- Avatar Bulat ala YouTube --}}
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full {{ $avatarClass }} flex items-center justify-center shrink-0 mt-0.5 text-xs sm:text-sm font-bold">
                                    <i class="fas fa-user"></i>
                                </div>

                                {{-- Konten Komentar --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Baris 1: Penulis & Waktu --}}
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <span class="text-[13px] sm:text-sm font-bold text-slate-900">
                                            Peserta Anonim
                                        </span>
                                        <span class="text-xs text-slate-500" title="{{ $resp->created_at->translatedFormat('d M Y, H:i') }} WIB">
                                            {{ $resp->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    {{-- Baris 2: Isi Komentar / Catatan --}}
                                    <div class="mt-1 space-y-2">
                                        @if ($textAnswers->count() === 1)
                                            <p class="text-sm sm:text-[15px] text-slate-800 leading-relaxed whitespace-pre-line">
                                                {{ $textAnswers->first()->text_answer }}
                                            </p>
                                        @elseif ($textAnswers->count() > 1)
                                            @foreach ($textAnswers as $ans)
                                                <div class="space-y-0.5">
                                                    <p class="text-xs font-semibold text-slate-500">{{ $ans->question?->question ?? 'Catatan & Masukan' }}</p>
                                                    <p class="text-sm sm:text-[15px] text-slate-800 leading-relaxed whitespace-pre-line">{{ $ans->text_answer }}</p>
                                                </div>
                                            @endforeach
                                        @elseif ($fallbackFeedback !== '')
                                            <p class="text-sm sm:text-[15px] text-slate-800 leading-relaxed whitespace-pre-line">
                                                {{ $fallbackFeedback }}
                                            </p>
                                        @else
                                            <p class="text-sm text-slate-400 italic">
                                                Tidak ada catatan tambahan.
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Baris 3: Baris Meta Bawah (Rating Bintang & Event) --}}
                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star text-[11px] {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                                @endfor
                                            </span>
                                            <span class="ml-0.5 tabular-nums">{{ number_format($avgRating, 1) }}</span>
                                        </span>
                                        <span class="text-slate-300 select-none">•</span>
                                        <span class="font-medium text-slate-500">
                                            {{ $resp->bimtekEvent?->batch_name ?? $resp->course?->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center text-slate-400 text-sm">
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
