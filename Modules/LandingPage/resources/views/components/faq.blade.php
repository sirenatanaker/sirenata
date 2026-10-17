<section id="faq" class="py-12 sm:py-18 lg:py-16 px-4 bg-white border-t border-slate-200 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 sm:gap-6 lg:gap-16 items-center">

            <!-- Kolom Kiri: Judul & Accordion -->
            <div class="order-2 reveal-left lg:order-2">
                <div class="text-left mb-6 lg:mb-10">
                    <h2 class="section-title text-slate-900 mb-4 lg:mb-6">
                        Pertanyaan yang Sering <span class="text-[#13416B]">Diajukan</span>
                    </h2>
                    <p class="text-slate-600 text-sm lg:text-lg leading-relaxed">
                        Temukan jawaban cepat untuk pertanyaan seputar penggunaan aplikasi SIRENATA atau kunjungi <a
                            href="https://bantuan.kemnaker.go.id/" target="_blank"
                            class="text-amber-500 font-bold hover:underline">Pusat Bantuan Kemnaker</a> untuk
                        kendala teknis lainnya.
                    </p>
                </div>

                <!-- Accordion FAQ menggunakan Alpine.js -->
                <div x-data="{ activeAccordion: null }" class="space-y-3 lg:space-y-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                        :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 1 }">
                        <button @click="activeAccordion = activeAccordion === 1 ? null : 1"
                            class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                :class="{ 'text-[#13416B]': activeAccordion === 1 }">
                                Siapa saja yang dapat menggunakan aplikasi SIRENATA?
                            </h3>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 1 }"></i>
                        </button>
                        <div x-show="activeAccordion === 1" x-collapse x-cloak>
                            <div class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                Aplikasi ini ditujukan khusus bagi para pemangku kepentingan ketenagakerjaan,
                                termasuk Super Admin, Admin Pusat, Admin Instansi Provinsi, Admin Instansi
                                Kabupaten/Kota, dan Pengguna ASN (Aparatur Sipil Negara) selaku perencana di daerah.
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                        :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 2 }">
                        <button @click="activeAccordion = activeAccordion === 2 ? null : 2"
                            class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                :class="{ 'text-[#13416B]': activeAccordion === 2 }">
                                Bagaimana cara mendaftar atau masuk ke dalam aplikasi?
                            </h3>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 2 }"></i>
                        </button>
                        <div x-show="activeAccordion === 2" x-collapse x-cloak>
                            <div class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                SIRENATA terintegrasi dengan sistem <strong>Single Sign-On (SSO)</strong> Kemnaker.
                                Anda dapat langsung masuk menggunakan akun <strong>SIAPKerja ID</strong> yang telah
                                terdaftar. Jika Anda mewakili instansi daerah, hubungi Admin Pusat untuk penyesuaian
                                hak akses.
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                        :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 3 }">
                        <button @click="activeAccordion = activeAccordion === 3 ? null : 3"
                            class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                :class="{ 'text-[#13416B]': activeAccordion === 3 }">
                                Apa perbedaan RTK Makro, RTK Mikro, dan IPK?
                            </h3>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 3 }"></i>
                        </button>
                        <div x-show="activeAccordion === 3" x-collapse x-cloak>
                            <div class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li><strong>RTK Makro:</strong> Proyeksi tenaga kerja di tingkat wilayah (Nasional/Provinsi/Kabupaten/Kota) berdasarkan ekonomi makro.</li>
                                    <li><strong>RTK Mikro:</strong> Analisis kebutuhan pegawai/tenaga kerja spesifik di dalam internal suatu instansi atau perusahaan.</li>
                                    <li><strong>IPK:</strong> Indeks Pembangunan Ketenagakerjaan, yaitu pengukuran capaian kinerja daerah berdasarkan 7 indikator utama ketenagakerjaan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all hover:border-[#13416B]/30 hover:shadow-sm"
                        :class="{ 'border-[#13416B]/40 shadow-md bg-white': activeAccordion === 4 }">
                        <button @click="activeAccordion = activeAccordion === 4 ? null : 4"
                            class="w-full px-3 py-3 md:px-3 md:py-3 lg:px-6 lg:py-5 text-left flex items-center justify-between focus:outline-none">
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-sm lg:text-lg pr-4"
                                :class="{ 'text-[#13416B]': activeAccordion === 4 }">
                                Apa fungsi fitur LMS Terintegrasi?
                            </h3>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 shrink-0"
                                :class="{ 'rotate-180 text-[#13416B]': activeAccordion === 4 }"></i>
                        </button>
                        <div x-show="activeAccordion === 4" x-collapse x-cloak>
                            <div class="px-4 pb-4 md:px-3 md:pb-3 lg:px-6 lg:pb-6 text-slate-600 leading-relaxed border-t border-slate-100 pt-3 lg:pt-4 text-xs lg:text-base">
                                Fitur LMS difungsikan sebagai sarana transfer pengetahuan dari pusat ke daerah.
                                Pengguna dapat mengikuti kursus interaktif secara mandiri untuk meningkatkan
                                kompetensi terkait perencanaan, serta mendapatkan <strong>sertifikat resmi</strong>
                                setelah lulus ujian (post-test).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Ilustrasi Bantuan -->
            <div class="order-1 relative hidden h-[300px] items-center justify-center reveal-right sm:flex lg:h-full">
                <div class="absolute right-0 top-1/2 h-[240px] w-[240px] -translate-y-1/2 rounded-full border border-[#13416B]/15 border-dashed animate-[spin_55s_linear_infinite] lg:h-[420px] lg:w-[420px]"></div>
                <div class="absolute right-12 top-1/2 h-[210px] w-[210px] -translate-y-1/2 overflow-hidden bg-gradient-to-tr from-amber-400 to-yellow-200 opacity-90 lg:right-32 lg:h-[380px] lg:w-[380px]"
                    style="border-radius: 44% 56% 62% 38% / 52% 42% 58% 48%;">
                    <svg class="absolute inset-0 h-full w-full opacity-25" viewBox="0 0 380 380" fill="none"
                        preserveAspectRatio="none" aria-hidden="true">
                        <path d="M48 -24C96 72 10 130 58 222C88 282 38 336 60 404" stroke="white" stroke-width="18" />
                        <path d="M132 -24C180 72 94 130 142 222C172 282 122 336 144 404" stroke="white" stroke-width="18" />
                        <path d="M216 -24C264 72 178 130 226 222C256 282 206 336 228 404" stroke="white" stroke-width="18" />
                        <path d="M300 -24C348 72 262 130 310 222C340 282 290 336 312 404" stroke="white" stroke-width="18" />
                    </svg>
                </div>
                <img src="{{ asset('images/FAQ.webp') }}" alt="Pusat Bantuan Kemnaker"
                    class="relative z-10 w-full max-w-[280px] md:max-w-[340px] lg:max-w-[480px] h-auto object-contain drop-shadow-2xl animate-float mt-4 lg:mt-8"
                    style="-webkit-mask-image: linear-gradient(to bottom, black 87%, transparent 100%); mask-image: linear-gradient(to bottom, black 87%, transparent 100%);">
            </div>

        </div>
    </div>
</section>
