<ul
    class="flex flex-row lg:flex-col gap-1 sm:gap-2 lg:gap-1.5 lg:space-y-1 px-2 sm:px-4 lg:px-4 font-semibold w-full h-full lg:h-auto items-center lg:items-stretch justify-around lg:justify-start">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('user.dashboard'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.dashboard') }}"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isDashboard ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-home text-base sm:text-lg lg:text-base transition duration-200 {{ $isDashboard ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
            </span>
            <span
                class="text-[8px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Dashboard</span>
        </a>
    </li>

    <!-- Kursus Saya -->
    @php $isKursus = request()->routeIs('user.course.my-course*'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.course.my-course') }}"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isKursus ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-base sm:text-lg lg:text-base transition duration-200 {{ $isKursus ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
            </span>
            <span
                class="text-[8px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Kursus
                Saya</span>
        </a>
    </li>

     <!-- Katalog -->
    @php $isKatalog = request()->routeIs('user.catalog.*'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.catalog.index') }}"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isKatalog ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-book-open text-base sm:text-lg lg:text-base transition duration-200 {{ $isKatalog ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
            </span>
            <span
                class="text-[8px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Katalog</span>
        </a>
    </li>

    <!-- Perpustakaan -->
    @php $isLibrary = request()->routeIs('user.library.index'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.library.index') }}"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isLibrary ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-bookmark text-base sm:text-lg lg:text-base transition duration-200 {{ $isLibrary ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
            </span>
            <span
                class="text-[8px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Perpustakaan</span>
        </a>
    </li>

    <!-- Penghitung RTK -->
    @php $isKalkulator = request()->routeIs('user.kalkulator.sandbox'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.kalkulator.sandbox') }}" target="_blank"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isKalkulator ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-calculator text-base sm:text-lg lg:text-base transition duration-200 {{ $isKalkulator ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
            </span>
            <span
                class="text-[8px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1 flex justify-center lg:justify-start items-center gap-1">Penghitung RTK</span>
        </a>
    </li>

    <!-- Tim Kerja (Conditional) -->
    @php
        $userProjects = \Modules\Project\Models\Project::where('team_leader', auth()->id())
            ->orWhereJsonContains('team_members', auth()->id())
            ->exists();
        $isTimKerja = request()->routeIs('user.tim-kerja.*');
    @endphp
    @if ($userProjects)
        <li class="flex-1 lg:flex-none">
            <a href="{{ route('user.tim-kerja.index') }}"
                class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isTimKerja ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

                <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                    <i class="fas fa-users text-base sm:text-lg lg:text-base transition duration-200 {{ $isTimKerja ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}"></i>
                </span>
                <span
                    class="text-[10px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Tim
                    Kerja</span>
            </a>
        </li>
    @endif
</ul>