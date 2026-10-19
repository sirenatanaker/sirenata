<x-dashboard::layouts.dashboard title="Profil Saya">

    <div class="p-2 sm:p-6" x-data="{
        tab: (window.location.hash === '#pengaturan' || window.location.hash === '#settings')
            ? 'settings'
            : ((window.location.hash === '#certificates' || window.location.hash === '#sertifikat')
                ? 'certificates'
                : 'personal'),
        isDark: window.isDarkMode ? window.isDarkMode() : false,
        readerPrefs: {
            fontSize: 'normal',
            fontFamily: 'sans',
            lineHeight: 'normal',
            contentWidth: 'full'
        },
        savedFeedback: false,
        feedbackTimer: null,

        init() {
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash;
                if (hash === '#pengaturan' || hash === '#settings') {
                    this.tab = 'settings';
                } else if (hash === '#certificates' || hash === '#sertifikat') {
                    this.tab = 'certificates';
                } else if (hash === '#personal' || hash === '#informasi') {
                    this.tab = 'personal';
                }
            });

            const saved = localStorage.getItem('sirenata_reading_prefs');
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    this.readerPrefs = Object.assign(this.readerPrefs, parsed);
                } catch (e) {}
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
                lineHeight: 'normal',
                contentWidth: 'full'
            };
            this.persist();
            this.setDarkMode(false);
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
    }" @dark-mode-toggled.window="isDark = $event.detail.isDark">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[['label' => 'Profil Saya']]" />

        <x-validation-errors class="mb-2" />

        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="relative h-48">
                <img src="{{ asset('images/login.jpg') }}" alt="Profile Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -bottom-20 left-6 md:left-12">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->profile?->full_name ?? $user->name) }}&background=6366f1&color=fff&size=160"
                            alt="Profile" class="w-40 h-40 rounded-full border-4 border-white">
                    </div>
                </div>
            </div>

            <div class="pt-24 px-6 md:px-12 pb-6 text-left">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-1">
                    {{ $user->profile?->full_name ?? $user->name }}
                </h1>
                <p class="text-sm text-slate-500 flex flex-wrap items-center justify-start gap-2">
                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-slate-400"></i> {{ $user->email }}</span>
                    <span class="hidden md:inline text-slate-300">•</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-calendar text-slate-400"></i> Bergabung sejak {{ $user->created_at->translatedFormat('M Y') }}</span>
                </p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 pt-6 max-w-2xl text-left">
                    <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-layer-group text-slate-400"></i> Kursus Diikuti</p>
                        <p class="text-base sm:text-lg md:text-xl font-bold text-slate-800">{{ $stats['enrolled'] }} Kursus</p>
                    </div>
                    <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-check-double text-slate-400"></i> Selesai</p>
                        <p class="text-base sm:text-lg md:text-xl font-bold text-slate-800">{{ $stats['completed'] }} Kursus</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1 p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-award text-[#13416B]"></i> Sertifikat</p>
                        <p class="text-base sm:text-lg md:text-xl font-bold text-[#13416B]">{{ $stats['certificates'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex gap-1 px-4 sm:px-6 pt-4 border-b border-slate-200 overflow-x-auto">
                <button type="button" @click="tab = 'personal'; window.location.hash = 'personal'"
                    :class="tab === 'personal' ? 'text-[#13416B] border-[#13416B]' : 'text-slate-500 border-transparent hover:text-slate-800'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap">
                    <i class="fas fa-user"></i> Informasi Pribadi
                </button>
                <button type="button" @click="tab = 'certificates'; window.location.hash = 'certificates'"
                    :class="tab === 'certificates' ? 'text-[#13416B] border-[#13416B]' : 'text-slate-500 border-transparent hover:text-slate-800'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap">
                    <i class="fas fa-award"></i> Sertifikat Saya
                    <span class="text-xs font-bold bg-[#13416B]/10 text-[#13416B] rounded-full px-2 py-0.5">{{ $stats['certificates'] }}</span>
                </button>
                <button type="button" @click="tab = 'settings'; window.location.hash = 'pengaturan'"
                    :class="tab === 'settings' ? 'text-[#13416B] border-[#13416B]' : 'text-slate-500 border-transparent hover:text-slate-800'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap">
                    <i class="fas fa-sliders-h"></i> Pengaturan
                </button>
            </div>

            <div class="p-6 lg:p-8">
                <!-- Tab: Personal -->
                <div x-show="tab === 'personal'" x-cloak>
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-slate-900">Informasi Pribadi</h3>
                            <p class="text-sm text-slate-500">Kelola data diri akun Anda.</p>
                        </div>

                        @php $ssoUser = ! empty($user->siapkerja_id); @endphp
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            @if ($ssoUser)
                                <x-form.input name="full_name" label="Nama Lengkap" value="{{ $user->profile?->full_name ?? $user->name }}" disabled helper="Mengikuti nama akun SIAPKerja" />
                            @else
                                <x-form.input name="full_name" label="Nama Lengkap" :required="true" value="{{ $user->profile?->full_name }}" />
                            @endif
                            <x-form.input name="nik" label="NIK" value="{{ $user->profile?->nik }}" readonly class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <x-form.input name="email" label="Email" value="{{ $user->email }}" readonly helper="Mengikuti akun login" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                            <x-form.input name="phone" label="Nomor Handphone" type="tel" inputmode="numeric" pattern="[0-9+]*" placeholder="08xxxx atau +628xxxx" value="{{ $user->profile?->phone }}" helper="Boleh dikosongkan" />
                            <x-form.select name="gender" label="Gender">
                                <option value="" disabled selected>Pilih Gender</option>
                                <option value="male"
                                    {{ old('gender', $user->profile?->gender) == 'male' ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="female"
                                    {{ old('gender', $user->profile?->gender) == 'female' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </x-form.select>
                            <x-form.input name="instansi" label="Kementerian/Lembaga" value="{{ $user->profile?->instansi }}" placeholder="Kementerian Ketenagakerjaan RI" />
                            <x-form.input name="unit_kerja" label="Unit Kerja" value="{{ $user->profile?->unit_kerja }}" placeholder="SDM" />
                            <x-form.input name="province_name" label="Provinsi" value="{{ $user->scopeArea?->province?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                            <x-form.input name="regency_name" label="Kab/Kota" value="{{ $user->scopeArea?->regency?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                        </div>

                        <div class="flex justify-end">
                            <x-button type="submit" variant="success" size="lg">
                                Simpan Perubahan
                            </x-button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Certificates -->
                <div x-show="tab === 'certificates'" x-cloak>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900">Sertifikat Saya</h3>
                        <p class="text-sm text-slate-500">Sertifikat kelulusan yang telah Anda peroleh dari modul kursus.</p>
                    </div>

                    @if ($certificates->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($certificates as $certificate)
                            <div class="flex flex-col p-5 rounded-2xl border border-slate-200 bg-white hover:border-[#13416B]/40 hover:shadow-md transition-all">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#13416B]/10 text-[#13416B]">
                                        <i class="fas fa-award text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 leading-snug line-clamp-2">{{ $certificate->name }}</h4>
                                        <p class="text-xs text-slate-500 mt-1 font-mono">{{ $certificate->pivot->certificate_code }}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mb-4">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Diterbitkan {{ \Carbon\Carbon::parse($certificate->pivot->certificate_issued_at)->translatedFormat('d M Y') }}
                                </p>
                                <div class="mt-auto flex gap-2">
                                    @if ($certificate->pivot->certificate_file)
                                        <a href="{{ Storage::url($certificate->pivot->certificate_file) }}" target="_blank" download
                                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-lg hover:bg-[#0f3354] transition-colors">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    @endif
                                    <a href="{{ route('user.course.my-course.detail', $certificate->slug) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-[#13416B] border border-[#13416B]/30 rounded-lg hover:bg-[#13416B]/5 transition-colors">
                                        Lihat Kursus
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-14 px-4 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <div class="w-16 h-16 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <i class="fas fa-award text-2xl text-slate-300"></i>
                            </div>
                            <h4 class="font-bold text-slate-800">Belum ada sertifikat</h4>
                            <p class="text-sm text-slate-500 mt-2 max-w-sm">Selesaikan kursus dan lulus evaluasinya untuk mendapatkan sertifikat kelulusan di sini.</p>
                            <a href="{{ route('user.course.my-course') }}"
                                class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-lg hover:bg-[#0f3354] transition-colors">
                                Lihat Kursus Saya
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tab: Settings (Pengaturan Akun & Tampilan) -->
                <div x-show="tab === 'settings'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- Left: Settings Form Controls (7 cols) -->
                        <div class="lg:col-span-7 space-y-7">
                            <!-- 1. Mode Tampilan Aplikasi (Dark Mode Seluruh Halaman) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Mode Tampilan (Tema Aplikasi)</label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Atur tampilan tema terang atau gelap untuk seluruh halaman SIRENATA.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Light Mode -->
                                    <button type="button" @click="setDarkMode(false)"
                                        class="text-left p-4 rounded-xl border-2 transition-all flex flex-col justify-between"
                                        :class="!isDark ? 'border-[#13416B] bg-[#13416B]/5 ring-2 ring-[#13416B]/20 dark:border-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center">
                                                <i class="fas fa-sun text-amber-500 text-sm"></i>
                                            </div>
                                            <i class="fas fa-check-circle text-base text-[#13416B] dark:text-white" x-show="!isDark"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800 dark:text-slate-100">Mode Terang</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latar putih kontras tinggi untuk penggunaan di siang hari.</p>
                                        </div>
                                    </button>

                                    <!-- Dark Mode -->
                                    <button type="button" @click="setDarkMode(true)"
                                        class="text-left p-4 rounded-xl border-2 transition-all flex flex-col justify-between"
                                        :class="isDark ? 'border-[#13416B] bg-[#13416B]/5 ring-2 ring-[#13416B]/20 dark:border-white dark:bg-white/10 dark:ring-white/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center">
                                                <i class="fas fa-moon text-white text-sm"></i>
                                            </div>
                                            <i class="fas fa-check-circle text-base text-[#13416B] dark:text-white" x-show="isDark"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800 dark:text-slate-100">Mode Gelap (Dark Mode)</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latar gelap redup untuk kenyamanan mata di malam hari.</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Ukuran Huruf (Font Size) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-bold text-slate-800">Ukuran Huruf</label>
                                    <span class="text-xs font-semibold text-[#13416B]" x-text="{ 'sm': 'Kecil (14px)', 'normal': 'Standar (16px)', 'lg': 'Besar (18px)', 'xl': 'Sangat Besar (20px)' }[readerPrefs.fontSize]"></span>
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Atur skala besar huruf teks bacaan materi pembelajaran.</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                                    <!-- Sm -->
                                    <button type="button" @click="updatePref('fontSize', 'sm')"
                                        class="p-2.5 rounded-xl font-bold text-center transition-all flex flex-col items-center justify-center gap-1"
                                        :class="readerPrefs.fontSize === 'sm' ? 'bg-white text-[#13416B] shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'">
                                        <span class="text-xs">Aa</span>
                                        <span class="text-[11px] font-medium">Kecil (14px)</span>
                                    </button>

                                    <!-- Normal -->
                                    <button type="button" @click="updatePref('fontSize', 'normal')"
                                        class="p-2.5 rounded-xl font-bold text-center transition-all flex flex-col items-center justify-center gap-1"
                                        :class="readerPrefs.fontSize === 'normal' ? 'bg-white text-[#13416B] shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'">
                                        <span class="text-sm">Aa</span>
                                        <span class="text-[11px] font-medium">Standar (16px)</span>
                                    </button>

                                    <!-- Lg -->
                                    <button type="button" @click="updatePref('fontSize', 'lg')"
                                        class="p-2.5 rounded-xl font-bold text-center transition-all flex flex-col items-center justify-center gap-1"
                                        :class="readerPrefs.fontSize === 'lg' ? 'bg-white text-[#13416B] shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'">
                                        <span class="text-base">Aa</span>
                                        <span class="text-[11px] font-medium">Besar (18px)</span>
                                    </button>

                                    <!-- Xl -->
                                    <button type="button" @click="updatePref('fontSize', 'xl')"
                                        class="p-2.5 rounded-xl font-bold text-center transition-all flex flex-col items-center justify-center gap-1"
                                        :class="readerPrefs.fontSize === 'xl' ? 'bg-white text-[#13416B] shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'">
                                        <span class="text-lg">Aa</span>
                                        <span class="text-[11px] font-medium">Ekstra (20px)</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Jenis Huruf (Font Family) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Jenis Huruf (Font Family)</label>
                                <p class="text-xs text-slate-500 mb-3">Pilih tipe tipografi yang memudahkan pemahaman Anda.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Sans -->
                                    <button type="button" @click="updatePref('fontFamily', 'sans')"
                                        class="text-left p-4 rounded-xl border-2 transition-all flex flex-col justify-between font-sans"
                                        :class="readerPrefs.fontFamily === 'sans' ? 'border-[#13416B] bg-[#13416B]/5 ring-2 ring-[#13416B]/20' : 'border-slate-200 bg-white hover:border-slate-300'">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-bold text-slate-800">Aa Bb Gg 123</span>
                                            <i class="fas fa-check-circle text-sm text-[#13416B]" x-show="readerPrefs.fontFamily === 'sans'"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800">Sans-Serif (Modern)</p>
                                            <p class="text-xs text-slate-500 mt-0.5">Karakter bersih, tajam dan ramah layar digital.</p>
                                        </div>
                                    </button>

                                    <!-- Serif -->
                                    <button type="button" @click="updatePref('fontFamily', 'serif')"
                                        class="text-left p-4 rounded-xl border-2 transition-all flex flex-col justify-between font-serif"
                                        :class="readerPrefs.fontFamily === 'serif' ? 'border-[#13416B] bg-[#13416B]/5 ring-2 ring-[#13416B]/20' : 'border-slate-200 bg-white hover:border-slate-300'">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-bold text-slate-800">Aa Bb Gg 123</span>
                                            <i class="fas fa-check-circle text-sm text-[#13416B]" x-show="readerPrefs.fontFamily === 'serif'"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800">Serif (Buku & Naskah)</p>
                                            <p class="text-xs text-slate-500 mt-0.5">Karakter berkait ala buku fisik dan naskah regulasi.</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- 4. Jarak Baris & Mode Lebar -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Line Height -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-800 mb-1">Jarak Baris Paragraf</label>
                                    <p class="text-xs text-slate-500 mb-2.5">Kerapatan teks saat membaca.</p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="updatePref('lineHeight', 'normal')"
                                            class="flex-1 py-2.5 px-3 rounded-xl border-2 text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                                            :class="readerPrefs.lineHeight === 'normal' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B]' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                            <i class="fas fa-align-left text-xs"></i> Standar (1.75x)
                                        </button>
                                        <button type="button" @click="updatePref('lineHeight', 'loose')"
                                            class="flex-1 py-2.5 px-3 rounded-xl border-2 text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                                            :class="readerPrefs.lineHeight === 'loose' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B]' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                            <i class="fas fa-bars-staggered text-xs"></i> Renggang (2.2x)
                                        </button>
                                    </div>
                                </div>

                                <!-- Reading Width / Focus Mode -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-800 mb-1">Mode Lebar Baca</label>
                                    <p class="text-xs text-slate-500 mb-2.5">Panjang kolom teks pada layar.</p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="updatePref('contentWidth', 'full')"
                                            class="flex-1 py-2.5 px-3 rounded-xl border-2 text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                                            :class="readerPrefs.contentWidth === 'full' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B]' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                            <i class="fas fa-arrows-left-right text-xs"></i> Penuh
                                        </button>
                                        <button type="button" @click="updatePref('contentWidth', 'focus')"
                                            class="flex-1 py-2.5 px-3 rounded-xl border-2 text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                                            :class="readerPrefs.contentWidth === 'focus' ? 'border-[#13416B] bg-[#13416B]/5 text-[#13416B]' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                            <i class="fas fa-compress-alt text-xs"></i> Mode Fokus
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                                <i class="fas fa-shield-alt text-[#13416B] mt-0.5"></i>
                                <div class="text-xs text-slate-600 leading-relaxed">
                                    <p class="font-bold text-slate-800 mb-0.5">Tersimpan di Perangkat Ini</p>
                                    Pengaturan tampilan ini disimpan langsung di penyimpanan peramban (browser) Anda. Anda juga dapat mengubah tampilan kapan saja saat membaca materi melalui tombol <strong>Tampilan</strong> di pojok kanan atas materi.
                                </div>
                            </div>
                        </div>

                        <!-- Right: Live Interactive Preview (5 cols) -->
                        <div class="lg:col-span-5">
                            <div class="sticky top-6">
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <i class="fas fa-eye text-[#13416B] dark:text-white"></i> Pratinjau Langsung
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span x-show="savedFeedback" x-transition.opacity.duration.300ms
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800 shadow-xs">
                                            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xs"></i> Tersimpan
                                        </span>
                                        <button type="button" @click="resetPrefs()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer"
                                            title="Kembalikan semua preferensi ke standar">
                                            <i class="fas fa-undo-alt text-xs"></i> Reset Bawaan
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Reader Card Mockup -->
                                <div class="rounded-2xl border shadow-sm overflow-hidden transition-all duration-300 reader-container"
                                    :class="{
                                        'reader-theme-dark': isDark,
                                        'bg-white border-slate-200 text-slate-800': !isDark
                                    }">

                                    <!-- Top mock bar -->
                                    <div class="p-4 border-b flex items-center justify-between reader-card-header"
                                        :class="{
                                            'border-[#263346] bg-[#131A26]': isDark,
                                            'border-slate-100 bg-slate-50': !isDark
                                        }">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md border"
                                            :class="{
                                                'bg-white/10 text-white border-white/20': isDark,
                                                'bg-[#13416B]/10 text-[#13416B] border-[#13416B]/20': !isDark
                                            }">
                                            Topik Pembelajaran
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">Simulasi Materi</span>
                                    </div>

                                    <!-- Body Mock Content -->
                                    <div class="p-5 sm:p-6 transition-all duration-300"
                                        :class="{
                                            'max-w-md mx-auto': readerPrefs.contentWidth === 'focus'
                                        }">
                                        <h4 class="font-extrabold leading-tight mb-4"
                                            :class="{
                                                'text-lg': readerPrefs.fontSize === 'sm',
                                                'text-xl': readerPrefs.fontSize === 'normal',
                                                'text-2xl': readerPrefs.fontSize === 'lg',
                                                'text-3xl': readerPrefs.fontSize === 'xl',
                                                'font-serif': readerPrefs.fontFamily === 'serif',
                                                'font-sans': readerPrefs.fontFamily === 'sans'
                                            }">
                                            Prinsip Ergonomi & Keselamatan Kerja
                                        </h4>

                                        <div class="quill-content-render"
                                            :class="[
                                                'reader-font-' + readerPrefs.fontSize,
                                                readerPrefs.fontFamily === 'serif' ? 'reader-font-serif' : 'reader-font-sans',
                                                readerPrefs.lineHeight === 'loose' ? 'reader-leading-loose' : 'reader-leading-normal'
                                            ]">
                                            <p class="mb-3">
                                                Penerapan standar ergonomi dan kenyamanan visual di lingkungan belajar digital terbukti dapat menjaga fokus serta mengurangi ketegangan mata secara signifikan saat membaca materi berdurasi panjang.
                                            </p>

                                            <blockquote class="my-3">
                                                Kenyamanan membaca adalah fondasi utama keberhasilan penyerapan materi pelatihan teknis.
                                            </blockquote>

                                            <p class="mb-2">
                                                Langkah-langkah yang dianjurkan dalam sesi belajar daring:
                                            </p>
                                            <ul class="list-disc pl-5 space-y-1 mb-3">
                                                <li>Gunakan pencahayaan ruangan yang cukup dan hindari silau layar.</li>
                                                <li>Sesuaikan ukuran huruf agar tidak perlu mendekatkan wajah ke monitor.</li>
                                                <li>Lakukan peregangan mata secara berkala dengan aturan 20-20-20.</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Bottom mock bar -->
                                    <div class="p-3 border-t text-center text-[11px] reader-card-footer"
                                        :class="{
                                            'border-[#263346] bg-[#131A26] text-slate-400': isDark,
                                            'border-slate-100 bg-slate-50 text-slate-500': !isDark
                                        }">
                                        <i class="fas fa-check-circle mr-1"></i> Tampilan materi kursus akan otomatis mengikuti format ini.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @include('lms::partials.quill-content-style')
            @include('lms::partials.reader-styles')
        </style>
    @endpush

</x-dashboard::layouts.dashboard>
