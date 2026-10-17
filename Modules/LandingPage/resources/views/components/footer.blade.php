<footer class="relative py-12 md:py-14 lg:py-20 px-4 overflow-hidden" style="background-color: #0b2641;">
    <!-- Dekorasi Latar Belakang Footer -->
    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-[500px] h-[500px] rounded-full bg-blue-500/5 blur-[100px] pointer-events-none z-0"></div>

    <div class="max-w-7xl mx-auto relative z-10 reveal-up">
        <div class="flex flex-col sm:flex-row gap-6 sm:gap-8 lg:gap-24 mb-10 lg:mb-16">

            <!-- Kiri: Brand, Deskripsi & Sosial Media -->
            <div class="sm:w-5/12 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-4 mb-6">
                        <img src="{{ asset('images/logo.png') }}" alt="SIRENATA" class="h-8 w-auto brightness-0 invert">
                        <span class="text-xl font-bold text-white tracking-tight">SIRENATA</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed text-sm mb-10 max-w-md">
                        Aplikasi digital terpadu untuk kebutuhan penyusunan RTK Makro, RTK Mikro, dan pengukuran
                        Indeks Pembangunan Ketenagakerjaan.
                    </p>
                </div>

                <!-- Sosial Media -->
                <div>
                    <h3 class="font-bold text-white mb-5 uppercase tracking-widest text-xs opacity-60">Terhubung Bersama Kami</h3>
                    <div class="flex items-center gap-4">
                        <a href="#"
                            class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#"
                            class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                        <!-- Ikon X Menggunakan Inline SVG agar pasti muncul -->
                        <a href="#"
                            class="group w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#13416B] hover:text-white hover:border-[#13416B] hover:-translate-y-1 transition-all duration-300 shadow-lg">
                            <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kanan: Tautan & Hubungi Kami -->
            <div class="sm:w-7/12 grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-16 pt-2">
                <!-- Tautan Publik -->
                <div>
                    <h3 class="font-bold text-white mb-6 uppercase tracking-widest text-xs border-b border-white/10 pb-4 inline-block">
                        Tautan Publik
                    </h3>
                    <ul class="space-y-4 text-slate-400 text-sm">
                        <li>
                            <a href="https://kemnaker.go.id" target="_blank"
                                class="hover:text-white transition-colors flex items-center gap-2 group">
                                <i class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                Kemnaker RI
                            </a>
                        </li>
                        <li>
                            <a href="https://pusren.kemnaker.go.id/" target="_blank"
                                class="hover:text-white transition-colors flex items-center gap-2 group">
                                <i class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                Pusrenaker
                            </a>
                        </li>
                        <li>
                            <a href="https://siapkerja.kemnaker.go.id" target="_blank"
                                class="hover:text-white transition-colors flex items-center gap-2 group">
                                <i class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                SIAPkerja Kemnaker
                            </a>
                        </li>
                        <li>
                            <a href="https://satudata.kemnaker.go.id" target="_blank"
                                class="hover:text-white transition-colors flex items-center gap-2 group">
                                <i class="fas fa-arrow-right text-[10px] opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300 text-blue-400"></i>
                                Satu Data Ketenagakerjaan
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Hubungi Kami -->
                <div>
                    <h3 class="font-bold text-white mb-6 uppercase tracking-widest text-xs border-b border-white/10 pb-4 inline-block">
                        Pusat Informasi
                    </h3>
                    <ul class="space-y-5 text-slate-400 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-envelope mt-1 shrink-0 text-white"></i>
                            <div>
                                <a href="mailto:support@sirenata.go.id" class="hover:text-white transition-colors">support@sirenata.go.id</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 shrink-0 text-white"></i>
                            <div>
                                <span>Kementerian Ketenagakerjaan RI<br>Jakarta, Indonesia</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- Garis Bawah & Copyright -->
        <div class="pt-8 border-t border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4 text-slate-400 text-sm font-medium">
            <p>&copy; 2026 Kementerian Ketenagakerjaan Republik Indonesia.</p>
            <p class="text-xs opacity-60 tracking-wider">ALL RIGHTS RESERVED.</p>
        </div>
    </div>
</footer>
