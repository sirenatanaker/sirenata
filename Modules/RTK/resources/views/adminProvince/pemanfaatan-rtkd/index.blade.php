<x-dashboard::layouts.dashboard title="Pemanfaatan RTKD">
    <div class="p-2 sm:p-6">
        <x-breadcrumb :items="[['label' => 'Hasil Pemanfaatan RTKD']]" />

        @if ($activePeriod && !$submission && !($activePeriod->tanggal_selesai && now()->gt($activePeriod->tanggal_selesai->endOfDay())))
            <div class="mb-6 relative overflow-hidden bg-white border border-indigo-100 rounded-2xl shadow-sm">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-indigo-50 rounded-full opacity-30"></div>

                <div class="relative p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Survei Pemanfaatan RTKD Periode {{ $activePeriod->tahun }} Telah Dibuka!</h3>
                            <div class="text-sm text-slate-600 mt-1 space-y-1">
                                <p>Silakan lakukan pengisian kuesioner pemanfaatan Rencana Tenaga Kerja Daerah.</p>
                                <p>Pengisian berlangsung dari 
                                    <span class="font-semibold text-indigo-600">{{ $activePeriod->tanggal_mulai ? $activePeriod->tanggal_mulai->format('d M Y') : '-' }}</span> 
                                    sampai dengan 
                                    <span class="font-semibold text-indigo-600">{{ $activePeriod->tanggal_selesai ? $activePeriod->tanggal_selesai->format('d M Y') : '-' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <x-button href="{{ route('admin-province.pemanfaatan-rtkd.create') }}" 
                            variant="primary" size="lg" icon="fas fa-arrow-right">
                            Isi Kuesioner Sekarang
                        </x-button>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-base font-semibold text-slate-800">Status Pemanfaatan Rencana Tenaga Kerja Daerah</h2>
                </div>

            <x-table.table plain>
                <thead>
                    <tr>
                        <x-table.th align="center" class="w-12">No</x-table.th>
                        <x-table.th align="left">Periode</x-table.th>
                        <x-table.th align="center">Tanggal Pengisian</x-table.th>
                        <x-table.th align="center">Status Verifikasi</x-table.th>
                        <x-table.th align="center">Oleh</x-table.th>
                        <x-table.th align="center">Aksi</x-table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @if (!$activePeriod)
                        <tr>
                            <x-table.td colspan="6" align="center" class="py-8">
                                Admin Pusat belum membuka periode survei pemanfaatan RTKD saat ini. Silakan kembali lagi nanti.
                            </x-table.td>
                        </tr>
                    @elseif($activePeriod->tanggal_selesai && now()->gt($activePeriod->tanggal_selesai->endOfDay()) && $submissions->isEmpty())
                        <tr>
                            <x-table.td colspan="6" align="center" class="py-8 text-red-500 font-medium bg-red-50/50">
                                Batas waktu pengisian kuesioner untuk periode ini telah berakhir dan Anda belum mengisi kuesioner.
                            </x-table.td>
                        </tr>
                    @elseif($submissions->isEmpty())
                        <tr>
                            <x-table.td colspan="6" align="center" class="py-8">
                                Belum ada kuesioner yang diisi untuk periode aktif.
                            </x-table.td>
                        </tr>
                    @else
                        @foreach($submissions as $key => $item)
                            <tr class="hover:bg-slate-50 transition">
                                <x-table.td align="center">
                                    {{ $key + 1 }}
                                </x-table.td>
                                <x-table.td align="left">
                                    <div class="text-sm font-semibold text-slate-900">{{ $activePeriod->nama }}</div>
                                    <div class="text-xs text-slate-500">Batas: {{ $activePeriod->tanggal_selesai ? $activePeriod->tanggal_selesai->format('d M Y') : '-' }}</div>
                                </x-table.td>
                                <x-table.td align="center">
                                    {{ $item->created_at->format('d M Y, H:i') }}
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($item->status_verifikasi === 'verified')
                                        <x-badge color="success">Sudah Disetujui</x-badge>
                                    @elseif($item->status_verifikasi === 'rejected')
                                        <x-badge color="danger">Revisi Diperlukan</x-badge>
                                    @else
                                        <x-badge color="indigo">Menunggu Verifikasi</x-badge>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    @if($item->creator && $item->creator->hasRole('admin-pusat'))
                                        <x-badge color="danger">Admin Pusat</x-badge>
                                    @else
                                        <span class="text-xs text-slate-500">Mandiri</span>
                                    @endif
                                </x-table.td>
                                <x-table.td align="center">
                                    @php
                                        $itemHasNotes = false;
                                        if (is_array($item->field_verifications)) {
                                            foreach ($item->field_verifications as $v) {
                                                if (!empty($v['catatan'])) {
                                                    $itemHasNotes = true;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @if($activePeriod && $activePeriod->tanggal_selesai && now()->gt($activePeriod->tanggal_selesai->endOfDay()))
                                        <x-badge color="secondary">Batas Waktu Habis</x-badge>
                                    @else
                                        @if($item->status_verifikasi === 'verified')
                                            <x-button href="{{ route('admin-province.pemanfaatan-rtkd.edit', $item->id) }}" variant="success" size="sm">
                                                Edit / Isi Ulang
                                            </x-button>
                                        @elseif($item->status_verifikasi === 'rejected')
                                            <x-button href="{{ route('admin-province.pemanfaatan-rtkd.edit', $item->id) }}" variant="danger" size="sm">
                                                Revisi / Edit
                                            </x-button>
                                        @else
                                            <x-button href="{{ route('admin-province.pemanfaatan-rtkd.edit', $item->id) }}" variant="warning" size="sm">
                                                Edit / Isi Ulang
                                            </x-button>
                                        @endif
                                    @endif
                                </x-table.td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </x-table.table>
            </div>

            @if($submission)
                <div class="mt-8 space-y-6">
                    <h2 class="text-lg font-bold text-slate-800">Detail Kuesioner yang Diisi</h2>
                    
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">1</span>
                                <h3 class="text-base font-semibold text-slate-800">Kepemilikan Dokumen RTK Provinsi</h3>
                            </div>
                            @if(isset($submission->field_verifications['q1_punya_rtkd']) && $submission->field_verifications['q1_punya_rtkd']['status'] === 'rejected')
                                <x-badge color="danger">Revisi Diminta</x-badge>
                            @elseif(isset($submission->field_verifications['q1_punya_rtkd']) && $submission->field_verifications['q1_punya_rtkd']['status'] === 'verified')
                                <x-badge color="success">Disetujui</x-badge>
                            @endif
                        </div>
                        <div class="p-4">
                            @if(isset($submission->field_verifications['q1_punya_rtkd']) && $submission->field_verifications['q1_punya_rtkd']['status'] === 'rejected')
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['q1_punya_rtkd']['catatan'] }}</div>
                                </div>
                            @endif
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $submission->rtk_document_id ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                    @if($submission->rtk_document_id)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-500 font-medium">Apakah Memiliki RTKD?</p>
                                    <p class="text-lg font-bold {{ $submission->rtk_document_id ? 'text-emerald-700' : 'text-red-700' }} flex items-center gap-2">
                                        <i class="fas {{ $submission->rtk_document_id ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                        {{ $submission->rtk_document_id ? 'Ya, memiliki' : 'Tidak memiliki' }}
                                    </p>
                                    
                                    @if($submission->rtk_document_id)
                                        <div class="mt-4 grid grid-cols-2 gap-4">
                                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                                <p class="text-xs text-slate-500 mb-1">Masa Berlaku Dokumen</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $submission->rtkDocument->start_date }} - {{ $submission->rtkDocument->end_date }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$submission->rtk_document_id)
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex justify-between items-center">
                                <h3 class="text-base font-semibold text-slate-800">Alasan Tidak Memiliki RTKD</h3>
                                @if(isset($submission->field_verifications['alasan_tidak_punya']) && $submission->field_verifications['alasan_tidak_punya']['status'] === 'rejected')
                                    <x-badge color="danger">Revisi Diminta</x-badge>
                                @elseif(isset($submission->field_verifications['alasan_tidak_punya']) && $submission->field_verifications['alasan_tidak_punya']['status'] === 'verified')
                                    <x-badge color="success">Disetujui</x-badge>
                                @endif
                            </div>
                            <div class="p-4">
                                @if(isset($submission->field_verifications['alasan_tidak_punya']) && $submission->field_verifications['alasan_tidak_punya']['status'] === 'rejected')
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['alasan_tidak_punya']['catatan'] }}</div>
                                    </div>
                                @endif
                                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                                    @if(is_array($submission->alasan_tidak_punya))
                                        @foreach($submission->alasan_tidak_punya as $alasan)
                                            <li>
                                                {{ $alasan['alasan'] }}
                                                @if($alasan['alasan'] === 'Lainnya' && !empty($alasan['keterangan_lainnya']))
                                                    (<i>{{ $alasan['keterangan_lainnya'] }}</i>)
                                                @endif
                                            </li>
                                        @endforeach
                                    @else
                                        <li class="text-slate-400 italic">Tidak ada data alasan.</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($submission->rtk_document_id)
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex justify-between items-center">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#13416B] text-white text-xs font-bold shrink-0">2</span>
                                    <h3 class="text-base font-semibold text-slate-800">Menjadi Acuan Perencanaan Pembangunan</h3>
                                </div>
                                @if(isset($submission->field_verifications['q2_jadi_acuan']) && $submission->field_verifications['q2_jadi_acuan']['status'] === 'rejected')
                                    <x-badge color="danger">Revisi Diminta</x-badge>
                                @elseif(isset($submission->field_verifications['q2_jadi_acuan']) && $submission->field_verifications['q2_jadi_acuan']['status'] === 'verified')
                                    <x-badge color="success">Disetujui</x-badge>
                                @endif
                            </div>
                            <div class="p-4">
                                @if(isset($submission->field_verifications['q2_jadi_acuan']) && $submission->field_verifications['q2_jadi_acuan']['status'] === 'rejected')
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['q2_jadi_acuan']['catatan'] }}</div>
                                    </div>
                                @endif
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $submission->q2_jadi_acuan === 'ya' ? 'bg-indigo-100 text-indigo-600' : 'bg-amber-100 text-amber-600' }}">
                                        @if($submission->q2_jadi_acuan === 'ya')
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 font-medium">Apakah telah dijadikan acuan?</p>
                                        <p class="text-lg font-bold {{ $submission->q2_jadi_acuan === 'ya' ? 'text-indigo-700' : 'text-amber-700' }} flex items-center gap-2">
                                            <i class="fas {{ $submission->q2_jadi_acuan === 'ya' ? 'fa-check-circle' : 'fa-minus-circle' }}"></i>
                                            {{ $submission->q2_jadi_acuan === 'ya' ? 'Ya, menjadi acuan' : 'Belum menjadi acuan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($submission->q2_jadi_acuan === 'ya')
                            @php
                                $docLabels = ['rpjmd' => 'RPJMD', 'renstra' => 'RENSTRA Disnaker', 'lainnya' => 'Dokumen Lainnya'];
                                $docDescriptions = ['rpjmd' => 'Rencana Pembangunan Jangka Menengah Daerah', 'renstra' => 'Rencana Strategis Perangkat Daerah', 'lainnya' => ''];
                                $submittedDocs = is_array($submission->dokumen_acuan) ? collect($submission->dokumen_acuan)->pluck('doc_type')->toArray() : [];
                                $komponenByDoc = [];
                                if (is_array($submission->komponen_acuan)) {
                                    foreach ($submission->komponen_acuan as $k) {
                                        $komponenByDoc[$k['doc_type']][] = $k;
                                    }
                                }
                                $uploadsByDoc = [];
                                if (is_array($submission->dokumen_uploads)) {
                                    foreach ($submission->dokumen_uploads as $u) {
                                        $uploadsByDoc[$u['doc_type']] = $u;
                                    }
                                }
                            @endphp

                            @foreach($submittedDocs as $docType)
                                @php
                                    $fieldKey = 'dok_' . $docType;
                                    $docLabel = $docLabels[$docType] ?? strtoupper($docType);
                                    $docDesc = $docDescriptions[$docType] ?? '';
                                    $docAcuanEntry = collect($submission->dokumen_acuan)->firstWhere('doc_type', $docType);
                                    $kompList = $komponenByDoc[$docType] ?? [];
                                    $uploadEntry = $uploadsByDoc[$docType] ?? null;
                                @endphp
                                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex justify-between items-center">
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-800">
                                                {{ $docLabel }}
                                                @if($docDesc)
                                                    <span class="text-sm font-normal text-slate-500">({{ $docDesc }})</span>
                                                @endif
                                            </h3>
                                            @if($docType === 'lainnya' && !empty($docAcuanEntry['nama_lainnya']))
                                                <p class="text-sm text-slate-500 mt-0.5">Nama Dokumen: <strong>{{ $docAcuanEntry['nama_lainnya'] }}</strong></p>
                                            @endif
                                        </div>
                                        @if(isset($submission->field_verifications[$fieldKey]) && $submission->field_verifications[$fieldKey]['status'] === 'rejected')
                                            <x-badge color="danger">Revisi Diminta</x-badge>
                                        @elseif(isset($submission->field_verifications[$fieldKey]) && $submission->field_verifications[$fieldKey]['status'] === 'verified')
                                            <x-badge color="success">Disetujui</x-badge>
                                        @endif
                                    </div>
                                    <div class="p-4 space-y-4">
                                        @if(isset($submission->field_verifications[$fieldKey]) && $submission->field_verifications[$fieldKey]['status'] === 'rejected')
                                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications[$fieldKey]['catatan'] }}</div>
                                            </div>
                                        @endif

                                        @if($uploadEntry)
                                            <div>
                                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">📎 File Dokumen</p>
                                                <a href="{{ Storage::url($uploadEntry['file_path']) }}" target="_blank" class="inline-flex items-center gap-3 p-3 border border-indigo-200 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition group">
                                                    <div class="w-10 h-10 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-indigo-800 group-hover:text-indigo-900">{{ $uploadEntry['original_name'] }}</p>
                                                        <p class="text-xs text-indigo-600">Klik untuk membuka</p>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif

                                        @if(count($kompList) > 0)
                                            <div>
                                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Komponen RTKD yang Diacu</p>
                                                <table class="min-w-full text-sm border border-slate-200 rounded-lg overflow-hidden">
                                                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left font-medium">Komponen</th>
                                                            <th class="px-4 py-2 text-left font-medium">Halaman</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 bg-white">
                                                        @foreach($kompList as $komp)
                                                            <tr>
                                                                <td class="px-4 py-2">
                                                                    {{ $komp['komponen'] }}
                                                                    @if($komp['komponen'] === 'Lainnya' && !empty($komp['keterangan_lainnya']))
                                                                        (<i>{{ $komp['keterangan_lainnya'] }}</i>)
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-slate-500">{{ $komp['halaman_acuan'] ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        @else
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex justify-between items-center">
                                    <h3 class="text-base font-semibold text-slate-800">Alasan Belum Menjadi Acuan</h3>
                                    @if(isset($submission->field_verifications['alasan_belum_acuan']) && $submission->field_verifications['alasan_belum_acuan']['status'] === 'rejected')
                                        <x-badge color="danger">Revisi Diminta</x-badge>
                                    @elseif(isset($submission->field_verifications['alasan_belum_acuan']) && $submission->field_verifications['alasan_belum_acuan']['status'] === 'verified')
                                        <x-badge color="success">Disetujui</x-badge>
                                    @endif
                                </div>
                                <div class="p-4">
                                    @if(isset($submission->field_verifications['alasan_belum_acuan']) && $submission->field_verifications['alasan_belum_acuan']['status'] === 'rejected')
                                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['alasan_belum_acuan']['catatan'] }}</div>
                                        </div>
                                    @endif
                                    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                                        @if(is_array($submission->alasan_belum_acuan))
                                            @foreach($submission->alasan_belum_acuan as $alasan)
                                                <li>
                                                    {{ $alasan['alasan'] }}
                                                    @if($alasan['alasan'] === 'Lainnya' && !empty($alasan['keterangan_lainnya']))
                                                        (<i>{{ $alasan['keterangan_lainnya'] }}</i>)
                                                    @endif
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="text-slate-400 italic">Tidak ada data alasan.</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-dashboard::layouts.dashboard>
