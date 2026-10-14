<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body x-data="{ 
          sidebarOpen: window.innerWidth >= 1024,
          wasDesktop: window.innerWidth >= 1024,
          checkResize() {
              let isDesktop = window.innerWidth >= 1024;
            
              if (this.wasDesktop !== isDesktop) {
                  this.sidebarOpen = isDesktop;
                  this.wasDesktop = isDesktop;
              }
          }
      }" 
      @resize.window="checkResize()" 
      class="bg-gray-50 min-h-screen">
      
    <!-- Navbar -->
    @include('dashboard::partials.navbar')
    
    <!-- Overlay (Mobile & Tablet) -->
    @include('dashboard::partials.overlay')

    <!-- Sidebar -->
    @include('dashboard::partials.sidebar')

    <!-- Main Content -->
    <main class="p-2 sm:p-0 mt-20 md:mt-18 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'">
        {{ $slot }}
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    {!! ToastMagic::scripts() !!}
    <x-dashboard::help-modal />
    @stack('scripts')
</body>

</html>