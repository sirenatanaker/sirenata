<ul class="dashboard-sidebar-items flex flex-col gap-1.5 space-y-1 font-bold w-full transition-all duration-300"
    :class="sidebarOpen ? 'px-3' : 'px-2'">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('admin-pusat.dashboard'); @endphp
    <li>
        <a href="{{ route('admin-pusat.dashboard') }}"
            title="Dashboard"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isDashboard
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isDashboard)
                    <!-- Solid House (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                        <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                    </svg>
                @else
                    <!-- Outline House (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Dashboard</span>
        </a>
    </li>

    <!-- Manajemen Proyek (Dropdown) -->
    @php $isProyek = request()->routeIs('admin-pusat.project.*', 'admin-pusat.prerequisite.*'); @endphp
    <li x-data="{ open: {{ $isProyek ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Manajemen Proyek"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isProyek
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isProyek)
                    <!-- Solid Briefcase (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033a48.86 48.86 0 0 1-7.5 1.125V10.5a.75.75 0 0 0-1.5 0v1.944a48.908 48.908 0 0 1-4.5 0V10.5a.75.75 0 0 0-1.5 0v1.944c-2.532-.224-5.035-.61-7.5-1.125V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-9.75 8.794a49.56 49.56 0 0 0 4.5.766v1.44a.75.75 0 0 0 1.5 0v-1.44c1.516.147 3.033.22 4.5.22s2.984-.073 4.5-.22v1.44a.75.75 0 0 0 1.5 0v-1.44a49.56 49.56 0 0 0 4.5-.766v4.615c0 1.434-1.022 2.7-2.476 2.917a48.85 48.85 0 0 1-16.048 0C2.772 21.054 1.75 19.788 1.75 18.354v-4.31Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Briefcase (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen Proyek</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('admin-pusat.project.index', ['type' => 'pusat']) }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.project.index') && request('type', 'pusat') === 'pusat'
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Proyek Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.project.index', ['type' => 'daerah']) }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.project.index') && request('type') === 'daerah'
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Proyek Daerah
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Manajemen Proyek
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('admin-pusat.project.index', ['type' => 'pusat']) }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.project.index') && request('type', 'pusat') === 'pusat' ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Proyek Pusat
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-pusat.project.index', ['type' => 'daerah']) }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.project.index') && request('type') === 'daerah' ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Proyek Daerah
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Pelaporan RTK (Dropdown) -->
    @php $isPelaporan = request()->routeIs('admin-pusat.rtkn*', 'admin-pusat.rtkd*'); @endphp
    <li x-data="{ open: {{ $isPelaporan ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Pelaporan RTK"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isPelaporan
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isPelaporan)
                    <!-- Solid Document (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V7.875a1.875 1.875 0 0 0-.549-1.326l-4.5-4.5A1.875 1.875 0 0 0 13.875 1.5H5.625ZM8.25 9.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Zm0 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Zm0 3.75a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Document (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Pelaporan RTK</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('admin-pusat.rtkn.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.rtkn.index*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    RTK Nasional
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.rtkd.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.rtkd*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    RTK Daerah
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Pelaporan RTK
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('admin-pusat.rtkn.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.rtkn.index*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        RTK Nasional
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-pusat.rtkd.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.rtkd*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        RTK Daerah
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Rekapitulasi SDM (Dropdown) -->
    @php $isRekap = request()->routeIs('admin-pusat.rekapitulasi*'); @endphp
    <li x-data="{ open: {{ $isRekap ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Rekapitulasi SDM"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isRekap
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isRekap)
                    <!-- Solid Users (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 5.69 3.117C18.99 16.035 19.5 17.202 19.5 18.423v1.827H4.5v-1.827c0-1.221.51-2.388 1.81-3.306Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Users (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Rekapitulasi SDM</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('admin-pusat.rekapitulasi.pusat') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.rekapitulasi.pusat*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.rekapitulasi.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.rekapitulasi.index*') || request()->routeIs('admin-pusat.rekapitulasi.kab-kota*') || request()->routeIs('admin-pusat.rekapitulasi.rekap-user-province*') || request()->routeIs('admin-pusat.rekapitulasi.rekap-user-kab-kota*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Daerah
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Rekapitulasi SDM
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('admin-pusat.rekapitulasi.pusat') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.rekapitulasi.pusat*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Pusat
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-pusat.rekapitulasi.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.rekapitulasi.index*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Daerah
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Pemanfaatan RTKD (Dropdown) -->
    @php $isPemanfaatan = request()->routeIs('admin-pusat.survey-periods.*', 'admin-pusat.hasil-pemanfaatan-rtkd.*'); @endphp
    <li x-data="{ open: {{ $isPemanfaatan ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Pemanfaatan RTKD"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isPemanfaatan
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isPemanfaatan)
                    <!-- Solid Chart Pie (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd" />
                        <path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Chart Pie (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Pemanfaatan RTKD</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('admin-pusat.survey-periods.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.survey-periods.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Periode Survei
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-pusat.hasil-pemanfaatan-rtkd.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Hasil Kuesioner
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Pemanfaatan RTKD
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('admin-pusat.survey-periods.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.survey-periods.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Periode Survei
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-pusat.hasil-pemanfaatan-rtkd.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Hasil Kuesioner
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Manajemen Kursus -->
    @php $isCourse = request()->routeIs('admin-pusat.management-course.courses.*', 'admin-pusat.management-course.course-sections.*', 'admin-pusat.management-course.post-tests.*'); @endphp
    <li>
        <a href="{{ route('admin-pusat.management-course.courses.index') }}"
            title="Manajemen Kursus"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isCourse
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isCourse)
                    <!-- Solid Academic Cap (Active) -->
                    <svg class="w-6 h-6 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.7 2.805a.75.75 0 01.6 0A60.65 60.65 0 0122.83 8.72a.75.75 0 01-.231 1.337 49.948 49.948 0 00-9.902 3.912l-.003.002c-.114.06-.24.09-.366.09a.75.75 0 01-.366-.09 49.95 49.95 0 00-9.902-3.912.75.75 0 01-.23-1.337A60.653 60.653 0 0111.7 2.805z" />
                        <path d="M13.06 15.473a48.455 48.455 0 017.623-2.669l.408 4.078a.75.75 0 01-.392.748c-2.483 1.343-5.32 2.12-8.324 2.12-3.004 0-5.84-.777-8.324-2.12a.75.75 0 01-.392-.748l.408-4.078a48.455 48.455 0 017.623 2.67c.435.166.906.25 1.378.25.472 0 .943-.084 1.378-.25l.004-.001z" />
                        <path d="M22.5 12.75a.75.75 0 01.75.75v3.81a.75.75 0 01-1.5 0V13.5a.75.75 0 01.75-.75z" />
                    </svg>
                @else
                    <!-- Outline Academic Cap (Inactive) -->
                    <svg class="w-6 h-6 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v6" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen Kursus</span>
        </a>
    </li>

    <!-- Bimtek & Event Online -->
    @php $isBimtek = request()->routeIs('admin-pusat.management-course.bimtek-events.*'); @endphp
    <li>
        <a href="{{ route('admin-pusat.management-course.bimtek-events.index') }}"
            title="Bimtek & Event Online"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isBimtek
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isBimtek)
                    <!-- Solid Video Camera (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4.5 4.5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h8.25a3 3 0 0 0 3-3v-9a3 3 0 0 0-3-3H4.5ZM19.94 18.75l-2.69-2.69V7.94l2.69-2.69c.944-.945 2.56-.276 2.56 1.06v11.38c0 1.336-1.616 2.005-2.56 1.06Z" />
                    </svg>
                @else
                    <!-- Outline Video Camera (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Bimtek & Event</span>
        </a>
    </li>

    <!-- Pengajuan Sertifikat PPSDM -->
    @php $isPPSDM = request()->routeIs('admin-pusat.management-course.certificate-submissions.*'); @endphp
    <li>
        <a href="{{ route('admin-pusat.management-course.certificate-submissions.index') }}"
            title="Pengajuan Sertifikat PPSDM"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isPPSDM
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isPPSDM)
                    <!-- Solid Shield Check (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Shield Check (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Pengajuan PPSDM</span>
        </a>
    </li>

    <!-- Manajemen Perpustakaan -->
    @php $isLibrary = request()->routeIs('admin-pusat.libraries.*', 'admin-pusat.library-categories.*'); @endphp
    <li>
        <a href="{{ route('admin-pusat.libraries.index') }}"
            title="Manajemen Perpustakaan"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isLibrary
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isLibrary)
                    <!-- Solid Bookmark (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.58A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Bookmark (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen Perpustakaan</span>
        </a>
    </li>

    <!-- Manajemen Sertifikat -->
    @php $isCert = request()->routeIs('admin-pusat.certificates.*'); @endphp
    <li>
        <a href="{{ route('admin-pusat.certificates.index') }}"
            title="Manajemen Sertifikat"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isCert
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isCert)
                    <!-- Solid Check Badge (Active) -->
                    <svg class="w-6 h-6 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Check Badge (Inactive) -->
                    <svg class="w-6 h-6 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen Sertifikat</span>
        </a>
    </li>
</ul>
