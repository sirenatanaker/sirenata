<x-dashboard::layouts.dashboard title="Detail Proyek - E-Learning">
    @push('styles')
        @include('project::partials.create-styles')
    @endpush

    @php
        $isDaerah = in_array($project->type, ['Provinsi', 'Kab/Kota', 'Kabupaten/Kota', 'provinsi', 'kab_kota']);
        $projectScope = request('type', $isDaerah ? 'daerah' : 'pusat');
        $breadcrumbLabel = $projectScope === 'daerah' ? 'Proyek Daerah' : 'Proyek Pusat';

        $statusColor = match ($project->status) {
            'Menunggu Tim' => 'amber-solid',
            'On Progress' => 'blue-solid',
            'Completed' => 'green-solid',
            'Kedaluwarsa' => 'red-solid',
            default => 'slate-solid',
        };

        $teamMembersArr = is_array($project->team_members)
            ? $project->team_members
            : json_decode($project->team_members ?? '[]', true) ?? [];

        // Hitung durasi presisi dalam satuan Hari
        $durationDays =
            $project->start_date && $project->end_date
                ? \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->end_date))
                : $project->duration ?? 0;

        // 1. Gunakan method dari Model Project (Tetap mempertahankan string UUID)
        $prerequisiteIds = $project->prerequisiteCourseIds();

        // 2. Deteksi namespace Model Course secara dinamis
        $courseModelClass = match (true) {
            class_exists('Modules\LMS\Models\Course') => 'Modules\LMS\Models\Course',
            class_exists('Modules\Course\Models\Course') => 'Modules\Course\Models\Course',
            class_exists('Modules\Lms\Models\Course') => 'Modules\Lms\Models\Course',
            class_exists('App\Models\Course') => 'App\Models\Course',
            default => null,
        };

        // 3. Query HANYA ID kursus yang terdaftar
        $prerequisiteCourses = ($courseModelClass && !empty($prerequisiteIds))
            ? $courseModelClass::whereIn('id', $prerequisiteIds)->get()
            : collect();
    @endphp

    <div class="p-2 sm:p-6 space-y-5">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => $breadcrumbLabel, 'url' => route($routePrefix . 'index', ['type' => $projectScope])],
            ['label' => 'Detail Proyek'],
        ]" />

        <!-- Header Card -->
        <div class="bg-white rounded-md border border-slate-200 shadow-sm p-5 sm:p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <x-badge :color="$statusColor" :text="$project->status === 'Completed' ? 'Selesai' : ($project->status ?? 'Draft')" class="uppercase tracking-wider" />
                        
                        <!-- Indikator Batas Waktu Menunggu Tim -->
                        @if ($project->status === 'Menunggu Tim')
                            <span class="inline-flex items-center text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200">
                                <i class="fas fa-clock mr-1.5"></i> Batas Penentuan Tim: Sisa {{ $project->sisa_hari_penentuan_tim }} Hari
                            </span>
                        @elseif ($project->status === 'Kedaluwarsa')
                            <span class="inline-flex items-center text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-1 rounded-md border border-red-200">
                                <i class="fas fa-exclamation-circle mr-1.5"></i> Penentuan Tim Kedaluwarsa
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $project->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Dibuat oleh: <span class="font-semibold text-slate-700">{{ $project->creator->name ?? 'Sistem' }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @if (str_contains($routePrefix, 'admin-pusat'))
                        @can('project-edit')
                            <x-button :href="route($routePrefix . 'prerequisite', $project->id)" variant="primary" class="rounded-md">
                                <i class="fas fa-edit mr-2 text-xs"></i> 
                                {{ $project->status === 'Kedaluwarsa' ? 'Buka Kembali Proyek' : 'Edit Prasyarat & Status' }}
                            </x-button>
                        @endcan
                    @endif
                </div>
            </div>

            <!-- Ringkasan Statistik / KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                <div class="bg-slate-50 border border-slate-200/80 rounded-md p-4">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Progress</span>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-lg font-bold text-slate-800">{{ $project->progress ?? 0 }}%</span>
                        <div class="flex-1 bg-slate-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-md p-4">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Durasi Proyek</span>
                    <span class="block text-lg font-bold text-slate-800 mt-1">{{ $durationDays }} Hari</span>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-md p-4">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Mulai</span>
                    <span class="block text-sm font-semibold text-slate-800 mt-1">
                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-md p-4">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Selesai</span>
                    <span class="block text-sm font-semibold text-slate-800 mt-1">
                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Konten Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Informasi Wilayah, SK & Prasyarat (Left - 1 Col) -->
            <div class="space-y-5">
                <div class="bg-white border border-slate-200 rounded-md p-5 shadow-sm space-y-5">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Informasi Wilayah & Aturan
                    </h2>

                    <div>
                        <span class="block text-xs text-slate-400 font-semibold mb-1.5">Asal Wilayah</span>
                        @php $creatorScope = $project->creator?->scopeArea; @endphp
                        @if ($creatorScope?->regency)
                            <div class="space-y-1.5">
                                <p class="text-sm font-bold text-slate-800 leading-snug">{{ $creatorScope->regency->name }}</p>
                                @if ($creatorScope->province?->name)
                                    <span class="inline-block text-[11px] font-semibold text-slate-600 bg-slate-100 border border-slate-200/80 px-2 py-0.5 rounded-md">
                                        {{ $creatorScope->province->name }}
                                    </span>
                                @endif
                            </div>
                        @elseif ($creatorScope?->province)
                            <span class="inline-block text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-md">
                                {{ $creatorScope->province->name }}
                            </span>
                        @else
                            <span class="inline-block text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                                Pusat / Nasional
                            </span>
                        @endif
                    </div>

                    <div>
                        <span class="block text-xs text-slate-400 font-semibold mb-1">Dokumen Surat Keputusan (SK)</span>
                        @if ($project->sk_document)
                            <a href="{{ asset('storage/' . $project->sk_document) }}" target="_blank"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                <i class="fas fa-file-pdf text-red-500 text-sm"></i> Unduh / Lihat SK
                            </a>
                        @else
                            <span class="text-xs text-red-500 italic">Belum ada dokumen SK diunggah</span>
                        @endif
                    </div>

                    <!-- List Prasyarat Kursus LMS (Badge Grid dengan Max Height & Scroll) -->
                    <div class="pt-4 border-t border-slate-100">
                        <span class="block text-xs text-slate-400 font-semibold mb-2.5">Prasyarat Kursus LMS</span>
                        @if ($project->is_prerequisite_active)
                            @if ($prerequisiteCourses->count() > 0)
                                <div class="max-h-48 overflow-y-auto pr-1">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($prerequisiteCourses as $course)
                                            <span class="inline-block text-xs font-medium text-slate-700 bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-full hover:bg-slate-200/70 transition">
                                                {{ $course->name ?? $course->title ?? 'Kursus #' . $course->id }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-100 font-medium inline-block">
                                    Prasyarat Aktif (Belum Pilih Kursus)
                                </span>
                            @endif
                        @else
                            <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md font-medium inline-block">
                                Tidak Ada Prasyarat Kursus
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Struktur Tim Kerja (Right - 2 Cols) -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-slate-200 rounded-md p-5 shadow-sm">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4">
                        Tim Kerja Proyek
                    </h2>

                    <!-- Ketua Tim -->
                    <div class="mb-5">
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ketua Tim</span>
                        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-md">
                            <span class="block text-sm font-bold text-slate-900">{{ $project->leader->name ?? 'Belum Ditentukan' }}</span>
                            <div class="mt-1 space-y-0.5">
                                <span class="text-xs text-slate-500"><span class="font-semibold text-slate-600">Email:</span> {{ $project->leader->email ?? '-' }}</span>
                                <span class="block text-xs text-slate-500"><span class="font-semibold text-slate-600">Instansi/Lembaga:</span> {{ $project->leader->profile->instansi ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Anggota Tim -->
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                            Anggota Tim ({{ count($teamMembersArr) }})
                        </span>
                        @if (count($teamMembersArr) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach (App\Models\User::with('profile')->whereIn('id', $teamMembersArr)->get() as $member)
                                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-md">
                                        <span class="block text-xs font-semibold text-slate-800">{{ $member->name }}</span>
                                        <div class="mt-1 space-y-0.5">
                                            <span class="text-[11px] text-slate-400"><span class="font-semibold text-slate-500">Email:</span> {{ $member->email }}</span>
                                            <span class="block text-[11px] text-slate-400"><span class="font-semibold text-slate-500">Instansi/Lembaga:</span> {{ $member->profile->instansi ?? '-' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center border border-dashed border-slate-200 rounded-md">
                                <p class="text-xs text-slate-400 italic">Belum ada anggota tim yang didaftarkan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard::layouts.dashboard>