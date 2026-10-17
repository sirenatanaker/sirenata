<x-dashboard::layouts.dashboard title="Detail Kuesioner Pemanfaatan RTKD">
    @php
        $isReadOnly = in_array($submission->status_verifikasi, ['verified', 'rejected']) || $isOverridden;
    @endphp
    <div class="p-4 sm:p-6 max-w-6xl mx-auto  pt-6 sm:pt-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <x-breadcrumb :home="route('admin-pusat.dashboard')" :items="[
                ['label' => 'Hasil Pemanfaatan RTKD', 'url' => route('admin-pusat.hasil-pemanfaatan-rtkd.index')],
                ['label' => 'Detail & Verifikasi'],
            ]" />
        </div>

        @if ($isOverridden)
            <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mb-5 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm">Kuesioner Telah Disetujui Sepenuhnya</h4>
                    <p class="text-xs mt-0.5">Dikirim pada: {{ $submission->created_at->format('d F Y, H:i') }}</p>
                </div>
            </div>
        @elseif($submission->status_verifikasi === 'rejected')
            <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z">
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
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-white px-4 sm:px-5 py-3.5 border-b border-slate-200 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">1</span>
                        <h3 class="font-semibold text-slate-800 text-sm">Kepemilikan Dokumen RTK Provinsi</h3>
                    </div>
                    <span
                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-100 text-slate-600 tracking-wide">Otomatis</span>
                </div>
                <div class="p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div>
                        <p
                            class="flex items-center gap-2 font-semibold text-base {{ $submission->rtk_document_id ? 'text-emerald-700' : 'text-red-600' }}">
                            <i
                                class="fas {{ $submission->rtk_document_id ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $submission->rtk_document_id ? 'Ya, memiliki' : 'Tidak memiliki' }}
                        </p>
                    </div>

                    @if ($submission->rtk_document_id && $submission->rtkDocument)
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-sm flex items-center gap-4">
                            <div>
                                <span class="text-gray-500">Masa Berlaku:</span>
                                <span
                                    class="font-semibold text-gray-800 ml-1">{{ $submission->rtkDocument->start_date }}
                                    s/d {{ $submission->rtkDocument->end_date }}</span>
                            </div>
                            <div class="h-4 w-px bg-gray-300"></div>
                            <a href="{{ route('admin-pusat.rtkd.show-province', $submission->rtkDocument->province_code) }}"
                                target="_blank" class="text-blue-600 hover:underline font-medium">Lihat Dokumen
                                &rarr;</a>
                        </div>
                    @endif
                    <input type="hidden" name="verifications[q1_punya_rtkd][status]" value="verified">
                </div>
            </div>

            <!-- Jika TIDAK Punya -->
            @if (!$submission->rtk_document_id)
                @php
                    $fieldKey = 'alasan_tidak_punya';
                    $oldData = $submission->field_verifications[$fieldKey] ?? [];
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                    x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                    <div class="bg-white px-4 sm:px-5 py-3.5 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800 text-sm">Alasan Tidak Memiliki RTKD</h3>
                        <span x-show="status === 'verified'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-green-100 text-green-700">Disetujui</span>
                        <span x-show="status === 'rejected'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-700">Revisi</span>
                    </div>
                    <div class="p-4">
                        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-sm mb-4">
                            @forelse($submission->alasan_tidak_punya ?? [] as $alasan)
                                <li>{{ $alasan['alasan'] }} @if (!empty($alasan['keterangan_lainnya']))
                                        <span class="text-gray-500">({{ $alasan['keterangan_lainnya'] }})</span>
                                    @endif
                                </li>
                            @empty
                                <li class="text-gray-400 italic">Tidak ada alasan yang diberikan.</li>
                            @endforelse
                        </ul>

                        <div class="mt-4 pt-4 border-t border-gray-100 bg-gray-50/50 -mx-4 -mb-4 p-4">
                            <p class="text-xs font-semibold mb-2 text-slate-500 tracking-wide">Status
                                Verifikasi</p>
                            @if ($isReadOnly)
                                @if (($oldData['status'] ?? 'verified') === 'verified')
                                    <div
                                        class="inline-flex items-center gap-2 p-2 bg-green-50 text-green-700 rounded border border-green-200 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg> Disetujui
                                    </div>
                                @else
                                    <div class="p-3 bg-red-50 text-red-800 rounded border border-red-200">
                                        <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg> Ditolak / Minta Revisi
                                        </div>
                                        <p class="text-sm text-red-600 bg-white p-2 rounded border border-red-100">
                                            {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                    </div>
                                @endif
                            @else
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <label
                                        class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                        :class="status === 'verified' ? 'border-green-400 bg-green-50 shadow-sm' :
                                            'border-gray-300 bg-white'">
                                        <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                            value="verified" x-model="status"
                                            class="w-4 h-4 text-green-600 focus:ring-green-500">
                                        <span class="text-sm font-medium text-gray-800">Setujui</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                        :class="status === 'rejected' ? 'border-red-400 bg-red-50 shadow-sm' :
                                            'border-gray-300 bg-white'">
                                        <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                            value="rejected" x-model="status"
                                            class="w-4 h-4 text-red-600 focus:ring-red-500">
                                        <span class="text-sm font-medium text-gray-800">Minta Revisi</span>
                                    </label>
                                    <div x-show="status === 'rejected'" x-cloak class="flex-1 w-full sm:w-auto">
                                        <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                            class="w-full text-sm border-gray-300 rounded shadow-sm focus:ring-red-500 focus:border-red-500" rows="1"
                                            placeholder="Catatan revisi...">{{ $oldData['catatan'] ?? '' }}</textarea>
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
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                    x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                    <div class="bg-white px-4 sm:px-5 py-3.5 border-b border-slate-200 flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">2</span>
                        <h3 class="font-semibold text-slate-800 text-sm">Menjadi Acuan Perencanaan Pembangunan</h3>
                    </div>
                        <span x-show="status === 'verified'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-green-100 text-green-700">Disetujui</span>
                        <span x-show="status === 'rejected'"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-700">Revisi</span>
                    </div>
                    <div class="p-4">
                        <p
                            class="flex items-center gap-2 font-semibold text-base {{ $submission->q2_jadi_acuan === 'ya' ? 'text-blue-700' : 'text-orange-600' }}">
                            <i
                                class="fas {{ $submission->q2_jadi_acuan === 'ya' ? 'fa-check-circle' : 'fa-minus-circle' }}"></i>
                            {{ $submission->q2_jadi_acuan === 'ya' ? 'Ya, menjadi acuan' : 'Belum menjadi acuan' }}
                        </p>

                        <div class="mt-4 pt-4 border-t border-gray-100 bg-gray-50/50 -mx-4 -mb-4 p-4">
                            <p class="text-xs font-semibold mb-2 text-slate-500 tracking-wide">Status
                                Verifikasi</p>
                            @if ($isReadOnly)
                                @if (($oldData['status'] ?? 'verified') === 'verified')
                                    <div
                                        class="inline-flex items-center gap-2 p-2 bg-green-50 text-green-700 rounded border border-green-200 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg> Disetujui
                                    </div>
                                @else
                                    <div class="p-3 bg-red-50 text-red-800 rounded border border-red-200">
                                        <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg> Ditolak / Minta Revisi
                                        </div>
                                        <p class="text-sm text-red-600 bg-white p-2 rounded border border-red-100">
                                            {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                    </div>
                                @endif
                            @else
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <label
                                        class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                        :class="status === 'verified' ? 'border-green-400 bg-green-50 shadow-sm' :
                                            'border-gray-300 bg-white'">
                                        <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                            value="verified" x-model="status"
                                            class="w-4 h-4 text-green-600 focus:ring-green-500">
                                        <span class="text-sm font-medium text-gray-800">Setujui</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                        :class="status === 'rejected' ? 'border-red-400 bg-red-50 shadow-sm' :
                                            'border-gray-300 bg-white'">
                                        <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                            value="rejected" x-model="status"
                                            class="w-4 h-4 text-red-600 focus:ring-red-500">
                                        <span class="text-sm font-medium text-gray-800">Minta Revisi</span>
                                    </label>
                                    <div x-show="status === 'rejected'" x-cloak class="flex-1 w-full sm:w-auto">
                                        <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                            class="w-full text-sm border-gray-300 rounded shadow-sm focus:ring-red-500 focus:border-red-500" rows="1"
                                            placeholder="Catatan revisi...">{{ $oldData['catatan'] ?? '' }}</textarea>
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
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col"
                                x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                                <div
                                    class="bg-white px-4 sm:px-5 py-3.5 border-b border-slate-200 flex justify-between items-center">
                                    <h3 class="font-semibold text-indigo-900 text-sm">
                                        Dokumen: {{ strtoupper($docType) }}
                                        @if ($docType === 'lainnya')
                                            <span
                                                class="font-normal">({{ collect($submission->dokumen_acuan)->firstWhere('doc_type', 'lainnya')['nama_lainnya'] ?? '-' }})</span>
                                        @endif
                                    </h3>
                                    <span x-show="status === 'verified'"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-green-100 text-green-700">Disetujui</span>
                                    <span x-show="status === 'rejected'"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-700">Revisi</span>
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <!-- File -->
                                    <div class="mb-4">
                                        <p class="text-xs text-slate-500 mb-1 font-semibold">Bukti dokumen</p>
                                        @if ($upload)
                                            <a href="{{ Storage::url($upload['file_path']) }}" target="_blank"
                                                class="inline-flex items-center gap-2 p-2 bg-blue-50 border border-blue-100 rounded text-blue-700 hover:bg-blue-100 transition text-sm max-w-full truncate">
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
                                                class="inline-block p-2 bg-gray-50 border border-gray-200 text-gray-500 rounded text-xs italic">Tidak
                                                ada file.</span>
                                        @endif
                                    </div>
                                    <!-- Komponen -->
                                    <div class="mb-4 flex-1">
                                        <p class="text-xs text-slate-500 mb-1 font-semibold">Komponen diacu
                                        </p>
                                        <div class="border border-gray-100 rounded overflow-hidden">
                                            <table class="w-full text-xs text-left">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 border-b text-gray-600">Komponen</th>
                                                        <th class="px-3 py-2 border-b text-gray-600">Halaman</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 bg-white">
                                                    @forelse($kompList as $k)
                                                        <tr>
                                                            <td class="px-3 py-2 text-gray-800">{{ $k['komponen'] }}
                                                                @if (!empty($k['keterangan_lainnya']))
                                                                    <span
                                                                        class="text-gray-500">({{ $k['keterangan_lainnya'] }})</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2 text-gray-600">
                                                                {{ $k['halaman_acuan'] ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2"
                                                                class="px-3 py-3 text-gray-400 text-center italic">
                                                                Tidak ada komponen.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Verifikasi -->
                                    <div class="mt-auto pt-4 border-t border-slate-100 bg-slate-50/50 -mx-4 -mb-4 p-4">
                                        <p class="text-xs font-semibold mb-2 text-slate-500 tracking-wide">
                                            Status verifikasi</p>
                                        @if ($isReadOnly)
                                            @if (($oldData['status'] ?? 'verified') === 'verified')
                                                <div
                                                    class="inline-flex items-center gap-2 p-2 bg-green-50 text-green-700 rounded border border-green-200 text-sm font-medium w-full">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg> Disetujui
                                                </div>
                                            @else
                                                <div
                                                    class="p-3 bg-red-50 text-red-800 rounded border border-red-200 w-full">
                                                    <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg> Ditolak / Minta Revisi
                                                    </div>
                                                    <p
                                                        class="text-xs text-red-600 bg-white p-2 rounded border border-red-100">
                                                        {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex flex-col gap-2">
                                                <div class="flex gap-2">
                                                    <label
                                                        class="flex-1 flex items-center justify-center gap-1.5 p-2 rounded border cursor-pointer hover:bg-white transition"
                                                        :class="status === 'verified' ?
                                                            'border-green-400 bg-green-50 shadow-sm' :
                                                            'border-gray-300 bg-white'">
                                                        <input type="radio"
                                                            name="verifications[{{ $fieldKey }}][status]"
                                                            value="verified" x-model="status"
                                                            class="w-3.5 h-3.5 text-green-600 focus:ring-green-500">
                                                        <span class="text-xs font-medium text-gray-800">Setujui</span>
                                                    </label>
                                                    <label
                                                        class="flex-1 flex items-center justify-center gap-1.5 p-2 rounded border cursor-pointer hover:bg-white transition"
                                                        :class="status === 'rejected' ? 'border-red-400 bg-red-50 shadow-sm' :
                                                            'border-gray-300 bg-white'">
                                                        <input type="radio"
                                                            name="verifications[{{ $fieldKey }}][status]"
                                                            value="rejected" x-model="status"
                                                            class="w-3.5 h-3.5 text-red-600 focus:ring-red-500">
                                                        <span class="text-xs font-medium text-gray-800">Revisi</span>
                                                    </label>
                                                </div>
                                                <div x-show="status === 'rejected'" x-cloak>
                                                    <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                                        class="w-full text-xs border-gray-300 rounded shadow-sm focus:ring-red-500 focus:border-red-500" rows="2"
                                                        placeholder="Catatan revisi...">{{ $oldData['catatan'] ?? '' }}</textarea>
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
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                        x-data="{ status: '{{ $oldData['status'] ?? 'verified' }}' }">
                        <div class="bg-white px-4 sm:px-5 py-3.5 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800 text-sm">Alasan Belum Menjadi Acuan</h3>
                            <span x-show="status === 'verified'"
                                class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-green-100 text-green-700">Disetujui</span>
                            <span x-show="status === 'rejected'"
                                class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-700">Revisi</span>
                        </div>
                        <div class="p-4">
                            <ul class="list-disc pl-5 space-y-1 text-gray-700 text-sm mb-4">
                                @forelse($submission->alasan_belum_acuan ?? [] as $alasan)
                                    <li>{{ $alasan['alasan'] }} @if (!empty($alasan['keterangan_lainnya']))
                                            <span class="text-gray-500">({{ $alasan['keterangan_lainnya'] }})</span>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-gray-400 italic">Tidak ada data alasan.</li>
                                @endforelse
                            </ul>

                            <div class="mt-4 pt-4 border-t border-slate-100 bg-slate-50/50 -mx-4 -mb-4 p-4">
                                <p class="text-xs font-semibold mb-2 text-slate-500 tracking-wide">Status
                                    verifikasi</p>
                                @if ($isReadOnly)
                                    @if (($oldData['status'] ?? 'verified') === 'verified')
                                        <div
                                            class="inline-flex items-center gap-2 p-2 bg-green-50 text-green-700 rounded border border-green-200 text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg> Disetujui
                                        </div>
                                    @else
                                        <div class="p-3 bg-red-50 text-red-800 rounded border border-red-200">
                                            <div class="flex items-center gap-2 mb-1 font-medium text-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg> Ditolak / Minta Revisi
                                            </div>
                                            <p class="text-sm text-red-600 bg-white p-2 rounded border border-red-100">
                                                {{ $oldData['catatan'] ?? 'Tidak ada catatan' }}</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <label
                                            class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                            :class="status === 'verified' ? 'border-green-400 bg-green-50 shadow-sm' :
                                                'border-gray-300 bg-white'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="verified" x-model="status"
                                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                                            <span class="text-sm font-medium text-gray-800">Setujui</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 px-3 rounded border cursor-pointer hover:bg-white transition"
                                            :class="status === 'rejected' ? 'border-red-400 bg-red-50 shadow-sm' :
                                                'border-gray-300 bg-white'">
                                            <input type="radio" name="verifications[{{ $fieldKey }}][status]"
                                                value="rejected" x-model="status"
                                                class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <span class="text-sm font-medium text-gray-800">Minta Revisi</span>
                                        </label>
                                        <div x-show="status === 'rejected'" x-cloak class="flex-1 w-full sm:w-auto">
                                            <textarea name="verifications[{{ $fieldKey }}][catatan]"
                                                class="w-full text-sm border-gray-300 rounded shadow-sm focus:ring-red-500 focus:border-red-500" rows="1"
                                                placeholder="Catatan revisi...">{{ $oldData['catatan'] ?? '' }}</textarea>
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
                    class="bg-slate-800 rounded-lg p-4 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 mt-6 sticky bottom-4 z-50">
                    <div>
                        <h4 class="text-white font-bold text-sm">Simpan Verifikasi</h4>
                        <p class="text-slate-300 text-xs mt-0.5">Sistem akan otomatis menentukan status
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
