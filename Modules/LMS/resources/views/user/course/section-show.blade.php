<x-dashboard::layouts.dashboard
    title="Hari {{ $sectionIndex + 1 }}: {{ data_get($section, 'name') }} | {{ data_get($course, 'course_name') ?? data_get($course, 'name') }} | SIRENATA">

    @php
        $courseName = data_get($course, 'course_name') ?? data_get($course, 'name', 'Course');
        $courseSlug = $slug ?? data_get($course, 'slug');
        $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
        $rawSectionName = data_get($section, 'name', '');
        $cleanSectionName = preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $rawSectionName);
        $sectionName = !empty(trim($cleanSectionName)) ? trim($cleanSectionName) : ($rawSectionName ?: 'Hari ' . ($sectionIndex + 1));
        $sectionContents = data_get($section, 'section_contents', []) ?: data_get($section, 'contents', []);
        $totalCount = count($sectionContents);
        $completedCount = data_get($section, 'completed_count', 0);
        $postTestBab = data_get($section, 'post_test_bab');
        $isPostTestBabCompleted = data_get($section, 'is_post_test_bab_completed', false);
        $isContentCompleted = data_get($section, 'is_content_completed', false);
        $isSectionCompleted = data_get($section, 'is_section_completed', false);
        $canReviewAll = data_get($course, 'can_review_all', false);
        $isWaitingForStart = data_get($course, 'is_waiting_for_start', false);
        $sectionProgress = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;

        $dayDate = null;
        try {
            if ($activeBimtek && $activeBimtek->start_date) {
                $dayDate = \Carbon\Carbon::parse($activeBimtek->start_date)->addDays($sectionIndex);
            }
        } catch (\Exception $e) {}
    @endphp

    <div class="px-0 py-2 sm:p-6 max-w-full mx-auto">

        {{-- Breadcrumb Navigation --}}
        <nav class="flex mb-3 sm:mb-4" aria-label="Breadcrumb">
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
                            Hari ke-{{ $sectionIndex + 1 }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Tombol Navigasi Kembali ke Agenda Bimtek --}}
        <div class="mb-5 sm:mb-6">
            <a href="{{ route('user.course.my-course.detail', $courseSlug) }}"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs sm:text-sm font-bold text-[#13416B] bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-2xs">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Kembali ke Agenda Bimtek</span>
            </a>
        </div>

        {{-- Header Card Modul / Hari --}}
        <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 lg:p-7 mb-6 transition-all">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5 sm:gap-4 flex-1 min-w-0">
                    {{-- Badge Hari seperti Kalender dengan rounded-md, border-b, dan border-r --}}
                    <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-md bg-[#13416B] text-white border border-[#13416B] border-r-2 border-b-2 border-r-[#092035] border-b-[#092035] shadow-2xs shrink-0">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-300 leading-none">HARI</span>
                        <span class="text-base sm:text-lg font-black leading-tight mt-0.5">{{ $sectionIndex + 1 }}</span>
                    </div>

                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-[#13416B]">Agenda Hari ke-{{ $sectionIndex + 1 }}</span>
                            @if ($activeBimtek && $activeBimtek->batch_name)
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

                {{-- Progres Hari Ini --}}
                <div class="shrink-0 md:text-right w-full md:w-56 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                    <div class="flex justify-between md:justify-end items-center gap-2 mb-1.5">
                        <span class="text-xs font-semibold text-slate-500">Progres Hari Ini</span>
                        <span class="text-xs sm:text-sm font-bold {{ $isSectionCompleted ? 'text-emerald-600' : 'text-[#13416B]' }}">
                            {{ $completedCount }}/{{ $totalCount }} Selesai ({{ $sectionProgress }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/60">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $isSectionCompleted ? 'bg-emerald-500' : 'bg-[#0B5CFF]' }}"
                            style="width: {{ $sectionProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">

            {{-- 1. CARD SESI LIVE ZOOM (Pindahan ke Dalam Modul) --}}
            @if ($activeBimtek && $activeBimtek->zoom_url)
                @php
                    $isZoomAttendanceLocked = false;
                    $zoomScheduleFormatted = $activeBimtek->zoom_schedule_formatted ?? null;
                    if ($activeBimtek->zoom_scheduled_at && now()->lessThan($activeBimtek->zoom_scheduled_at)) {
                        $isZoomAttendanceLocked = true;
                    }
                @endphp
                <div x-data="{ hasJoinedZoom: {{ $hasJoinedZoom ? 'true' : 'false' }}, showZoomModal: false }"
                    @keydown.escape.window="showZoomModal = false"
                    class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 transition-all">
                    
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
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fas fa-calendar-alt text-[#1E62D0]"></i>
                                        {{ $zoomScheduleFormatted ?? $activeBimtek->zoom_scheduled_at->translatedFormat('d F Y, H:i') . ' WIB' }}
                                    </span>
                                @endif
                                @if ($activeBimtek->zoom_meeting_id)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fas fa-hashtag text-slate-400"></i> ID: {{ $activeBimtek->zoom_meeting_id }}
                                    </span>
                                @endif
                                @if ($activeBimtek->zoom_passcode)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
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
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md font-bold text-xs border border-slate-300 text-slate-700 hover:bg-slate-100 transition-all">
                                    <i class="fas fa-clipboard-check text-xs text-[#13416B]"></i>
                                    <span>Konfirmasi Presensi</span>
                                </button>
                            @endif
                        </div>
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
                                        *Pastikan Anda telah menekan tombol "Masuk Ruang Zoom" sebelum konfirmasi.
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

            {{-- 2. CARD DAFTAR TOPIK MATERI PEMBELAJARAN --}}
            <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 transition-all">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-book-open text-slate-700 text-lg sm:text-xl shrink-0"></i>
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight">Topik Pembelajaran Mandiri</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Pelajari topik secara berurutan dan tandai selesai</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md">
                        {{ $totalCount }} Topik
                    </span>
                </div>

                <div class="space-y-3">
                    @php $isPreviousContentDone = true; @endphp
                    @forelse ($sectionContents as $content)
                        @php
                            $contentId = data_get($content, 'id');
                            $contentName = data_get($content, 'name', 'Topik Tanpa Judul');
                            $isContentItemCompleted = data_get($content, 'is_completed', false);
                            $isContentLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isPreviousContentDone && !$isContentItemCompleted));
                            $contentIconBox = $isContentLocked ? 'bg-slate-400 text-white border-slate-400' : 'bg-[#547996] text-white border-[#446680]';
                            $contentIcon = $isContentLocked ? 'fa-lock' : 'fa-file-alt';
                        @endphp

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-md border border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs transition-all gap-3 sm:gap-4">
                            <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full {{ $contentIconBox }} shrink-0 border">
                                    <i class="fas {{ $contentIcon }} {{ $isContentLocked ? 'text-xs' : 'text-xs sm:text-sm' }}"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <p class="font-bold text-slate-800 text-sm leading-snug break-words line-clamp-2" title="{{ $contentName }}">
                                            {{ $contentName }}
                                        </p>
                                        @if ($isContentItemCompleted)
                                            <i class="fas fa-check-circle text-emerald-600 text-xs sm:text-sm shrink-0" title="Selesai Dipelajari"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0">
                                @if (!$isContentLocked)
                                    <a href="{{ route('user.course.content.show', ['slug' => $courseSlug, 'content' => $contentId]) }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 {{ $isContentItemCompleted ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200' : 'bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm' }} rounded-md text-xs font-bold transition-all text-center">
                                        <i class="fas {{ $isContentItemCompleted ? 'fa-eye' : 'fa-play' }}"></i>
                                        <span>{{ $isContentItemCompleted ? 'Lihat Kembali' : 'Pelajari Topik' }}</span>
                                    </a>
                                @else
                                    <button type="button" disabled
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-bold cursor-not-allowed select-none">
                                        <i class="fas fa-lock text-[10px]"></i>
                                        <span>Terkunci</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        @php $isPreviousContentDone = $isContentItemCompleted; @endphp
                    @empty
                        <div class="p-6 text-center text-slate-500 bg-slate-50 rounded-md border border-dashed border-slate-200">
                            <i class="fas fa-folder-open mb-2 text-slate-300 text-2xl block"></i>
                            <p class="text-sm font-medium">Topik pembelajaran sedang disiapkan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 3. CARD UPLOAD BUKTI PELENGKAP & DOKUMEN PENDUKUNG --}}
            <div x-data="{ isEditingProof: {{ $zoomProof ? 'false' : 'true' }} }"
                class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 transition-all">
                
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-md bg-[#13416B] text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight">Bukti Pelengkap & Berkas Pendukung</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Unggah berkas bukti pelengkap sesi tatap muka (tangkapan layar/dokumen) atau tautan Google Drive</p>
                        </div>
                    </div>

                    @if ($zoomProof)
                        <button type="button" @click="isEditingProof = !isEditingProof"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors">
                            <i class="fas" :class="isEditingProof ? 'fa-times' : 'fa-edit'"></i>
                            <span x-text="isEditingProof ? 'Tutup Form' : 'Perbarui Bukti Pelengkap'"></span>
                        </button>
                    @endif
                </div>

                @if ($zoomProof && !$errors->any())
                    <div x-show="!isEditingProof" class="bg-emerald-50/60 border border-emerald-200 rounded-md p-4 sm:p-5 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2 text-emerald-800 font-bold text-sm">
                                <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                                <span>Bukti Pelengkap Telah Dikirim</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-100/70 border border-emerald-200 px-2.5 py-0.5 rounded">
                                {{ $zoomProof->submitted_at ? $zoomProof->submitted_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Tersimpan' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5 pt-1">
                            @if ($zoomProof->file_path)
                                <a href="{{ $zoomProof->file_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-bold text-[#13416B] bg-white border border-slate-200 hover:bg-slate-50 shadow-2xs transition-all">
                                    <i class="fas fa-paperclip text-slate-400"></i>
                                    <span>Lihat Berkas Bukti Pelengkap</span>
                                    <i class="fas fa-external-link-alt text-[10px] text-slate-400 ml-0.5"></i>
                                </a>
                            @endif

                            @if ($zoomProof->drive_link)
                                <a href="{{ $zoomProof->drive_link }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-bold text-blue-700 bg-white border border-blue-200 hover:bg-blue-50 shadow-2xs transition-all">
                                    <i class="fab fa-google-drive text-[#0B5CFF]"></i>
                                    <span>Buka Tautan Google Drive</span>
                                    <i class="fas fa-external-link-alt text-[10px] text-slate-400 ml-0.5"></i>
                                </a>
                            @endif
                        </div>

                        @if ($zoomProof->notes)
                            <div class="text-xs text-slate-600 pt-1 border-t border-emerald-200/50">
                                <span class="font-bold text-slate-700">Catatan Peserta:</span> {{ $zoomProof->notes }}
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Form Upload Bukti Pelengkap --}}
                <div x-show="isEditingProof" class="pt-2">
                    <form action="{{ route('user.course.section.upload-zoom-proof', ['slug' => $courseSlug, 'sectionId' => $sectionId]) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Input 1: Upload File --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700">
                                    Unggah Berkas Gambar / PDF (Bukti Pelengkap)
                                </label>
                                <input type="file"
                                    name="proof_file"
                                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#13416B] file:text-white hover:file:bg-[#0f3354] file:cursor-pointer border border-slate-200 rounded-md bg-slate-50/50 cursor-pointer p-1" />
                                <p class="text-[11px] text-slate-400">
                                    Format: JPG, PNG, WEBP, atau PDF (Maks. 5 MB).
                                </p>
                            </div>

                            {{-- Input 2: Tautan Google Drive --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700">
                                    Tautan Google Drive (Opsi Bukti Pelengkap)
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                        <i class="fab fa-google-drive"></i>
                                    </span>
                                    <input type="url"
                                        name="drive_link"
                                        value="{{ old('drive_link', $zoomProof->drive_link ?? '') }}"
                                        placeholder="https://drive.google.com/..."
                                        class="w-full pl-9 pr-3.5 py-2 text-xs rounded-md border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]" />
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Pastikan hak akses tautan Drive diatur ke <em>"Siapa saja yang memiliki tautan"</em>.
                                </p>
                            </div>
                        </div>

                        {{-- Input 3: Catatan Singkat (Opsional) --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700">
                                Catatan / Keterangan (Opsional)
                            </label>
                            <input type="text"
                                name="notes"
                                value="{{ old('notes', $zoomProof->notes ?? '') }}"
                                placeholder="Contoh: Tangkapan layar bukti kehadiran tatap muka hari ke-{{ $sectionIndex + 1 }}"
                                class="w-full px-3.5 py-2 text-xs rounded-md border border-slate-300 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#13416B]/20 focus:border-[#13416B]" />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            @if ($zoomProof)
                                <button type="button" @click="isEditingProof = false"
                                    class="px-4 py-2 rounded-md text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                                    Batal
                                </button>
                            @endif
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md text-xs font-bold text-white bg-[#13416B] hover:bg-[#0f3354] transition-all shadow-sm">
                                <i class="fas fa-save text-xs"></i>
                                <span>Simpan Bukti Pelengkap</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4. CARD EVALUASI HARIAN (POST TEST BAB) --}}
            @if ($postTestBab)
                @php
                    $isPostTestBabLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isContentCompleted && !$isPostTestBabCompleted));
                @endphp
                <div class="bg-white rounded-none sm:rounded-md shadow-sm border-y border-x-0 sm:border border-slate-200 p-4 sm:p-6 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 flex-1 min-w-0">
                            <span class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-[#1E62D0] text-white shrink-0 border border-[#1752b0] shadow-xs">
                                <i class="fas fa-file-signature text-base sm:text-lg"></i>
                            </span>
                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-base leading-tight break-words">
                                        {{ data_get($postTestBab, 'title', 'Evaluasi Hari ke-' . ($sectionIndex + 1) . ': ' . $sectionName) }}
                                    </h3>
                                    @if ($isPostTestBabCompleted)
                                        <i class="fas fa-check-circle text-emerald-600 text-sm shrink-0" title="Evaluasi Selesai"></i>
                                    @endif
                                </div>

                                {{-- Info Soal & Durasi --}}
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 pt-0.5">
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                                        <i class="fas fa-list-ol text-slate-400 text-xs"></i>
                                        {{ data_get($postTestBab, 'questions', collect())->count() ?? 0 }} Soal
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                                        <i class="fas fa-stopwatch text-slate-400 text-xs"></i>
                                        {{ data_get($postTestBab, 'duration', 0) }} Menit
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                                        <i class="fas fa-bullseye text-slate-400 text-xs"></i>
                                        KKM: {{ data_get($postTestBab, 'passing_score', 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 self-start sm:self-center w-full sm:w-auto">
                            @if ($isPostTestBabLocked)
                                <button type="button" disabled
                                    title="Tuntaskan semua topik pembelajaran mandiri pada modul ini terlebih dahulu"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-xs font-bold cursor-not-allowed select-none">
                                    <i class="fas fa-lock text-[10px]"></i>
                                    <span>Evaluasi Terkunci</span>
                                </button>
                            @else
                                <a href="{{ route('user.course.test.show', ['slug' => $courseSlug, 'postTestId' => $postTestBab->id]) }}"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 {{ $isPostTestBabCompleted ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200' : 'bg-[#13416B] hover:bg-[#0f3354] text-white shadow-sm' }} rounded-md text-xs font-bold transition-all text-center">
                                    <i class="fas {{ $isPostTestBabCompleted ? 'fa-eye' : 'fa-pencil-alt' }}"></i>
                                    <span>{{ $isPostTestBabCompleted ? 'Lihat Hasil' : 'Kerjakan Evaluasi' }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

</x-dashboard::layouts.dashboard>
