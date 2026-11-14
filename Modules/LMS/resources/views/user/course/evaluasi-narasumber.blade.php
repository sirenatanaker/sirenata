<x-dashboard::layouts.dashboard title="Evaluasi Narasumber & Bimtek - {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">
    <div class="min-h-screen bg-slate-100/70 py-6 sm:py-10 px-3 sm:px-6 font-sans">
        <div class="max-w-3xl mx-auto space-y-4">

            {{-- Breadcrumb Navigasi Kembali --}}
            <div class="px-1">
                <a href="{{ $backToUrl }}"
                   class="group inline-flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-[#13416B] transition-colors">
                    <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center shrink-0 border border-slate-200 shadow-2xs group-hover:border-[#13416B] transition-colors">
                        <i class="fas fa-arrow-left text-xs text-slate-600 group-hover:text-[#13416B]"></i>
                    </div>
                    <span>Kembali ke Pelatihan: <strong>{{ data_get($course, 'course_name') ?? data_get($course, 'name') }}</strong></span>
                </a>
            </div>

            @if ($showSuccessState)
                {{-- STATE SUKSES / TANGGAPAN TERCATAT (GAYA GOOGLE FORM) --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="h-3.5 bg-emerald-600 w-full"></div>
                    <div class="p-6 sm:p-8 space-y-5 text-center">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto text-2xl shadow-xs">
                            <i class="fas fa-check-circle"></i>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
                                Tanggapan Anda Telah Berhasil Direkam!
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600 max-w-xl mx-auto leading-relaxed">
                                Terima kasih atas partisipasi dan penilaian objektif yang Anda berikan. Masukan Anda sangat berharga bagi peningkatan mutu narasumber dan pelaksanaan bimbingan teknis berikutnya.
                            </p>
                        </div>

                        {{-- Info Persyaratan Sertifikat Tuntas --}}
                        <div class="p-4 rounded-lg bg-emerald-50/70 border border-emerald-200 text-left max-w-md mx-auto space-y-1">
                            <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs sm:text-sm">
                                <i class="fas fa-award text-emerald-600"></i>
                                <span>Syarat Kelulusan Terpenuhi</span>
                            </div>
                            <p class="text-[11px] sm:text-xs text-emerald-700 leading-relaxed">
                                Tahap evaluasi narasumber & sarana bimtek telah tuntas. Anda dapat kembali ke halaman kursus untuk memantau status penerbitan e-sertifikat Anda.
                            </p>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a href="{{ $backToUrl }}"
                               class="w-full sm:w-auto px-6 py-2.5 rounded-lg text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-colors shadow-2xs inline-flex items-center justify-center gap-2">
                                <i class="fas fa-arrow-left text-xs"></i>
                                <span>Kembali ke Halaman Kursus</span>
                            </a>
                            <a href="{{ route('user.course.my-course.evaluasi-narasumber', ['slug' => $courseSlug, 'edit' => 1]) }}"
                               class="w-full sm:w-auto px-5 py-2.5 rounded-lg text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors inline-flex items-center justify-center gap-2">
                                <i class="fas fa-pen text-xs"></i>
                                <span>Ubah / Perbarui Jawaban</span>
                            </a>
                        </div>
                    </div>
                </div>

            @else

                {{-- FORM KUESIONER EVALUASI (GAYA GOOGLE FORM RESMI) --}}
                <form action="{{ route('user.course.my-course.submit-evaluasi-narasumber', ['slug' => $courseSlug]) }}" method="POST"
                      class="space-y-4">
                    @csrf

                    {{-- 1. HEADER CARD (GAYA GOOGLE FORM) --}}
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        {{-- Top Accent Strip --}}
                        <div class="h-3.5 bg-[#13416B] w-full"></div>

                        <div class="p-6 sm:p-8 space-y-4">
                            <div class="space-y-1.5">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/10">
                                    Kuesioner Resmi Bimtek
                                </span>
                                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                                    Evaluasi Narasumber & Penyelenggaraan Bimtek
                                </h1>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700">
                                    {{ $activeBimtek?->batch_name ?? (data_get($course, 'course_name') ?? data_get($course, 'name')) }}
                                </p>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                                Mohon berikan penilaian secara objektif mengenai kompetensi narasumber, metode penyampaian, dan fasilitasi teknis selama kegiatan bimbingan teknis berlangsung. Data kuesioner ini digunakan untuk pengendalian mutu penyelenggaraan program.
                            </p>

                            {{-- Badges Kerahasiaan & Petunjuk --}}
                            <div class="flex flex-wrap items-center gap-2.5 pt-1">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200">
                                    <i class="fas fa-user-secret text-[#13416B]"></i> Anonim & Rahasia
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200">
                                    <i class="far fa-clock text-[#13416B]"></i> Estimasi ± 3 Menit
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200">
                                    <i class="fas fa-check-double text-emerald-600"></i> Syarat Klaim e-Sertifikat
                                </span>
                            </div>

                            {{-- Tanda Bintang Wajib (Ciri Khas Google Form) --}}
                            <div class="border-t border-slate-100 pt-3 text-[11px] sm:text-xs text-rose-500 font-semibold">
                                * Menunjukkan pertanyaan yang wajib diisi
                            </div>
                        </div>
                    </div>

                    {{-- 2. KARTU PERTANYAAN (LOOP PER INSTRUMEN) --}}
                    @foreach ($questions as $index => $q)
                        @php
                            $savedAnswer = $existingAnswers->get($q->id);
                            $oldRating = old("answers.{$q->id}", $savedAnswer?->rating);
                            $oldText = old("answers.{$q->id}", $savedAnswer?->text_answer ?? ($savedAnswer ? null : ($existingEvaluation?->feedback ?? '')));
                            $fieldName = "answers[{$q->id}]";
                        @endphp

                        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-xs space-y-4 hover:border-slate-300 transition-colors"
                             id="card-q-{{ $q->id }}">
                            
                            {{-- Header Pertanyaan --}}
                            <div class="space-y-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                                        {{ $loop->iteration }}. {{ $q->question }}
                                        @if ($q->is_required)
                                            <span class="text-rose-500 font-black ml-0.5" title="Wajib Diisi">*</span>
                                        @endif
                                    </h3>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider shrink-0 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $q->section }}
                                    </span>
                                </div>

                                @if ($q->description)
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        {{ $q->description }}
                                    </p>
                                @endif
                            </div>

                            @if ($q->type === 'scale')
                                {{-- FORMAT JAWABAN SKALA LINEAR 1 - 5 (GAYA GOOGLE FORM) --}}
                                <div class="pt-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6 bg-slate-50/70 p-4 sm:p-5 rounded-lg border border-slate-150">
                                        {{-- Label Ujung Min --}}
                                        <div class="text-xs font-semibold text-slate-500 sm:w-28 text-left shrink-0">
                                            {{ $q->min_label ?: 'Sangat Kurang' }}
                                        </div>

                                        {{-- Pilihan Angka Skala (1 - 5) Horizontal --}}
                                        <div class="flex items-center justify-around sm:justify-center gap-3 sm:gap-6 flex-1 py-1">
                                            @for ($star = 1; $star <= ($q->scale_max ?: 5); $star++)
                                                <label class="flex flex-col items-center gap-1.5 cursor-pointer group select-none">
                                                    <span class="text-xs font-bold text-slate-700 group-hover:text-[#13416B]">
                                                        {{ $star }}
                                                    </span>
                                                    <input type="radio"
                                                           name="{{ $fieldName }}"
                                                           value="{{ $star }}"
                                                           @checked($oldRating == $star || (!$savedAnswer && !old("answers.{$q->id}") && $star === 5))
                                                           {{ $q->is_required ? 'required' : '' }}
                                                           class="w-5 h-5 text-[#13416B] border-slate-300 focus:ring-[#13416B] cursor-pointer transition-transform group-hover:scale-110">
                                                    <i class="fas fa-star text-[10px] text-amber-400 opacity-60 group-hover:opacity-100"></i>
                                                </label>
                                            @endfor
                                        </div>

                                        {{-- Label Ujung Max --}}
                                        <div class="text-xs font-semibold text-slate-500 sm:w-28 text-left sm:text-right shrink-0">
                                            {{ $q->max_label ?: 'Sangat Baik' }}
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- FORMAT JAWABAN URAIAN TEKS TERBUKA (GAYA GOOGLE FORM) --}}
                                <div class="pt-1">
                                    <textarea name="{{ $fieldName }}"
                                              rows="4"
                                              placeholder="Tuliskan masukan, evaluasi, atau catatan Anda di sini..."
                                              {{ $q->is_required ? 'required' : '' }}
                                              class="w-full text-xs sm:text-sm rounded-lg border border-slate-300 p-3.5 focus:border-[#13416B] focus:ring-1 focus:ring-[#13416B] outline-none transition-all placeholder:text-slate-400 bg-slate-50/30">{{ $oldText }}</textarea>
                                </div>
                            @endif

                            @error("answers.{$q->id}")
                                <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    {{-- 3. FOOTER AKSI SUBMIT (GAYA GOOGLE FORM) --}}
                    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="submit"
                                    class="w-full sm:w-auto px-7 py-3 rounded-lg text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-sm inline-flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane text-xs"></i>
                                <span>{{ $isSubmitted ? 'Perbarui Tanggapan Evaluasi' : 'Kirim Tanggapan Evaluasi' }}</span>
                            </button>

                            <a href="{{ $backToUrl }}"
                               class="hidden sm:inline-flex px-5 py-3 rounded-lg text-xs sm:text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                                Batal
                            </a>
                        </div>

                        <span class="text-[11px] text-slate-400 text-center sm:text-right">
                            Jangan pernah mengirimkan kata sandi melalui Google Form / Formulir ini.
                        </span>
                    </div>

                </form>

            @endif

        </div>
    </div>
</x-dashboard::layouts.dashboard>
