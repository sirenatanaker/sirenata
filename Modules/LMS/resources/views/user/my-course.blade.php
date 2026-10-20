<x-dashboard::layouts.dashboard title="Kursus Saya | SIRENATA">
    <div class="p-2 sm:p-6">
        <!-- Breadcrumb -->
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1">
                <li class="inline-flex items-center">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                            </path>
                        </svg>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-700 md:ml-2">Kursus Saya</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Filter Tabs -->
        <div class="mb-4 sm:mb-6" x-data="{ activeFilter: 'all' }">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-2 sm:space-x-4 overflow-x-auto pb-2 scrollbar-hide -mx-2 px-2">
                    <button @click="activeFilter = 'all'"
                        :class="activeFilter === 'all' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-2 sm:py-3 px-1 text-xs sm:text-sm whitespace-nowrap transition-colors">Semua
                        (6)</button>
                    <button @click="activeFilter = 'belum-selesai'"
                        :class="activeFilter === 'belum-selesai' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-2 sm:py-3 px-1 text-xs sm:text-sm whitespace-nowrap transition-colors">Belum
                        Selesai (4)</button>
                    <button @click="activeFilter = 'sudah-selesai'"
                        :class="activeFilter === 'sudah-selesai' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-2 sm:py-3 px-1 text-xs sm:text-sm whitespace-nowrap transition-colors">Selesai
                        (2)</button>
                    <button @click="activeFilter = 'sertifikat'"
                        :class="activeFilter === 'sertifikat' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-2 sm:py-3 px-1 text-xs sm:text-sm whitespace-nowrap transition-colors">Sertifikat
                        (2)</button>
                </nav>
            </div>

            <!-- Courses Grid -->
            <div x-show="activeFilter !== 'sertifikat'" class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mt-4 sm:mt-6">
                <!-- Course Card 1 - SELESAI -->
                <div x-show="activeFilter === 'all' || activeFilter === 'sudah-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/macro/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-emerald-600 text-white">Selesai</span>
                            <span class="text-xs text-gray-500">100%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Perencanaan
                            Tenaga Kerja Makro</h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>8/8 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-emerald-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 100%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-emerald-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-emerald-700 transition-colors text-center text-xs sm:text-base">Detail</a>
                    </div>
                </div>

                <!-- Course Card 2 - SELESAI -->
                <div x-show="activeFilter === 'all' || activeFilter === 'sudah-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/micro/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-emerald-600 text-white">Selesai</span>
                            <span class="text-xs text-gray-500">100%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Perencanaan
                            Tenaga Kerja Mikro</h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>8/8 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-emerald-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 100%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-emerald-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-emerald-700 transition-colors text-center text-xs sm:text-base">Detail</a>
                    </div>
                </div>

                <!-- Course Card 3 - SEDANG BERJALAN -->
                <div x-show="activeFilter === 'all' || activeFilter === 'belum-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/ipk/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-500 text-white">Sedang
                                Berjalan</span>
                            <span class="text-xs text-gray-500">33%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Indeks
                            Pembangunan Ketenagakerjaan</h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>1 dari 3 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-indigo-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 33%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors text-center text-xs sm:text-base">Lanjutkan
                            Belajar</a>
                    </div>
                </div>

                <!-- Course Card 4 - SEDANG BERJALAN -->
                <div x-show="activeFilter === 'all' || activeFilter === 'belum-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/macro-adv/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-500 text-white">Sedang
                                Berjalan</span>
                            <span class="text-xs text-gray-500">25%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Perencanaan
                            Tenaga Kerja Makro (Advanced)</h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>2 dari 8 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-indigo-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 25%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors text-center text-xs sm:text-base">Lanjutkan
                            Belajar</a>
                    </div>
                </div>

                <!-- Course Card 5 - SEDANG BERJALAN -->
                <div x-show="activeFilter === 'all' || activeFilter === 'belum-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/micro-adv/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-500 text-white">Sedang
                                Berjalan</span>
                            <span class="text-xs text-gray-500">25%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Perencanaan
                            Tenaga Kerja Mikro (Advanced)</h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>2 dari 8 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-indigo-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 25%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors text-center text-xs sm:text-base">Lanjutkan
                            Belajar</a>
                    </div>
                </div>

                <!-- Course Card 6 - SEDANG BERJALAN -->
                <div x-show="activeFilter === 'all' || activeFilter === 'belum-selesai'"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/ipk-adv/400/200" alt="Course"
                        class="w-full h-24 sm:h-40 object-cover">
                    <div class="p-3 sm:p-5">
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-500 text-white">Sedang
                                Berjalan</span>
                            <span class="text-xs text-gray-500">0%</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg line-clamp-2">Indeks
                            Pembangunan Ketenagakerjaan (Advanced)
                        </h3>
                        <div class="mb-3 sm:mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>0 dari 3 modul</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                                <div class="bg-indigo-600 h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                    style="width: 0%"></div>
                            </div>
                        </div>
                        <a href="#"
                            class="block w-full bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors text-center text-xs sm:text-base">Lanjutkan
                            Belajar</a>
                    </div>
                </div>
            </div>

            <!-- Certificates Grid -->
            <div x-show="activeFilter === 'sertifikat'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 sm:mt-6">
                <!-- Certificate 1 -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/cert-macro/600/400"
                        alt="Sertifikat Perencanaan Tenaga Kerja Makro" class="w-full h-48 object-cover">
                    <div class="p-3 sm:p-5">
                        <h3 class="font-bold text-gray-900 text-lg mb-4">Perencanaan Tenaga Kerja Makro</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                                <p class="text-sm font-semibold text-gray-900">10 Des 2024</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">No. Sertifikat</p>
                                <p class="text-sm font-semibold text-gray-900">CERT-2024-PTK-001</p>
                            </div>
                        </div>
                        <button
                            class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Sertifikat
                        </button>
                    </div>
                </div>

                <!-- Certificate 2 -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500">
                    <img src="https://picsum.photos/seed/cert-micro/600/400"
                        alt="Sertifikat Perencanaan Tenaga Kerja Mikro" class="w-full h-48 object-cover">
                    <div class="p-3 sm:p-5">
                        <h3 class="font-bold text-gray-900 text-lg mb-4">Perencanaan Tenaga Kerja Mikro</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                                <p class="text-sm font-semibold text-gray-900">15 Des 2024</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">No. Sertifikat</p>
                                <p class="text-sm font-semibold text-gray-900">CERT-2024-PTK-002</p>
                            </div>
                        </div>
                        <button
                            class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Sertifikat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard::layouts.dashboard>