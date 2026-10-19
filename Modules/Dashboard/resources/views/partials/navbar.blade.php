<!-- Navbar berada di lapisan bawah Sidebar (z-40). -->
<nav class="fixed top-0 z-40 w-full bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 transition-all duration-300"
    :class="sidebarOpen ? 'md:pl-64 pl-0' : 'md:pl-20 pl-0'">

    <div
        class="px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between w-full gap-3 sm:gap-6 min-h-[72px] sm:min-h-[80px]">

        <!-- Bagian Kiri & Tengah (Toggle + Searchbar) -->
        <div class="flex items-center flex-1 gap-3 sm:gap-5">

            <!-- Tombol Hamburger: Muncul di Navbar saat Sidebar Terbuka (semua role di desktop/tablet) atau di mobile (admin) -->
            @if (auth()->check() && auth()->user()->hasRole('user'))
                <button @click="typeof toggleSidebar === 'function' ? toggleSidebar() : (sidebarOpen = !sidebarOpen)"
                    type="button"
                    class="w-10 h-10 items-center justify-center rounded-xl cursor-pointer text-slate-600 dark:text-slate-300 transition-colors hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#13416B] dark:hover:text-white focus:outline-none shrink-0"
                    :class="sidebarOpen ? 'hidden md:inline-flex' : 'hidden'"
                    :title="sidebarOpen ? 'Sembunyikan menu' : 'Buka menu'"
                    aria-label="Toggle menu">
                    <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
                </button>
            @else
                <button @click="typeof toggleSidebar === 'function' ? toggleSidebar() : (sidebarOpen = !sidebarOpen)"
                    type="button"
                    class="w-10 h-10 items-center justify-center rounded-xl cursor-pointer text-slate-600 dark:text-slate-300 transition-colors hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#13416B] dark:hover:text-white focus:outline-none shrink-0"
                    :class="sidebarOpen ? 'inline-flex' : 'inline-flex md:hidden'"
                    :title="sidebarOpen ? 'Sembunyikan menu' : 'Buka menu'"
                    aria-label="Toggle menu">
                    <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
                </button>
            @endif

            <!-- Logo di Navbar: Hanya muncul di desktop/tablet ketika sidebar tertutup (mini), tidak muncul di mobile -->
            <div class="hidden items-center gap-2.5 shrink-0" :class="sidebarOpen ? 'md:hidden' : 'md:flex'">
                <a href="{{ route('landingpage.index') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto shrink-0 group-hover:scale-105 transition-transform dark:brightness-0 dark:invert" alt="logo">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight text-[#13416B] dark:text-white">SIRENATA</span>
                </a>
            </div>

            @if (auth()->check() && auth()->user()->hasRole('user'))

                <!-- Searchbar AUTO-SUGGEST KHUSUS USER -->
                <div class="flex-1 w-full max-w-full relative" x-data="searchSuggest()" @click.outside="isOpen = false">
                    <div class="relative w-full group">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-2 pointer-events-none">
                            <i class="fas fa-search text-sm text-slate-400 transition-colors group-focus-within:text-[#13416B]" aria-hidden="true"></i>
                        </div>

                        <!-- Input Pencarian -->
                        <input type="text" x-model="query" @input.debounce.500ms="fetchResults"
                            @focus="if(query.length > 1) isOpen = true"
                            class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400 text-[10px] sm:text-sm rounded-xl focus:ring-[#13416B] dark:focus:ring-white focus:border-[#13416B] dark:focus:border-white block w-full ps-9 sm:ps-11 pe-8 sm:pe-10 py-2 sm:py-2.5 transition-colors"
                            placeholder="Cari kursus dan koleksi perpustakaan..." autocomplete="off">

                        <!-- Ikon Loading -->
                        <div x-show="isLoading" x-cloak
                            class="absolute inset-y-0 end-0 flex items-center pe-3 sm:pe-4 pointer-events-none">
                            <i class="fas fa-circle-notch fa-spin text-[#13416B] dark:text-white text-xs sm:text-sm" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Dropdown Hasil Pencarian -->
                    <div x-show="isOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1" x-cloak
                        class="absolute top-full inset-x-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden w-full max-h-[70vh] flex flex-col">

                        <div class="overflow-y-auto py-1 sm:py-2 hide-scrollbar">

                            <!-- State Kosong / Tidak Ditemukan -->
                            <template
                                x-if="!isLoading && enrolledCourses.length === 0 && availableCatalogs.length === 0 && libraries.length === 0 && query.length > 1">
                                <div class="p-6 text-center">
                                    <div class="mb-3 flex items-center justify-center text-slate-300 dark:text-slate-600">
                                        <i class="fas fa-search text-xl" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-white">Tidak ada hasil ditemukan</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Coba gunakan kata kunci yang lebih umum.</p>
                                </div>
                            </template>

                            <!-- Hasil: Kursus Terdaftar -->
                            <template x-if="enrolledCourses.length > 0">
                                <div>
                                    <div
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 dark:bg-slate-900/40 border-y border-slate-100 dark:border-slate-700/60 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Kursus Terdaftar</div>
                                    <ul>
                                        <template x-for="item in enrolledCourses">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-l-2 border-transparent hover:border-[#13416B] dark:hover:border-white group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs md:text-sm font-bold text-slate-800 dark:text-white truncate group-hover:text-[#13416B] dark:group-hover:text-white"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                            <!-- Hasil: Katalog Kursus (Belum Terdaftar) -->
                            <template x-if="availableCatalogs.length > 0">
                                <div>
                                    <div
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 dark:bg-slate-900/40 border-y border-slate-100 dark:border-slate-700/60 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Katalog (Belum Terdaftar)</div>
                                    <ul>
                                        <template x-for="item in availableCatalogs">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-l-2 border-transparent hover:border-[#13416B] dark:hover:border-white group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs md:text-sm font-bold text-slate-800 dark:text-white truncate group-hover:text-[#13416B] dark:group-hover:text-white"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                            <!-- Hasil: Perpustakaan / Buku -->
                            <template x-if="libraries.length > 0">
                                <div>
                                    <div
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 dark:bg-slate-900/40 border-y border-slate-100 dark:border-slate-700/60 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Buku / Perpustakaan</div>
                                    <ul>
                                        <template x-for="item in libraries">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-l-2 border-transparent hover:border-[#13416B] dark:hover:border-white group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs md:text-sm font-bold text-slate-800 dark:text-white truncate group-hover:text-[#13416B] dark:group-hover:text-white"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>

                <!-- SCRIPT ALPINE AJAX -->
                <script>
                    function searchSuggest() {
                        return {
                            query: '',
                            isOpen: false,
                            isLoading: false,
                            enrolledCourses: [],
                            availableCatalogs: [],
                            libraries: [],
                            fetchResults() {
                                if (this.query.trim().length < 2) {
                                    this.isOpen = false;
                                    this.enrolledCourses = [];
                                    this.availableCatalogs = [];
                                    this.libraries = [];
                                    return;
                                }

                                this.isLoading = true;
                                this.isOpen = true;

                                fetch(`{{ route('user.search.suggest') }}?q=${encodeURIComponent(this.query)}`, {
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        this.enrolledCourses = data.enrolled_courses || [];
                                        this.availableCatalogs = data.available_catalogs || [];
                                        this.libraries = data.libraries || [];
                                        this.isLoading = false;
                                    })
                                    .catch(err => {
                                        console.error('Pencarian gagal:', err);
                                        this.isLoading = false;
                                    });
                            }
                        }
                    }
                </script>
            @endif

        </div>

        <!-- Bagian Kanan (Notifikasi & Profil) -->
        @auth
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                
                <!-- Notifikasi In-App (User, Admin Provinsi, & Admin Kab/Kota) -->
                @if (auth()->user()->hasRole(['user', 'admin-province', 'admin-kab-kota']))
                    @include('dashboard::partials.notifications.dropdown')
                @endif

                <!-- Dropdown Profil -->
                <div class="relative shrink-0" x-data="{
                    open: false,
                    isDarkMode: window.isDarkMode ? window.isDarkMode() : false,
                    toggleDarkMode() {
                        if (window.toggleDarkMode) {
                            this.isDarkMode = window.toggleDarkMode();
                        }
                    }
                }" @dark-mode-toggled.window="isDarkMode = $event.detail.isDark">
                    <div class="flex items-center gap-2">
                        <!-- Tombol Cepat Dark Mode Navbar (Role Admin) -->
                        @if (!auth()->user()->hasRole('user'))
                            <button type="button" @click="toggleDarkMode()"
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[#13416B] dark:hover:text-white transition-colors shrink-0"
                                :title="isDarkMode ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'">
                                <i class="fas fa-moon text-sm sm:text-base text-slate-500 dark:text-slate-400" x-show="!isDarkMode"></i>
                                <i class="fas fa-sun text-sm sm:text-base text-slate-400 dark:text-slate-300" x-show="isDarkMode"></i>
                            </button>
                        @endif

                        <button @click="open = !open"
                            class="rounded-full overflow-hidden focus:outline-none cursor-pointer border-2 border-transparent hover:border-[#13416B] transition-colors">
                            <img class="w-9 h-9 sm:w-11 sm:h-11 rounded-full object-cover"
                                src="https://ui-avatars.com/api/?name={{ auth()->user()->profile?->full_name ?? auth()->user()->name }}&background=13416b&color=fff"
                                alt="user">
                        </button>
                    </div>

                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute right-0 top-full mt-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl w-64 z-50 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/80 dark:bg-slate-900/40">
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">
                                {{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                        </div>
                        <ul class="p-2 text-sm text-slate-700 dark:text-slate-300 space-y-1">
                            @if (auth()->user()->hasRole('user'))
                                <!-- Navigasi Khusus User: Informasi Saya, Sertifikat Saya, Pengaturan -->
                                <li>
                                    <a href="{{ route('user.profile') }}#personal"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-[#13416B]/10 hover:text-[#13416B] dark:hover:bg-slate-700 dark:hover:text-white font-medium transition-colors">
                                        <i class="fas fa-user-circle w-4 shrink-0 text-center text-slate-400 dark:text-slate-500" aria-hidden="true"></i> Informasi Saya
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.profile') }}#certificates"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-[#13416B]/10 hover:text-[#13416B] dark:hover:bg-slate-700 dark:hover:text-white font-medium transition-colors">
                                        <i class="fas fa-award w-4 shrink-0 text-center text-slate-400 dark:text-slate-500" aria-hidden="true"></i> Sertifikat Saya
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.profile') }}#pengaturan"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-[#13416B]/10 hover:text-[#13416B] dark:hover:bg-slate-700 dark:hover:text-white font-medium transition-colors">
                                        <i class="fas fa-sliders-h w-4 shrink-0 text-center text-slate-400 dark:text-slate-500" aria-hidden="true"></i> Pengaturan
                                    </a>
                                </li>
                            @else
                                <!-- Tombol Ganti Dark Mode di User Dropdown (Role Admin) -->
                                <li>
                                    <button type="button" @click="toggleDarkMode()"
                                        class="flex w-full cursor-pointer items-center justify-between p-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-[#13416B]/10 hover:text-[#13416B] dark:hover:bg-slate-700 dark:hover:text-white font-medium transition-colors text-left group">
                                        <span class="flex items-center gap-3">
                                            <i class="fas fa-moon w-4 shrink-0 text-center text-slate-400 dark:text-slate-400" x-show="!isDarkMode" aria-hidden="true"></i>
                                            <i class="fas fa-sun w-4 shrink-0 text-center text-slate-400 dark:text-slate-400" x-show="isDarkMode" aria-hidden="true"></i>
                                            <span>Mode Gelap</span>
                                        </span>
                                        <!-- Saklar Mini Switch Toggle -->
                                        <div class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full border border-transparent transition-colors duration-200 ease-in-out"
                                            :class="isDarkMode ? 'bg-[#13416B] dark:bg-white' : 'bg-slate-200 dark:bg-slate-700'">
                                            <span class="pointer-events-none inline-block h-3.5 w-3.5 rounded-full bg-white dark:bg-slate-900 shadow-xs transform transition duration-200 ease-in-out"
                                                :class="isDarkMode ? 'translate-x-4.5' : 'translate-x-0.5'"></span>
                                        </div>
                                    </button>
                                </li>
                            @endif

                            <li class="my-1 border-t border-slate-100 dark:border-slate-700/60"></li>

                            <!-- MENU BANTUAN DINAMIS -->
                            <li>
                                <button type="button" @click="open = false; $dispatch('open-help-modal')"
                                    class="flex w-full cursor-pointer items-center gap-3 p-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-[#13416B]/10 hover:text-[#13416B] dark:hover:bg-slate-700 dark:hover:text-white font-medium transition-colors text-left">
                                    <i class="fas fa-question-circle w-4 shrink-0 text-center text-slate-400 dark:text-slate-500" aria-hidden="true"></i> Pusat Bantuan
                                </button>
                            </li>

                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full cursor-pointer items-center gap-3 p-2.5 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-bold transition-colors text-left">
                                        <i class="fas fa-sign-out-alt w-4 shrink-0 text-center" aria-hidden="true"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</nav>