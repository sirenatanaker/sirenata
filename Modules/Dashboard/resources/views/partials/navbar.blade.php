<!-- Navbar berada di lapisan bawah Sidebar (z-40). -->
<nav class="fixed top-0 z-40 w-full bg-white border-b border-slate-200 transition-all duration-300"
    :class="sidebarOpen ? 'lg:pl-64' : 'pl-0'">

    <div
        class="px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between w-full gap-3 sm:gap-6 min-h-[72px] sm:min-h-[80px]">

        <!-- Bagian Kiri & Tengah (Toggle + Searchbar) -->
        <div class="flex items-center flex-1 gap-3 sm:gap-5">

            @if (auth()->check() && auth()->user()->hasRole('user'))
                <!-- Toggle Sidebar KHUSUS USER (Hanya di Desktop karena mobile pakai Bottom Nav) -->
                <button @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:inline-flex p-2.5 items-center justify-center rounded-xl cursor-pointer text-slate-500 transition-colors hover:bg-slate-100 hover:text-[#13416B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30 shrink-0">
                    <i class="fas fa-bars text-lg" aria-hidden="true"></i>
                </button>

                <!-- Searchbar AUTO-SUGGEST KHUSUS USER -->
                <div class="flex-1 w-full max-w-full relative" x-data="searchSuggest()" @click.outside="isOpen = false">
                    <div class="relative w-full group">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-2 pointer-events-none">
                            <i class="fas fa-search text-sm text-slate-400 transition-colors group-focus-within:text-[#13416B]" aria-hidden="true"></i>
                        </div>

                        <!-- Input Pencarian -->
                        <input type="text" x-model="query" @input.debounce.500ms="fetchResults"
                            @focus="if(query.length > 1) isOpen = true"
                            class="bg-slate-50 border border-slate-200 text-slate-900 text-[10px] sm:text-sm rounded-xl focus:ring-[#13416B] focus:border-[#13416B] block w-full ps-9 sm:ps-11 pe-8 sm:pe-10 py-2 sm:py-2.5 transition-colors"
                            placeholder="Cari kursus dan koleksi perpustakaan..." autocomplete="off">

                        <!-- Ikon Loading -->
                        <div x-show="isLoading" x-cloak
                            class="absolute inset-y-0 end-0 flex items-center pe-3 sm:pe-4 pointer-events-none">
                            <i class="fas fa-circle-notch fa-spin text-[#13416B] text-xs sm:text-sm" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Dropdown Hasil Pencarian -->
                    <div x-show="isOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1" x-cloak
                        class="absolute top-full inset-x-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden w-full max-h-[70vh] flex flex-col">

                        <div class="overflow-y-auto py-1 sm:py-2 hide-scrollbar">

                            <!-- State Kosong / Tidak Ditemukan -->
                            <template
                                x-if="!isLoading && enrolledCourses.length === 0 && availableCatalogs.length === 0 && libraries.length === 0 && query.length > 1">
                                <div class="p-6 text-center">
                                    <div class="mb-3 flex items-center justify-center text-slate-300">
                                        <i class="fas fa-search text-xl" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tidak ada hasil ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci yang lebih umum.</p>
                                </div>
                            </template>

                            <!-- Hasil: Kursus Terdaftar -->
                            <template x-if="enrolledCourses.length > 0">
                                <div>
                                    <div
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 border-y border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        Kursus Terdaftar</div>
                                    <ul>
                                        <template x-for="item in enrolledCourses">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 transition-colors border-l-2 border-transparent hover:border-[#13416B] group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold text-slate-800 truncate group-hover:text-[#13416B]"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
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
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 border-y border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        Katalog (Belum Terdaftar)</div>
                                    <ul>
                                        <template x-for="item in availableCatalogs">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 transition-colors border-l-2 border-transparent hover:border-[#13416B] group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold text-slate-800 truncate group-hover:text-[#13416B]"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
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
                                        class="px-3 sm:px-5 py-2 bg-slate-50/80 border-y border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        Buku / Perpustakaan</div>
                                    <ul>
                                        <template x-for="item in libraries">
                                            <li>
                                                <a :href="item.url"
                                                    class="flex items-center gap-3 px-3 sm:px-5 py-2 sm:py-3 hover:bg-slate-50 transition-colors border-l-2 border-transparent hover:border-[#13416B] group">
                                                    <div class="relative w-10 h-[50px] rounded-md overflow-hidden flex items-center justify-center shrink-0 border border-slate-200"
                                                        :class="!item.cover && item.color">
                                                        <img x-show="item.cover" :src="item.cover" :alt="item.title"
                                                            class="w-full h-full object-cover" loading="lazy">
                                                        <span x-show="!item.cover" x-text="item.initials"
                                                            class="text-white font-bold"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold text-slate-800 truncate group-hover:text-[#13416B]"
                                                            x-text="item.title"></p>
                                                        <p class="text-[10px] text-slate-500 truncate mt-0.5"
                                                            x-text="item.subtitle"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-xs text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
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
            @else
                <!-- Toggle Sidebar SEMUA ROLE LAIN (Muncul di Mobile & Desktop) -->
                <button @click="sidebarOpen = !sidebarOpen"
                    class="inline-flex p-2.5 items-center justify-center rounded-xl cursor-pointer text-slate-500 transition-colors hover:bg-slate-100 hover:text-[#13416B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#13416B]/30 shrink-0">
                    <i class="fas fa-bars text-lg" aria-hidden="true"></i>
                </button>
            @endif

        </div>

        <!-- Bagian Kanan (Notifikasi & Profil) -->
        @auth
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                
                <!-- Notifikasi LIVE (SSE) — Khusus Admin Provinsi & Admin Kab/Kota -->
                @if (auth()->user()->hasRole(['admin-province', 'admin-kab-kota']))
                    @include('dashboard::partials.notifications.dropdown')
                @endif

                <!-- Dropdown Profil -->
                <div class="relative shrink-0" x-data="{ open: false }">
                    <div class="flex items-center">
                        <button @click="open = !open"
                            class="rounded-full overflow-hidden focus:outline-none cursor-pointer border-2 border-transparent hover:border-[#13416B] transition-colors">
                            <img class="w-9 h-9 sm:w-11 sm:h-11 rounded-full object-cover"
                                src="https://ui-avatars.com/api/?name={{ auth()->user()->profile?->full_name ?? auth()->user()->name }}&background=13416b&color=fff"
                                alt="user">
                        </button>
                    </div>

                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute right-0 top-full mt-3 bg-white border border-slate-200 rounded-2xl shadow-xl w-64 z-50 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/80">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1">
                                {{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                        </div>
                        <ul class="p-2 text-sm text-slate-700 space-y-1">
                            <!-- Akses Cepat Profil & Dashboard -->
                            @if (auth()->user()->hasRole('super-admin'))
                                <li><a href="{{ route('super-admin.profile') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors"><i class="far fa-user w-4 shrink-0 text-center" aria-hidden="true"></i> Profil</a></li>
                               
                            @elseif(auth()->user()->hasRole('admin-pusat'))
                                <li><a href="{{ route('admin-pusat.profile') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors"><i class="far fa-user w-4 shrink-0 text-center" aria-hidden="true"></i> Profil</a></li>
                                
                            @elseif(auth()->user()->hasRole('admin-province'))
                               
                            @elseif(auth()->user()->hasRole('user'))
                                <li><a href="{{ route('user.profile') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors"><i class="far fa-user w-4 shrink-0 text-center" aria-hidden="true"></i> Profil Saya</a></li>
                            @endif

                            <li class="my-1 border-t border-slate-100"></li>

                            <!-- MENU BANTUAN DINAMIS -->
                            <li>
                                @if (auth()->user()->hasRole(['super-admin', 'admin-pusat']))
                                    <a href="{{ route('admin-pusat.help') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors">
                                        <i class="fas fa-question-circle w-4 shrink-0 text-center text-slate-400" aria-hidden="true"></i> Pusat Bantuan
                                    </a>
                                @elseif(auth()->user()->hasRole('admin-province'))
                                    <a href="{{ route('admin-province.help') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors">
                                        <i class="fas fa-question-circle w-4 shrink-0 text-center text-slate-400" aria-hidden="true"></i> Pusat Bantuan
                                    </a>
                                @elseif(auth()->user()->hasRole('admin-kab-kota'))
                                    <a href="{{ route('admin-kab-kota.help') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors">
                                        <i class="fas fa-question-circle w-4 shrink-0 text-center text-slate-400" aria-hidden="true"></i> Pusat Bantuan
                                    </a>
                                @else
                                    <a href="{{ route('user.help') }}"
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-[#13416B]/10 hover:text-[#13416B] font-medium transition-colors">
                                        <i class="fas fa-question-circle w-4 shrink-0 text-center text-slate-400" aria-hidden="true"></i> Pusat Bantuan
                                    </a>
                                @endif
                            </li>

                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full cursor-pointer items-center gap-3 p-2.5 rounded-xl text-red-600 hover:bg-red-50 font-bold transition-colors text-left">
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