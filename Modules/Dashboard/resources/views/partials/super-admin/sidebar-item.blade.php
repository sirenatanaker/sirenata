<ul class="dashboard-sidebar-items flex flex-col gap-1.5 space-y-1 font-bold w-full transition-all duration-300"
    :class="sidebarOpen ? 'px-3' : 'px-2'">

    <!-- Dashboard -->
    @php $isDashboard = request()->routeIs('super-admin.dashboard'); @endphp
    <li>
        <a href="{{ route('super-admin.dashboard') }}"
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

    <!-- Manajemen User (Dropdown) -->
    @php $isUserMgmt = request()->routeIs('super-admin.user-management*', 'super-admin.roles.*', 'super-admin.permissions.*'); @endphp
    <li x-data="{ open: {{ $isUserMgmt ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Manajemen User"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isUserMgmt
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isUserMgmt)
                    <!-- Solid User Group (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 5.69 3.117C18.99 16.035 19.5 17.202 19.5 18.423v1.827H4.5v-1.827c0-1.221.51-2.388 1.81-3.306Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline User Group (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen User</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('super-admin.user-management.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('super-admin.user-management*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Manajemen User
                </a>
            </li>
            <li>
                <a href="{{ route('super-admin.roles.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('super-admin.roles.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Roles
                </a>
            </li>
            <li>
                <a href="{{ route('super-admin.permissions.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('super-admin.permissions.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Permission
                </a>
            </li>
        </ul>

        <!-- Submenu Flyout (Collapsed Mini Mode) -->
        <div x-show="!sidebarOpen && flyout" x-transition.opacity.duration.150ms x-cloak
             @click.outside="flyout = false"
             class="absolute left-full top-0 ms-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
            <div class="px-3.5 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                Manajemen User
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('super-admin.user-management.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('super-admin.user-management*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Manajemen User
                    </a>
                </li>
                <li>
                    <a href="{{ route('super-admin.roles.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('super-admin.roles.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Roles
                    </a>
                </li>
                <li>
                    <a href="{{ route('super-admin.permissions.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('super-admin.permissions.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Permission
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Manajemen Instansi (Dropdown) -->
    @php $isInstansi = request()->routeIs('super-admin.lembaga.*', 'super-admin.instansi.*'); @endphp
    <li x-data="{ open: {{ $isInstansi ? 'true' : 'false' }}, flyout: false }" @mouseleave="flyout = false" class="relative">
        <button @click="sidebarOpen ? open = !open : flyout = !flyout"
            type="button"
            title="Manajemen Instansi"
            class="relative flex transition-all duration-200 group py-3 rounded-xl w-full cursor-pointer
            {{ $isInstansi
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isInstansi)
                    <!-- Solid Building Office (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 0 0-.75.75v18c0 .414.336.75.75.75h15a.75.75 0 0 0 .75-.75V3a.75.75 0 0 0-.75-.75H4.5ZM6 6.75A.75.75 0 0 1 6.75 6h1.5A.75.75 0 0 1 9 6.75v1.5A.75.75 0 0 1 8.25 9h-1.5A.75.75 0 0 1 6 8.25v-1.5Zm4.5 0A.75.75 0 0 1 11.25 6h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5Zm4.5 0A.75.75 0 0 1 15.75 6h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5ZM6 11.25A.75.75 0 0 1 6.75 10.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5A.75.75 0 0 1 6 12.75v-1.5Zm4.5 0a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5Zm4.5 0a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5ZM6 15.75a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5A.75.75 0 0 1 8.25 18h-1.5A.75.75 0 0 1 6 17.25v-1.5Zm4.5 0a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5Zm4.5 0a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Building Office (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen Instansi</span>
            <i x-show="sidebarOpen" class="fas fa-chevron-down w-3.5 shrink-0 text-xs text-slate-400 transition-transform duration-200"
               :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <!-- Submenu Accordion (Expanded) -->
        <ul x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
            <li>
                <a href="{{ route('super-admin.lembaga.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('super-admin.lembaga.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('super-admin.instansi.index') }}"
                    class="flex items-center px-3.5 py-2.5 rounded-lg transition-colors text-sm leading-snug break-words font-semibold
                    {{ request()->routeIs('super-admin.instansi.*')
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
                Manajemen Instansi
            </div>
            <ul class="space-y-0.5 px-1.5">
                <li>
                    <a href="{{ route('super-admin.lembaga.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('super-admin.lembaga.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Pusat
                    </a>
                </li>
                <li>
                    <a href="{{ route('super-admin.instansi.index') }}"
                        class="block px-3.5 py-2.5 rounded-lg text-sm leading-snug break-words font-semibold transition-colors
                        {{ request()->routeIs('super-admin.instansi.*') ? 'text-[#13416B] bg-[#13416B]/10 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-[#13416B]' }}">
                        Daerah
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <!-- Log Aktivitas -->
    @php $isActivityLog = request()->routeIs('super-admin.activity-logs.*'); @endphp
    <li>
        <a href="{{ route('super-admin.activity-logs.index') }}"
            title="Log Aktivitas"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isActivityLog
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isActivityLog)
                    <!-- Solid Clock / History (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Clock / History (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Log Aktivitas</span>
        </a>
    </li>

    <!-- Manajemen FAQ -->
    @php $isFaq = request()->routeIs('super-admin.faq.*'); @endphp
    <li>
        <a href="{{ route('super-admin.faq.index') }}"
            title="Manajemen FAQ"
            class="relative flex transition-all duration-200 group py-3 rounded-xl
            {{ $isFaq
                ? 'text-[#13416B] font-bold bg-[#13416B]/10'
                : 'text-slate-500 hover:text-[#13416B] hover:bg-slate-100/70' }}"
            :class="sidebarOpen ? 'flex-row items-center justify-start px-3.5 gap-3.5' : 'flex-col items-center justify-center w-12 h-12 mx-auto p-0'">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                @if ($isFaq)
                    <!-- Solid Question Mark (Active) -->
                    <svg class="w-5 h-5 text-[#13416B]" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.076-.656-.582-1.162-1.238-1.238a4.99 4.99 0 0 0-1.89 0c-.656.076-1.162.582-1.238 1.238a.75.75 0 0 1-1.488-.182 6.49 6.49 0 0 1 1.61-1.61 6.49 6.49 0 0 1 4.108-.002c.624.237 1.167.63 1.583 1.144.417.514.685 1.128.777 1.776.19 1.332-.472 2.582-1.572 3.255l-.105.064c-.452.276-.73.766-.73 1.298V14.25a.75.75 0 0 1-1.5 0v-.75c0-.986.516-1.897 1.353-2.408l.106-.065c.604-.37.947-.997.842-1.734a2.99 2.99 0 0 0-.416-.976 2.99 2.99 0 0 0-.602-.634ZM12 15.75a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-1.5 0V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <!-- Outline Question Mark (Inactive) -->
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#13416B]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                @endif
            </span>
            <span class="text-[15px] text-left leading-snug break-words"
                  :class="sidebarOpen ? 'block' : 'hidden'">Manajemen FAQ</span>
        </a>
    </li>
</ul>