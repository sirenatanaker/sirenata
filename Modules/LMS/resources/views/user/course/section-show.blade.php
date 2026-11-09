<x-dashboard::layouts.dashboard
    title="{{ ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date)) ? 'Hari ' : 'Modul ' }}{{ $sectionIndex + 1 }}: {{ data_get($section, 'name') }} | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    @php
        $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
        $courseSlug = $slug ?? data_get($course, 'slug');
        $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
        $rawSectionName = data_get($section, 'name', '');
        $cleanSectionName = preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $rawSectionName);
        $isBimtekEvent = (bool) ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date));
        $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
        $adminDriveLink = $activeBimtek?->submission_drive_link ?? null;
        $defaultPrefix = $isBimtekEvent ? 'Hari ' : 'Modul ';
        $sectionName = !empty(trim($cleanSectionName)) ? trim($cleanSectionName) : ($rawSectionName ?: $defaultPrefix . ($sectionIndex + 1));
        $sectionContents = data_get($section, 'section_contents', []) ?: data_get($section, 'contents', []);
        $totalCount = count($sectionContents);
        $completedCount = data_get($section, 'completed_count', 0);
        $postTestBab = data_get($section, 'post_test_bab');
        $isPostTestBabCompleted = data_get($section, 'is_post_test_bab_completed', false);
        $isContentCompleted = data_get($section, 'is_content_completed', false);
        $isSectionCompleted = data_get($section, 'is_section_completed', false);
        $canReviewAll = data_get($course, 'can_review_all', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);

        $dayDate = null;
        try {
            if ($activeBimtek && $activeBimtek->start_date) {
                $dayDate = \Carbon\Carbon::parse($activeBimtek->start_date)->addDays($sectionIndex);
            }
        } catch (\Exception $e) {}
    @endphp

    <div class="px-0 py-2 sm:p-6 max-w-full mx-auto">

        {{-- Breadcrumb Navigation (Adaptif: Bimtek vs Modul Mandiri) --}}
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-y-1 gap-x-1.5 sm:gap-x-2">
                <li>
                    <a href="{{ route('user.course.my-course') }}"
                        class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors whitespace-nowrap">
                        Kursus Saya
                    </a>
                </li>
                <li>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-[10px]"></i>
                        <a href="{{ route('user.course.my-course.detail', $courseSlug) }}"
                            class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors truncate max-w-[160px] sm:max-w-xs"
                            title="{{ $courseName }}">
                            {{ $courseName }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <i class="fas fa-chevron-right text-slate-400 text-[9px] sm:text-[10px]"></i>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug truncate max-w-[200px] sm:max-w-none">
                            {{ $isBimtekEvent ? 'Hari ke-' . ($sectionIndex + 1) : 'Modul ' . ($sectionIndex + 1) }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Card Agenda Utama (Membungkus Header dan Seluruh Bagian Agenda) --}}
        <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 lg:p-7 transition-all">
            
            {{-- Header Agenda Modul / Hari --}}
            <div class="flex items-start gap-3.5 sm:gap-4 min-w-0 pb-5 sm:pb-6 border-b border-slate-100">
                {{-- Badge Hari / Modul seperti Kalender --}}
                <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-md bg-[#13416B] text-white border border-[#13416B] border-r-2 border-b-2 border-r-[#092035] border-b-[#092035] shadow-2xs shrink-0">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-300 leading-none">
                        {{ $isBimtekEvent ? 'HARI' : 'MODUL' }}
                    </span>
                    <span class="text-base sm:text-lg font-black leading-tight mt-0.5">{{ $sectionIndex + 1 }}</span>
                </div>

                <div class="space-y-1 min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-[#13416B]">
                            {{ $isBimtekEvent ? 'Agenda Hari ke-' . ($sectionIndex + 1) : 'Modul Pembelajaran ' . ($sectionIndex + 1) }}
                        </span>
                        @if ($isBimtekEvent && $activeBimtek && $activeBimtek->batch_name)
                            <span class="text-slate-300">•</span>
                            <div class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-bold text-slate-600">
                                <i class="fas fa-satellite-dish text-[10px] text-[#13416B]"></i>
                                <span>{{ $activeBimtek->batch_name }}</span>
                            </div>
                        @endif
                    </div>
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-slate-800 leading-tight break-words">
                        {{ $sectionName }}
                    </h1>
                    @if (data_get($section, 'description'))
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pt-1">
                            {{ data_get($section, 'description') }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Seluruh Card Bagian Agenda di dalam Card Utama --}}
            <div class="mt-5 sm:mt-6 space-y-5 sm:space-y-6">

                {{-- 1. CARD PEMBELAJARAN MANDIRI --}}
                <div class="rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200/80">
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight">Pembelajaran Mandiri</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Pelajari materi secara berurutan dan tandai selesai</p>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-md shadow-2xs">
                            {{ $totalCount }} Materi
                        </span>
                    </div>

                    <div class="space-y-3">
                        @php $isPreviousContentDone = true; @endphp
                        @forelse ($sectionContents as $content)
                            @php
                                $contentId = data_get($content, 'id');
                                $contentName = data_get($content, 'name', 'Materi Tanpa Judul');
                                $isContentItemCompleted = data_get($content, 'is_completed', false);
                                $isContentLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isPreviousContentDone && !$isContentItemCompleted));
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs transition-all gap-3 sm:gap-4">
                                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                    <p class="font-bold text-slate-800 text-sm leading-snug break-words line-clamp-2" title="{{ $contentName }}">
                                        {{ $contentName }}
                                    </p>
                                    @if ($isContentItemCompleted)
                                        <i class="fas fa-check-circle text-emerald-600 text-xs sm:text-sm shrink-0" title="Selesai Dipelajari"></i>
                                    @endif
                                </div>

                                <div class="shrink-0 self-start sm:self-center w-full sm:w-auto">
                                    @if ($isContentLocked)
                                        <button type="button" disabled
                                            title="Pelajari materi sebelumnya terlebih dahulu"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-semibold cursor-not-allowed select-none">
                                            <span>Terkunci</span>
                                        </button>
                                    @else
                                        <a href="{{ route('user.course.content.show', ['slug' => $courseSlug, 'content' => $contentId]) }}"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 {{ $isContentItemCompleted ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200' : 'bg-[#13416B] text-white hover:bg-[#0f3354] shadow-2xs' }} rounded-md text-xs font-bold transition-all text-center">
                                            <span>{{ $isContentItemCompleted ? 'Pelajari Lagi' : 'Mulai Belajar' }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @php
                                $isPreviousContentDone = $isContentItemCompleted;
                            @endphp
                        @empty
                            <div class="p-6 text-center text-slate-400 bg-white rounded-md border border-slate-200/60">
                                <i class="fas fa-folder-open mb-2 text-slate-300 text-2xl block"></i>
                                <p class="text-sm font-medium">Materi pembelajaran sedang disiapkan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 2. CARD SESI LIVE ZOOM (DILENGKAPI PRESENSI GOOGLE DRIVE & FOTO SCREENSHOT) --}}
                @if ($hasZoom)
                    @php
                        $isZoomAttendanceLocked = false;
                        $zoomScheduleFormatted = $activeBimtek->zoom_schedule_formatted ?? null;
                        if ($activeBimtek->zoom_scheduled_at && now()->lessThan($activeBimtek->zoom_scheduled_at)) {
                            $isZoomAttendanceLocked = true;
                        }
                    @endphp
                    <div x-data="{ hasJoinedZoom: {{ $hasJoinedZoom ? 'true' : 'false' }}, showZoomModal: false }"
                        @keydown.escape.window="showZoomModal = false"
                        class="rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:p-5 lg:p-6 shadow-2xs transition-all">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-3 flex-1 min-w-0">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-md bg-[#1E62D0] text-white flex items-center justify-center shrink-0 text-xl sm:text-2xl shadow-xs">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div class="space-y-1 min-w-0">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">Sesi Tatap Muka Online</h3>
                                        @if ($isZoomAttended)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] sm:text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                                <i class="fas fa-check-circle"></i> Presensi Kehadiran Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] sm:text-xs font-bold bg-amber-500 text-white shadow-xs">
                                                <i class="fas fa-clock"></i> Belum Konfirmasi Hadir
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Ikuti sesi tatap muka online bersama narasumber. Klik tautan di bawah untuk bergabung ke ruang pertemuan daring dan lakukan konfirmasi kehadiran menggunakan kode presensi.
                                </p>

                                {{-- Info Sesi Tatap Muka --}}
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 pt-1">
                                    @if ($activeBimtek->zoom_scheduled_at)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-calendar-alt text-[#1E62D0]"></i>
                                            {{ $zoomScheduleFormatted ?? $activeBimtek->zoom_scheduled_at->translatedFormat('d F Y, H:i') . ' WIB' }}
                                        </span>
                                    @endif
                                    @if ($activeBimtek->zoom_meeting_id)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-hashtag text-slate-400"></i> ID: {{ $activeBimtek->zoom_meeting_id }}
                                        </span>
                                    @endif
                                    @if ($activeBimtek->zoom_passcode)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            <i class="fas fa-key text-slate-400"></i> Passcode: {{ $activeBimtek->zoom_passcode }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Tombol Aksi Tatap Muka --}}
                            <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-2 self-start md:self-center w-full md:w-auto">
                                <a href="{{ route('user.course.my-course.join-zoom', $courseSlug) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    @click="hasJoinedZoom = true"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md font-bold text-xs sm:text-sm bg-[#13416B] text-white hover:bg-[#0f3354] transition-all shadow-sm">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                    <span>Masuk Tatap Muka</span>
                                </a>

                                @if (!$isZoomAttended)
                                    <button type="button"
                                        @click="showZoomModal = true"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md font-bold text-xs border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition-all shadow-2xs">
                                        <i class="fas fa-clipboard-check text-xs text-[#13416B]"></i>
                                        <span>Konfirmasi Presensi</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Bukti Pendukung Presensi (Google Drive Penyelenggara & Upload Screenshot) --}}
                        <div class="mt-5 pt-4 border-t border-slate-200/80 space-y-3.5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-camera text-slate-600 text-xs sm:text-sm"></i>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">Bukti Pendukung Presensi (Google Drive Penyelenggara & Foto Screenshot)</h4>
                                </div>
                                @if ($zoomProof && ($zoomProof->file_path || $zoomProof->notes))
                                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                        <i class="fas fa-check-circle text-emerald-600"></i> Bukti Presensi Tersimpan
                                    </span>
                                @endif
                            </div>

                            <p class="text-[11px] sm:text-xs text-slate-500">
                                Penyelenggara menyediakan folder Google Drive untuk pengumpulan bukti kehadiran tatap muka. Anda dapat mengunggah tangkapan layar (screenshot) sesi Zoom langsung ke folder Drive panitia tersebut atau mengunggah berkas foto di bawah.
                            </p>

                            {{-- Banner Folder Google Drive Presensi dari Penyelenggara --}}
                            @if ($adminDriveLink)
                                <div class="p-3.5 rounded-md bg-white border border-blue-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-md bg-blue-50 text-[#0B5CFF] border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                            <i class="fab fa-google-drive text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">Folder Google Drive Presensi Penyelenggara</p>
                                            <p class="text-[11px] text-slate-500">Kumpulkan bukti foto/screenshot kehadiran sesi Zoom Anda ke folder Drive panitia berikut.</p>
                                        </div>
                                    </div>

                                    <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 transition-all shadow-2xs shrink-0 whitespace-nowrap">
                                        <i class="fab fa-google-drive"></i>
                                        <span>Buka Drive Presensi</span>
                                        <i class="fas fa-external-link-alt text-[10px] ml-0.5"></i>
                                    </a>
                                </div>
                            @endif

                            {{-- Form & Status Bukti Presensi --}}
                            @if ($zoomProof && ($zoomProof->file_path || $zoomProof->notes) && !$errors->any())
                                <div x-data="{ isEditingPhoto: false }" class="space-y-2">
                                    <div x-show="!isEditingPhoto" class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-md bg-white border border-emerald-200/90 shadow-2xs">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                                                <i class="fas fa-check-circle text-sm"></i>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-xs font-bold text-slate-800">Bukti Presensi Telah Dikonfirmasi</p>
                                                <p class="text-[10px] text-slate-500">
                                                    {{ $zoomProof->submitted_at ? $zoomProof->submitted_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Tersimpan' }}
                                                    @if ($zoomProof->notes)
                                                        • <span class="italic text-slate-600">"{{ $zoomProof->notes }}"</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($zoomProof->file_path)
                                                <a href="{{ $zoomProof->file_url }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-bold text-[#13416B] bg-slate-50 hover:bg-slate-100 border border-slate-200 shadow-2xs">
                                                    <i class="fas fa-eye text-[11px]"></i>
                                                    <span>Lihat Foto</span>
                                                </a>
                                            @endif
                                            <button type="button" @click="isEditingPhoto = true"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200">
                                                <i class="fas fa-redo text-[11px]"></i>
                                                <span>Perbarui Bukti</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="isEditingPhoto" class="pt-1">
                                        <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                            method="POST"
                                            enctype="multipart/form-data"
                                            class="space-y-2.5 p-3 rounded-md bg-white border border-slate-200">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                        Unggah Foto / Screenshot (Opsional)
                                                    </label>
                                                    <input type="file"
                                                        name="proof_file"
                                                        accept=".jpg,.jpeg,.png,.webp"
                                                        class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                        Catatan Presensi / Nama Berkas di Drive Panitia
                                                    </label>
                                                    <input type="text"
                                                        name="notes"
                                                        value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                                        placeholder="Contoh: Bukti hadir diunggah ke Drive panitia nama Budi_Presensi.png"
                                                        class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="button" @click="isEditingPhoto = false"
                                                    class="px-3 py-1.5 rounded text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200">
                                                    Batal
                                                </button>
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs">
                                                    <i class="fas fa-save text-[11px]"></i>
                                                    <span>Simpan Bukti</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="space-y-2.5 p-3 rounded-md bg-white border border-slate-200">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Unggah Foto / Screenshot (Opsional)
                                            </label>
                                            <input type="file"
                                                name="proof_file"
                                                accept=".jpg,.jpeg,.png,.webp"
                                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Catatan Presensi / Nama Berkas di Drive Panitia
                                            </label>
                                            <input type="text"
                                                name="notes"
                                                value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                                placeholder="Contoh: Bukti hadir diunggah ke Drive panitia nama Budi_Presensi.png"
                                                class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                        </div>
                                    </div>
                                    <div class="flex justify-end pt-1">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs">
                                            <i class="fas fa-check text-[11px]"></i>
                                            <span>Konfirmasi Bukti Presensi</span>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- Modal Konfirmasi Presensi Kode Zoom --}}
                        <div x-show="showZoomModal"
                            x-cloak
                            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            
                            <div @click.away="showZoomModal = false"
                                class="bg-white rounded-lg shadow-xl max-w-md w-full p-5 sm:p-6 space-y-4 border border-slate-200 relative">
                                
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-[#13416B] text-white flex items-center justify-center text-lg shrink-0">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-slate-800 leading-tight">Konfirmasi Presensi Zoom</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Masukkan kode presensi yang dibagikan pemateri</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showZoomModal = false" class="text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <form action="{{ route('user.course.my-course.attend-zoom', $courseSlug) }}" method="POST" class="space-y-4 pt-1">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                                            Kode Presensi
                                        </label>
                                        <input type="text"
                                            name="attendance_code"
                                            required
                                            placeholder="Contoh: HADIR-SPBE-01"
                                            class="w-full px-3.5 py-2.5 rounded-md border border-slate-300 text-sm font-mono uppercase tracking-wider text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]" />
                                        <p class="text-[11px] text-slate-500 mt-1">
                                            *Pastikan Anda telah menekan tombol "Masuk Tatap Muka" sebelum konfirmasi.
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                        <button type="button" @click="showZoomModal = false"
                                            class="px-4 py-2 rounded-md text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-5 py-2 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-sm">
                                            Verifikasi Hadir
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 3. CARD EVALUASI HARIAN / MODUL (DESAIN SEPERTI EVALUASI AKHIR & FLEKSIBEL) --}}
                @php
                    $postTestQuestionsCount = $postTestBab ? data_get($postTestBab, 'questions', collect())->count() : 0;
                    $hasPostTestQuestions = $postTestQuestionsCount > 0;
                    $isPostTestBabLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isContentCompleted && !$isPostTestBabCompleted));
                    $postTestTitle = data_get($postTestBab, 'title', ($isBimtekEvent ? 'Evaluasi Hari ke-' : 'Evaluasi Modul ') . ($sectionIndex + 1) . ': ' . $sectionName);
                    $hasDriveAssignment = (bool) ($adminDriveLink || (!$postTestBab || $postTestQuestionsCount === 0));
                @endphp

                <div class="rounded-md border border-slate-200 bg-slate-50/40 p-4 sm:px-6 sm:py-5 lg:px-7 lg:py-6 shadow-2xs transition-all">
                    <div class="flex flex-row items-end justify-between gap-6 lg:gap-8">
                        {{-- Kolom Kiri: Informasi Evaluasi & Aksi --}}
                        <div class="flex-1 min-w-0 sm:py-1 space-y-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-[#13416B]">
                                        {{ $isBimtekEvent ? 'Evaluasi & Penugasan Hari ke-' . ($sectionIndex + 1) : 'Evaluasi Modul ' . ($sectionIndex + 1) }}
                                    </span>
                                    @if ($isPostTestBabCompleted)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                            <i class="fas fa-check-circle text-xs text-emerald-600"></i> Selesai
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 leading-snug max-w-full lg:max-w-[85%]">
                                    {{ $postTestTitle }}
                                </h3>

                                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 leading-relaxed max-w-full lg:max-w-[90%]">
                                    @if ($hasPostTestQuestions)
                                        Ujian evaluasi materi pada {{ $isBimtekEvent ? 'hari ini' : 'modul ini' }} untuk menguji pemahaman dan capaian pembelajaran Anda.
                                    @else
                                        Penyelenggara menyediakan penugasan berkas untuk agenda ini. Silakan akses folder Google Drive yang telah disiapkan penyelenggara untuk mengunggah pengerjaan tugas Anda.
                                    @endif
                                </p>

                                {{-- Badges Spesifikasi Evaluasi (Pilihan Ganda / Uraian / Tugas Drive) --}}
                                <div class="mt-3 flex flex-wrap items-center gap-2.5 sm:gap-3">
                                    @if ($hasPostTestQuestions)
                                        <span class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-list-ol text-[10px] sm:text-xs text-white"></i>
                                            {{ $postTestQuestionsCount }} Soal Pilihan Ganda
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-stopwatch text-[10px] sm:text-xs text-white"></i>
                                            {{ data_get($postTestBab, 'duration', 0) }} Menit
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-bullseye text-[10px] sm:text-xs text-white"></i>
                                            KKM: {{ data_get($postTestBab, 'passing_score', 0) }}
                                        </span>
                                    @else
                                        <span class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fab fa-google-drive text-[10px] sm:text-xs text-white"></i>
                                            Tugas Google Drive Penyelenggara
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-semibold text-white bg-[#547996] px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-md border border-[#446680] flex items-center gap-1.5 shadow-xs">
                                            <i class="fas fa-file-alt text-[10px] sm:text-xs text-white"></i>
                                            Penugasan Berkas / Uraian
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Mode Penugasan Google Drive Penyelenggara --}}
                            @if ($hasDriveAssignment || $adminDriveLink)
                                <div class="p-3.5 sm:p-4 rounded-md bg-white border border-blue-200/90 shadow-2xs space-y-3">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-md bg-blue-50 text-[#0B5CFF] border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                                <i class="fab fa-google-drive text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs sm:text-sm font-bold text-slate-800">Folder Google Drive Tugas dari Penyelenggara</p>
                                                <p class="text-[11px] text-slate-500">Folder pengumpulan tugas telah disiapkan oleh panitia. Kumpulkan tugas Anda ke tautan Drive di bawah.</p>
                                            </div>
                                        </div>

                                        @if ($adminDriveLink)
                                            <a href="{{ $adminDriveLink }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-xs font-bold text-white bg-[#0B5CFF] hover:bg-blue-700 transition-all shadow-2xs shrink-0 whitespace-nowrap">
                                                <i class="fab fa-google-drive"></i>
                                                <span>Buka Folder Drive Tugas</span>
                                                <i class="fas fa-external-link-alt text-[10px] ml-0.5"></i>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Status Penyerahan Tugas & Form Konfirmasi --}}
                                    @if ($zoomProof && $zoomProof->status === 'submitted')
                                        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <div class="flex items-center gap-1.5 text-emerald-700 font-bold">
                                                <i class="fas fa-check-circle text-emerald-600"></i>
                                                <span>Tugas Telah Dikonfirmasi / Dikumpulkan</span>
                                                @if ($zoomProof->submitted_at)
                                                    <span class="text-slate-400 font-normal">({{ $zoomProof->submitted_at->translatedFormat('d M Y, H:i') }} WIB)</span>
                                                @endif
                                            </div>
                                            @if ($zoomProof->notes)
                                                <p class="text-slate-600 text-[11px] w-full"><span class="font-bold">Keterangan:</span> {{ $zoomProof->notes }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                                            method="POST"
                                            enctype="multipart/form-data"
                                            class="pt-2 border-t border-slate-100 space-y-2.5">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                        Unggah Dokumen Tugas (PDF/DOCX/ZIP, Opsional)
                                                    </label>
                                                    <input type="file"
                                                        name="proof_file"
                                                        accept=".pdf,.docx,.doc,.zip,.jpg,.png"
                                                        class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded bg-slate-50 p-1" />
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                        Catatan / Keterangan Penyerahan Tugas
                                                    </label>
                                                    <input type="text"
                                                        name="notes"
                                                        value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                                        placeholder="Contoh: Berkas telah diunggah ke Drive panitia dengan nama Tugas_SPBE.docx"
                                                        class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-[#13416B]" />
                                                </div>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] shadow-xs">
                                                    <i class="fas fa-paper-plane text-[10px]"></i>
                                                    <span>Konfirmasi Pengumpulan Tugas</span>
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            {{-- Tombol Aksi untuk CBT Pilihan Ganda / Uraian --}}
                            @if ($hasPostTestQuestions)
                                <div class="pt-1 flex flex-wrap items-center gap-3">
                                    @if ($isPostTestBabCompleted)
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-xs">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Lihat Hasil Evaluasi</span>
                                        </a>
                                    @elseif ($isPostTestBabLocked)
                                        <button type="button" disabled
                                            title="Tuntaskan semua materi pada modul ini terlebih dahulu"
                                            class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none">
                                            <i class="fas fa-lock text-xs"></i>
                                            <span>Evaluasi Terkunci</span>
                                        </button>
                                    @else
                                        <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-md text-xs sm:text-sm font-bold bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm transition-all">
                                            <i class="fas fa-play text-xs"></i>
                                            <span>Mulai Evaluasi</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Kanan: Ilustrasi Gambar khusus Layar Tablet/Desktop --}}
                        <div class="hidden sm:flex sm:shrink-0 items-end justify-center self-end -mb-3.5 lg:-mb-4">
                            <img src="{{ asset('images/modul_ilustrasi.webp') }}"
                                alt="Ilustrasi Evaluasi Modul"
                                class="sm:h-36 md:h-40 lg:h-44 w-auto object-contain object-bottom select-none pointer-events-none" />
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-dashboard::layouts.dashboard>