<nav x-data="{ isScrolled: false, mobileMenuOpen: false }" @scroll.window="isScrolled = (window.pageYOffset > 20)"
    :class="isScrolled ? 'bg-white/95 shadow-sm backdrop-blur-md' : 'bg-white/80 backdrop-blur-sm'"
    class="fixed top-0 left-0 right-0 z-[100] transition-all duration-300 border-b border-slate-200/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

        <a href="{{ route('landingpage.index') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="SIRENATA Logo" class="h-8 sm:h-10 w-auto">
            <span class="text-lg sm:text-xl font-extrabold tracking-tight" style="color: #13416B;">SIRENATA</span>
        </a>

        <!-- Menu Desktop -->
        <div class="hidden md:flex items-center gap-2">
            <a href="#features"
                class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">Fitur</a>
            <a href="#courses"
                class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">LMS</a>
            <a href="#faq"
                class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">FAQ</a>
            <a href="#cta"
                class="text-slate-600 font-medium px-4 py-2 rounded-full hover:text-[#13416B] hover:bg-[#13416B]/10 transition-colors">CTA</a>
        </div>

        <!-- Auth Buttons (Normal Landing Page) -->
        <div class="hidden md:flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}"
                    class="px-5 py-2 font-bold text-slate-700 hover:bg-slate-100 rounded-full transition-colors">Masuk</a>
                @unless (session('logged_out'))
                    <a href="{{ config('services.siapkerja.register_url') }}"
                        class="px-6 py-2.5 font-bold text-white rounded-full shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                        style="background-color: #13416B;">Daftar Gratis</a>
                @endunless
            @else
                <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                    class="px-6 py-2.5 font-bold text-white rounded-full shadow-md hover:shadow-lg transition-all"
                    style="background-color: #13416B;">Masuk</a>
            @endguest
        </div>

        <!-- Mobile Toggle -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 focus:outline-none">
            <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
            <i class="fas fa-times text-xl" x-show="mobileMenuOpen" x-cloak></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-collapse
        class="md:hidden bg-white border-t border-slate-100 shadow-xl absolute w-full">
        <div class="p-4 space-y-2">
            <a href="#features" @click="mobileMenuOpen = false"
                class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">Fitur</a>
            <a href="#courses" @click="mobileMenuOpen = false"
                class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">LMS</a>
            <a href="#faq" @click="mobileMenuOpen = false"
                class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">FAQ</a>
            <a href="#cta" @click="mobileMenuOpen = false"
                class="block px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-50 hover:text-[#13416B]">CTA</a>
            @guest
                <div class="pt-4 mt-2 border-t border-slate-100 flex gap-3">
                    <a href="{{ route('login') }}"
                        class="flex-1 text-center py-3 rounded-xl font-bold bg-slate-100 text-slate-700">Masuk</a>
                    @unless (session('logged_out'))
                        <a href="{{ config('services.siapkerja.register_url') }}" class="flex-1 text-center py-3 rounded-xl font-bold text-white"
                            style="background-color: #13416B;">Daftar</a>
                    @endunless
                </div>
            @else
                <a href="{{ route(auth()->user()->getRedirectRoute()) }}"
                    class="block border-t border-slate-100 pt-4 mt-2 text-center py-3 rounded-xl font-bold text-white"
                    style="background-color: #13416B;">Masuk</a>
            @endguest
        </div>
    </div>
</nav>
