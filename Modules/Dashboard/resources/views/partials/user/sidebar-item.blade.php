<ul
    class="flex flex-row lg:flex-col gap-1 sm:gap-2 lg:gap-1.5 lg:space-y-1 px-2 sm:px-4 lg:px-4 font-semibold w-full h-full lg:h-auto items-center lg:items-stretch justify-around lg:justify-start">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('user.dashboard'); @endphp
    <li class="flex-1 lg:flex-none">
        <a href="{{ route('user.dashboard') }}"
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-start px-1 sm:px-2 lg:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-300 group {{ $isDashboard ? 'text-[#13416B] font-bold lg:bg-[#13416B]/5' : 'text-slate-400 lg:text-slate-500 hover:text-[#13416B] lg:hover:bg-slate-50' }}">

            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <svg class="h-5 w-5 transition duration-200 {{ $isDashboard ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7H10v7H4a1 1 0 0 1-1-1z" /></svg>
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
                <svg class="h-5 w-5 transition duration-200 {{ $isKursus ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 10-10-5L2 10l10 5 10-5z" /><path d="M6 12v5c3.5 3 8.5 3 12 0v-5m4-2v6" /></svg>
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
                <svg class="h-5 w-5 transition duration-200 {{ $isKatalog ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 7v14m0-14C10.5 5.5 8 5 4 5v14c4 0 6.5.5 8 2m0-14c1.5-1.5 4-2 8-2v14c-4 0-6.5.5-8 2" /></svg>
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
                <svg class="h-5 w-5 transition duration-200 {{ $isLibrary ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-6-4-6 4z" /></svg>
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
                <svg class="h-5 w-5 transition duration-200 {{ $isKalkulator ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2" /><path d="M8 6h8m-8 5h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h.01M12 19h.01M16 19h.01" /></svg>
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
                    <svg class="h-5 w-5 transition duration-200 {{ $isTimKerja ? 'text-[#13416B]' : 'group-hover:text-[#13416B]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="10" cy="7" r="4" /><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                </span>
                <span
                    class="text-[10px] sm:text-xs lg:text-[15px] mt-1 sm:mt-1.5 lg:mt-0 lg:ms-3 text-center lg:text-left line-clamp-1">Tim
                    Kerja</span>
            </a>
        </li>
    @endif
</ul>