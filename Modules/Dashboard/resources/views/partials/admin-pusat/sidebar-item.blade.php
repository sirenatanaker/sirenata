<ul class="dashboard-sidebar-items space-y-3 px-3 font-medium text-sm">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('admin-pusat.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.dashboard')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-home text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Dashboard</span>
        </a>
    </li>

    <!-- Manajemen Proyek (Dropdown) -->
    <li x-data="{ open: {{ request()->routeIs('admin-pusat.project.*', 'admin-pusat.prerequisite.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.project.*', 'admin-pusat.prerequisite.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <i class="fas fa-briefcase text-base" aria-hidden="true"></i>
            </span>

            <span class="flex-1 text-left text-[15px]">Manajemen Proyek</span>

            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-1 space-y-1">
            <li>
                <a href="{{ route('admin-pusat.project.index', ['type' => 'pusat']) }}"
                    class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('admin-pusat.project.index') && request('type', 'pusat') === 'pusat'
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Proyek Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.project.index', ['type' => 'daerah']) }}"
                    class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('admin-pusat.project.index') && request('type') === 'daerah'
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Proyek Daerah
                </a>
            </li>
        </ul>
    </li>

    <!-- Dropdown: Pelaporan -->
    <li x-data="{ open: {{ request()->routeIs('admin-pusat.rtkn*', 'admin-pusat.rtkd*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center cursor-pointer w-full px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.rtkn*', 'admin-pusat.rtkd*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="far fa-file-alt text-base" aria-hidden="true"></i>
            </span>
            <span class="flex-1 ms-3 text-left text-[15px]">Pelaporan RTK</span>
            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1 pl-6">
            <li>
                <a href="{{ route('admin-pusat.rtkn.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-pusat.rtkn.index*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi Rencana Tenaga Kerja Nasional
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.rtkd.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-pusat.rtkd*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Rekapitulasi Rencana Tenaga Kerja Provinsi
                </a>
            </li>
        </ul>
    </li>

    <!-- Dropdown: Rekapitulasi SDM -->
    <li x-data="{ open: {{ request()->routeIs('admin-pusat.rekapitulasi*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.rekapitulasi*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <i class="far fa-user text-base" aria-hidden="true"></i>
            </span>

            <span class="flex-1 text-left text-[15px]">Rekapitulasi SDM</span>

            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-1 space-y-1">
            <li>
                <a href="{{ route('admin-pusat.rekapitulasi.pusat') }}"
                    class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('admin-pusat.rekapitulasi.pusat*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Pusat
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.rekapitulasi.index') }}"
                    class="block w-full pl-[3.25rem] pr-4 py-3 rounded-2xl transition-colors text-[14px]
                    {{ request()->routeIs('admin-pusat.rekapitulasi.index*') || request()->routeIs('admin-pusat.rekapitulasi.kab-kota*') || request()->routeIs('admin-pusat.rekapitulasi.rekap-user-province*') || request()->routeIs('admin-pusat.rekapitulasi.rekap-user-kab-kota*')
                        ? 'text-[#13416B] bg-slate-100 font-bold'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
                    Daerah
                </a>
            </li>
        </ul>
    </li>

    <!-- Dropdown: Pemanfaatan RTKD -->
    <li x-data="{ open: {{ request()->routeIs('admin-pusat.survey-periods.*', 'admin-pusat.hasil-pemanfaatan-rtkd.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="flex items-center cursor-pointer w-full px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.survey-periods.*', 'admin-pusat.hasil-pemanfaatan-rtkd.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-chart-pie text-base" aria-hidden="true"></i>
            </span>
            <span class="flex-1 ms-3 text-left text-[15px]">Pemanfaatan RTKD</span>
            <i class="fas fa-chevron-down w-4 shrink-0 text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1 pl-6">
            <li>
                <a href="{{ route('admin-pusat.survey-periods.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-pusat.survey-periods.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Periode Survei
                </a>
            </li>
            <li>
                <a href="{{ route('admin-pusat.hasil-pemanfaatan-rtkd.index') }}"
                    class="flex items-center pl-7 px-4 py-2.5 rounded-lg transition-colors text-[13px]
                    {{ request()->routeIs('admin-pusat.hasil-pemanfaatan-rtkd.*')
                        ? 'text-[#13416B] bg-[#13416B]/10 font-bold'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-[#13416B]' }}">
                    Hasil Kuesioner
                </a>
            </li>
        </ul>
    </li>

    <!-- Management Course (Single Menu) -->
    <li>
        <a href="{{ route('admin-pusat.management-course.courses.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.management-course*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Manajemen Kursus</span>
        </a>
    </li>

    <!-- Manajemen Perpustakaan -->
    <li>
        <a href="{{ route('admin-pusat.libraries.index') }}"
            class="flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.libraries.*', 'admin-pusat.library-categories.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-6 shrink-0 flex items-center justify-center">
                <i class="far fa-bookmark text-base" aria-hidden="true"></i>
            </span>

            <span class="flex-1 text-left text-[15px]">Manajemen Perpustakaan</span>
        </a>
    </li>

    <!-- Sertifikat -->
    <li>
        <a href="{{ route('admin-pusat.certificates.index') }}"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200
            {{ request()->routeIs('admin-pusat.certificates.*')
                ? 'text-[#13416B] bg-slate-100 font-bold'
                : 'text-slate-500 hover:bg-slate-50 hover:text-[#13416B]' }}">
            <span class="w-7 shrink-0 flex items-center justify-center">
                <i class="fas fa-award text-base" aria-hidden="true"></i>
            </span>
            <span class="ms-3 text-[15px]">Sertifikat</span>
        </a>
    </li>
</ul>
