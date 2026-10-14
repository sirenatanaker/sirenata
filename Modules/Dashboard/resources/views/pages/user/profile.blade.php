<x-dashboard::layouts.dashboard title="Profil Saya">

    <div class="p-2 sm:p-6" x-data="{ tab: 'personal' }">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[['label' => 'Profil Saya']]" />

        <x-validation-errors class="mb-2" />

        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="relative h-48">
                <img src="{{ asset('images/login.jpg') }}" alt="Profile Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -bottom-20 left-6 md:left-12">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->profile?->full_name ?? $user->name) }}&background=6366f1&color=fff&size=160"
                            alt="Profile" class="w-40 h-40 rounded-full border-4 border-white">
                    </div>
                </div>
            </div>

            <div class="pt-24 px-6 md:px-12 pb-6 text-left">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-1">
                    {{ $user->profile?->full_name ?? $user->name }}
                </h1>
                <p class="text-sm text-slate-500 flex flex-wrap items-center justify-start gap-2">
                    <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-slate-400"></i> {{ $user->email }}</span>
                    <span class="hidden md:inline text-slate-300">•</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-calendar text-slate-400"></i> Bergabung sejak {{ $user->created_at->translatedFormat('M Y') }}</span>
                </p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-4 pt-6 max-w-2xl text-left">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-layer-group"></i> Kursus Diikuti</p>
                        <p class="text-lg md:text-xl font-bold text-slate-800">{{ $stats['enrolled'] }} Kursus</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-check-double"></i> Selesai</p>
                        <p class="text-lg md:text-xl font-bold text-slate-800">{{ $stats['completed'] }} Kursus</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-start">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-award"></i> Sertifikat</p>
                        <p class="text-lg md:text-xl font-bold text-[#13416B]">{{ $stats['certificates'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex gap-1 px-4 sm:px-6 pt-4 border-b border-slate-200 overflow-x-auto">
                <button type="button" @click="tab = 'personal'"
                    :class="tab === 'personal' ? 'text-[#13416B] border-[#13416B]' : 'text-slate-500 border-transparent hover:text-slate-800'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap">
                    <i class="fas fa-user"></i> Informasi Pribadi
                </button>
                <button type="button" @click="tab = 'certificates'"
                    :class="tab === 'certificates' ? 'text-[#13416B] border-[#13416B]' : 'text-slate-500 border-transparent hover:text-slate-800'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 -mb-px transition-colors whitespace-nowrap">
                    <i class="fas fa-award"></i> Sertifikat Saya
                    <span class="text-xs font-bold bg-[#13416B]/10 text-[#13416B] rounded-full px-2 py-0.5">{{ $stats['certificates'] }}</span>
                </button>
            </div>

            <div class="p-6 lg:p-8">
                <!-- Tab: Personal -->
                <div x-show="tab === 'personal'" x-cloak>
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-slate-900">Informasi Pribadi</h3>
                            <p class="text-sm text-slate-500">Kelola data diri akun Anda.</p>
                        </div>

                        @php $ssoUser = ! empty($user->siapkerja_id); @endphp
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            @if ($ssoUser)
                                <x-form.input name="full_name" label="Nama Lengkap" value="{{ $user->profile?->full_name ?? $user->name }}" disabled helper="Mengikuti nama akun SIAPKerja" />
                            @else
                                <x-form.input name="full_name" label="Nama Lengkap" :required="true" value="{{ $user->profile?->full_name }}" />
                            @endif
                            <x-form.input name="nik" label="NIK" value="{{ $user->profile?->nik }}" readonly class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <x-form.input name="email" label="Email" value="{{ $user->email }}" readonly helper="Mengikuti akun login" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                            <x-form.input name="phone" label="Nomor Handphone" type="tel" inputmode="numeric" pattern="[0-9+]*" placeholder="08xxxx atau +628xxxx" value="{{ $user->profile?->phone }}" helper="Boleh dikosongkan" />
                            <x-form.select name="gender" label="Gender">
                                <option value="" disabled selected>Pilih Gender</option>
                                <option value="male"
                                    {{ old('gender', $user->profile?->gender) == 'male' ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="female"
                                    {{ old('gender', $user->profile?->gender) == 'female' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </x-form.select>
                            <x-form.input name="instansi" label="Kementerian/Lembaga" value="{{ $user->profile?->instansi }}" placeholder="Kementerian Ketenagakerjaan RI" />
                            <x-form.input name="unit_kerja" label="Unit Kerja" value="{{ $user->profile?->unit_kerja }}" placeholder="SDM" />
                            <x-form.input name="province_name" label="Provinsi" value="{{ $user->scopeArea?->province?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                            <x-form.input name="regency_name" label="Kab/Kota" value="{{ $user->scopeArea?->regency?->name }}" readonly helper="Mengikuti data wilayah" class="bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed" />
                        </div>

                        <div class="flex justify-end">
                            <x-button type="submit" variant="success" size="lg">
                                Simpan Perubahan
                            </x-button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Certificates -->
                <div x-show="tab === 'certificates'" x-cloak>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900">Sertifikat Saya</h3>
                        <p class="text-sm text-slate-500">Sertifikat kelulusan yang telah Anda peroleh dari modul kursus.</p>
                    </div>

                    @if ($certificates->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($certificates as $certificate)
                            <div class="flex flex-col p-5 rounded-2xl border border-slate-200 bg-white hover:border-[#13416B]/40 hover:shadow-md transition-all">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#13416B]/10 text-[#13416B]">
                                        <i class="fas fa-award text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 leading-snug line-clamp-2">{{ $certificate->name }}</h4>
                                        <p class="text-xs text-slate-500 mt-1 font-mono">{{ $certificate->pivot->certificate_code }}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mb-4">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Diterbitkan {{ \Carbon\Carbon::parse($certificate->pivot->certificate_issued_at)->translatedFormat('d M Y') }}
                                </p>
                                <div class="mt-auto flex gap-2">
                                    @if ($certificate->pivot->certificate_file)
                                        <a href="{{ Storage::url($certificate->pivot->certificate_file) }}" target="_blank" download
                                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-lg hover:bg-[#0f3354] transition-colors">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    @endif
                                    <a href="{{ route('user.course.my-course.detail', $certificate->slug) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-[#13416B] border border-[#13416B]/30 rounded-lg hover:bg-[#13416B]/5 transition-colors">
                                        Lihat Kursus
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-14 px-4 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <div class="w-16 h-16 bg-white border border-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <i class="fas fa-award text-2xl text-slate-300"></i>
                            </div>
                            <h4 class="font-bold text-slate-800">Belum ada sertifikat</h4>
                            <p class="text-sm text-slate-500 mt-2 max-w-sm">Selesaikan kursus dan lulus evaluasinya untuk mendapatkan sertifikat kelulusan di sini.</p>
                            <a href="{{ route('user.course.my-course') }}"
                                class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-[#13416B] rounded-lg hover:bg-[#0f3354] transition-colors">
                                Lihat Kursus Saya
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.dashboard>
