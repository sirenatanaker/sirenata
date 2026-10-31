<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian e-Sertifikat Resmi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800">

    {{-- Header Sederhana --}}
    <header class="bg-white border-b border-slate-200 py-4 px-6 shadow-2xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#13416B] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                    <i class="fas fa-landmark"></i>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight">Sistem Verifikasi Sertifikat</h1>
                    <p class="text-xs text-slate-500">Pusat Pengembangan Sumber Daya Manusia (PPSDM)</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="fas fa-shield-halved text-emerald-600"></i>
                <span>Verifikasi Resmi</span>
            </span>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="max-w-2xl mx-auto px-4 py-8 sm:py-12 w-full">
        @if ($isValid)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
                {{-- Status Banner Valid --}}
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 text-white text-center">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center mx-auto mb-3 text-3xl text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Sertifikat Sah & Terverifikasi</h2>
                    <p class="text-xs sm:text-sm text-emerald-100 mt-1 max-w-md mx-auto">
                        Dokumen pelatihan ini sah dan tercatat secara resmi pada pangkalan data penomoran sertifikat PPSDM.
                    </p>
                </div>

                {{-- Detail Rincian Sertifikat --}}
                <div class="p-6 sm:p-8 space-y-5 text-sm">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Sertifikat Resmi (PPSDM)</span>
                            <p class="text-base sm:text-lg font-mono font-extrabold text-[#13416B] mt-0.5">
                                {{ $officialCertNumber ?: 'Nomor Terdaftar' }}
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono">Kode Sistem: #{{ $code }}</p>
                        </div>
                    </div>

                    <div class="space-y-4 divide-y divide-slate-100">
                        <div class="pt-3 first:pt-0">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Penerima / ASN</span>
                            <p class="text-base font-bold text-slate-900 mt-0.5">{{ $studentName }}</p>
                            @if ($studentEmail)
                                <p class="text-xs text-slate-500">{{ $studentEmail }}</p>
                            @endif
                        </div>

                        <div class="pt-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Bimbingan Teknis (Bimtek)</span>
                            <p class="text-sm sm:text-base font-bold text-slate-800 mt-0.5">{{ $courseName }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Gelombang: {{ $batchName }}</p>
                        </div>

                        <div class="pt-3 grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Selesai</span>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $completedAt }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tanggal Diterbitkan</span>
                                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $issuedAt }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200/60 flex items-start gap-3 text-xs text-blue-900">
                        <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5 shrink-0"></i>
                        <p class="leading-relaxed">
                            Data verifikasi ini diterbitkan secara elektronik oleh sistem e-Learning terakreditasi dan dapat digunakan sebagai bukti keabsahan kompetensi kedinasan.
                        </p>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 text-center">
                    <p class="text-xs text-slate-500">
                        Pusat Pengembangan Sumber Daya Manusia (PPSDM) &copy; {{ date('Y') }}
                    </p>
                </div>
            </div>
        @else
            {{-- Status Tidak Ditemukan --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden p-8 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-3xl">
                    <i class="fas fa-circle-exclamation"></i>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">Sertifikat Tidak Ditemukan</h2>
                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                        Kode registrasi <strong class="font-mono text-slate-700">{{ $code }}</strong> tidak terdaftar dalam pangkalan data resmi sertifikat kami.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#13416B] rounded-lg">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center text-xs text-slate-400">
        LMS E-Learning Kementerian &copy; {{ date('Y') }}. Hak Cipta Dilindungi Undang-Undang.
    </footer>

</body>
</html>
