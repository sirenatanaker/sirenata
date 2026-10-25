<ul
    class="dashboard-sidebar-items flex flex-row md:flex-col gap-1 sm:gap-1.5 md:gap-1.5 md:space-y-1 font-bold w-full h-full md:h-auto items-center md:items-stretch justify-around md:justify-start px-1 sm:px-2 md:px-0 transition-all duration-300"
    :class="sidebarOpen ? 'md:px-3' : 'md:px-2'">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('user.dashboard'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.dashboard') }}"
            title="Dashboard"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isDashboard
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isDashboard)
                    <!-- Solid House (Active) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                        <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                    </svg>
                @else
                    <!-- Outline House (Inactive) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                @endif
            </span>

            @if ($isDashboard)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Dashboard</span>
        </a>
    </li>

    <!-- Katalog -->
    @php $isKatalog = request()->routeIs('user.catalog.*'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.catalog.index') }}"
            title="Katalog Kursus"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isKatalog
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isKatalog)
                    <!-- Solid Book Open (Active) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                    </svg>
                @else
                    <!-- Outline Book Open (Inactive) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                @endif
            </span>

            @if ($isKatalog)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Katalog</span>
        </a>
    </li>

    <!-- Kursus Saya -->
    @php $isKursus = request()->routeIs('user.course.my-course*'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.course.my-course') }}"
            title="Kursus Saya"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isKursus
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isKursus)
                    <!-- Solid Academic Cap (Active) -->
                    <svg class="w-5 h-5 md:w-6.5 md:h-6.5 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.7 2.805a.75.75 0 01.6 0A60.65 60.65 0 0122.83 8.72a.75.75 0 01-.231 1.337 49.948 49.948 0 00-9.902 3.912l-.003.002c-.114.06-.24.09-.366.09a.75.75 0 01-.366-.09 49.95 49.95 0 00-9.902-3.912.75.75 0 01-.23-1.337A60.653 60.653 0 0111.7 2.805z" />
                        <path d="M13.06 15.473a48.455 48.455 0 017.623-2.669l.408 4.078a.75.75 0 01-.392.748c-2.483 1.343-5.32 2.12-8.324 2.12-3.004 0-5.84-.777-8.324-2.12a.75.75 0 01-.392-.748l.408-4.078a48.455 48.455 0 017.623 2.67c.435.166.906.25 1.378.25.472 0 .943-.084 1.378-.25l.004-.001z" />
                        <path d="M22.5 12.75a.75.75 0 01.75.75v3.81a.75.75 0 01-1.5 0V13.5a.75.75 0 01.75-.75z" />
                    </svg>
                @else
                    <!-- Outline Academic Cap (Inactive) -->
                    <svg class="w-6 h-6 md:w-6.5 md:h-6.5 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v6" />
                    </svg>
                @endif
            </span>

            @if ($isKursus)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Kursus Saya</span>
        </a>
    </li>

    <!-- Perpustakaan -->
    @php $isLibrary = request()->routeIs('user.library.index'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.library.index') }}"
            title="Perpustakaan"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isLibrary
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isLibrary)
                    <!-- Solid Bookmark (Active) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.58A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Bookmark (Inactive) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                    </svg>
                @endif
            </span>

            @if ($isLibrary)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Perpustakaan</span>
        </a>
    </li>

    <!-- Penghitung RTK -->
    @php $isKalkulator = request()->routeIs('user.kalkulator.sandbox'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.kalkulator.sandbox') }}" target="_blank"
            title="Penghitung RTK"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isKalkulator
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isKalkulator)
                    <!-- Solid Calculator (Active) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.5 2.5h11A2.5 2.5 0 0 1 20 5v14a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 19V5A2.5 2.5 0 0 1 6.5 2.5ZM7.75 5.5h8.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-8.5a.75.75 0 0 1-.75-.75v-1.5a.75.75 0 0 1 .75-.75ZM8 12m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0M12 12m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0M16 12m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0M8 16m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0M12 16m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0M16 16m-1.1 0a1.1 1.1 0 1 0 2.2 0a1.1 1.1 0 1 0-2.2 0" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Calculator (Inactive) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <rect x="4" y="2.5" width="16" height="19" rx="2.5" />
                        <rect x="7" y="5.5" width="10" height="3" rx="0.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01" />
                    </svg>
                @endif
            </span>

            @if ($isKalkulator)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Penghitung RTK</span>
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
        <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
            <a href="{{ route('user.tim-kerja.index') }}"
                title="Tim Kerja"
                class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                       /* Mobile Bottom Nav: Icon-only pill */
                       w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                       /* Tablet & Desktop Base */
                       md:active:scale-100 md:font-bold
                       {{ $isTimKerja
                           ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                           : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
                :class="sidebarOpen 
                    ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                    : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

                <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                    @if ($isTimKerja)
                        <!-- Solid Users (Active) -->
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 015.69 3.117C18.99 16.035 19.5 17.202 19.5 18.423v1.827H4.5v-1.827c0-1.221.51-2.388 1.81-3.306z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <!-- Outline Users (Inactive) -->
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    @endif
                </span>

                @if ($isTimKerja)
                    <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
                @endif

                <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                      :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Tim Kerja</span>
            </a>
        </li>
    @endif

    <!-- Profil Saya -->
    @php $isProfile = request()->routeIs('user.profile*'); @endphp
    <li class="flex-1 flex items-center justify-center md:flex-none md:w-full md:block">
        <a href="{{ route('user.profile') }}"
            title="Profil Saya"
            class="relative flex items-center justify-center transition-all duration-200 group active:scale-95
                   /* Mobile Bottom Nav: Icon-only pill */
                   w-10 h-10 sm:w-11 sm:h-11 rounded-xl
                   /* Tablet & Desktop Base */
                   md:active:scale-100 md:font-bold
                   {{ $isProfile
                       ? 'text-[#13416B] bg-[#13416B]/10 font-bold dark:bg-white/10 dark:text-sky-400'
                       : 'text-slate-500 md:text-slate-700 hover:text-[#13416B] hover:bg-slate-100/70 dark:text-slate-400 dark:md:text-slate-200 dark:hover:text-white dark:hover:bg-slate-800/60' }}"
            :class="sidebarOpen 
                ? 'md:w-full md:h-auto md:py-3.5 md:px-4 md:rounded-xl md:flex-row md:items-center md:justify-start md:gap-3.5' 
                : 'md:w-12 md:h-12 md:mx-auto md:p-0 md:rounded-xl md:flex-col md:items-center md:justify-center'">

            <span class="w-6 h-6 md:w-7 md:h-7 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isProfile)
                    <!-- Solid User (Active) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-[#13416B] dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline User (Inactive) -->
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-slate-500 md:text-slate-600 group-hover:text-[#13416B] dark:text-slate-400 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                @endif
            </span>

            @if ($isProfile)
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-[#13416B] dark:bg-sky-400 md:hidden"></span>
            @endif

            <span class="hidden text-base font-bold text-left leading-snug whitespace-nowrap"
                  :class="sidebarOpen ? 'md:inline-block' : 'md:hidden'">Profil Saya</span>
        </a>
    </li>
</ul>
