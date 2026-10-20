<x-dashboard::layouts.dashboard title="Detail Kuesioner Pemanfaatan RTKD">
    @php
        $isReadOnly = in_array($submission->status_verifikasi, ['verified', 'rejected']) || $isOverridden;
        $provinceName = $submission->user->scopeArea?->province?->name ?? $submission->user->name ?? 'Provinsi';
        $periodName = 'Periode Terkait';
        if ($submission->period) {
            $periodName = $submission->period->nama;
            if ($submission->period->tahun && !str_contains((string) $submission->period->nama, (string) $submission->period->tahun)) {
                $periodName .= ' (' . $submission->period->tahun . ')';
            }
        }
    @endphp
    <div class="p-2 sm:p-6 w-full pt-6 sm:pt-8">
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 mb-4 text-xs sm:text-sm" aria-label="Breadcrumb">
            <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.index') }}"
                class="font-medium text-slate-500 dark:text-slate-400 hover:text-[#13416B] dark:hover:text-sky-400 transition-colors">
                Hasil Pemanfaatan RTKD
            </a>
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-semibold text-slate-800 dark:text-slate-200">
                Detail & Verifikasi
            </span>
        </nav>

        <!-- Header Info -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">
                    Detail Kuesioner &mdash; {{ $provinceName }}
                </h1>
                <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    <span>Periode: <strong class="font-semibold text-slate-700 dark:text-slate-200">{{ $periodName }}</strong></span>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <span>Disubmit pada: <strong class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->created_at->format('d M Y, H:i') }} WIB</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.edit-on-behalf', $submission->id) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition">
                    <i class="fas fa-edit text-xs"></i>
                    <span>Ubah Data</span>
                </a>
            </div>
        </div>

        @if ($isOverridden)
            <div class="mb-5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 text-amber-800 dark:text-amber-300 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm">Data Ini Telah Diubah Oleh Pusat</h4>
                    <p class="text-xs mt-0.5">Kuesioner ini adalah versi asli (sebelum diubah). Anda tidak dapat
                        memverifikasi versi ini lagi.</p>
                </div>
            </div>
        @elseif($submission->status_verifikasi === 'verified')
            <div class="mb-5 bg-green-50 dark:bg-emerald-950/40 border border-green-200 dark:border-emerald-800/80 text-green-800 dark:text-emerald-300 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm">Kuesioner Telah Disetujui Sepenuhnya</h4>
                    <p class="text-xs mt-0.5">Dikirim pada: {{ $submission->created_at->format('d F Y, H:i') }}</p>
                </div>
            </div>
        @elseif($submission->status_verifikasi === 'rejected')
            <div class="mb-5 bg-red-50 dark:bg-rose-950/40 border border-red-200 dark:border-rose-800/80 text-red-800 dark:text-rose-300 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm">Kuesioner Dikembalikan ke Provinsi</h4>
                    <p class="text-xs mt-0.5">Membutuhkan revisi berdasarkan catatan verifikator.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.verify', $submission->id) }}" method="POST"
            class="space-y-4" x-data="{
                hasRejection: false,
                checkStatus() {
                    this.hasRejection = !!this.$el.querySelector('input[type=\'radio\'][value=\'rejected\']:checked');
                }
            }" x-init="setTimeout(() => checkStatus(), 100)" @change="checkStatus()">
            @csrf
            @method('PATCH')

            <!-- 1. Kepemilikan RTKD -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                x-data="{ showRtkDoc: true }">
                <div class="bg-white dark:bg-slate-800/90 px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">1</span>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Kepemilikan Dokumen RTK Provinsi</h3>
                    </div>
                    <span
                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-600 text-white tracking-wide shadow-xs">Otomatis</span>
                </div>
                <div class="p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div>
                        <p
                            class="flex items-center gap-2 font-semibold text-base {{ $submission->rtk_document_id ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-rose-400' }}">
                            <i
                                class="fas {{ $submission->rtk_document_id ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $submission->rtk_document_id ? 'Ya, memiliki' : 'Tidak memiliki' }}
                        </p>
                    </div>

                    @if ($submission->rtk_document_id && $submission->rtkDocument)
                        <div class="p-3 bg-slate-50 dark:bg-slate-750 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600 text-sm flex items-center gap-4">
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">Masa Berlaku:</span>
                                <span
                                    class="font-semibold text-gray-800 dark:text-slate-200 ml-1">{{ $submission->rtkDocument->start_date }}
                                    s/d {{ $submission->rtkDocument->end_date }}</span>
                            </div>
                            <div class="h-4 w-px bg-gray-300 dark:bg-slate-600"></div>
                            @if ($submission->rtkDocument->document_url)
                                <button type="button"
                                    @click="showRtkDoc = !showRtkDoc"
                                    class="text-blue-600 dark:text-sky-400 hover:text-blue-800 dark:hover:text-sky-300 hover:underline font-medium inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fas fa-file-pdf text-red-500"></i>
                                    <span x-text="showRtkDoc ? 'Sembunyikan Dokumen' : 'Lihat Dokumen'"></span>
                                    <i class="fas" :class="showRtkDoc ? 'fa-chevron-up text-xs' : 'fa-chevron-down text-xs'"></i>
                                </button>
                                <button type="button"
                                    @click="$dispatch('open-modal', 'modal-preview-rtkd')"
                                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded hover:bg-slate-200/60 dark:hover:bg-slate-600/50 transition cursor-pointer"
                                    title="Perbesar Layar Penuh (Modal)">
                                    <i class="fas fa-expand text-xs"></i>
                                </button>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 italic text-xs">Tidak ada file</span>
                            @endif
                        </div>
                    @endif
                    <input type="hidden" name="verifications[q1_punya_rtkd][status]" value="verified">
                </div>

                <!-- Tampilan Dokumen RTKD Langsung di Show (Tanpa Pindah Tab) -->
                @if ($submission->rtk_document_id && $submission->rtkDocument && $submission->rtkDocument->document_url)
                    <div x-show="showRtkDoc" x-collapse x-cloak class="px-4 pb-4 border-t border-slate-200 dark:border-slate-700 pt-4">
                        <div class="flex items-center justify-between pb-2.5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-pdf text-red-500 text-sm"></i>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $submission->rtkDocument->name ?? 'Dokumen RTK Provinsi' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="$dispatch('open-modal', 'modal-preview-rtkd')"
                                    class="text-xs text-blue-600 dark:text-sky-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-expand text-[10px]"></i> Layar Penuh
                                </button>
                                <a href="{{ $submission->rtkDocument->document_url }}" target="_blank"
                                    class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka Tab Baru
                                </a>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-900 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                            <iframe src="{{ $submission->rtkDocument->document_url }}" class="w-full h-[650px] border-0" frameborder="0"></iframe>
                        </div>
                    </div>

                    <!-- Modal Layar Penuh Dokumen RTKD -->
                    <x-modal name="modal-preview-rtkd" title="Dokumen RTKD: {{ $submission->rtkDocument->name ?? 'Dokumen RTK Provinsi' }}" maxWidth="sm:max-w-6xl">
                        <div class="p-2 sm:p-4">
                            <iframe src="{{ $submission->rtkDocument->document_url }}" class="w-full h-[80vh] rounded-md border border-slate-200 dark:border-slate-700"></iframe>
                        </div>
                        <x-slot:footer>
                            <div class="flex items-center justify-between w-full">
                                <a href="{{ $submission->rtkDocument->document_url }}" target="_blank" class="text-xs text-blue-600 dark:text-sky-400 hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-xs"></i> Buka di Tab Baru
                                </a>
                                <button type="button" @click="$dispatch('close-modal', 'modal-preview-rtkd')" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg transition">
                                    Tutup
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-modal>
                @endif
            </div>

            <!-- Jika TIDAK Punya -->
            @if (!$submission->rtk_document_id)
                @php
                    $fieldKey = 'alasan_tidak_punya';
                    $oldData = $submission->field_verifications[$fieldKey] ?? [];
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                    x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                    <div class="bg-white dark:bg-slate-800/90 px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Alasan Tidak Memiliki RTKD</h3>
                        <span x-show="status === 'verified'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-600 text-white shadow-xs">Disetujui</span>
                        <span x-show="status === 'rejected'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-600 text-white shadow-xs">Revisi</span>
                    </div>
                    <div class="p-4">
                        <ul class="list-disc pl-5 space-y-1 text-gray-700 dark:text-slate-300 text-sm mb-4">
                            @forelse($submission->alasan_tidak_punya ?? [] as $alasan)
                                <li>{{ $alasan['alasan'] }} @if (!empty($alasan['keterangan_lainnya']))
                                        <span class="text-gray-500 dark:text-slate-400">({{ $alasan['keterangan_lainnya'] }})</span>
                                    @endif
                                </li>
                            @empty
                                <li class="text-gray-400 dark:text-slate-500 italic">Tidak ada alasan yang diberikan.</li>
                            @endforelse
                        </ul>

                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 -mx-4 -mb-4 p-4">
                            <p class="text-xs font-semibold mb-2 text-slate-500 dark:text-slate-400 tracking-wide">Status
                                Verifikasi</p>
                            @if ($isReadOnly)
                                @if (($oldData['status'] ?? 'verified') === 'verified')
                                    <div
                                        class="inline-flex items-center gap-2 p-2 bg-green-50 dark:bg-emerald-950/40 text-green-700 dark:text-emerald-300 rounded border border-green-200 dark:border-emerald-800/80 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg> Disetujui
                                    </div>
                                @else
                                    <div class="p-3 bg-red-50 dark:bg-rose-950/40 text-red-800 dark:text-rose-300 rounded border border-red-200 dark:border-rose-800/80">
                                        <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg> Ditolak / Minta Revisi
                                        </div>
                                        <p class="text-sm text-red-600 dark:text-rose-300 bg-white dark:bg-slate-800 p-2 rounded border border-red-100 dark:border-rose-900/60">
                                            {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                    </div>
                                @endif
                            @else
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'verified' ? 'border-green-400 bg-green-50 dark:bg-emerald-950/50 dark:border-emerald-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="verified" x-model="status"
                                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Setujui</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'rejected' ? 'border-red-400 bg-red-50 dark:bg-rose-950/50 dark:border-rose-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="rejected" x-model="status"
                                                class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Minta Revisi</span>
                                        </label>
                                    </div>
                                    <div x-show="status === 'rejected'" x-cloak class="w-full">
                                        <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1.5">
                                            Catatan Revisi:
                                        </label>
                                        <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                            rows="3"
                                            class="w-full min-h-[100px] p-3 text-sm border-gray-300 dark:border-slate-600 rounded-lg shadow-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 transition-colors"
                                            placeholder="Tuliskan catatan detail revisi yang perlu diperbaiki...">{{ $oldData['catatan'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Jika Punya RTKD -->
            @if ($submission->rtk_document_id)
                @php
                    $fieldKey = 'q2_jadi_acuan';
                    $oldData = $submission->field_verifications[$fieldKey] ?? [];
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                    x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                    <div class="bg-white dark:bg-slate-800/90 px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">2</span>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Menjadi Acuan Perencanaan Pembangunan</h3>
                    </div>
                        <span x-show="status === 'verified'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-600 text-white shadow-xs">Disetujui</span>
                        <span x-show="status === 'rejected'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-600 text-white shadow-xs">Revisi</span>
                    </div>
                    <div class="p-4">
                        <p
                            class="flex items-center gap-2 font-semibold text-base {{ $submission->q2_jadi_acuan === 'ya' ? 'text-blue-700 dark:text-blue-400' : 'text-orange-600 dark:text-amber-400' }}">
                            <i
                                class="fas {{ $submission->q2_jadi_acuan === 'ya' ? 'fa-check-circle' : 'fa-minus-circle' }}"></i>
                            {{ $submission->q2_jadi_acuan === 'ya' ? 'Ya, menjadi acuan' : 'Belum menjadi acuan' }}
                        </p>

                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 -mx-4 -mb-4 p-4">
                            <p class="text-xs font-semibold mb-2 text-slate-500 dark:text-slate-400 tracking-wide">Status
                                Verifikasi</p>
                            @if ($isReadOnly)
                                @if (($oldData['status'] ?? 'verified') === 'verified')
                                    <div
                                        class="inline-flex items-center gap-2 p-2 bg-green-50 dark:bg-emerald-950/40 text-green-700 dark:text-emerald-300 rounded border border-green-200 dark:border-emerald-800/80 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg> Disetujui
                                    </div>
                                @else
                                    <div class="p-3 bg-red-50 dark:bg-rose-950/40 text-red-800 dark:text-rose-300 rounded border border-red-200 dark:border-rose-800/80">
                                        <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg> Ditolak / Minta Revisi
                                        </div>
                                        <p class="text-sm text-red-600 dark:text-rose-300 bg-white dark:bg-slate-800 p-2 rounded border border-red-100 dark:border-rose-900/60">
                                            {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                    </div>
                                @endif
                            @else
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'verified' ? 'border-green-400 bg-green-50 dark:bg-emerald-950/50 dark:border-emerald-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="verified" x-model="status"
                                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Setujui</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'rejected' ? 'border-red-400 bg-red-50 dark:bg-rose-950/50 dark:border-rose-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="rejected" x-model="status"
                                                class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Minta Revisi</span>
                                        </label>
                                    </div>
                                    <div x-show="status === 'rejected'" x-cloak class="w-full">
                                        <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1.5">
                                            Catatan Revisi:
                                        </label>
                                        <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                            rows="3"
                                            class="w-full min-h-[100px] p-3 text-sm border-gray-300 dark:border-slate-600 rounded-lg shadow-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 transition-colors"
                                            placeholder="Tuliskan catatan detail revisi yang perlu diperbaiki...">{{ $oldData['catatan'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($submission->q2_jadi_acuan === 'ya')
                    @php
                        $submittedDocs = is_array($submission->dokumen_acuan)
                            ? collect($submission->dokumen_acuan)->pluck('doc_type')->toArray()
                            : [];
                        $komponenByDoc = collect($submission->komponen_acuan ?? [])->groupBy('doc_type');
                        $uploadsByDoc = collect($submission->dokumen_uploads ?? [])->keyBy('doc_type');
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($submittedDocs as $docType)
                            @php
                                $fieldKey = 'dok_' . $docType;
                                $oldData = $submission->field_verifications[$fieldKey] ?? [];
                                $upload = $uploadsByDoc[$docType] ?? null;
                                $kompList = $komponenByDoc[$docType] ?? [];
                            @endphp
                            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col"
                                x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                                <div
                                    class="bg-white dark:bg-slate-800/90 px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                    <h3 class="font-semibold text-indigo-900 dark:text-indigo-300 text-sm">
                                        Dokumen: {{ strtoupper($docType) }}
                                        @if ($docType === 'lainnya')
                                            <span
                                                class="font-normal">({{ collect($submission->dokumen_acuan)->firstWhere('doc_type', 'lainnya')['nama_lainnya'] ?? '-' }})</span>
                                        @endif
                                    </h3>
                                    <span x-show="status === 'verified'"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-600 text-white shadow-xs">Disetujui</span>
                                    <span x-show="status === 'rejected'"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-600 text-white shadow-xs">Revisi</span>
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <!-- File (Bukti Dokumen Asli) -->
                                    <div class="mb-4">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-semibold">Bukti dokumen</p>
                                        @if ($upload)
                                            <a href="{{ Storage::url($upload['file_path']) }}" target="_blank"
                                                class="inline-flex items-center gap-2 p-2 bg-blue-50 dark:bg-slate-700/60 border border-blue-100 dark:border-slate-600 rounded text-blue-700 dark:text-sky-300 hover:bg-blue-100 dark:hover:bg-slate-700 transition text-sm max-w-full truncate">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <span
                                                    class="font-medium truncate">{{ $upload['original_name'] }}</span>
                                            </a>
                                        @else
                                            <span
                                                class="inline-block p-2 bg-gray-50 dark:bg-slate-700/40 border border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 rounded text-xs italic">Tidak
                                                ada file.</span>
                                        @endif
                                    </div>
                                    <!-- Komponen -->
                                    <div class="mb-4 flex-1">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-semibold">Komponen diacu
                                        </p>
                                        <div class="border border-gray-100 dark:border-slate-700 rounded overflow-hidden">
                                            <table class="w-full text-xs text-left">
                                                <thead class="bg-gray-50 dark:bg-slate-700/60">
                                                    <tr>
                                                        <th class="px-3 py-2 border-b border-gray-100 dark:border-slate-700 text-gray-600 dark:text-slate-300">Komponen</th>
                                                        <th class="px-3 py-2 border-b border-gray-100 dark:border-slate-700 text-gray-600 dark:text-slate-300 text-center w-24">Halaman</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                                    @forelse($kompList as $k)
                                                        <tr>
                                                            <td class="px-3 py-2 text-gray-800 dark:text-slate-200">{{ $k['komponen'] }}
                                                                @if (!empty($k['keterangan_lainnya']))
                                                                    <span
                                                                        class="text-gray-500 dark:text-slate-400">({{ $k['keterangan_lainnya'] }})</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2 text-gray-600 dark:text-slate-300 text-center font-medium">
                                                                {{ $k['halaman_acuan'] ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2"
                                                                class="px-3 py-3 text-gray-400 dark:text-slate-500 text-center italic">
                                                                Tidak ada komponen.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Verifikasi -->
                                    <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 -mx-4 -mb-4 p-4">
                                        <p class="text-xs font-semibold mb-2 text-slate-500 dark:text-slate-400 tracking-wide">
                                            Status verifikasi</p>
                                        @if ($isReadOnly)
                                            @if (($oldData['status'] ?? 'verified') === 'verified')
                                                <div
                                                    class="inline-flex items-center gap-2 p-2 bg-green-50 dark:bg-emerald-950/40 text-green-700 dark:text-emerald-300 rounded border border-green-200 dark:border-emerald-800/80 text-sm font-medium w-full">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg> Disetujui
                                                </div>
                                            @else
                                                <div
                                                    class="p-3 bg-red-50 dark:bg-rose-950/40 text-red-800 dark:text-rose-300 rounded border border-red-200 dark:border-rose-800/80 w-full">
                                                    <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg> Ditolak / Minta Revisi
                                                    </div>
                                                    <p
                                                        class="text-xs text-red-600 dark:text-rose-300 bg-white dark:bg-slate-800 p-2 rounded border border-red-100 dark:border-rose-900/60">
                                                        {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="space-y-2.5">
                                                <div class="flex gap-2">
                                                    <label
                                                        class="flex-1 flex items-center justify-center gap-1.5 p-2 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                                        :class="status === 'verified' ?
                                                            'border-green-400 bg-green-50 dark:bg-emerald-950/50 dark:border-emerald-600 shadow-xs' :
                                                            'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                                        <input type="radio"
                                                            name="verifications[{{ $fieldKey }}][status]"
                                                            value="verified" x-model="status"
                                                            class="w-3.5 h-3.5 text-green-600 focus:ring-green-500">
                                                        <span class="text-xs font-medium text-gray-800 dark:text-slate-200">Setujui</span>
                                                    </label>
                                                    <label
                                                        class="flex-1 flex items-center justify-center gap-1.5 p-2 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                                        :class="status === 'rejected' ? 'border-red-400 bg-red-50 dark:bg-rose-950/50 dark:border-rose-600 shadow-xs' :
                                                            'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                                        <input type="radio"
                                                            name="verifications[{{ $fieldKey }}][status]"
                                                            value="rejected" x-model="status"
                                                            class="w-3.5 h-3.5 text-red-600 focus:ring-red-500">
                                                        <span class="text-xs font-medium text-gray-800 dark:text-slate-200">Revisi</span>
                                                    </label>
                                                </div>
                                                <div x-show="status === 'rejected'" x-cloak class="w-full">
                                                    <label class="block text-[11px] font-semibold text-rose-600 dark:text-rose-400 mb-1">
                                                        Catatan Revisi:
                                                    </label>
                                                    <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                                        rows="3"
                                                        class="w-full min-h-[90px] p-2.5 text-xs border-gray-300 dark:border-slate-600 rounded-lg shadow-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 transition-colors"
                                                        placeholder="Tuliskan catatan detail revisi dokumen ini...">{{ $oldData['catatan'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php
                        $fieldKey = 'alasan_belum_acuan';
                        $oldData = $submission->field_verifications[$fieldKey] ?? [];
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                        x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                        <div class="bg-white dark:bg-slate-800/90 px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                            <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Alasan Belum Menjadi Acuan</h3>
                            <span x-show="status === 'verified'"
                                class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-600 text-white shadow-xs">Disetujui</span>
                            <span x-show="status === 'rejected'"
                                class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-600 text-white shadow-xs">Revisi</span>
                        </div>
                        <div class="p-4">
                            <ul class="list-disc pl-5 space-y-1 text-gray-700 dark:text-slate-300 text-sm mb-4">
                                @forelse($submission->alasan_belum_acuan ?? [] as $alasan)
                                    <li>{{ $alasan['alasan'] }} @if (!empty($alasan['keterangan_lainnya']))
                                            <span class="text-gray-500 dark:text-slate-400">({{ $alasan['keterangan_lainnya'] }})</span>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-gray-400 dark:text-slate-500 italic">Tidak ada data alasan.</li>
                                @endforelse
                            </ul>

                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 -mx-4 -mb-4 p-4">
                                <p class="text-xs font-semibold mb-2 text-slate-500 dark:text-slate-400 tracking-wide">Status
                                    verifikasi</p>
                                @if ($isReadOnly)
                                    @if (($oldData['status'] ?? 'verified') === 'verified')
                                        <div
                                            class="inline-flex items-center gap-2 p-2 bg-green-50 dark:bg-emerald-950/40 text-green-700 dark:text-emerald-300 rounded border border-green-200 dark:border-emerald-800/80 text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg> Disetujui
                                        </div>
                                    @else
                                        <div class="p-3 bg-red-50 dark:bg-rose-950/40 text-red-800 dark:text-rose-300 rounded border border-red-200 dark:border-rose-800/80">
                                            <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg> Ditolak / Minta Revisi
                                            </div>
                                            <p class="text-sm text-red-600 dark:text-rose-300 bg-white dark:bg-slate-800 p-2 rounded border border-red-100 dark:border-rose-900/60">
                                                {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                        </div>
                                    @endif
                                @else
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'verified' ? 'border-green-400 bg-green-50 dark:bg-emerald-950/50 dark:border-emerald-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="verified" x-model="status"
                                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Setujui</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center gap-2 p-2 px-3.5 rounded-lg border cursor-pointer hover:bg-white dark:hover:bg-slate-700/50 transition"
                                            :class="status === 'rejected' ? 'border-red-400 bg-red-50 dark:bg-rose-950/50 dark:border-rose-600 shadow-xs' :
                                                'border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="rejected" x-model="status"
                                                class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <span class="text-sm font-medium text-gray-800 dark:text-slate-200">Minta Revisi</span>
                                        </label>
                                    </div>
                                    <div x-show="status === 'rejected'" x-cloak class="w-full">
                                        <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1.5">
                                            Catatan Revisi:
                                        </label>
                                        <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                            rows="3"
                                            class="w-full min-h-[100px] p-3 text-sm border-gray-300 dark:border-slate-600 rounded-lg shadow-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 transition-colors"
                                            placeholder="Tuliskan catatan detail revisi yang perlu diperbaiki...">{{ $oldData['catatan'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Tombol Submit Akhir -->
            @if (!$isReadOnly)
                <div
                    class="bg-slate-800 dark:bg-slate-900 rounded-lg p-4 shadow-sm border border-transparent dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 mt-6 sticky bottom-4 z-50">
                    <div>
                        <h4 class="text-white font-bold text-sm">Simpan Verifikasi</h4>
                        <p class="text-slate-300 dark:text-slate-400 text-xs mt-0.5">Sistem akan otomatis menentukan status
                            Disetujui/Ditolak.</p>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <x-button
                            href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.edit-on-behalf', $submission->id) }}"
                            variant="white" class="w-full sm:w-auto">
                            Ubah
                        </x-button>
                        <x-button type="submit" variant="danger" class="w-full sm:w-auto" x-show="hasRejection">
                            Kembalikan
                        </x-button>
                        <x-button type="submit" variant="success" class="w-full sm:w-auto" x-show="!hasRejection">
                            Terverifikasi
                        </x-button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</x-dashboard::layouts.dashboard>
