@php
    $activeBimtek = $course->bimtekEvents->first();
    $isEnrolledInCourse = in_array($course->id, $enrolledCourseIds);
    $isEnrolledInThisBimtek = $activeBimtek && in_array($activeBimtek->id, $userBimtekEventIds);
@endphp

@if ($activeBimtek)
    {{-- ================================================= --}}
    {{-- EVENT CARD DESAIN KHUSUS (PELATIHAN ONLINE RESMI) --}}
    {{-- ================================================= --}}
    <div class="group flex flex-col h-auto self-start sm:self-auto sm:h-full bg-transparent sm:bg-white rounded-none sm:rounded-md border-0 sm:border border-slate-200 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden w-full">

        {{-- Thumbnail & Badges (Tanpa Digelapkan) --}}
        <div class="relative h-24 sm:h-48 overflow-hidden bg-slate-100 rounded-t-md">
            @if (!empty($course->thumbnail))
                <img src="{{ str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail) }}"
                    alt="{{ $course->name }}"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                    <i class="fas fa-graduation-cap text-2xl sm:text-4xl text-slate-300"></i>
                </div>
            @endif

            {{-- Badge Status Event di Sebelah Kanan Atas (Teks Ringkas di Mobile) --}}
            @php
                $curPhase = $activeBimtek->current_timeline_phase;
            @endphp
            <span class="absolute top-0 right-0 z-10 px-2 sm:px-3 py-0.5 sm:py-1 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-white {{ $curPhase['badge_bg'] ?? 'bg-emerald-600' }} shadow-xs flex items-center gap-1 rounded-none rounded-bl-md rounded-tr-md">
                <i class="fas {{ $curPhase['icon'] ?? 'fa-clock' }} text-[7px] sm:text-[9px]"></i>
                <span>{{ $curPhase['badge'] ?? 'Pelatihan Terjadwal' }}</span>
            </span>
        </div>

        {{-- Konten Text (Mobile: h-auto sesuai konten, Desktop: sm:flex-1) --}}
        <div class="p-0 pt-2 sm:p-5 flex flex-col h-auto sm:flex-1">
            {{-- Judul Event / Nama Gelombang --}}
            <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-extrabold text-amber-600 mb-1">
                <i class="fas fa-satellite-dish text-[10px] sm:text-[11px] text-amber-500 shrink-0"></i>
                <span class="truncate" title="{{ $activeBimtek->batch_name }}">{{ $activeBimtek->batch_name }}</span>
            </div>

            <h3 class="text-xs sm:text-base font-bold text-slate-800 leading-snug mb-1.5 sm:mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2" title="{{ $course->name }}">
                {{ $course->name }}
            </h3>

            <p class="line-clamp-2 text-[11px] sm:text-xs text-slate-500 mb-2.5 sm:mb-4 leading-relaxed">
                {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
            </p>

            {{-- Footer Aksi (Mobile: mt-2.5 rapat ke konten, Desktop: sm:mt-auto) --}}
            <div class="mt-2.5 sm:mt-auto pt-2.5 sm:pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-1.5 sm:gap-2">
                @if ($isEnrolledInThisBimtek)
                    <button type="button" @click="show('{{ $course->id }}')"
                        class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2.5 text-[10px] sm:text-sm font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                        <span class="sm:hidden">Detail</span>
                        <span class="hidden sm:inline">Lihat Detail</span>
                    </button>
                    <a href="{{ route('user.course.my-course.detail', $course->slug) }}"
                       class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2.5 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                        <span>Sudah Terdaftar</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                    </a>
                @elseif ($activeBimtek->status !== 'open_registration')
                    {{-- Pelatihan sudah mulai / tutup: cukup tombol lihat detail saja --}}
                    <button type="button" @click="show('{{ $course->id }}')"
                        class="w-full py-1.5 sm:py-2.5 text-[10px] sm:text-sm font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                        <span class="sm:hidden">Detail</span>
                        <span class="hidden sm:inline">Lihat Detail</span>
                    </button>
                @else
                    <button type="button" @click="show('{{ $course->id }}')"
                        class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2.5 text-[10px] sm:text-sm font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                        <span class="sm:hidden">Detail</span>
                        <span class="hidden sm:inline">Lihat Detail</span>
                    </button>
                    <button type="button" @click="askEnroll('{{ $course->id }}')"
                        class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2.5 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-sm">
                        <span>Daftar</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@else
    {{-- ================================================= --}}
    {{-- REGULAR COURSE CARD (KURSUS MANDIRI / SELF-PACED) --}}
    {{-- ================================================= --}}
    <div class="group flex flex-col h-auto self-start sm:self-auto sm:h-full bg-transparent sm:bg-white rounded-none sm:rounded-md border-0 sm:border border-slate-200 shadow-none sm:shadow-sm hover:shadow-xl hover:border-[#13416B]/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden w-full">

        {{-- Thumbnail --}}
        <div class="relative h-24 sm:h-48 overflow-hidden bg-slate-100 rounded-t-md">
            @if (!empty($course->thumbnail))
                <img src="{{ str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail) }}"
                    alt="{{ $course->name }}"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                    <i class="fas fa-graduation-cap text-2xl sm:text-4xl text-slate-300"></i>
                </div>
            @endif
        </div>

        {{-- Konten Text --}}
        <div class="p-0 pt-2 sm:p-5 flex flex-col h-auto sm:flex-1">
            <h3 class="text-xs sm:text-base font-bold text-slate-800 leading-snug mb-1.5 sm:mb-2 group-hover:text-[#13416B] transition-colors line-clamp-2" title="{{ $course->name }}">
                {{ $course->name }}
            </h3>

            {{-- Statistik: modul & peserta --}}
            <div class="flex items-center gap-2 sm:gap-2.5 mb-2 sm:mb-2.5 text-[10px] sm:text-xs font-medium text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    <i class="far fa-folder-open text-slate-400 text-[10px] sm:text-xs"></i>
                    <span>{{ $course->sections_count }} Modul</span>
                </span>
                <span class="text-slate-300 select-none">•</span>
                <span class="inline-flex items-center gap-1.5">
                    <i class="far fa-user text-slate-400 text-[10px] sm:text-xs"></i>
                    <span>{{ number_format($course->students_count ?? 0, 0, ',', '.') }} Peserta</span>
                </span>
            </div>

            <p class="line-clamp-2 text-[11px] sm:text-xs text-slate-500 mb-2.5 sm:mb-4 leading-relaxed">
                {{ $course->description ?? 'Tidak ada deskripsi singkat yang tersedia untuk kursus ini.' }}
            </p>

            {{-- Footer Aksi --}}
            <div class="mt-2.5 sm:mt-auto pt-2.5 sm:pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-1.5 sm:gap-2">
                <button type="button" @click="show('{{ $course->id }}')"
                    class="order-2 sm:order-1 flex-1 py-1.5 sm:py-2.5 text-[10px] sm:text-sm font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-white text-[#13416B] border border-[#13416B]/30 hover:bg-[#13416B]/5">
                    <span class="sm:hidden">Detail</span>
                    <span class="hidden sm:inline">Lihat Detail</span>
                </button>
                <button type="button" @click="askEnroll('{{ $course->id }}')"
                    class="order-1 sm:order-2 flex-1 py-1.5 sm:py-2.5 text-[11px] sm:text-sm inline-flex items-center justify-center gap-1.5 font-bold text-center rounded-lg sm:rounded-xl transition-colors bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 shadow-sm">
                    <span>Daftar</span>
                    <i class="fas fa-arrow-up-right-from-square text-[9px] sm:text-xs"></i>
                </button>
            </div>
        </div>
    </div>
@endif
