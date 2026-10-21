<x-dashboard::layouts.dashboard title="{{ data_get($content, 'name', 'Detail Topik') }} | SIRENATA">
    <div class="p-0 sm:p-4 md:p-6 max-w-full mx-auto min-h-screen" x-data="{
        reachedEnd: {{ data_get($content, 'is_completed') ? 'true' : 'false' }},
        videoCompleted: {{ data_get($content, 'is_completed') ? 'true' : 'false' }},
        submitting: false,
        showQuickSettings: false,
        isDark: window.isDarkMode ? window.isDarkMode() : false,
        readerPrefs: {
            fontSize: 'normal',
            fontFamily: 'sans',
            lineHeight: '1.5',
            textAlign: 'left',
            paperTheme: 'white',
            fontWeight: 'normal'
        },
    
        init() {
            // Muat preferensi tampilan baca dari localStorage
            const saved = localStorage.getItem('sirenata_reading_prefs');
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    this.readerPrefs = Object.assign(this.readerPrefs, parsed);
                } catch (e) {}
            }

            if (this.reachedEnd && this.videoCompleted) return;
    
            // 1. Observer untuk deteksi baca sampai bawah
            const sentinel = this.$refs.endSentinel;
            if (sentinel && ('IntersectionObserver' in window)) {
                const io = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        this.reachedEnd = true;
                        io.disconnect(); // matikan observer jika sudah scroll ke ujung
                    }
                }, { threshold: 0 });
                io.observe(sentinel);
            } else {
                this.reachedEnd = true;
            }
    
            // 2. Logic deteksi video ditonton 90%
            const videos = document.querySelectorAll('.quill-content-render video');
            if (videos.length === 0) {
                // Jika tidak ada konten video internal, anggap syarat video langsung beres
                this.videoCompleted = true;
            } else {
                let videosStatus = Array.from(videos).map(() => false);
                videos.forEach((video, index) => {
                    video.addEventListener('timeupdate', () => {
                        // Jika durasi saat ini / durasi total >= 0.90 (90%)
                        if (!videosStatus[index] && (video.currentTime / video.duration) >= 0.9) {
                            videosStatus[index] = true;
    
                            // Validasi jika semua video dalam materi sudah 90%
                            if (videosStatus.every(status => status)) {
                                this.videoCompleted = true;
                            }
                        }
                    });
                });
            }
        },
    
        // Getter untuk mengecek apakah kedua syarat sudah terpenuhi
        get canComplete() {
            return this.reachedEnd && this.videoCompleted;
        },
    
        scrollToEnd() {
            this.$refs.endSentinel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        setPref(key, value) {
            this.readerPrefs[key] = value;
            localStorage.setItem('sirenata_reading_prefs', JSON.stringify(this.readerPrefs));
        },

        toggleDarkMode() {
            if (window.toggleDarkMode) {
                this.isDark = window.toggleDarkMode();
            }
        },

        resetPrefs() {
            this.readerPrefs = {
                fontSize: 'normal',
                fontFamily: 'sans',
                lineHeight: '1.5',
                textAlign: 'left',
                paperTheme: 'white',
                fontWeight: 'normal'
            };
            localStorage.setItem('sirenata_reading_prefs', JSON.stringify(this.readerPrefs));
            if (this.isDark && window.toggleDarkMode) {
                this.isDark = window.toggleDarkMode();
            }
        },

        getLineHeightClass() {
            const lh = this.readerPrefs.lineHeight;
            if (lh === '1' || lh === '1.0') return 'reader-leading-1-0';
            if (lh === '1.2') return 'reader-leading-1-2';
            if (lh === '2' || lh === '2.0' || lh === 'loose') return 'reader-leading-2-0';
            if (lh === '2.5') return 'reader-leading-2-5';
            return 'reader-leading-1-5';
        }
    }" @dark-mode-toggled.window="isDark = $event.detail.isDark"
    x-effect="document.body.classList.toggle('overflow-hidden', showQuickSettings && window.innerWidth < 640)">

        {{-- Main Content Container --}}
        <div
            class="bg-white sm:rounded-md sm:shadow-sm border-b sm:border border-slate-200 overflow-hidden min-h-screen sm:min-h-0 reader-container transition-colors duration-200"
            :class="{
                'reader-theme-dark-sepia': isDark && readerPrefs.paperTheme === 'sepia',
                'reader-theme-dark': isDark && readerPrefs.paperTheme !== 'sepia',
                'reader-theme-sepia': !isDark && readerPrefs.paperTheme === 'sepia'
            }">

            {{-- Body Content --}}
            <div class="p-5 sm:p-10 lg:p-12 transition-all duration-200">

                {{-- Header Nav: Tombol Kembali --}}
                <div class="mb-6 sm:mb-8">
                    <a href="{{ route('user.course.my-course.detail', $slug) }}"
                        class="group inline-flex items-center gap-3 text-sm font-medium text-slate-500 hover:text-[#13416B] transition-colors"
                        :class="{
                            '!text-slate-400 hover:!text-white': isDark
                        }">

                        {{-- Lingkaran Ikon Panah --}}
                        <div
                            class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-[#13416B]/10 transition-colors"
                            :class="{
                                '!bg-[#263346] !text-slate-300': isDark
                            }">
                            <i class="fas fa-arrow-left text-xs text-slate-600 group-hover:text-[#13416B]"
                                :class="{
                                    '!text-slate-300': isDark
                                }"></i>
                        </div>

                        {{-- Teks --}}
                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center sm:gap-1.5 leading-tight">
                            <span class="text-[11px] sm:text-sm text-slate-400 mb-0.5 sm:mb-0"
                                :class="{
                                    '!text-slate-400': isDark
                                }">Kembali ke</span>
                            <span
                                class="font-bold text-slate-700 truncate group-hover:text-[#13416B] transition-colors"
                                :class="{
                                    '!text-slate-200 group-hover:!text-white': isDark
                                }">
                                {{ data_get($course, 'course_name', 'Kursus') }}
                            </span>
                        </div>
                    </a>
                </div>

                {{-- Header & Title Materi --}}
                <div class="border-b border-slate-100 pb-6 sm:pb-8 mb-6 sm:mb-8"
                    :class="{
                        '!border-[#263346]': isDark
                    }">
                    <div class="flex items-center gap-2.5 mb-3 sm:mb-4">
                        <span
                            class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#13416B] bg-[#13416B]/10 rounded-lg border border-[#13416B]/20"
                            :class="{
                                '!bg-white/10 !text-white !border-white/20': isDark
                            }">
                            Topik Pembelajaran
                        </span>

                        {{-- Tombol Tampilan Baca (Quick Settings) di samping badge topik --}}
                        <div class="relative" @click.away="if (window.innerWidth >= 640) showQuickSettings = false">
                            <button type="button" @click="showQuickSettings = !showQuickSettings"
                                class="inline-flex shrink-0 items-center gap-1.5 px-2.5 py-1 text-[10px] sm:text-[11px] font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer"
                                :class="{
                                    '!bg-[#263346] !border-[#334155] !text-slate-200': isDark,
                                    'ring-2 ring-[#13416B]/30 !border-[#13416B]': showQuickSettings
                                }"
                                title="Sesuaikan tampilan baca">
                                <i class="fas fa-font text-[10px]"
                                    :class="{
                                        'text-white': isDark,
                                        'text-[#13416B]': !isDark
                                    }"></i>
                                <span>Tampilan</span>
                            </button>

                            {{-- Dropdown Quick Settings (Desktop only: sm:block) --}}
                            <div x-show="showQuickSettings" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                class="hidden sm:block absolute left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-3.5 z-50 text-slate-800 text-left"
                                :class="{
                                    '!bg-[#1a2230] !border-[#263346] !text-slate-200': isDark
                                }">

                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100"
                                    :class="{ '!border-[#263346]': isDark }">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5"
                                        :class="{ '!text-slate-200': isDark }">
                                        <i class="fas fa-sliders-h text-[#13416B]" :class="{ '!text-white': isDark }"></i> Tampilan Baca
                                    </h4>
                                    <button type="button" @click="resetPrefs()" class="text-[11px] font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 cursor-pointer"
                                        :class="{ 'hover:!text-slate-200': isDark }">
                                        <i class="fas fa-undo text-[10px]"></i> Reset
                                    </button>
                                </div>

                                {{-- 1. Mode Gelap Toggle --}}
                                <div class="mb-3">
                                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-slate-50 transition-colors cursor-pointer select-none"
                                        :class="{ '!border-[#263346] !bg-[#131A26]': isDark }"
                                        @click="toggleDarkMode()">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-moon text-xs" :class="isDark ? 'text-white' : 'text-slate-400'"></i>
                                            <span class="text-xs font-bold text-slate-700" :class="{ '!text-slate-200': isDark }">Mode Gelap</span>
                                        </div>
                                        <button type="button" @click.stop="toggleDarkMode()"
                                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            :class="isDark ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600'"
                                            role="switch" :aria-checked="isDark">
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full shadow-xs ring-0 transition duration-200 ease-in-out"
                                                :class="isDark ? 'translate-x-4 bg-slate-900' : 'translate-x-0 bg-white'"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- 2. Pilihan Warna Kertas (Putih / Sepia) --}}
                                <div class="mb-3">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5"
                                        :class="{ '!text-slate-300': isDark }">Warna Kertas</label>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button type="button" @click="setPref('paperTheme', 'white')"
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                                            :class="(readerPrefs.paperTheme === 'white' || !readerPrefs.paperTheme) ? (isDark ? 'bg-[#263346] text-white ring-1 ring-white/30' : 'bg-white text-[#13416B] ring-1 ring-[#13416B]/30 shadow-xs') : (isDark ? 'bg-[#131A26] text-slate-400 hover:text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900')">
                                            <span class="w-3 h-3 rounded-full bg-white border border-slate-300 shrink-0"></span>
                                            <span>Putih</span>
                                        </button>
                                        <button type="button" @click="setPref('paperTheme', 'sepia')"
                                            class="py-1.5 px-2 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                                            :class="readerPrefs.paperTheme === 'sepia' ? (isDark ? 'bg-[#2a2119] text-[#e8dcce] ring-1 ring-[#a46322]' : 'bg-[#F8F1E5] text-[#4A3B2C] ring-1 ring-[#8C531B]/40 shadow-xs') : (isDark ? 'bg-[#131A26] text-slate-400 hover:text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900')">
                                            <span class="w-3 h-3 rounded-full bg-[#F8F1E5] border border-[#D5C2A5] shrink-0"></span>
                                            <span>Sepia</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- 3. Ukuran Font --}}
                                <div class="mb-3">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1.5"
                                        :class="{ '!text-slate-300': isDark }">Ukuran Huruf</label>
                                    <div class="grid grid-cols-4 gap-1 bg-slate-100 p-1 rounded-xl"
                                        :class="{ '!bg-[#131A26]': isDark }">
                                        <button type="button" @click="setPref('fontSize', 'sm')"
                                            class="py-1 rounded-lg text-xs font-semibold text-center transition-colors cursor-pointer"
                                            :class="readerPrefs.fontSize === 'sm' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                            14px
                                        </button>
                                        <button type="button" @click="setPref('fontSize', 'normal')"
                                            class="py-1 rounded-lg text-xs font-semibold text-center transition-colors cursor-pointer"
                                            :class="(readerPrefs.fontSize === 'normal' || !readerPrefs.fontSize) ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                            16px
                                        </button>
                                        <button type="button" @click="setPref('fontSize', 'lg')"
                                            class="py-1 rounded-lg text-xs font-semibold text-center transition-colors cursor-pointer"
                                            :class="readerPrefs.fontSize === 'lg' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                            18px
                                        </button>
                                        <button type="button" @click="setPref('fontSize', 'xl')"
                                            class="py-1 rounded-lg text-xs font-semibold text-center transition-colors cursor-pointer"
                                            :class="readerPrefs.fontSize === 'xl' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                            20px
                                        </button>
                                    </div>
                                </div>

                                {{-- Link to full profile settings --}}
                                <a href="{{ route('user.profile') }}#pengaturan" target="_blank"
                                    class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-[#13416B] hover:underline"
                                    :class="{ '!border-[#263346] !text-blue-400': isDark }">
                                    <span>Pengaturan Lengkap di Profil</span>
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            </div>

                            {{-- Bottom Sheet Quick Settings (Mobile only: sm:hidden) --}}
                            <div x-show="showQuickSettings" x-cloak style="display: none;"
                                class="fixed inset-0 z-50 sm:hidden flex items-end justify-center"
                                role="dialog" aria-modal="true" aria-label="Pengaturan Tampilan Baca">

                                <!-- Backdrop -->
                                <div x-show="showQuickSettings"
                                    x-transition.opacity.duration.200ms
                                    @click="showQuickSettings = false"
                                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

                                <!-- Panel Bottom Sheet -->
                                <div x-show="showQuickSettings"
                                    x-transition:enter="transition ease-out duration-300 transform"
                                    x-transition:enter-start="translate-y-full"
                                    x-transition:enter-end="translate-y-0"
                                    x-transition:leave="transition ease-in duration-200 transform"
                                    x-transition:leave-start="translate-y-0"
                                    x-transition:leave-end="translate-y-full"
                                    @keydown.escape.window="showQuickSettings = false"
                                    class="relative w-full max-h-[85vh] flex flex-col bg-white rounded-t-2xl shadow-2xl border-t border-slate-200 overflow-hidden text-slate-800 text-left"
                                    :class="{
                                        '!bg-[#1a2230] !border-[#263346] !text-slate-200': isDark
                                    }">

                                    <!-- Drag Handle -->
                                    <div class="pt-3 pb-1 flex justify-center shrink-0">
                                        <div class="w-10 h-1 bg-slate-300 rounded-full"
                                            :class="{ '!bg-slate-600': isDark }"></div>
                                    </div>

                                    <!-- Header Sheet -->
                                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100 shrink-0"
                                        :class="{ '!border-[#263346]': isDark }">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-sliders-h text-xs text-[#13416B]" :class="{ '!text-white': isDark }"></i>
                                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800" :class="{ '!text-white': isDark }">
                                                Tampilan Baca
                                            </h3>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="resetPrefs()"
                                                class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 cursor-pointer transition-colors"
                                                :class="{ 'hover:!text-slate-200': isDark }">
                                                <i class="fas fa-undo text-[10px]"></i>
                                                <span>Reset</span>
                                            </button>
                                            <button type="button" @click="showQuickSettings = false"
                                                class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors cursor-pointer"
                                                :class="{ '!bg-[#263346] !text-slate-300 hover:!bg-[#334155]': isDark }"
                                                aria-label="Tutup">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Sheet Body -->
                                    <div class="p-4 overflow-y-auto space-y-3.5">
                                        {{-- 1. Mode Gelap Toggle --}}
                                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-slate-50 transition-colors cursor-pointer select-none"
                                            :class="{ '!border-[#263346] !bg-[#131A26]': isDark }"
                                            @click="toggleDarkMode()">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                                                    :class="isDark ? 'bg-white/10 text-white' : 'bg-[#13416B]/10 text-[#13416B]'">
                                                    <i class="fas fa-moon text-xs"></i>
                                                </div>
                                                <div>
                                                    <span class="text-xs font-bold text-slate-800 block" :class="{ '!text-white': isDark }">Mode Gelap</span>
                                                    <span class="text-[10px] text-slate-400 block" :class="{ '!text-slate-400': isDark }">Kurangi silau pada layar</span>
                                                </div>
                                            </div>
                                            <button type="button" @click.stop="toggleDarkMode()"
                                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="isDark ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600'"
                                                role="switch" :aria-checked="isDark">
                                                <span aria-hidden="true"
                                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full shadow-xs ring-0 transition duration-200 ease-in-out"
                                                    :class="isDark ? 'translate-x-4 bg-slate-900' : 'translate-x-0 bg-white'"></span>
                                            </button>
                                        </div>

                                        {{-- 2. Pilihan Warna Kertas (Putih / Sepia) --}}
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5"
                                                :class="{ '!text-slate-200': isDark }">Warna Kertas / Latar</label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="button" @click="setPref('paperTheme', 'white')"
                                                    class="py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer border"
                                                    :class="(readerPrefs.paperTheme === 'white' || !readerPrefs.paperTheme) ? (isDark ? 'bg-[#263346] text-white border-white/40 ring-1 ring-white/30' : 'bg-white text-[#13416B] border-[#13416B] ring-1 ring-[#13416B]/30 shadow-xs') : (isDark ? 'bg-[#131A26] border-slate-700 text-slate-400 hover:text-white' : 'bg-slate-50 border-slate-200 text-slate-600 hover:text-slate-900')">
                                                    <span class="w-3.5 h-3.5 rounded-full bg-white border border-slate-300 shrink-0"></span>
                                                    <span>Putih</span>
                                                </button>
                                                <button type="button" @click="setPref('paperTheme', 'sepia')"
                                                    class="py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer border"
                                                    :class="readerPrefs.paperTheme === 'sepia' ? (isDark ? 'bg-[#2a2119] text-[#e8dcce] border-[#a46322] ring-1 ring-[#a46322]/40' : 'bg-[#F8F1E5] text-[#4A3B2C] border-[#8C531B] ring-1 ring-[#8C531B]/30 shadow-xs') : (isDark ? 'bg-[#131A26] border-slate-700 text-slate-400 hover:text-white' : 'bg-slate-50 border-slate-200 text-slate-600 hover:text-slate-900')">
                                                    <span class="w-3.5 h-3.5 rounded-full bg-[#F8F1E5] border border-[#D5C2A5] shrink-0"></span>
                                                    <span>Sepia</span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 3. Ukuran Font --}}
                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-xs font-bold text-slate-700"
                                                    :class="{ '!text-slate-200': isDark }">Ukuran Huruf</label>
                                                <span class="text-[10px] text-slate-400 font-medium">
                                                    <span x-show="readerPrefs.fontSize === 'sm'">14px</span>
                                                    <span x-show="readerPrefs.fontSize === 'normal' || !readerPrefs.fontSize">16px (Normal)</span>
                                                    <span x-show="readerPrefs.fontSize === 'lg'">18px</span>
                                                    <span x-show="readerPrefs.fontSize === 'xl'">20px</span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-4 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/60"
                                                :class="{ '!bg-[#131A26] !border-slate-800': isDark }">
                                                <button type="button" @click="setPref('fontSize', 'sm')"
                                                    class="py-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer"
                                                    :class="readerPrefs.fontSize === 'sm' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                                    14px
                                                </button>
                                                <button type="button" @click="setPref('fontSize', 'normal')"
                                                    class="py-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer"
                                                    :class="(readerPrefs.fontSize === 'normal' || !readerPrefs.fontSize) ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                                    16px
                                                </button>
                                                <button type="button" @click="setPref('fontSize', 'lg')"
                                                    class="py-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer"
                                                    :class="readerPrefs.fontSize === 'lg' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                                    18px
                                                </button>
                                                <button type="button" @click="setPref('fontSize', 'xl')"
                                                    class="py-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer"
                                                    :class="readerPrefs.fontSize === 'xl' ? (isDark ? 'bg-[#263346] text-white shadow-xs' : 'bg-white text-[#13416B] shadow-xs') : (isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900')">
                                                    20px
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 4. Link ke Pengaturan Lengkap di Profil --}}
                                        <a href="{{ route('user.profile') }}#pengaturan" target="_blank"
                                            class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-[#13416B] hover:underline"
                                            :class="{ '!border-[#263346] !text-blue-400': isDark }">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fas fa-sliders-h text-xs"></i>
                                                <span>Pengaturan Lengkap di Profil</span>
                                            </span>
                                            <i class="fas fa-external-link-alt text-[10px]"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h1
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight sm:leading-tight"
                        :class="{
                            'font-serif': readerPrefs.fontFamily === 'serif',
                            'font-sans': readerPrefs.fontFamily === 'sans',
                            '!text-slate-100': isDark
                        }">
                        {{ data_get($content, 'name') }}
                    </h1>
                </div>

                {{-- 1. Text & Media Content --}}
                @php
                    $contentText = is_object($content)
                        ? $content->content_text
                        : (is_array($content)
                            ? $content['content_text'] ?? null
                            : null);
                @endphp

                @if (!empty($contentText))
                    <div data-tour="materi"
                        class="prose prose-slate max-w-none mb-10 text-slate-700 quill-content-render transition-all duration-150"
                        :class="[
                            'reader-font-' + readerPrefs.fontSize,
                            readerPrefs.fontFamily === 'serif' ? 'reader-font-serif' : 'reader-font-sans',
                            getLineHeightClass(),
                            readerPrefs.textAlign === 'justify' ? 'reader-align-justify' : 'reader-align-left',
                            readerPrefs.fontWeight === 'medium' ? 'reader-weight-medium' : ''
                        ]">
                        {!! $contentText !!}
                    </div>
                @endif

                {{-- 2. Document Attachment --}}
                @php
                    $docUrl = data_get($content, 'document_url') ?? data_get($content, 'document');
                @endphp

                @if ($docUrl)
                    <div class="{{ !empty($contentText) ? 'pt-8 border-t border-slate-100' : '' }}"
                        :class="{
                            '!border-[#263346]': isDark
                        }">
                        <h3
                            class="text-xs sm:text-sm font-bold text-slate-800 mb-4 flex items-center gap-2 uppercase tracking-wider"
                            :class="{
                                '!text-slate-400': isDark
                            }">
                            <i class="fas fa-paperclip text-slate-400"></i> Lampiran Pendukung
                        </h3>
                        <a href="{{ $docUrl }}" target="_blank"
                            class="group flex flex-col sm:flex-row sm:items-center p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-[#13416B]/5 hover:border-[#13416B]/30 transition-all duration-300 gap-4"
                            :class="{
                                '!bg-[#1E2837] !border-[#263346]': isDark
                            }">

                            <div class="flex items-center gap-4 flex-1">
                                <div
                                    class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 group-hover:border-[#13416B]/40 group-hover:shadow-sm transition-all"
                                    :class="{
                                        '!bg-[#263346] !border-[#334155]': isDark
                                    }">
                                    <i class="fas fa-file-pdf text-xl sm:text-2xl text-red-500"></i>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-[#13416B] transition-colors leading-tight"
                                        :class="{
                                            '!text-slate-200 group-hover:!text-white': isDark
                                        }">
                                        Unduh Dokumen Materi
                                    </p>
                                    <p class="text-[11px] sm:text-xs text-slate-500 mt-1"
                                        :class="{
                                            '!text-slate-400': isDark
                                        }">Format PDF/DOCX tersedia untuk
                                        dipelajari luring.</p>
                                </div>
                            </div>

                            <div
                                class="w-full sm:w-10 h-10 rounded-xl sm:rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs sm:text-sm group-hover:text-white group-hover:bg-[#13416B] group-hover:border-[#13416B] transition-all gap-2"
                                :class="{
                                    '!bg-[#263346] !border-[#334155] !text-slate-300': isDark
                                }">
                                <i class="fas fa-download"></i> <span class="sm:hidden">Unduh Sekarang</span>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- Empty State --}}
                @if (empty($contentText) && !$docUrl)
                    <div
                        class="text-center py-12 sm:py-16 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200"
                        :class="{
                            '!bg-[#1E2837] !border-[#263346]': isDark
                        }">
                        <div
                            class="w-16 h-16 sm:w-20 sm:h-20 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow-sm"
                            :class="{
                                '!bg-[#263346] !border-[#334155]': isDark
                            }">
                            <i class="fas fa-tools text-2xl sm:text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800"
                            :class="{
                                '!text-slate-100': isDark
                            }">Materi Sedang Disiapkan</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-sm mx-auto"
                            :class="{
                                '!text-slate-400': isDark
                            }">Tutor belum mengunggah konten
                            bacaan, video, maupun dokumen lampiran untuk modul ini.</p>
                    </div>
                @endif

                {{-- Penanda akhir konten --}}
                <div x-ref="endSentinel" class="h-px w-full" aria-hidden="true"></div>
            </div>

            {{-- Action Footer --}}
            <div data-tour="aksi"
                class="px-4 py-4 sm:px-10 sm:py-6 border-t border-slate-100 bg-slate-50 flex flex-col-reverse sm:flex-row justify-between items-center gap-2.5 sm:gap-4 reader-footer transition-colors duration-200"
                :class="{
                    '!bg-[#131A26] !border-[#263346]': isDark
                }">
                <a href="{{ route('user.course.my-course.detail', $slug) }}"
                    class="w-full sm:w-auto text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors text-center py-2.5 sm:py-3 px-3.5 sm:px-5 border border-slate-200 sm:border-slate-300 rounded-xl bg-white sm:bg-transparent"
                    :class="{
                        '!text-slate-300 sm:!bg-transparent !border-[#263346] !bg-[#1E2837]': isDark
                    }">
                    <i class="fas fa-list mr-1.5"></i> Daftar Modul
                </a>

                @if (!data_get($content, 'is_completed'))
                    <button type="button" x-show="!canComplete" @click="scrollToEnd()"
                        class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold text-slate-500 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center gap-1.5 sm:gap-2 hover:bg-slate-200 transition-colors"
                        :class="{
                            '!bg-[#1E2837] !border-[#263346] !text-slate-300': isDark
                        }">
                        <i class="fas fa-info-circle text-[11px] sm:text-xs"></i>
                        <span
                            x-text="(!reachedEnd ? 'Baca materi hingga akhir' : '') + (!reachedEnd && !videoCompleted ? ' & ' : '') + (!videoCompleted ? 'Tonton video materi minimal 90%' : '') + ' untuk lanjut'"></span>
                    </button>

                    <form action="{{ route('user.course.content.complete', ['content' => data_get($content, 'id')]) }}"
                        method="POST" class="w-full sm:w-auto m-0" style="display: none;" x-show="canComplete"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0" @submit="submitting = true">
                        @csrf
                        <button type="submit" :disabled="submitting"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm flex items-center justify-center gap-1.5 sm:gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <i class="fas fa-check-circle text-xs sm:text-base" x-show="!submitting"></i>
                            <i class="fas fa-spinner fa-spin text-xs sm:text-base" x-show="submitting"
                                style="display: none;"></i>
                            <span x-text="submitting ? 'Menyimpan...' : 'Tandai Selesai'"></span>
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span
                            class="completed-pop flex-1 sm:flex-initial px-3 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-emerald-600 border border-emerald-600 rounded-xl text-center flex items-center justify-center gap-1.5 sm:gap-2 shadow-sm cursor-default select-none">
                            <i class="fas fa-check-double text-xs sm:text-base"></i> Selesai Dipelajari
                        </span>
                        @if (!empty($nextUrl) && $nextUrl !== route('user.course.my-course.detail', $slug))
                            <a href="{{ $nextUrl }}"
                                class="flex-1 sm:flex-initial px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold text-white bg-[#13416B] rounded-xl hover:bg-[#0f3354] transition-all shadow-sm flex items-center justify-center text-center whitespace-nowrap">
                                Lanjut
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
        <style>
            @keyframes completedPop {
                0% { transform: scale(0.94); opacity: 0.6; }
                60% { transform: scale(1.03); opacity: 1; }
                100% { transform: scale(1); }
            }

            .completed-pop {
                animation: completedPop 0.45s ease-out;
            }

            @include('lms::partials.quill-content-style')
            @include('lms::partials.reader-styles')
        </style>
    @endpush

    @push('scripts')
        @include('lms::user.course.partials.guide-assets')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.hljs) {
                    try { hljs.highlightAll(); } catch (e) {}
                }
                if (window.katex) {
                    document.querySelectorAll('.quill-content-render .ql-formula[data-value]').forEach(function (el) {
                        try { katex.render(el.getAttribute('data-value'), el, { throwOnError: false }); } catch (e) {}
                    });
                }
            });
        </script>
        <script>
            (function () {
                const PAGE = 'content_reader';
                const STEPS = [
                    {
                        element: '[data-tour="materi"]',
                        popover: {
                            title: 'Fokus pada materi',
                            description: 'Baca materi hingga akhir. Jika ada video, tonton minimal 90% agar syarat terpenuhi.',
                        },
                    },
                    {
                        element: '[data-tour="aksi"]',
                        popover: {
                            title: 'Tandai selesai',
                            description: 'Tombol aktif setelah semua syarat terpenuhi. Tekan untuk membuka topik berikutnya.',
                        },
                    },
                    {
                        popover: {
                            title: 'Selamat belajar!',
                            description: 'Selesaikan setiap topik untuk membuka modul dan evaluasi selanjutnya.',
                        },
                    },
                ];

                window.startContentReaderTour = function () {
                    window.SirenataTour.start(PAGE, STEPS);
                };

                document.addEventListener('DOMContentLoaded', function () {
                    window.SirenataTour.autoStart(PAGE, STEPS);
                });
            })();
        </script>
    @endpush
</x-dashboard::layouts.dashboard>