<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light light-only">

<head>
    @include('partials.head', ['disableDarkMode' => true])

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-landingpage', 'resources/assets/sass/app.scss') }} --}}
</head>

<body class="bg-gray-50">
    <!-- Noise Overlay -->
    <div class="fixed inset-0 pointer-events-none z-[9999] opacity-[0.25] mix-blend-overlay"
        style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E&quot;);">
    </div>

    <!-- Global Layout Navbar -->
    <x-landingpage::navbar />

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Global Layout Footer & Scroll to Top -->
    <x-landingpage::footer />
    <x-landingpage::scroll-top />

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    {!! ToastMagic::scripts() !!}
    @stack('scripts')
    {{-- Vite JS --}}
    {{-- {{ module_vite('build-landingpage', 'resources/assets/js/app.js') }} --}}
</body>

</html>
