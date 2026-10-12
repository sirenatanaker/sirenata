<div>
    <div class="my-3">
        <x-validation-errors />
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-6 card-hover">
        @php
            $scope = $this->scopeLevel();
            $needsInstansiForm = ($scope === 'provinsi' && ! empty($province))
                || ($scope === 'kabkota' && ! empty($regency));
            $pusatInstitution = \App\Livewire\Dashboard\SuperAdmin\UserManagementCreate::PUSAT_INSTITUTION;
        @endphp
        <form wire:submit="store">
            <!-- Stepper -->
            <div class="mb-8 flex items-center gap-2 sm:gap-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $currentStep === 1 ? 'bg-indigo-600 text-white' : 'bg-emerald-500 text-white' }}">
                        @if ($currentStep === 1)
                            1
                        @else
                            <i class="fas fa-check text-xs"></i>
                        @endif
                    </div>
                    <span class="text-sm font-semibold {{ $currentStep === 1 ? 'text-slate-900' : 'text-slate-500' }}">Role & Akses</span>
                </div>
                <div class="h-0.5 flex-1 rounded-full {{ $currentStep === 2 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $currentStep === 2 ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">2</div>
                    <span class="text-sm font-semibold {{ $currentStep === 2 ? 'text-slate-900' : 'text-slate-500' }}">Informasi Pribadi</span>
                </div>
            </div>

            @if ($currentStep === 1)
            <!-- Langkah 1: Role dan Izin Akses -->
            <div class="mb-8">
                <div class="mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Role dan Izin Akses</h3>
                        <p class="text-sm text-slate-500">Langkah 1 — pilih satu role untuk menentukan alur form berikutnya</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2  after:ml-0.5 after:text-red-500 after:content-['*'] ">
                            Role
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @forelse ($roles as $role)
                                <div class="relative">
                                    <input type="radio" id="role_{{ $role->uuid }}" wire:model.live="roleId"
                                        value="{{ $role->uuid }}" class="hidden peer">

                                    <label for="role_{{ $role->uuid }}"
                                        class="flex flex-col h-full p-4 border-2 border-slate-200 rounded-xl cursor-pointer
                                        hover:border-indigo-300
                                        peer-checked:border-indigo-500
                                        peer-checked:bg-indigo-50
                                        transition">
                                        <div class="flex items-center mb-2">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 mr-3">
                                                <i class="fas fa-user-shield"></i>
                                            </div>

                                            <span class="font-medium text-slate-900">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        </div>

                                        <p class="text-sm text-slate-600 leading-relaxed">
                                            Akses sesuai role
                                            <span class="font-medium">{{ $role->name }}</span>
                                        </p>
                                    </label>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 col-span-full">
                                    Role tidak tersedia
                                </p>
                            @endforelse
                        </div>
                        @error('roleId')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-button type="button" size="lg" rounded="md" wire:click="goToStep2">
                        Lanjutkan
                    </x-button>
                </div>
            </div>
            @else

            <!-- Langkah 2: Informasi Pribadi -->
            <div class="mb-8">
                <div class="mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Pribadi</h3>
                        <p class="text-sm text-slate-500">Langkah 2 — data identitas pengguna</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 form-grid">
                    @if (! $isSsoMode)
                        <x-form.input name="name" label="Name" wire:model.lazy="name" required placeholder="Masukkan name" />

                        <x-form.input name="full_name" label="Nama Lengkap" wire:model.lazy="full_name" required placeholder="Masukkan nama lengkap" helper="Contoh: Sarah Putri" />
                    @endif

                    <x-form.input type="email" name="email" label="Email" wire:model.lazy="email" required placeholder="contoh@kemnaker.go.id" helper="Email SSO Siapkerja pengguna" />

                    @if (! $isSsoMode)
                        <x-form.input type="tel" name="phone" label="Nomor Telepon" wire:model.lazy="phone" required placeholder="081234567890" helper="Format: 081234567890" inputmode="numeric" />
                    @endif

                    @if (empty($roleId))
                        <div class="md:col-span-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            <i class="fas fa-arrow-up mt-0.5"></i>
                            <p>Pilih role terlebih dahulu untuk menampilkan form wilayah dan instansi.</p>
                        </div>
                    @elseif ($scope === 'pusat')
                        <x-form.input name="lembaga" label="Kementerian/Lembaga" :value="$pusatInstitution" disabled helper="Akun pusat otomatis terafiliasi Kementerian Ketenagakerjaan" />

                        <x-form.input name="unitKerja" label="Unit Kerja" wire:model.lazy="unitKerja" required placeholder="Contoh: Direktorat Bina Perencanaan" helper="Diisi manual sesuai unit kerja pengguna" />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Surat Tugas <span class="text-red-500">*</span>
                            </label>
                            <input type="file" wire:model="suratTugas" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white text-sm text-slate-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:bg-indigo-600 file:text-white file:text-sm file:font-semibold hover:file:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="mt-1 text-xs text-slate-500">PDF/JPG/PNG, maks 5 MB</p>
                            <p wire:loading wire:target="suratTugas" class="mt-1 text-xs text-indigo-600">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...
                            </p>
                            @error('suratTugas')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif ($scope === null)
                        <div class="md:col-span-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            <i class="fas fa-circle-info mt-0.5"></i>
                            <p>Role user: wilayah dan instansi diisi mandiri oleh pengguna saat pertama kali membuka dashboard.</p>
                        </div>
                    @else
                        @if ($scope === 'kabkota' || $scope === 'provinsi')
                            <x-form.select name="province" label="Provinsi" wire:model.live="province" helper="Pilih provinsi untuk memfilter data di bawahnya">
                                <option value="">Pilih provinsi</option>
                                @foreach ($this->provinces as $prov)
                                    <option value="{{ $prov->code }}">{{ $prov->name }}</option>
                                @endforeach
                            </x-form.select>
                        @endif

                        @if ($scope === 'kabkota')
                            <div>
                                @if (empty($province))
                                    <x-form.select name="regency" label="Kabupaten/Kota" wire:model.lazy="regency" disabled class="disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" helper="Pilih provinsi terlebih dahulu">
                                        <option value="">Pilih provinsi terlebih dahulu</option>
                                    </x-form.select>
                                @else
                                    <x-form.select name="regency" label="Kabupaten/Kota" wire:model.lazy="regency" helper="Pilih kabupaten/kota">
                                        <option value="">Pilih kabupaten/kota</option>
                                        @foreach ($this->regencies as $reg)
                                            <option value="{{ $reg->code }}">{{ $reg->name }}</option>
                                        @endforeach
                                    </x-form.select>
                                @endif
                                <p wire:loading wire:target="province" class="mt-1 text-xs text-indigo-600">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat kabupaten/kota...
                                </p>
                            </div>
                        @endif

                        @if ($needsInstansiForm)
                            <div>
                                <x-form.select name="instansiOption" label="Instansi" wire:model.lazy="instansiOption" helper="Pilih instansi sesuai wilayah atau tambah manual bila belum terdaftar">
                                    <option value="">Pilih instansi</option>
                                    @foreach ($this->institutions as $inst)
                                        <option value="{{ $inst->name }}">{{ $inst->name }}</option>
                                    @endforeach
                                    <option value="__custom">Lainnya — tulis manual</option>
                                </x-form.select>
                                <p wire:loading wire:target="province,regency" class="mt-1 text-xs text-indigo-600">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat daftar instansi...
                                </p>
                            </div>

                            @if ($instansiOption === '__custom')
                                <x-form.input name="instansiCustom" label="Nama Instansi Baru" wire:model.lazy="instansiCustom" required placeholder="Tulis nama instansi" helper="Akan dicatat ke data master instansi" />
                            @endif
                        @endif

                        <x-form.input name="unitKerja" label="Unit Kerja" wire:model.lazy="unitKerja" required placeholder="Contoh: Bidang Pelatihan" helper="Diisi manual sesuai unit kerja pengguna" />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Surat Tugas <span class="text-red-500">*</span>
                            </label>
                            <input type="file" wire:model="suratTugas" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white text-sm text-slate-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:bg-indigo-600 file:text-white file:text-sm file:font-semibold hover:file:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="mt-1 text-xs text-slate-500">PDF/JPG/PNG, maks 5 MB</p>
                            <p wire:loading wire:target="suratTugas" class="mt-1 text-xs text-indigo-600">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...
                            </p>
                            @error('suratTugas')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-between gap-3 action-buttons form-section">
                <x-button type="button" variant="secondary" size="lg" rounded="md" wire:click="goToStep1" icon="fas fa-arrow-left">
                    Kembali
                </x-button>
                <x-button type="submit" id="btn-submit" size="lg" rounded="md" icon="fas fa-save">
                    Simpan User
                </x-button>
            </div>
            @endif
        </form>
    </div>
</div>

