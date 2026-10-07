<ul class="dashboard-sidebar-items space-y-3 px-3 font-medium text-sm">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('admin-province.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-province.dashboard')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-home text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Dashboard</span>
        </a>
    </li>

    <!-- Proyek -->
    <li>
        <a href="{{ route('admin-province.project.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-province.project.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-briefcase text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Proyek</span>
        </a>
    </li>

    <!-- Dropdown: Pelaporan -->
    <li x-data="{ open: {{ request()->routeIs('admin-province.rtkdp*', 'admin-province.laporan*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center cursor-pointer w-full px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-province.rtkdp*', 'admin-province.laporan*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="far fa-file-alt text-base" aria-hidden="true"></i>
            </span>

            <span class="flex-1 ms-3 text-left text-[15px]">Pelaporan</span>

            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1 pl-6">
            <li>
                <a href="{{ route('admin-province.rtkdp.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-province.rtkdp*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Rekapitulasi RTK Provinsi
                </a>
            </li>

            <li>
                <a href="{{ route('admin-province.laporan.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-province.laporan*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Rekapitulasi RTK Keseluruhan
                </a>
            </li>
        </ul>
    </li>

    <!-- Dropdown: Rekapitulasi SDM -->
    <li x-data="{ open: {{ request()->routeIs('admin-province.rekapitulasi*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center cursor-pointer w-full px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-province.rekapitulasi*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="far fa-user text-base" aria-hidden="true"></i>
            </span>

            <span class="flex-1 ms-3 text-left text-[15px]">Rekapitulasi SDM</span>

            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1 pl-6">
            <li>
                <a href="{{ route('admin-province.rekapitulasi.rekap-user-province') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-province.rekapitulasi.rekap-user-province*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Rekapitulasi SDM Provinsi
                </a>
            </li>
            <li>
                <a href="{{ route('admin-province.rekapitulasi.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-province.rekapitulasi.index*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Rekapitulasi SDM Kab/Kota
                </a>
            </li>
        </ul>
    </li>

    <!-- Pemanfaatan RTKD -->
    <li>
        <a href="{{ route('admin-province.pemanfaatan-rtkd.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-province.pemanfaatan-rtkd*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-chart-pie text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Pemanfaatan RTKD</span>
        </a>
    </li>
</ul>
