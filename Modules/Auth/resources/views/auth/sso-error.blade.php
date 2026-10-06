<x-layouts.app title="Login SIAPKerja gagal">
    <main class="min-h-screen flex items-center justify-center bg-gray-50 px-6 py-12">
        <section class="w-full max-w-lg rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
            <h1 class="mb-4 text-xl font-bold text-gray-900">Login SIAPKerja gagal</h1>
            <p class="mb-2 text-sm text-gray-700">Autentikasi gagal diproses. Silakan coba lagi atau hubungi administrator.</p>
            <p class="mb-6 break-words text-xs text-gray-500">Referensi error: {{ $errorReference }}</p>

            <a href="{{ route('siapkerja.redirect') }}"
                class="inline-block rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                Coba login lagi
            </a>
        </section>
    </main>
</x-layouts.app>
