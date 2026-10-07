<ul class="space-y-2 px-4 font-medium text-sm">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('super-admin.dashboard')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7H10v7H4a1 1 0 0 1-1-1z" /></svg>
            </span>
            <span class="text-[15px]">Dashboard</span>
        </a>
    </li>

    <!-- Manajemen User (Dropdown) -->
    <li x-data="{ open: {{ request()->routeIs('super-admin.user-management*', 'super-admin.roles.*', 'super-admin.permissions.*') ? 'true' : 'false' }} }">
        <button @click="open = !open" class="flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('super-admin.user-management*', 'super-admin.roles.*', 'super-admin.permissions.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="10" cy="7" r="4" /><path d="M20 8v6m3-3h-6" /></svg>
            </span>

            <span class="flex-1 text-left text-[15px]">Manajemen User</span>

            <svg class="w-4 h-4 shrink-0 transition-transform duration-200 text-slate-400" :class="{ 'rotate-180': open }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-1 space-y-1">
            <li>
                <a href="{{ route('super-admin.user-management.index') }}" class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('super-admin.user-management*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Manajemen User
                </a>
            </li>

            <li>
                <a href="{{ route('super-admin.roles.index') }}" class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('super-admin.roles.*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Roles
                </a>
            </li>
            <li>
                <a href="{{ route('super-admin.permissions.index') }}" class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('super-admin.permissions.*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Permission
                </a>
            </li>
        </ul>
    </li>

    <!-- Manajemen Instansi (Dropdown) -->
    <li x-data="{ open: {{ request()->routeIs('super-admin.lembaga.*', 'super-admin.instansi.*') ? 'true' : 'false' }} }">
        <button @click="open = !open" class="flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('super-admin.lembaga.*', 'super-admin.instansi.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M9 21V9h6v12M3 9h6m6 0h6" /></svg>
            </span>

            <span class="flex-1 text-left text-[15px]">Manajemen Instansi</span>

            <svg class="w-4 h-4 shrink-0 transition-transform duration-200 text-slate-400" :class="{ 'rotate-180': open }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-1 space-y-1">
            <li>
                <a href="{{ route('super-admin.lembaga.index') }}" class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('super-admin.lembaga.*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('super-admin.instansi.index') }}" class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('super-admin.instansi.*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Daerah
                </a>
            </li>
        </ul>
    </li>
</ul>