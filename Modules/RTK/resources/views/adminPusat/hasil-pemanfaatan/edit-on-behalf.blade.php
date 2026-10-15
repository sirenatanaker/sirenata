<x-dashboard::layouts.dashboard title="Ubah Data Kuesioner (Admin Pusat)">
    <div class="p-2 sm:p-6  pt-6 sm:pt-8" x-data="kuesionerForm()">
        <div class="mb-6">
        <x-breadcrumb :home="route('admin-pusat.dashboard')" :items="[
            ['label' => 'Hasil Pemanfaatan RTKD', 'url' => route('admin-pusat.hasil-pemanfaatan-rtkd.index')],
            ['label' => 'Ubah Data (On Behalf)']
        ]" />
        </div>

        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 flex items-start gap-3">
            <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h4 class="font-bold text-sm">Mode "Ubah Sendiri" (Edit on Behalf)</h4>
                <p class="text-sm mt-0.5">Anda sedang mengubah data kuesioner milik <strong>{{ $targetUser->scopeArea?->province?->name ?? $targetUser->name ?? 'Provinsi' }}</strong>. Mengklik <strong>Simpan Perubahan</strong> akan mengganti data lama dan mereset status verifikasi menjadi pending.</p>
            </div>
        </div>

        <form x-ref="mainForm" @submit.prevent="validateAndSubmit" action="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.store-on-behalf', $submission->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold">1</span>
                        <h3 class="text-base font-semibold text-slate-800">Status Kepemilikan RTK Provinsi</h3>
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-200 text-slate-700 uppercase tracking-wider">Otomatis</span>
                    </div>
                    @if(isset($submission->field_verifications['q1_punya_rtkd']) && $submission->field_verifications['q1_punya_rtkd']['status'] === 'rejected')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Revisi Diminta</span>
                    @endif
                </div>
                <div class="p-4">
                    @if(isset($submission->field_verifications['q1_punya_rtkd']) && $submission->field_verifications['q1_punya_rtkd']['status'] === 'rejected')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <span class="font-semibold block">Catatan Verifikator Pusat:</span>
                                {{ $submission->field_verifications['q1_punya_rtkd']['catatan'] }}
                            </div>
                        </div>
                    @endif
                    @if($latestRtk)
                        <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-sm text-emerald-800">
                                <strong>Memiliki RTKD Aktif:</strong> {{ $latestRtk->name }} ({{ $latestRtk->start_date }} s/d {{ $latestRtk->end_date }})
                            </span>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <div>
                                <p class="text-sm text-amber-800"><strong>Sistem tidak mendeteksi dokumen RTK Provinsi Aktif.</strong></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <template x-if="!hasRtkd">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-500 text-white text-xs font-bold">!</span>
                        <h3 class="text-base font-semibold text-slate-800">Alasan Tidak Memiliki RTKD</h3>
                    </div>
                        @if(isset($submission->field_verifications['alasan_tidak_punya']) && $submission->field_verifications['alasan_tidak_punya']['status'] === 'rejected')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Revisi Diminta</span>
                        @endif
                    </div>
                    <div class="p-4 space-y-3">
                        @if(isset($submission->field_verifications['alasan_tidak_punya']) && $submission->field_verifications['alasan_tidak_punya']['status'] === 'rejected')
                            <div class="mb-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <span class="font-semibold block">Catatan Verifikator Pusat:</span>
                                    {{ $submission->field_verifications['alasan_tidak_punya']['catatan'] }}
                                </div>
                            </div>
                        @endif
                        <p class="text-sm font-medium text-slate-700 mb-2">Pilih alasan utama mengapa provinsi Anda belum memiliki RTKD yang aktif (bisa pilih lebih dari satu):</p>
                        
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="alasan_tidak_punya[]" value="Tidak dianggarkan dalam APBD" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                x-model="alasanTidakPunya">
                            <span class="text-sm text-slate-700">Tidak dianggarkan dalam APBD</span>
                        </label>
                        
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="alasan_tidak_punya[]" value="Tidak lagi memiliki Tugas & Fungsi Penyusun PTK (Re-organisasi)" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                x-model="alasanTidakPunya">
                            <span class="text-sm text-slate-700">Tidak lagi memiliki Tugas & Fungsi Penyusun PTK (Re-organisasi)</span>
                        </label>

                        <div class="border border-slate-200 rounded-lg p-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="alasan_tidak_punya[]" value="Lainnya" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                    x-model="alasanTidakPunya">
                                <span class="text-sm text-slate-700">Lainnya</span>
                            </label>
                            
                            <div x-show="alasanTidakPunya.includes('Lainnya')" class="mt-3 ml-7">
                                <input type="text" name="alasan_tidak_punya_lainnya" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Sebutkan alasan lainnya..."
                                    :required="alasanTidakPunya.includes('Lainnya')"
                                    value="{{ collect($submission->alasan_tidak_punya)->firstWhere('alasan', 'Lainnya')['keterangan_lainnya'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="hasRtkd">
                <div class="space-y-4">
                    
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold">2</span>
                                <h3 class="text-base font-semibold text-slate-800">Pemanfaatan Dokumen</h3>
                            </div>
                            @if(isset($submission->field_verifications['q2_jadi_acuan']) && $submission->field_verifications['q2_jadi_acuan']['status'] === 'rejected')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Revisi Diminta</span>
                            @endif
                        </div>
                        <div class="p-4">
                            @if(isset($submission->field_verifications['q2_jadi_acuan']) && $submission->field_verifications['q2_jadi_acuan']['status'] === 'rejected')
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <span class="font-semibold block">Catatan Verifikator Pusat:</span>
                                        {{ $submission->field_verifications['q2_jadi_acuan']['catatan'] }}
                                    </div>
                                </div>
                            @endif
                            <p class="text-sm font-medium text-slate-700 mb-3">Apakah RTKD Anda telah dijadikan acuan/diintegrasikan dalam Dokumen Perencanaan Pembangunan di Daerah?</p>
                            
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="q2_jadi_acuan" value="ya" class="text-indigo-600 focus:ring-indigo-600" x-model="jadiAcuan" required>
                                    <span class="text-sm text-slate-800 font-medium">Ya, telah menjadi acuan</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="q2_jadi_acuan" value="tidak" class="text-indigo-600 focus:ring-indigo-600" x-model="jadiAcuan" required>
                                    <span class="text-sm text-slate-800 font-medium">Belum / Tidak</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <template x-if="jadiAcuan === 'tidak'">
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-amber-200">
                            <div class="px-4 py-3 border-b border-amber-200 bg-amber-50 flex justify-between items-center">
                                <h3 class="text-base font-semibold text-amber-900">Alasan Belum Menjadi Acuan</h3>
                                @if(isset($submission->field_verifications['alasan_belum_acuan']) && $submission->field_verifications['alasan_belum_acuan']['status'] === 'rejected')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Revisi Diminta</span>
                                @endif
                            </div>
                            <div class="p-4 space-y-3">
                                @if(isset($submission->field_verifications['alasan_belum_acuan']) && $submission->field_verifications['alasan_belum_acuan']['status'] === 'rejected')
                                    <div class="mb-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div>
                                            <span class="font-semibold block">Catatan Verifikator Pusat:</span>
                                            {{ $submission->field_verifications['alasan_belum_acuan']['catatan'] }}
                                        </div>
                                    </div>
                                @endif
                                <p class="text-sm text-slate-600 mb-2">Mengapa dokumen RTKD belum dijadikan acuan? <span class="text-red-500">* (Pilih minimal satu)</span></p>
                                
                                <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                    <input type="checkbox" name="alasan_belum_acuan[]" value="Koordinasi antar OPD yang membidangi ketenagakerjaan" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                        x-model="alasanBelumAcuan">
                                    <span class="text-sm text-slate-700">Kurangnya koordinasi antar OPD terkait</span>
                                </label>

                                <div class="border border-slate-200 rounded-lg p-3">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="alasan_belum_acuan[]" value="Lainnya" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                            x-model="alasanBelumAcuan">
                                        <span class="text-sm text-slate-700">Lainnya</span>
                                    </label>
                                    
                                    <div x-show="alasanBelumAcuan.includes('Lainnya')" class="mt-3 ml-7">
                                        <input type="text" name="alasan_belum_acuan_lainnya" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                                            placeholder="Sebutkan alasan lainnya..."
                                            :required="alasanBelumAcuan.includes('Lainnya')"
                                            value="{{ collect($submission->alasan_belum_acuan)->firstWhere('alasan', 'Lainnya')['keterangan_lainnya'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="jadiAcuan === 'ya'">
                        <div class="space-y-4 py-2">
                            <p class="text-sm text-slate-600 mb-2 font-medium">Pilih dokumen perencanaan yang menjadikan RTKD sebagai acuan, unggah buktinya, lalu pilih komponennya. <span class="text-red-500">* (Pilih minimal satu dokumen)</span></p>
                            
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <label class="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200 cursor-pointer hover:bg-slate-100 transition">
                                    <input type="checkbox" name="dokumen_acuan[]" value="rpjmd" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" x-model="dokumenAcuan">
                                    <span class="font-semibold text-slate-800">RPJMD <span class="font-normal text-slate-500 text-sm">(Rencana Pembangunan Jangka Menengah Daerah)</span></span>
                                </label>
                                
                                <div class="p-4 space-y-5" x-show="dokumenAcuan.includes('rpjmd')">
                                    @if(isset($submission->field_verifications['dok_rpjmd']) && $submission->field_verifications['dok_rpjmd']['status'] === 'rejected')
                                        <div class="mb-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['dok_rpjmd']['catatan'] }}</div>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">📎 Upload Dokumen RPJMD <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3">
                                            <input type="file" name="upload_rpjmd" accept=".pdf,.doc,.docx" 
                                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md"
                                                :required="dokumenAcuan.includes('rpjmd') && !{{ collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'rpjmd') ? 'true' : 'false' }}">
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Format: PDF/Word. Maks 5MB. @if(collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'rpjmd')) Biarkan kosong jika tidak ingin mengubah file lama. @endif</p>
                                        @if($rpjmdUpload = collect($submission->dokumen_uploads ?? [])->firstWhere('doc_type', 'rpjmd'))
                                            <div class="mt-2 p-2 bg-indigo-50 rounded text-sm flex items-center justify-between">
                                                <span class="text-indigo-700 font-medium truncate">File saat ini: {{ $rpjmdUpload['original_name'] }}</span>
                                                <a href="{{ Storage::url($rpjmdUpload['file_path']) }}" target="_blank" class="text-indigo-600 hover:underline shrink-0 ml-2">Lihat</a>
                                            </div>
                                        @endif
                                    </div>
                                    <hr class="border-slate-100">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Komponen RTKD yang Diacu dalam RPJMD <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-for="komp in availableKomponen" :key="komp">
                                                <div class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition">
                                                    <label class="flex items-start gap-3 cursor-pointer">
                                                        <input type="checkbox" :value="komp" x-model="komponenRpjmd" :name="'komponen_rpjmd[]'" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                        <span class="text-sm font-medium text-slate-700" x-text="komp"></span>
                                                    </label>
                                                    <div class="mt-2 ml-7" x-show="komponenRpjmd.includes(komp)">
                                                        <template x-if="komp === 'Lainnya'">
                                                            <input type="text" name="lainnya_rpjmd" 
                                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500 mb-2" 
                                                                placeholder="Nama komponen..." x-model="lainnyaRpjmd"
                                                                :required="komponenRpjmd.includes('Lainnya')">
                                                        </template>
                                                        <input type="text" :name="'halaman_rpjmd[' + komp + ']'" 
                                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500" 
                                                            placeholder="Halaman (misal: hal 12-14) *" 
                                                            x-model="halamanRpjmd[komp]"
                                                            :required="komponenRpjmd.includes(komp)">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <label class="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200 cursor-pointer hover:bg-slate-100 transition">
                                    <input type="checkbox" name="dokumen_acuan[]" value="renstra" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" x-model="dokumenAcuan">
                                    <span class="font-semibold text-slate-800">RENSTRA Disnaker <span class="font-normal text-slate-500 text-sm">(Rencana Strategis Perangkat Daerah)</span></span>
                                </label>
                                
                                <div class="p-4 space-y-5" x-show="dokumenAcuan.includes('renstra')">
                                    @if(isset($submission->field_verifications['dok_renstra']) && $submission->field_verifications['dok_renstra']['status'] === 'rejected')
                                        <div class="mb-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['dok_renstra']['catatan'] }}</div>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">📎 Upload Dokumen Renstra <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3">
                                            <input type="file" name="upload_renstra" accept=".pdf,.doc,.docx" 
                                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md"
                                                :required="dokumenAcuan.includes('renstra') && !{{ collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'renstra') ? 'true' : 'false' }}">
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Format: PDF/Word. Maks 5MB. @if(collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'renstra')) Biarkan kosong jika tidak ingin mengubah file lama. @endif</p>
                                        @if($renstraUpload = collect($submission->dokumen_uploads ?? [])->firstWhere('doc_type', 'renstra'))
                                            <div class="mt-2 p-2 bg-indigo-50 rounded text-sm flex items-center justify-between">
                                                <span class="text-indigo-700 font-medium truncate">File saat ini: {{ $renstraUpload['original_name'] }}</span>
                                                <a href="{{ Storage::url($renstraUpload['file_path']) }}" target="_blank" class="text-indigo-600 hover:underline shrink-0 ml-2">Lihat</a>
                                            </div>
                                        @endif
                                    </div>
                                    <hr class="border-slate-100">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Komponen RTKD yang Diacu dalam Renstra <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-for="komp in availableKomponen" :key="komp">
                                                <div class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition">
                                                    <label class="flex items-start gap-3 cursor-pointer">
                                                        <input type="checkbox" :value="komp" x-model="komponenRenstra" :name="'komponen_renstra[]'" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                        <span class="text-sm font-medium text-slate-700" x-text="komp"></span>
                                                    </label>
                                                    <div class="mt-2 ml-7" x-show="komponenRenstra.includes(komp)">
                                                        <template x-if="komp === 'Lainnya'">
                                                            <input type="text" name="lainnya_renstra" 
                                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500 mb-2" 
                                                                placeholder="Nama komponen..." x-model="lainnyaRenstra"
                                                                :required="komponenRenstra.includes('Lainnya')">
                                                        </template>
                                                        <input type="text" :name="'halaman_renstra[' + komp + ']'" 
                                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500" 
                                                            placeholder="Halaman (misal: hal 12-14) *" 
                                                            x-model="halamanRenstra[komp]"
                                                            :required="komponenRenstra.includes(komp)">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <label class="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200 cursor-pointer hover:bg-slate-100 transition">
                                    <input type="checkbox" name="dokumen_acuan[]" value="lainnya" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" x-model="dokumenAcuan">
                                    <span class="font-semibold text-slate-800">Dokumen Lainnya</span>
                                </label>
                                
                                <div class="p-4 space-y-5" x-show="dokumenAcuan.includes('lainnya')">
                                    @if(isset($submission->field_verifications['dok_lainnya']) && $submission->field_verifications['dok_lainnya']['status'] === 'rejected')
                                        <div class="mb-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-start gap-2">
                                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div><span class="font-semibold block">Catatan Verifikator Pusat:</span>{{ $submission->field_verifications['dok_lainnya']['catatan'] }}</div>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Dokumen Lainnya <span class="text-red-500">*</span></label>
                                        <input type="text" name="dokumen_acuan_lainnya" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                                            placeholder="Sebutkan nama dokumen lainnya (misal: RKPD)..." 
                                            :required="dokumenAcuan.includes('lainnya')"
                                            value="{{ collect($submission->dokumen_acuan)->firstWhere('doc_type', 'lainnya')['nama_lainnya'] ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">📎 Upload Dokumen <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3">
                                            <input type="file" name="upload_lainnya" accept=".pdf,.doc,.docx" 
                                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md"
                                                :required="dokumenAcuan.includes('lainnya') && !{{ collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'lainnya') ? 'true' : 'false' }}">
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Format: PDF/Word. Maks 5MB. @if(collect($submission->dokumen_uploads ?? [])->contains('doc_type', 'lainnya')) Biarkan kosong jika tidak ingin mengubah file lama. @endif</p>
                                        @if($lainnyaUpload = collect($submission->dokumen_uploads ?? [])->firstWhere('doc_type', 'lainnya'))
                                            <div class="mt-2 p-2 bg-indigo-50 rounded text-sm flex items-center justify-between">
                                                <span class="text-indigo-700 font-medium truncate">File saat ini: {{ $lainnyaUpload['original_name'] }}</span>
                                                <a href="{{ Storage::url($lainnyaUpload['file_path']) }}" target="_blank" class="text-indigo-600 hover:underline shrink-0 ml-2">Lihat</a>
                                            </div>
                                        @endif
                                    </div>
                                    <hr class="border-slate-100">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Komponen RTKD yang Diacu <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-for="komp in availableKomponen" :key="komp">
                                                <div class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition">
                                                    <label class="flex items-start gap-3 cursor-pointer">
                                                        <input type="checkbox" :value="komp" x-model="komponenLainnya" :name="'komponen_lainnya[]'" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                        <span class="text-sm font-medium text-slate-700" x-text="komp"></span>
                                                    </label>
                                                    <div class="mt-2 ml-7" x-show="komponenLainnya.includes(komp)">
                                                        <template x-if="komp === 'Lainnya'">
                                                            <input type="text" name="lainnya_lainnya" 
                                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500 mb-2" 
                                                                placeholder="Nama komponen..." x-model="lainnyaLainnya"
                                                                :required="komponenLainnya.includes('Lainnya')">
                                                        </template>
                                                        <input type="text" :name="'halaman_lainnya[' + komp + ']'" 
                                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:border-indigo-500" 
                                                            placeholder="Halaman (misal: hal 12-14) *" 
                                                            x-model="halamanLainnya[komp]"
                                                            :required="komponenLainnya.includes(komp)">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </template>

            <div class="flex justify-end gap-3 pt-4">
                <x-button href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.show', $submission->id) }}" variant="white">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kuesionerForm', () => {
                const hasRtkdDefault = @json($latestRtk ? true : false);
                const q2Default = @json($submission->q2_jadi_acuan ?? '');
                
                const oldAlasanTidakPunya = @json(is_array($submission->alasan_tidak_punya) ? collect($submission->alasan_tidak_punya)->pluck('alasan') : []);
                const oldAlasanBelumAcuan = @json(is_array($submission->alasan_belum_acuan) ? collect($submission->alasan_belum_acuan)->pluck('alasan') : []);
                const oldDokumenAcuan = @json(is_array($submission->dokumen_acuan) ? collect($submission->dokumen_acuan)->pluck('doc_type') : []);
                
                const oldKomponenAcuan = @json(is_array($submission->komponen_acuan) ? $submission->komponen_acuan : []);

                let kRpjmd = [];
                let hRpjmd = {};
                let lRpjmd = '';

                let kRenstra = [];
                let hRenstra = {};
                let lRenstra = '';

                let kLainnya = [];
                let hLainnya = {};
                let lLainnya = '';

                oldKomponenAcuan.forEach(k => {
                    if (k.doc_type === 'rpjmd') {
                        kRpjmd.push(k.komponen);
                        hRpjmd[k.komponen] = k.halaman_acuan || '';
                        if (k.komponen === 'Lainnya') lRpjmd = k.keterangan_lainnya || '';
                    } else if (k.doc_type === 'renstra') {
                        kRenstra.push(k.komponen);
                        hRenstra[k.komponen] = k.halaman_acuan || '';
                        if (k.komponen === 'Lainnya') lRenstra = k.keterangan_lainnya || '';
                    } else if (k.doc_type === 'lainnya') {
                        kLainnya.push(k.komponen);
                        hLainnya[k.komponen] = k.halaman_acuan || '';
                        if (k.komponen === 'Lainnya') lLainnya = k.keterangan_lainnya || '';
                    }
                });

                return {
                    hasRtkd: hasRtkdDefault,
                    jadiAcuan: q2Default,
                    alasanTidakPunya: oldAlasanTidakPunya,
                    alasanBelumAcuan: oldAlasanBelumAcuan,
                    dokumenAcuan: oldDokumenAcuan,
                    
                    availableKomponen: [
                        'Angka Pengangguran',
                        'Jumlah Pekerja Formal & Informal',
                        'Penduduk Bekerja Menurut Sektor',
                        'Angkatan Kerja Muda',
                        'Pekerja Muda',
                        'Rekomendasi Kebijakan',
                        'Lainnya'
                    ],

                    komponenRpjmd: kRpjmd,
                    halamanRpjmd: hRpjmd,
                    lainnyaRpjmd: lRpjmd,

                    komponenRenstra: kRenstra,
                    halamanRenstra: hRenstra,
                    lainnyaRenstra: lRenstra,

                    komponenLainnya: kLainnya,
                    halamanLainnya: hLainnya,
                    lainnyaLainnya: lLainnya,

                    validateAndSubmit() {
                        if (!this.$refs.mainForm.checkValidity()) {
                            this.$refs.mainForm.reportValidity();
                            return;
                        }
                        
                        if (!this.hasRtkd) {
                            if (this.alasanTidakPunya.length === 0) {
                                alert('Pilih minimal satu alasan tidak memiliki RTKD.');
                                return;
                            }
                        } 
                        else {
                            if (!this.jadiAcuan) {
                                alert('Pilih apakah RTKD sudah menjadi acuan atau belum.');
                                return;
                            }

                            if (this.jadiAcuan === 'tidak') {
                                if (this.alasanBelumAcuan.length === 0) {
                                    alert('Pilih minimal satu alasan belum menjadi acuan.');
                                    return;
                                }
                            } 
                            else if (this.jadiAcuan === 'ya') {
                                if (this.dokumenAcuan.length === 0) {
                                    alert('Pilih minimal satu dokumen perencanaan (RPJMD/RENSTRA/Lainnya).');
                                    return;
                                }

                                if (this.dokumenAcuan.includes('rpjmd') && this.komponenRpjmd.length === 0) {
                                    alert('Pilih minimal satu komponen untuk dokumen RPJMD.');
                                    return;
                                }
                                if (this.dokumenAcuan.includes('renstra') && this.komponenRenstra.length === 0) {
                                    alert('Pilih minimal satu komponen untuk dokumen RENSTRA.');
                                    return;
                                }
                                if (this.dokumenAcuan.includes('lainnya') && this.komponenLainnya.length === 0) {
                                    alert('Pilih minimal satu komponen untuk dokumen lainnya.');
                                    return;
                                }
                            }
                        }

                        this.$refs.mainForm.submit();
                    }
                }
            })
        })
    </script>
    @endpush
</x-dashboard::layouts.dashboard>
