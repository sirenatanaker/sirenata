@if(auth()->check() && auth()->user()->hasRole('user'))
    <!-- ========================================================= -->
    <!-- SIDEBAR KHUSUS USER (YouTube Style Desktop/Tablet Toggle) -->
    <!-- ========================================================= -->
    
    <!-- CSS Global Khusus untuk memberikan jarak aman konten bawah di layar HP/Mobile -->
    <style>
        @media (max-width: 767px) {
            body { padding-bottom: 76px !important; }
        }
    </style>

    <aside
        class="fixed z-50 bg-white transition-all duration-300
               /* MOBILE (Menjadi Bottom Navigation) */
               bottom-0 left-0 w-full h-[70px] sm:h-[76px] border-t border-slate-200 shadow-[0_-4px_15px_rgba(0,0,0,0.04)] translate-y-0
               /* TABLET & DESKTOP (Menjadi Left Sidebar YouTube Style) */
               md:top-0 md:bottom-auto md:h-full md:border-e md:border-t-0 md:shadow-none"
        :class="sidebarOpen ? 'md:w-64' : 'md:w-20'"
        @keydown.escape.window="sidebarOpen = false" x-cloak>
        
        <div class="h-full w-full flex flex-row md:flex-col overflow-hidden">

            <!-- Header Sidebar (HANYA MUNCUL DI TABLET & DESKTOP) -->
            <div class="hidden md:flex items-center h-[72px] sm:h-[80px] border-b border-slate-100 shrink-0 transition-all duration-300"
                 :class="sidebarOpen ? 'justify-start px-6' : 'justify-center px-4'">
                
                <!-- Hamburger Button (Hanya muncul saat sidebar tertutup di tablet & desktop) -->
                <button x-show="!sidebarOpen"
                    @click="typeof toggleSidebar === 'function' ? toggleSidebar() : (sidebarOpen = !sidebarOpen)"
                    type="button"
                    class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-600 hover:text-[#13416B] hover:bg-slate-100 transition-colors cursor-pointer shrink-0 focus:outline-none"
                    title="Buka menu">
                    <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
                </button>

                <!-- Logo & Brand Name (Hanya muncul saat sidebar terbuka di desktop/tablet) -->
                <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="flex items-center overflow-hidden whitespace-nowrap">
                    <a href="{{ route('landingpage.index') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto shrink-0 group-hover:scale-105 transition-transform dark:brightness-0 dark:invert" alt="logo">
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight text-[#13416B] dark:text-white">SIRENATA</span>
                    </a>
                </div>
            </div>

            <!-- Menu Wrapper -->
            <div class="flex-1 w-full h-full md:h-auto md:overflow-y-auto overflow-x-auto scrollbar-hide flex items-center justify-around md:justify-start md:block pb-safe md:py-3">
                @include('dashboard::partials.user.sidebar-item')
            </div>
        </div>
    </aside>

@else
    <!-- ========================================================= -->
    <!-- SIDEBAR ADMIN (YouTube Style Desktop/Tablet + Drawer Mobile) -->
    <!-- ========================================================= -->

    <aside
        class="fixed top-0 left-0 z-50 h-full bg-white border-r border-slate-200 transition-all duration-300 shadow-sm
               /* MOBILE: Drawer Slide-In */
               w-64 max-w-[80vw]
               /* TABLET & DESKTOP: YouTube Style (w-64 vs w-20) */
               md:max-w-none md:shadow-none"
        :class="[
            sidebarOpen ? 'translate-x-0 md:w-64' : '-translate-x-full md:translate-x-0 md:w-20'
        ]"
        @keydown.escape.window="sidebarOpen = false" x-cloak>
        
        <div class="h-full w-full flex flex-col overflow-hidden">

            <!-- Header Sidebar Desktop & Tablet (Hamburger Staggered + Logo) -->
            <div class="hidden md:flex items-center h-[72px] sm:h-[80px] border-b border-slate-100 shrink-0 transition-all duration-300"
                 :class="sidebarOpen ? 'justify-start px-6' : 'justify-center px-4'">
                
                <!-- Hamburger Button (Hanya muncul saat sidebar tertutup di tablet & desktop) -->
                <button x-show="!sidebarOpen"
                    @click="typeof toggleSidebar === 'function' ? toggleSidebar() : (sidebarOpen = !sidebarOpen)"
                    type="button"
                    class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-600 hover:text-[#13416B] hover:bg-slate-100 transition-colors cursor-pointer shrink-0 focus:outline-none"
                    title="Buka menu">
                    <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
                </button>

                <!-- Logo & Brand Name (Hanya muncul saat sidebar terbuka di desktop/tablet) -->
                <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="flex items-center overflow-hidden whitespace-nowrap">
                    <a href="{{ route('landingpage.index') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto shrink-0 group-hover:scale-105 transition-transform dark:brightness-0 dark:invert" alt="logo">
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight text-[#13416B] dark:text-white">SIRENATA</span>
                    </a>
                </div>
            </div>

            <!-- Header Sidebar Mobile (Drawer Header dengan Tombol Tutup X) -->
            <div class="flex md:hidden items-center justify-between px-5 h-[72px] border-b border-slate-100 shrink-0">
                <a href="{{ route('landingpage.index') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto shrink-0 dark:brightness-0 dark:invert" alt="logo">
                    <span class="text-lg font-extrabold tracking-tight text-[#13416B] dark:text-white">SIRENATA</span>
                </a>
                <button @click="sidebarOpen = false" type="button" aria-label="Tutup menu"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fas fa-times text-base" aria-hidden="true"></i>
                </button>
            </div>

            <!-- Menu Wrapper -->
            <div class="flex-1 w-full overflow-y-auto scrollbar-hide flex flex-col py-3">
                @role('super-admin')
                    @include('dashboard::partials.super-admin.sidebar-item')
                @endrole

                @role('admin-pusat')
                    @include('dashboard::partials.admin-pusat.sidebar-item')
                @endrole

                @role('admin-province')
                    @include('dashboard::partials.admin-province.sidebar-item')
                @endrole

                @role('admin-kab-kota')
                    @include('dashboard::partials.admin-kab-kota.sidebar-item')
                @endrole
            </div>
        </div>
    </aside>

    <!-- Overlay Gelap Mobile untuk menutup Sidebar Admin -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden transition-opacity" 
         x-transition.opacity x-cloak>
    </div>
@endif