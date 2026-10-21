<x-dashboard::layouts.dashboard title="Profil Saya">

    <script>
        function userProfilePage() {
            return {
                tab: (window.location.hash === '#pengaturan' || window.location.hash === '#settings')
                    ? 'settings'
                    : ((window.location.hash === '#certificates' || window.location.hash === '#sertifikat')
                        ? 'certificates'
                        : 'personal'),
                isDark: window.isDarkMode ? window.isDarkMode() : false,
                readerPrefs: {
                    fontSize: 'normal',
                    fontFamily: 'sans',
                    lineHeight: '1.5',
                    textAlign: 'left',
                    paperTheme: 'white',
                    fontWeight: 'normal'
                },
                savedFeedback: false,
                feedbackTimer: null,
                showScrollTop: false,

                init() {
                    this.scrollToActiveTab();
                    if (window.location.hash) {
                        this.scrollDownToTabs(150);
                        this.scrollDownToTabs(400);
                    }

                    window.addEventListener('hashchange', () => {
                        const hash = window.location.hash;
                        if (hash === '#pengaturan' || hash === '#settings') {
                            this.tab = 'settings';
                        } else if (hash === '#certificates' || hash === '#sertifikat') {
                            this.tab = 'certificates';
                        } else if (hash === '#personal' || hash === '#informasi') {
                            this.tab = 'personal';
                        }
                        this.scrollToActiveTab();
                        this.scrollDownToTabs(50);
                        this.$nextTick(() => this.checkPreviewReached());
                    });

                    const saved = localStorage.getItem('sirenata_reading_prefs');
                    if (saved) {
                        try {
                            const parsed = JSON.parse(saved);
                            this.readerPrefs = Object.assign(this.readerPrefs, parsed);
                        } catch (e) {}
                    }

                    this.$watch('tab', (val) => {
                        this.scrollToActiveTab();
                        if (val !== 'settings') {
                            this.showScrollTop = false;
                        } else {
                            this.$nextTick(() => this.checkPreviewReached());
                        }
                    });

                    let scrollTicking = false;
                    window.addEventListener('scroll', () => {
                        if (!scrollTicking) {
                            window.requestAnimationFrame(() => {
                                this.checkPreviewReached();
                                scrollTicking = false;
                            });
                            scrollTicking = true;
                        }
                    }, { passive: true });

                    window.addEventListener('resize', () => {
                        this.checkPreviewReached();
                    }, { passive: true });
                },

                checkPreviewReached() {
                    if (this.tab !== 'settings') {
                        this.showScrollTop = false;
                        return;
                    }
                    const el = document.getElementById('preview-section');
                    if (!el) {
                        this.showScrollTop = false;
                        return;
                    }
                    const rect = el.getBoundingClientRect();
                    if (window.innerWidth < 1024) {
                        // Pada mobile/tablet, pratinjau berada di bawah controls
                        this.showScrollTop = rect.top <= (window.innerHeight * 0.85) && window.scrollY > 120;
                    } else {
                        // Pada desktop (2 kolom), muncul jika halaman ter-scroll ke bawah
                        this.showScrollTop = window.scrollY > 350;
                    }
                },

                setDarkMode(enabled) {
                    if (window.setDarkMode) {
                        window.setDarkMode(enabled);
                        this.isDark = enabled;
                        this.showSavedToast();
                    }
                },

                updatePref(key, value) {
                    this.readerPrefs[key] = value;
                    this.persist();
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
                    this.persist();
                    this.setDarkMode(false);
                },

                selectPaperTheme(theme) {
                    this.updatePref('paperTheme', theme);
                },

                scrollToActiveTab() {
                    this.$nextTick(() => {
                        const container = this.$refs.tabContainer;
                        const activeBtn = container?.querySelector(`[data-tab='${this.tab}']`);
                        if (activeBtn && container) {
                            const scrollLeft = activeBtn.offsetLeft - (container.clientWidth / 2) + (activeBtn.clientWidth / 2);
                            container.scrollTo({ left: Math.max(0, scrollLeft), behavior: 'smooth' });
                        }
                    });
                },

                scrollDownToTabs(delay = 50) {
                    setTimeout(() => {
                        const tabsEl = document.getElementById('profile-tabs-section');
                        if (tabsEl) {
                            const navbarOffset = window.innerWidth >= 640 ? 95 : 85;
                            const elementPosition = tabsEl.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - navbarOffset;
                            window.scrollTo({
                                top: Math.max(0, offsetPosition),
                                behavior: 'smooth'
                            });
                        }
                    }, delay);
                },

                handleTabSwitch(targetTab) {
                    this.tab = targetTab;
                    const hashTarget = targetTab === 'settings' ? 'pengaturan' : (targetTab === 'certificates' ? 'certificates' : 'personal');
                    if (window.location.hash !== '#' + hashTarget) {
                        try {
                            history.replaceState(null, null, '#' + hashTarget);
                        } catch (e) {
                            window.location.hash = hashTarget;
                        }
                    }
                    this.scrollToActiveTab();
                    this.scrollDownToTabs(50);
                },

                getLineHeightClass() {
                    const lh = this.readerPrefs.lineHeight;
                    if (lh === '1' || lh === '1.0') return 'reader-leading-1-0';
                    if (lh === '1.2') return 'reader-leading-1-2';
                    if (lh === '2' || lh === '2.0' || lh === 'loose') return 'reader-leading-2-0';
                    if (lh === '2.5') return 'reader-leading-2-5';
                    return 'reader-leading-1-5';
                },

                persist() {
                    localStorage.setItem('sirenata_reading_prefs', JSON.stringify(this.readerPrefs));
                    this.showSavedToast();
                },

                showSavedToast() {
                    this.savedFeedback = true;
                    if (this.feedbackTimer) clearTimeout(this.feedbackTimer);
                    this.feedbackTimer = setTimeout(() => {
                        this.savedFeedback = false;
                    }, 2000);
                }
            };
        }
    </script>

    <div class="p-0 sm:p-4 md:p-6" x-data="userProfilePage()" @dark-mode-toggled.window="isDark = $event.detail.isDark" x-on:switch-profile-tab.window="handleTabSwitch($event.detail.tab)">
        <!-- Breadcrumb -->
        <div class="mb-3 px-3 sm:px-0">
            <x-breadcrumb :items="[['label' => 'Profil Saya']]" />
        </div>

        <div class="px-3 sm:px-0">
            <x-validation-errors class="mb-2" />
        </div>

        <!-- Profile Header -->
        <div class="bg-white dark:bg-slate-800 rounded-md shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-4 sm:mb-6">
            <div class="relative h-36 sm:h-44 md:h-48">
                <img src="{{ asset('images/login.jpg') }}" alt="Profile Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/10 dark:bg-black/30"></div>
                <div class="absolute -bottom-12 sm:-bottom-16 md:-bottom-20 left-4 sm:left-6 md:left-10">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->profile?->full_name ?? $user->name) }}&background=6366f1&color=fff&size=160"
                            alt="Profile" class="w-24 h-24 sm:w-32 sm:h-32 md:w-36 md:h-36 rounded-full border-4 border-white dark:border-slate-800 shadow-md object-cover">
                    </div>
                </div>
            </div>

            <div class="pt-14 sm:pt-18 md:pt-22 px-4 sm:px-6 md:px-8 pb-4 sm:pb-6 text-left">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-1">
                    {{ $user->profile?->full_name ?? $user->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex flex-wrap items-center justify-start gap-4 sm:gap-2">
                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-slate-400"></i> {{ $user->email }}</span>
                    <span class="hidden md:inline text-slate-300 dark:text-slate-600">•</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-calendar text-slate-400"></i> Bergabung sejak {{ $user->created_at->translatedFormat('M Y') }}</span>
                </p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3.5 pt-4 sm:pt-5 max-w-2xl text-left">
                    <div class="p-2.5 sm:p-3.5 rounded-md bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 flex flex-col items-start">
                        <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide font-semibold mb-0.5 sm:mb-1">Kursus Diikuti</p>
                        <p class="text-sm sm:text-base md:text-lg font-bold text-slate-800 dark:text-white">{{ $stats['enrolled'] }} Kursus</p>
                    </div>
                    <div class="p-2.5 sm:p-3.5 rounded-md bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 flex flex-col items-start">
                        <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide font-semibold mb-0.5 sm:mb-1">Selesai</p>
                        <p class="text-sm sm:text-base md:text-lg font-bold text-slate-800 dark:text-white">{{ $stats['completed'] }} Kursus</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1 p-2.5 sm:p-3.5 rounded-md bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 flex flex-col items-start">
                        <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide font-semibold mb-0.5 sm:mb-1">Sertifikat</p>
                        <p class="text-sm sm:text-base md:text-lg font-bold text-[#13416B] dark:text-white">{{ $stats['certificates'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div id="profile-tabs-section" class="scroll-mt-20 sm:scroll-mt-24 bg-white dark:bg-slate-800 rounded-md shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div x-ref="tabContainer" class="flex gap-1 px-3 sm:px-6 pt-2.5 sm:pt-3.5 border-b border-slate-200 dark:border-slate-700 overflow-x-auto hide-scrollbar scroll-smooth">
                <button type="button" data-tab="personal" @click="tab = 'personal'; window.location.hash = 'personal'"
                    :class="tab === 'personal' ? 'text-[#13416B] dark:text-white border-[#13416B] dark:border-white' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-800 dark:hover:text-white'"
                    class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap cursor-pointer">
                    <i class="fas fa-user text-xs sm:text-sm"></i> Informasi Pribadi
                </button>
                <button type="button" data-tab="certificates" @click="tab = 'certificates'; window.location.hash = 'certificates'"
                    :class="tab === 'certificates' ? 'text-[#13416B] dark:text-white border-[#13416B] dark:border-white' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-800 dark:hover:text-white'"
                    class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap cursor-pointer">
                    <i class="fas fa-award text-xs sm:text-sm"></i> Sertifikat Saya
                    <span class="text-[10px] sm:text-xs font-bold bg-[#13416B]/10 dark:bg-white/10 text-[#13416B] dark:text-white rounded-full px-1.5 sm:px-2 py-0.5">{{ $stats['certificates'] }}</span>
                </button>
                <button type="button" data-tab="settings" @click="tab = 'settings'; window.location.hash = 'pengaturan'"
                    :class="tab === 'settings' ? 'text-[#13416B] dark:text-white border-[#13416B] dark:border-white' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-800 dark:hover:text-white'"
                    class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap cursor-pointer">
                    <i class="fas fa-gear text-xs sm:text-sm"></i> Pengaturan
                </button>
            </div>

            <div class="p-3.5 sm:p-5 lg:p-6">
                <!-- Tab: Personal -->
                <div x-show="tab === 'personal'" x-cloak>
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4 sm:mb-5">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Informasi Pribadi</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Kelola data diri akun Anda.</p>
                        </div>

                        @php $ssoUser = ! empty($user->siapkerja_id); @endphp
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-5 mb-3.5 sm:mb-5">
                            @if ($ssoUser)
                                <x-form.input name="full_name" label="Nama Lengkap" value="{{ $user->profile?->full_name ?? $user->name }}" disabled helper="Mengikuti nama akun SIAPKerja" class="rounded-md text-xs sm:text-sm" />
                            @else
                                <x-form.input name="full_name" label="Nama Lengkap" :required="true" value="{{ $user->profile?->full_name }}" class="rounded-md text-xs sm:text-sm" />
                            @endif
                            <x-form.input name="nik" label="NIK" value="{{ $user->profile?->nik }}" readonly class="bg-gray-100 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-md cursor-not-allowed text-xs sm:text-sm" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-5 mb-4 sm:mb-5">
                            <x-form.input name="email" label="Email" value="{{ $user->email }}" readonly helper="Mengikuti akun login" class="bg-gray-100 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-md cursor-not-allowed text-xs sm:text-sm" />
                            <x-form.input name="phone" label="Nomor Handphone" type="tel" inputmode="numeric" pattern="[0-9+]*" placeholder="08xxxx atau +628xxxx" value="{{ $user->profile?->phone }}" helper="Boleh dikosongkan" class="rounded-md text-xs sm:text-sm" />
                            <x-form.select name="gender" label="Gender" class="rounded-md text-xs sm:text-sm">
                                <option value="" disabled selected>Pilih Gender</option>
                                <option value="male"
                                    {{ old('gender', $user->profile?->gender) == 'male' ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="female"
                                    {{ old('gender', $user->profile?->gender) == 'female' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </x-form.select>
                            <x-form.input name="instansi" label="Kementerian/Lembaga" value="{{ $user->profile?->instansi }}" placeholder="Kementerian Ketenagakerjaan RI" class="rounded-md text-xs sm:text-sm" />
                            <x-form.input name="unit_kerja" label="Unit Kerja" value="{{ $user->profile?->unit_kerja }}" placeholder="SDM" class="rounded-md text-xs sm:text-sm" />
                            <x-form.input name="province_name" label="Provinsi" value="{{ $user->scopeArea?->province?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-md cursor-not-allowed text-xs sm:text-sm" />
                            <x-form.input name="regency_name" label="Kab/Kota" value="{{ $user->scopeArea?->regency?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-md cursor-not-allowed text-xs sm:text-sm" />
                        </div>

                        <div class="flex justify-end">
                            <x-button type="submit" variant="success" size="sm" class="rounded-md text-xs sm:text-sm px-4 py-2 font-bold cursor-pointer">
                                Simpan Perubahan
                            </x-button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Certificates -->
                <div x-show="tab === 'certificates'" x-cloak>
                    <div class="mb-4 sm:mb-5">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Sertifikat Saya</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Sertifikat kelulusan yang telah Anda peroleh dari modul kursus.</p>
                    </div>

                    @if ($certificates->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            @foreach ($certificates as $certificate)
                            <div class="flex flex-col p-3.5 sm:p-4 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-[#13416B]/40 hover:shadow-md transition-all">
                                <div class="flex items-start gap-2.5 sm:gap-3 mb-3">
                                    <div class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-md bg-[#13416B]/10 dark:bg-white/10 text-[#13416B] dark:text-white">
                                        <i class="fas fa-award text-base sm:text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white leading-snug line-clamp-2">{{ $certificate->name }}</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">{{ $certificate->pivot->certificate_code }}</p>
                                    </div>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-3">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Diterbitkan {{ \Carbon\Carbon::parse($certificate->pivot->certificate_issued_at)->translatedFormat('d M Y') }}
                                </p>
                                <div class="mt-auto flex gap-2">
                                    @if ($certificate->pivot->certificate_file)
                                        <a href="{{ Storage::url($certificate->pivot->certificate_file) }}" target="_blank" download
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-[#13416B] rounded-md hover:bg-[#0f3354] transition-colors">
                                            <i class="fas fa-download text-xs"></i> Unduh
                                        </a>
                                    @endif
                                    <a href="{{ route('user.course.my-course.detail', $certificate->slug) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-[#13416B] dark:text-white border border-[#13416B]/30 dark:border-white/30 rounded-md hover:bg-[#13416B]/5 dark:hover:bg-white/5 transition-colors">
                                        Lihat Kursus
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 sm:py-12 px-4 text-center bg-slate-50 dark:bg-slate-700/30 rounded-md border border-dashed border-slate-200 dark:border-slate-700">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center mx-auto mb-3 shadow-xs text-slate-300 dark:text-slate-500">
                                <i class="fas fa-award text-lg sm:text-xl"></i>
                            </div>
                            <h4 class="font-bold text-xs sm:text-sm md:text-base text-slate-800 dark:text-white">Belum ada sertifikat</h4>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm">Selesaikan kursus dan lulus evaluasinya untuk mendapatkan sertifikat kelulusan di sini.</p>
                            <a href="{{ route('user.course.my-course') }}"
                                class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#13416B] rounded-md hover:bg-[#0f3354] transition-colors">
                                Lihat Kursus Saya
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tab: Settings (Pengaturan Akun & Tampilan) -->
                <div x-show="tab === 'settings'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 lg:gap-8 items-start">
                        
                        <!-- Left: Settings Form Controls (7 cols on desktop, First on mobile) -->
                        <div class="lg:col-span-7 space-y-8 sm:space-y-6">
                            <!-- 1. Mode Tampilan Aplikasi (Dark Mode Seluruh Halaman) -->
                            <div>
                                <div class="flex items-center justify-between mb-1 mt-4">
                                    <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">Mode Tampilan (Tema Aplikasi)</label>
                                    <span class="text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-full transition-colors border"
                                        :class="isDark 
                                            ? 'bg-slate-700/60 text-slate-200 border-slate-600' 
                                            : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600'">
                                        <span x-text="isDark ? 'Mode Gelap Aktif' : 'Mode Terang Aktif'"></span>
                                    </span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2.5">Atur tampilan tema terang atau gelap untuk seluruh halaman SIRENATA.</p>

                                <!-- Saklar (Switch Toggle Card) -->
                                <div @click="setDarkMode(!isDark)"
                                    class="p-3 sm:p-3.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-between gap-3.5 transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-600 shadow-xs cursor-pointer group select-none">
                                    
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Calm Icon Badge -->
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md flex items-center justify-center shrink-0 transition-colors border"
                                            :class="isDark 
                                                ? 'bg-slate-700/50 border-slate-600/80 text-slate-200' 
                                                : 'bg-slate-100 border-slate-200 text-slate-500'">
                                            <i class="fas fa-moon text-xs sm:text-sm transition-transform duration-300" x-show="isDark"></i>
                                            <svg x-show="!isDark" class="w-4 h-4 sm:w-4.5 sm:h-4.5 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M12 2v2"></path>
                                                <path d="M12 20v2"></path>
                                                <path d="m4.93 4.93 1.41 1.41"></path>
                                                <path d="m17.66 17.66 1.41 1.41"></path>
                                                <path d="M2 12h2"></path>
                                                <path d="M20 12h2"></path>
                                                <path d="m6.34 17.66-1.41 1.41"></path>
                                                <path d="m19.07 4.93-1.41 1.41"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 leading-snug">
                                                <span x-text="isDark ? 'Mode Gelap (Dark Mode)' : 'Mode Terang (Light Mode)'"></span>
                                            </p>
                                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                                <span x-show="!isDark">Latar putih kontras bersih untuk penggunaan di siang hari.</span>
                                                <span x-show="isDark">Latar redup ramah mata untuk kenyamanan di malam hari.</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Switch Toggle (Saklar) -->
                                    <button type="button"
                                        role="switch"
                                        :aria-checked="isDark.toString()"
                                        @click.stop="setDarkMode(!isDark)"
                                        class="relative inline-flex h-6 w-11 sm:h-7 sm:w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#13416B]/20 dark:focus:ring-white/20"
                                        :class="isDark ? 'bg-[#13416B] dark:bg-slate-600' : 'bg-slate-200 dark:bg-slate-700'">
                                        <span class="sr-only">Toggle mode gelap</span>
                                        <span class="pointer-events-none flex h-5 w-5 sm:h-6 sm:w-6 items-center justify-center rounded-full bg-white shadow-sm transform ring-0 transition duration-200 ease-in-out"
                                            :class="isDark ? 'translate-x-5' : 'translate-x-0'">
                                            <i class="fas fa-moon text-[9px] sm:text-[10px] text-[#13416B] dark:text-slate-800" x-show="isDark"></i>
                                            <svg x-show="!isDark" class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M12 2v2"></path>
                                                <path d="M12 20v2"></path>
                                                <path d="m4.93 4.93 1.41 1.41"></path>
                                                <path d="m17.66 17.66 1.41 1.41"></path>
                                                <path d="M2 12h2"></path>
                                                <path d="M20 12h2"></path>
                                                <path d="m6.34 17.66-1.41 1.41"></path>
                                                <path d="m19.07 4.93-1.41 1.41"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Ukuran Huruf (Font Size) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">Ukuran Huruf</label>
                                    <span class="text-[11px] sm:text-xs font-semibold text-[#13416B] dark:text-white" x-text="{ 'sm': 'Kecil (14px)', 'normal': 'Standar (16px)', 'lg': 'Besar (18px)', 'xl': 'Sangat Besar (20px)' }[readerPrefs.fontSize]"></span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2.5">Atur skala besar huruf teks bacaan materi pembelajaran.</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-100 dark:bg-slate-700/50 p-1.5 rounded-md border border-slate-200 dark:border-slate-700">
                                    <!-- Sm -->
                                    <button type="button" @click="updatePref('fontSize', 'sm')"
                                        class="p-2 sm:p-2.5 rounded-md font-bold text-center transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer"
                                        :class="readerPrefs.fontSize === 'sm' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'">
                                        <span class="text-xs">Aa</span>
                                        <span class="text-[10px] sm:text-[11px] font-medium">Kecil (14px)</span>
                                    </button>

                                    <!-- Normal -->
                                    <button type="button" @click="updatePref('fontSize', 'normal')"
                                        class="p-2 sm:p-2.5 rounded-md font-bold text-center transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer"
                                        :class="readerPrefs.fontSize === 'normal' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'">
                                        <span class="text-xs sm:text-sm">Aa</span>
                                        <span class="text-[10px] sm:text-[11px] font-medium">Standar (16px)</span>
                                    </button>

                                    <!-- Lg -->
                                    <button type="button" @click="updatePref('fontSize', 'lg')"
                                        class="p-2 sm:p-2.5 rounded-md font-bold text-center transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer"
                                        :class="readerPrefs.fontSize === 'lg' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'">
                                        <span class="text-sm sm:text-base">Aa</span>
                                        <span class="text-[10px] sm:text-[11px] font-medium">Besar (18px)</span>
                                    </button>

                                    <!-- Xl -->
                                    <button type="button" @click="updatePref('fontSize', 'xl')"
                                        class="p-2 sm:p-2.5 rounded-md font-bold text-center transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer"
                                        :class="readerPrefs.fontSize === 'xl' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'">
                                        <span class="text-base sm:text-lg">Aa</span>
                                        <span class="text-[10px] sm:text-[11px] font-medium">Ekstra (20px)</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Jenis Huruf (Font Family) -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Jenis Huruf (Font Family)</label>
                                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2.5">Pilih tipe tipografi yang memudahkan pemahaman Anda.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                    <!-- Sans -->
                                    <button type="button" @click="updatePref('fontFamily', 'sans')"
                                        class="text-left p-3 sm:p-3.5 rounded-md border-2 transition-all flex flex-col justify-between font-sans cursor-pointer"
                                        :class="readerPrefs.fontFamily === 'sans' ? 'border-[#13416B] bg-[#13416B]/5 ring-1.5 ring-[#13416B]/20 dark:border-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-base sm:text-lg font-bold text-slate-800 dark:text-white">Aa Bb Gg 123</span>
                                            <i class="fas fa-check-circle text-xs sm:text-sm text-[#13416B] dark:text-white" x-show="readerPrefs.fontFamily === 'sans'"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100">Sans-Serif (Modern)</p>
                                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Karakter bersih, tajam dan ramah layar digital.</p>
                                        </div>
                                    </button>

                                    <!-- Serif -->
                                    <button type="button" @click="updatePref('fontFamily', 'serif')"
                                        class="text-left p-3 sm:p-3.5 rounded-md border-2 transition-all flex flex-col justify-between font-serif cursor-pointer"
                                        :class="readerPrefs.fontFamily === 'serif' ? 'border-[#13416B] bg-[#13416B]/5 ring-1.5 ring-[#13416B]/20 dark:border-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-base sm:text-lg font-bold text-slate-800 dark:text-white">Aa Bb Gg 123</span>
                                            <i class="fas fa-check-circle text-xs sm:text-sm text-[#13416B] dark:text-white" x-show="readerPrefs.fontFamily === 'serif'"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100">Serif (Buku & Naskah)</p>
                                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Karakter berkait ala buku fisik dan naskah regulasi.</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- 4. Jarak Baris & Mode Lebar -->
                            <div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                                    <!-- Line Height -->
                                    <div>
                                        <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Jarak Baris Paragraf</label>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2">Pilihan kerapatan teks antar baris.</p>
                                        <div class="grid grid-cols-5 gap-1 bg-slate-100 dark:bg-slate-700/50 p-1 sm:p-1.5 rounded-md border border-slate-200 dark:border-slate-700">
                                            <button type="button" @click="updatePref('lineHeight', '1.0')"
                                                class="py-1.5 sm:py-2 px-1 rounded-md text-[11px] sm:text-xs font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                                :class="(readerPrefs.lineHeight === '1.0' || readerPrefs.lineHeight === '1') ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                                title="Rapat (1.0)">
                                                <span>1.0</span>
                                                <span class="text-[9px] font-medium hidden sm:inline opacity-75">Rapat</span>
                                            </button>
                                            <button type="button" @click="updatePref('lineHeight', '1.2')"
                                                class="py-1.5 sm:py-2 px-1 rounded-md text-[11px] sm:text-xs font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                                :class="readerPrefs.lineHeight === '1.2' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                                title="Normal (1.2)">
                                                <span>1.2</span>
                                                <span class="text-[9px] font-medium hidden sm:inline opacity-75">Normal</span>
                                            </button>
                                            <button type="button" @click="updatePref('lineHeight', '1.5')"
                                                class="py-1.5 sm:py-2 px-1 rounded-md text-[11px] sm:text-xs font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                                :class="(readerPrefs.lineHeight === '1.5' || readerPrefs.lineHeight === 'normal' || !readerPrefs.lineHeight) ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                                title="Sedang (1.5)">
                                                <span>1.5</span>
                                                <span class="text-[9px] font-medium hidden sm:inline opacity-75">Sedang</span>
                                            </button>
                                            <button type="button" @click="updatePref('lineHeight', '2.0')"
                                                class="py-1.5 sm:py-2 px-1 rounded-md text-[11px] sm:text-xs font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                                :class="(readerPrefs.lineHeight === '2.0' || readerPrefs.lineHeight === '2' || readerPrefs.lineHeight === 'loose') ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                                title="Lebar (2.0)">
                                                <span>2.0</span>
                                                <span class="text-[9px] font-medium hidden sm:inline opacity-75">Lebar</span>
                                            </button>
                                            <button type="button" @click="updatePref('lineHeight', '2.5')"
                                                class="py-1.5 sm:py-2 px-1 rounded-md text-[11px] sm:text-xs font-bold text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                                :class="readerPrefs.lineHeight === '2.5' ? 'bg-white dark:bg-slate-800 text-[#13416B] dark:text-white shadow-xs ring-1 ring-slate-200 dark:ring-slate-600' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                                                title="Sangat Lebar (2.5)">
                                                <span>2.5</span>
                                                <span class="text-[9px] font-medium hidden sm:inline opacity-75 truncate max-w-full">S.Lebar</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Text Alignment -->
                                    <div>
                                        <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Perataan Teks Paragraf</label>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2">Arah susunan tepi teks materi.</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="updatePref('textAlign', 'left')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="(readerPrefs.textAlign === 'left' || !readerPrefs.textAlign) ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B] dark:border-white dark:text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <i class="fas fa-align-left text-xs"></i> Rata Kiri
                                            </button>
                                            <button type="button" @click="updatePref('textAlign', 'justify')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="readerPrefs.textAlign === 'justify' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B] dark:border-white dark:text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <i class="fas fa-align-justify text-xs"></i> Rata Kiri-Kanan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Warna Kertas Baca & Ketebalan Huruf -->
                            <div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                                    <!-- Paper Theme -->
                                    <div>
                                        <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Warna Kertas Baca</label>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2">Pilihan latar belakang bacaan.</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="selectPaperTheme('white')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="(readerPrefs.paperTheme === 'white' || !readerPrefs.paperTheme) ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B] dark:border-white dark:text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <span class="w-3.5 h-3.5 rounded-full bg-white border border-slate-300 shadow-2xs shrink-0"></span>
                                                <span>Putih Bersih</span>
                                            </button>
                                            <button type="button" @click="selectPaperTheme('sepia')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="readerPrefs.paperTheme === 'sepia' ? 'border-[#8C531B] bg-[#F8F1E5] text-[#4A3B2C] ring-1 ring-[#8C531B]/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <span class="w-3.5 h-3.5 rounded-full bg-[#F8F1E5] border border-[#D5C2A5] shadow-2xs shrink-0"></span>
                                                <span>Krem Sepia</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Font Weight -->
                                    <div>
                                        <label class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Ketebalan Huruf</label>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mb-2">Tingkat kepekatan teks bacaan.</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="updatePref('fontWeight', 'normal')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-normal text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="(readerPrefs.fontWeight === 'normal' || !readerPrefs.fontWeight) ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B] font-bold dark:border-white dark:text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <i class="fas fa-font text-xs"></i> Standar (400)
                                            </button>
                                            <button type="button" @click="updatePref('fontWeight', 'medium')"
                                                class="py-2 px-2.5 sm:py-2.5 sm:px-3 rounded-md border-2 text-[11px] sm:text-xs font-semibold text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                                :class="readerPrefs.fontWeight === 'medium' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B] font-bold dark:border-white dark:text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                                <i class="fas fa-bold text-xs"></i> Lebih Pekat (500)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Live Interactive Preview (5 cols on desktop, Below on mobile) -->
                        <div class="lg:col-span-5" id="preview-section">
                            <div class="sticky top-20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <i class="fas fa-eye text-[#13416B] dark:text-white"></i> Pratinjau Langsung
                                    </span>
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <span x-show="savedFeedback" x-transition.opacity.duration.300ms
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800 shadow-xs">
                                            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xs"></i> Tersimpan
                                        </span>
                                        <button type="button" @click="resetPrefs()"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[11px] sm:text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-md transition-colors cursor-pointer"
                                            title="Kembalikan semua preferensi ke standar">
                                            <i class="fas fa-undo-alt text-xs"></i> Reset Bawaan
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Reader Card Mockup -->
                                <div class="rounded-md border shadow-sm overflow-hidden transition-all duration-300 reader-container"
                                    :class="{
                                        'reader-theme-dark-sepia': isDark && readerPrefs.paperTheme === 'sepia',
                                        'reader-theme-dark': isDark && readerPrefs.paperTheme !== 'sepia',
                                        'reader-theme-sepia': !isDark && readerPrefs.paperTheme === 'sepia',
                                        'bg-white border-slate-200 text-slate-800': !isDark && readerPrefs.paperTheme !== 'sepia'
                                    }">

                                    <!-- Body Mock Content -->
                                    <div class="p-3.5 sm:p-4 lg:p-5 transition-all duration-300">
                                        <h4 class="font-extrabold leading-tight mb-2 sm:mb-3"
                                            :class="{
                                                'text-sm sm:text-base lg:text-lg': readerPrefs.fontSize === 'sm',
                                                'text-base sm:text-lg lg:text-xl': readerPrefs.fontSize === 'normal',
                                                'text-lg sm:text-xl lg:text-2xl': readerPrefs.fontSize === 'lg',
                                                'text-xl sm:text-2xl lg:text-3xl': readerPrefs.fontSize === 'xl',
                                                'font-serif': readerPrefs.fontFamily === 'serif',
                                                'font-sans': readerPrefs.fontFamily === 'sans'
                                            }">
                                            Prinsip Ergonomi & Keselamatan Kerja
                                        </h4>

                                        <div class="quill-content-render"
                                            :class="[
                                                'reader-font-' + readerPrefs.fontSize,
                                                readerPrefs.fontFamily === 'serif' ? 'reader-font-serif' : 'reader-font-sans',
                                                getLineHeightClass(),
                                                readerPrefs.textAlign === 'justify' ? 'reader-align-justify' : 'reader-align-left',
                                                readerPrefs.fontWeight === 'medium' ? 'reader-weight-medium' : ''
                                            ]">
                                            <p class="mb-2 sm:mb-2.5">
                                                Penerapan standar ergonomi dan kenyamanan visual di lingkungan belajar digital terbukti dapat menjaga fokus serta mengurangi ketegangan mata secara signifikan saat membaca materi berdurasi panjang.
                                            </p>

                                            <blockquote class="my-2 sm:my-2.5">
                                                Kenyamanan membaca adalah fondasi utama keberhasilan penyerapan materi pelatihan teknis.
                                            </blockquote>

                                            <p class="mb-2">
                                                Langkah-langkah yang dianjurkan dalam sesi belajar daring:
                                            </p>
                                            <ul class="list-disc pl-5 space-y-1 mb-2.5">
                                                <li>Gunakan pencahayaan ruangan yang cukup dan hindari silau layar.</li>
                                                <li>Sesuaikan ukuran huruf agar tidak perlu mendekatkan wajah ke monitor.</li>
                                                <li>Lakukan peregangan mata secara berkala dengan aturan 20-20-20.</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Bottom mock bar -->
                                    <div class="px-3.5 sm:px-4 py-2.5 border-t text-left text-[10px] sm:text-[11px] reader-card-footer flex items-center gap-2"
                                        :class="{
                                            'border-[#263346] bg-[#131A26] text-slate-400': isDark,
                                            'border-[#E6D9C5] bg-[#F3E8D7] text-[#8C7B68]': !isDark && readerPrefs.paperTheme === 'sepia',
                                            'border-slate-100 bg-slate-50 text-slate-500': !isDark && readerPrefs.paperTheme !== 'sepia'
                                        }">
                                        <i class="fas fa-circle-info shrink-0 text-xs"></i>
                                        <span>Tampilan materi kursus akan otomatis mengikuti format ini.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Scroll To Top Button (Muncul saat mencapai bagian preview) -->
        <button type="button"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            x-show="showScrollTop"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-6 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-6 scale-90"
            class="fixed bottom-24 right-5 sm:right-6 md:bottom-8 md:right-8 z-40 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-full bg-[#13416B] text-white hover:bg-[#0f3354] dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 shadow-xl shadow-[#13416B]/20 dark:shadow-black/50 border border-white/20 dark:border-slate-300 transition-all duration-200 cursor-pointer group active:scale-95"
            title="Kembali ke atas">
            <i class="fas fa-arrow-up text-xs sm:text-sm group-hover:-translate-y-0.5 transition-transform"></i>
            <span class="text-xs font-bold tracking-tight">Ke Atas</span>
        </button>
    </div>

    @push('styles')
        <style>
            @include('lms::partials.quill-content-style')
            @include('lms::partials.reader-styles')
        </style>
    @endpush

</x-dashboard::layouts.dashboard>
