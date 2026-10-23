<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body x-data="{ 
          sidebarOpen: window.innerWidth >= 768 
              ? (localStorage.getItem('sidebar_collapsed') !== null 
                  ? localStorage.getItem('sidebar_collapsed') === 'false' 
                  : window.innerWidth >= 1024)
              : false,
          isTabletOrDesktop: window.innerWidth >= 768,
          currentWidth: window.innerWidth,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              // Hanya simpan state dan kirim event resize di layar desktop/tablet
              if (window.innerWidth >= 768) {
                  localStorage.setItem('sidebar_collapsed', (!this.sidebarOpen).toString());
                  setTimeout(() => {
                      window.dispatchEvent(new Event('resize'));
                  }, 310);
              }
          },
          checkResize() {
              // Abaikan jika lebar layar tidak benar-benar berubah (misal dari event resize buatan atau scroll)
              if (window.innerWidth === this.currentWidth) return;
              
              const wasTabletOrDesktop = this.isTabletOrDesktop;
              this.isTabletOrDesktop = window.innerWidth >= 768;
              this.currentWidth = window.innerWidth;
              
              // Hanya tutup sidebar jika pengguna mengubah ukuran layar dari desktop/tablet ke mobile
              if (wasTabletOrDesktop && !this.isTabletOrDesktop) {
                  this.sidebarOpen = false;
              }
          }
      }" 
      @resize.window="checkResize()" 
      class="bg-gray-50 min-h-screen">
      
    <!-- Navbar -->
    @include('dashboard::partials.navbar')

    <!-- Sidebar -->
    @include('dashboard::partials.sidebar')

    <!-- Main Content -->
    <main class="p-2 sm:p-0 mt-20 md:mt-18 transition-all duration-300" :class="sidebarOpen ? 'md:ml-64 ml-0' : 'md:ml-20 ml-0'">
        {{ $slot }}
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    {!! ToastMagic::scripts() !!}
    <x-dashboard::help-modal />
    @stack('scripts')
</body>

</html>