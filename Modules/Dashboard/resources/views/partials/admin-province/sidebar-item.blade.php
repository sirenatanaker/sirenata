<ul class="dashboard-sidebar-items flex flex-col gap-1.5 space-y-1 font-semibold w-full transition-all duration-300"
    :class="sidebarOpen ? 'px-3' : 'px-2'">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('admin-province.dashboard'); @endphp
    <li>
        <a href="{{ route('admin-province.dashboard') }}"
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

    <!-- Proyek -->
    @php $isProject = request()->routeIs('admin-province.project.*'); @endphp
    <li>
        <a href="{{ route('admin-province.project.index') }}"
            title="Proyek"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isProject
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isProject)
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
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Proyek</span>
        </a>
    </li>

    <!-- Dropdown: Pelaporan -->
    @php $isPelaporan = request()->routeIs('admin-province.rtkdp*', 'admin-province.laporan*'); @endphp
    <li x-data="{ open: {{ $isPelaporan ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Pelaporan"
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
                  :class="sidebarOpen ? 'block' : 'hidden'">Pelaporan</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('admin-province.rtkdp.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-province.rtkdp*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi RTK Provinsi
                </a>
            </li>
            <li>
                <a href="{{ route('admin-province.laporan.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-province.laporan*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi RTK Keseluruhan
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Pelaporan
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('admin-province.rtkdp.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-province.rtkdp*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Rekapitulasi RTK Provinsi
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-province.laporan.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-province.laporan*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Rekapitulasi RTK Keseluruhan
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Dropdown: Rekapitulasi SDM -->
    @php $isRekap = request()->routeIs('admin-province.rekapitulasi*'); @endphp
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
                <a href="{{ route('admin-province.rekapitulasi.rekap-user-province') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-province.rekapitulasi.rekap-user-province*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi SDM Provinsi
                </a>
            </li>
            <li>
                <a href="{{ route('admin-province.rekapitulasi.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('admin-province.rekapitulasi.index*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi SDM Kab/Kota
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
                    <a href="{{ route('admin-province.rekapitulasi.rekap-user-province') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-province.rekapitulasi.rekap-user-province*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Rekapitulasi SDM Provinsi
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin-province.rekapitulasi.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('admin-province.rekapitulasi.index*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Rekapitulasi SDM Kab/Kota
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Pemanfaatan RTKD -->
    @php $isPemanfaatan = request()->routeIs('admin-province.pemanfaatan-rtkd*'); @endphp
    <li>
        <a href="{{ route('admin-province.pemanfaatan-rtkd.index') }}"
            title="Pemanfaatan RTKD"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
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
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Pemanfaatan RTKD</span>
        </a>
    </li>
</ul>
