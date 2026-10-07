@if(auth()->check() && auth()->user()->hasRole('user'))
    <!-- ========================================================= -->
    <!-- SIDEBAR KHUSUS USER (Menjadi Bottom Nav di Layar Mobile)  -->
    <!-- ========================================================= -->
    
    <!-- CSS Global Khusus untuk memberikan jarak aman konten bawah -->
    <style>
        @media (max-width: 1023px) {
            body { padding-bottom: 76px !important; }
        }
    </style>

    <aside
        class="fixed z-50 bg-white transition-all duration-300
               /* MOBILE & TABLET (Menjadi Bottom Navigation) */
               bottom-0 left-0 w-full h-[70px] sm:h-[76px] border-t border-slate-200 shadow-[0_-4px_15px_rgba(0,0,0,0.04)] translate-y-0
               /* DESKTOP (Kembali menjadi Left Sidebar) */
               lg:top-0 lg:bottom-auto lg:w-64 lg:h-full lg:border-e lg:border-t-0 lg:shadow-none"
        :class="sidebarOpen ? 'lg:translate-x-0' : 'lg:-translate-x-full'"
        @keydown.escape.window="sidebarOpen = false" x-cloak>
        
        <div class="h-full w-full lg:py-4 flex flex-row lg:flex-col overflow-hidden">

            <!-- Logo (HANYA MUNCUL DI DESKTOP) -->
            <div class="hidden lg:flex items-center justify-between px-6 mb-8 mt-2 shrink-0">
                <a href="{{ route('landingpage.index') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto" alt="logo">
                    <span class="ms-2 text-xl font-extrabold tracking-tight" style="color:#13416B;">SIRENATA</span>
                </a>
            </div>

            <!-- Menu Wrapper -->
            <div class="flex-1 w-full h-full lg:h-auto lg:overflow-y-auto overflow-x-auto scrollbar-hide flex items-center justify-around lg:justify-start lg:block pb-safe">
                @include('dashboard::partials.user.sidebar-item')
            </div>
        </div>
    </aside>

@else
    <!-- ========================================================= -->
    <!-- SIDEBAR NORMAL UNTUK ADMIN DLL (Tetap di Sisi Kiri)       -->
    <!-- ========================================================= -->

    <aside
        class="fixed top-0 left-0 z-50 w-64 h-screen bg-white border-r border-slate-200 transition-transform duration-300 shadow-sm"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        @keydown.escape.window="sidebarOpen = false" x-cloak>
        
        <div class="h-full w-full py-4 flex flex-col overflow-hidden">

            <!-- Logo (MUNCUL DI SEMUA RESOLUSI) -->
            <div class="flex items-center justify-between px-6 mb-8 mt-2 shrink-0">
                <a href="{{ route('landingpage.index') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto" alt="logo">
                    <span class="ms-2 text-xl font-extrabold tracking-tight" style="color:#13416B;">SIRENATA</span>
                </a>
                <!-- Tombol tutup opsional untuk layar kecil -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xl" aria-hidden="true"></i>
                </button>
            </div>

            <!-- Menu Wrapper -->
            <div class="flex-1 w-full overflow-y-auto scrollbar-hide flex flex-col px-3">
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
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity" 
         x-transition.opacity x-cloak>
    </div>
@endif