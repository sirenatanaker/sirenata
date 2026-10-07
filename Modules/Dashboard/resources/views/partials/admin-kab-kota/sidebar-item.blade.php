<ul class="dashboard-sidebar-items space-y-3 px-3 font-medium text-sm">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('admin-kab-kota.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-kab-kota.dashboard')
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
        <a href="{{ route('admin-kab-kota.project.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-kab-kota.project.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-briefcase text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Proyek</span>
        </a>
    </li>

    <!-- Pelaporan -->
    <li>
        <a href="{{ route('admin-kab-kota.rtkd.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-kab-kota.rtkd*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="far fa-file-alt text-base" aria-hidden="true"></i>
            </span>
            <span class="flex-1 ms-3 text-[15px]">Pelaporan</span>
        </a>
    </li>

    <!-- Rekapitulasi SDM -->
    <li>
        <a href="{{ route('admin-kab-kota.rekapitulasi.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-kab-kota.rekapitulasi*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="far fa-user text-base" aria-hidden="true"></i>
            </span>
            <span class="flex-1 ms-3 whitespace-nowrap text-[15px]">Rekapitulasi SDM</span>
        </a>
    </li>
</ul>
