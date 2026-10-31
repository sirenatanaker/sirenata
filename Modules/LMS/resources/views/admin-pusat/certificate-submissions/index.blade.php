<x-dashboard::layouts.dashboard title="Pengajuan Sertifikat PPSDM - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- 4 Indikator Tata Kelola Sertifikasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Berkas Pengajuan</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                        {{ number_format($totalSubmissions, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Nota dinas ke PPSDM</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-[#13416B]/10 text-[#13416B] flex items-center justify-center shrink-0">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Diajukan ke PPSDM</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1 tabular-nums">
                        {{ number_format($pendingSubmissions, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Menunggu nomor registrasi</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-hourglass-half text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sertifikat Selesai</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1 tabular-nums">
                        {{ number_format($completedSubmissions, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Sudah diterbitkan resmi</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-check-double text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between shadow-2xs">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total ASN Bersertifikat</p>
                    <p class="text-2xl font-extrabold text-[#547996] mt-1 tabular-nums">
                        {{ number_format($totalCertifiedASN, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Memiliki nomor resmi PPSDM</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-[#547996]/10 text-[#547996] flex items-center justify-center shrink-0">
                    <i class="fas fa-certificate text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Filter Card & Tabel --}}
        <x-dashboard::filter-card
            title="Daftar Pengajuan Sertifikat ke PPSDM"
            :total="$submissions->total() . ' Berkas'"
            :resetUrl="route('admin-pusat.management-course.certificate-submissions.index')">

            <x-slot name="actions">
                <a href="{{ route('admin-pusat.management-course.certificate-submissions.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] hover:bg-[#0e2c48] rounded-lg transition-colors shadow-2xs">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Buat Pengajuan Baru</span>
                </a>
            </x-slot>

            <x-slot name="filter_inputs">
                {{-- Status --}}
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Pengajuan</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="submitted_to_ppsdm" @selected(request('status') === 'submitted_to_ppsdm')>Diajukan ke PPSDM</option>
                        <option value="numbered" @selected(request('status') === 'numbered')>Nomor Telah Terbit</option>
                        <option value="completed" @selected(request('status') === 'completed')>Sertifikat Diterbitkan</option>
                    </select>
                </div>

                {{-- Cari --}}
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Cari Kode / Bimtek / No. Nota</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari kode usulan, nomor nota dinas, atau nama bimtek..."
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm">
                    </div>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-3.5 w-12 text-center">No.</th>
                            <th class="py-3 px-3.5 min-w-[200px]">Kode Usulan & No. Nota</th>
                            <th class="py-3 px-3.5 min-w-[220px]">Bimtek / Kursus</th>
                            <th class="py-3 px-3.5 min-w-[120px] text-center">Jml Lulusan</th>
                            <th class="py-3 px-3.5 min-w-[140px]">Tanggal Pengajuan</th>
                            <th class="py-3 px-3.5 min-w-[150px] text-center">Status</th>
                            <th class="py-3 px-3.5 w-24 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($submissions as $index => $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3.5 text-center text-xs font-semibold text-slate-400">
                                    {{ $submissions->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.show', $item->id) }}"
                                       class="font-bold text-slate-900 hover:text-[#13416B] transition-colors font-mono">
                                        {{ $item->submission_code }}
                                    </a>
                                    @if ($item->submission_letter_number)
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Nota: <strong class="text-slate-700">{{ $item->submission_letter_number }}</strong>
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-800 line-clamp-1">
                                        {{ $item->bimtekEvent?->batch_name ?? '-' }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 line-clamp-1">
                                        {{ $item->bimtekEvent?->course?->name ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-[#13416B]/10 text-[#13416B] tabular-nums">
                                        {{ $item->total_graduates }} ASN
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <p class="font-bold text-slate-800 text-xs tabular-nums">
                                        {{ $item->submitted_at ? $item->submitted_at->format('d M Y') : '-' }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        Oleh: {{ $item->submitter?->name ?? 'Admin' }}
                                    </p>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    @php $badge = $item->status_badge; @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.show', $item->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-[#13416B] bg-[#13416B]/10 hover:bg-[#13416B]/20 transition-colors">
                                        <span>Buka</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fas fa-file-circle-check text-lg"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">Belum Ada Pengajuan ke PPSDM</p>
                                        <p class="text-xs text-slate-500">
                                            Peserta yang telah lulus Bimtek Online dapat diajukan secara terpusat ke PPSDM untuk mendapatkan nomor registrasi resmi sertifikat.
                                        </p>
                                        <a href="{{ route('admin-pusat.management-course.certificate-submissions.create') }}"
                                           class="inline-block mt-2 px-3.5 py-1.5 text-xs font-bold text-white bg-[#13416B] rounded-lg">
                                            Buat Pengajuan Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($submissions->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $submissions->links() }}
                </div>
            @endif
        </x-dashboard::filter-card>

    </div>
</x-dashboard::layouts.dashboard>
