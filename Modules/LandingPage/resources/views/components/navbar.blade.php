<nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('landingpage.index') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="SIRENATA Logo" class="h-10 w-auto">
                <span class="text-xl font-bold" style="color: #13416B;">SIRENATA</span>
            </a>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}"
                        class="px-5 py-2.5 text-gray-700 hover:text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-all">
                        Masuk
                    </a>
                    @unless (session('logged_out'))
                        <a href="{{ config('services.siapkerja.register_url') }}"
                            class="px-5 py-2.5 text-white font-semibold rounded-lg shadow-md transition-all hover:opacity-90"
                            style="background-color: #13416B;">
                            Daftar
                        </a>
                    @endunless
                @endguest

                @auth
                    <div class="relative" x-data="{ open: false }">
                        <div class="flex items-center gap-x-5">
                            <h1>{{ auth()->user()->name }}</h1>
                            <button @click="open = !open"
                                class="rounded-full overflow-hidden focus:outline-none cursor-pointer">
                                <img class="w-8 h-8 rounded-full"
                                    src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=13416b&color=fff"
                                    alt="user">
                            </button>
                        </div>

                        <!-- Dropdown -->
                        <div x-show="open" @click.outside="open = false" x-transition x-cloak
                            class="absolute right-0 top-full mt-2 bg-gray-50 border border-slate-200 rounded-lg shadow-lg w-60 z-50">
                            <div class="px-4 py-3 border-2 border-b border-slate-200">
                                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            </div>
                            <ul class="p-2 text-sm">
                                @role('super-admin')
                                    <li>
                                        <a href="{{ route('super-admin.profile') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">Profil</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('super-admin.dashboard') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">
                                            Dashboard Superadmin
                                        </a>
                                    </li>
                                @endrole
                                @role('admin-pusat')
                                    <li>
                                        <a href="{{ route('admin-pusat.profile') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">Profil</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin-pusat.dashboard') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">
                                            Dashboard Admin Pusat
                                        </a>
                                    </li>
                                @endrole
                                @role('admin-province')
                                    <li>
                                        <a href="{{ route('admin-province.profile') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">Profil</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin-province.dashboard') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">
                                            Dashboard Admin Provinsi
                                        </a>
                                    </li>
                                @endrole
                                @role('admin-kab-kota')
                                    <li>
                                        <a href="{{ route('admin-kab-kota.profile') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">Profil</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin-kab-kota.dashboard') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">
                                            Dashboard Admin Kab/Kota
                                        </a>
                                    </li>
                                @endrole
                                @role('user')
                                    <li>
                                        <a href="{{ route('user.profile') }}"
                                            class="block p-2 rounded hover:bg-[#13416B]/30">Profil</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('user.dashboard') }}" class="block p-2 rounded hover:bg-[#13416B]/30">Dashboard
                                            User/Peserta</a>
                                    </li>
                                @endrole

                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit"
                                            class="flex w-full cursor-pointer items-center gap-2 rounded px-3 py-2 text-sm font-medium text-gray-700 hover:bg-[#13416B]/30 hover:text-[#13416B] transition text-left">
                                            Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
