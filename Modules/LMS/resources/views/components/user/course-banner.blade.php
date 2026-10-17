@props([
    'illustration' => 'images/modul_ilustrasi.webp',
])

<div class="relative bg-[#13416B] rounded-md p-6 sm:p-8 lg:p-10 mb-6 sm:mb-8 flex items-center justify-between border border-blue-900/20 shadow-lg overflow-hidden min-h-[280px] sm:min-h-[280px] lg:min-h-[320px]">
    <!-- Efek Dekoratif Bubbles Geometris Profesional -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_1.5px,transparent_1.5px)] [background-size:24px_24px]"></div>
        <div class="absolute -top-24 -right-16 w-80 h-80 border-[30px] border-white/5 rounded-full"></div>
        <div class="absolute -bottom-20 right-[10%] w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute top-[15%] right-[38%] w-24 h-24 border-[8px] border-amber-400/20 rounded-full"></div>
        <div class="absolute bottom-[30%] right-[45%] w-8 h-8 bg-blue-400/20 rounded-full"></div>
        <div class="absolute top-[20%] left-[45%] w-12 h-12 bg-white/5 rounded-full"></div>
        <div class="absolute left-0 top-0 w-2/3 h-full bg-gradient-to-r from-[#13416B] via-[#13416B]/80 to-transparent z-10"></div>
    </div>

    <!-- Sisi Kiri: Teks Utama -->
    <div class="relative z-20 w-full sm:w-[60%] lg:w-[60%] text-left">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-md text-sm sm:text-base tracking-wider text-white border border-white/10 mb-4 shadow-sm" style="font-family: 'Kalam', cursive;">
            Ruang Belajar Pegawai
        </span>

        <h1 class="text-2xl md:text-3xl lg:text-4xl text-white mb-4" style="font-family: 'Oswald', sans-serif;">
            Pelatihan & Pengembangan Kompetensi
        </h1>

        <p class="text-sm md:text-base text-blue-100/90 leading-relaxed max-w-xl lg:max-w-2xl font-medium">
            Selesaikan modul pelatihan Anda, ikuti evaluasi dengan baik, dan raih sertifikat kelulusan untuk mendukung perjalanan karier serta profesionalisme Anda.
        </p>
    </div>

    <!-- Sisi Kanan: Ilustrasi Pegawai Kemnaker -->
    <div class="hidden sm:flex absolute bottom-0 right-0 lg:right-5 z-10 w-[45%] lg:w-[35%] h-[90%] lg:h-[95%] pointer-events-none justify-end items-end">
        <img src="{{ asset($illustration) }}"
             alt="Pegawai Kemnaker"
             class="w-full h-full object-contain object-bottom drop-shadow-[0_15px_25px_rgba(0,0,0,0.3)] relative z-20"
             onerror="this.style.display='none'">
    </div>
</div>
