<x-dashboard::layouts.dashboard title="Pengajuan Sertifikat PPSDM - Admin Pusat">
    <div class="p-3 sm:p-6 max-w-[1600px] mx-auto space-y-4 sm:space-y-6">

        {{-- Filter Card & Tabel --}}
        <x-dashboard::filter-card
            title="Daftar Pengajuan Sertifikat ke PPSDM"
            :total="$submissions->total() . ' Berkas'"
            :resetUrl="route('admin-pusat.management-course.certificate-submissions.index')">

            <x-slot name="actions">
                <a href="{{ route('admin-pusat.management-course.certificate-submissions.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm sm:text-[15px] font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors shadow-2xs">
                    <i class="fas fa-plus text-xs sm:text-sm"></i>
                    <span>Buat Pengajuan Baru</span>
                </a>
            </x-slot>

            <x-slot name="filter_inputs">
                {{-- Status --}}
                <div class="w-full sm:w-52">
                    <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1">Status Pengajuan</label>
                    <select name="status" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm sm:text-[15px]">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="submitted_to_ppsdm" @selected(request('status') === 'submitted_to_ppsdm')>Diajukan ke PPSDM</option>
                        <option value="numbered" @selected(request('status') === 'numbered')>Nomor Telah Terbit</option>
                        <option value="completed" @selected(request('status') === 'completed')>Sertifikat Diterbitkan</option>
                    </select>
                </div>

                {{-- Cari --}}
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs sm:text-sm font-medium text-slate-500 mb-1">Cari Kode / Bimtek / No. Nota</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari kode usulan, nomor nota dinas, atau nama bimtek..."
                               class="w-full pl-9 pr-3 py-2 rounded-md border border-slate-300 text-sm sm:text-[15px]">
                    </div>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">No.</th>
                            <th class="py-3 px-4 min-w-[210px]">Kode Usulan &amp; No. Nota</th>
                            <th class="py-3 px-4 min-w-[240px]">Bimtek / Kursus</th>
                            <th class="py-3 px-4 min-w-[130px] text-center">Jml Lulusan</th>
                            <th class="py-3 px-4 min-w-[155px]">Tanggal Pengajuan</th>
                            <th class="py-3 px-4 min-w-[165px] text-center">Status</th>
                            <th class="py-3 px-4 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-base">
                        @forelse ($submissions as $index => $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-sm sm:text-base font-semibold text-slate-400">
                                    {{ $submissions->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.show', $item->id) }}"
                                       class="font-bold text-slate-900 hover:text-[#13416B] transition-colors font-mono">
                                        {{ $item->submission_code }}
                                    </a>
                                    @if ($item->submission_letter_number)
                                        <p class="text-sm text-slate-500 mt-0.5">
                                            Nota: <strong class="text-slate-700">{{ $item->submission_letter_number }}</strong>
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800 line-clamp-1">
                                        {{ $item->bimtekEvent?->batch_name ?? '-' }}
                                    </p>
                                    <p class="text-sm text-slate-500 line-clamp-1">
                                        {{ $item->bimtekEvent?->course?->name ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-[#DCEBF8] text-[#13416B] border border-[#BBD6EE] tabular-nums">
                                        {{ $item->total_graduates }} ASN
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800 text-sm tabular-nums">
                                        {{ $item->submitted_at ? $item->submitted_at->translatedFormat('d M Y') : '-' }}
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        Oleh: {{ $item->submitter?->name ?? 'Admin' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @php $badge = $item->status_badge; @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-sm font-bold border {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('admin-pusat.management-course.certificate-submissions.show', $item->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md text-sm font-bold text-[#13416B] bg-slate-100 hover:bg-[#13416B] hover:text-white transition-colors">
                                        <span>Buka</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <div class="w-12 h-12 bg-slate-100 rounded-md flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fas fa-file-circle-check text-xl"></i>
                                        </div>
                                        <p class="text-base font-bold text-slate-700">Belum Ada Pengajuan ke PPSDM</p>
                                        <p class="text-sm text-slate-500">
                                            Peserta yang telah lulus Bimtek Online dapat diajukan secara terpusat ke PPSDM untuk mendapatkan nomor registrasi resmi sertifikat.
                                        </p>
                                        <a href="{{ route('admin-pusat.management-course.certificate-submissions.create') }}"
                                           class="inline-block mt-2 px-4 py-2 text-sm font-bold text-white bg-[#13416B] hover:bg-[#0f3354] rounded-md transition-colors">
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
                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/40">
                    {{ $submissions->links() }}
                </div>
            @endif
        </x-dashboard::filter-card>

    </div>
</x-dashboard::layouts.dashboard>
