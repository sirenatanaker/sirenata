<div>
    <div class="my-3">
        <x-validation-errors />
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-6 card-hover">
        <form wire:submit="store">
            <!-- Informasi Pribadi -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Pribadi</h3>
                        <p class="text-sm text-slate-500">Data identitas pengguna</p>
                    </div>
                </div>

                @if ($isSsoMode)
                    <div class="mb-4 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                        <i class="fas fa-circle-info mt-0.5"></i>
                        <p>Mode SSO aktif: nama dan nomor telepon diisi otomatis dari akun Siapkerja saat pengguna pertama kali login.</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 form-grid">
                    @if (! $isSsoMode)
                        <x-form.input name="name" label="Name" wire:model.lazy="name" required placeholder="Masukkan name" />

                        <x-form.input name="full_name" label="Nama Lengkap" wire:model.lazy="full_name" required placeholder="Masukkan nama lengkap" helper="Contoh: Sarah Putri" />
                    @endif

                    <x-form.input type="email" name="email" label="Email" wire:model.lazy="email" required placeholder="contoh@kemnaker.go.id" helper="Email SSO Siapkerja pengguna" />

                    @if (! $isSsoMode)
                        <x-form.input type="tel" name="phone" label="Nomor Telepon" wire:model.lazy="phone" required placeholder="081234567890" helper="Format: 081234567890" inputmode="numeric" />
                    @endif

                    <x-form.input name="instansi" label="Instansi" wire:model.lazy="instansi" required placeholder="Contoh: Dinas Tenaga Kerja Prov. Jawa Barat" />

                    <x-form.select name="province" label="Provinsi" wire:model.live="province" helper="Pilih provinsi untuk memfilter kabupaten/kota">
                        <option value="">Pilih provinsi</option>
                        @foreach ($this->provinces as $province)
                            <option value="{{ $province->code }}">{{ $province->name }}</option>
                        @endforeach
                    </x-form.select>

                    <div>
                        @if (empty($province))
                            <x-form.select name="regency" label="Kabupaten/Kota" wire:model.lazy="regency" disabled class="disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" helper="Pilih provinsi terlebih dahulu">
                                <option value="">Pilih provinsi terlebih dahulu</option>
                            </x-form.select>
                        @else
                            <x-form.select name="regency" label="Kabupaten/Kota" wire:model.lazy="regency" helper="Pilih kabupaten/kota">
                                <option value="">Pilih kabupaten/kota</option>
                                @foreach ($this->regencies as $regency)
                                    <option value="{{ $regency->code }}">{{ $regency->name }}</option>
                                @endforeach
                            </x-form.select>
                        @endif
                        <p wire:loading wire:target="province" class="mt-1 text-xs text-indigo-600">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat kabupaten/kota...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Role -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Role dan Izin Akses</h3>
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
                                    <input type="checkbox" id="role_{{ $role->uuid }}" wire:model="roleIds"
                                        value="{{ $role->uuid }}" class="hidden peer"
                                        :disabled="$wire.roleIds.length >= 2 && !$wire.roleIds.includes('{{ $role->uuid }}')">

                                    <label for="role_{{ $role->uuid }}"
                                        class="flex flex-col h-full p-4 border-2 border-slate-200 rounded-xl cursor-pointer
                                        hover:border-indigo-300
                                        peer-checked:border-indigo-500
                                        peer-checked:bg-indigo-50
                                        peer-disabled:opacity-50
                                        peer-disabled:cursor-not-allowed
                                        transition">
                                        <div class="flex items-center mb-2">
                                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 mr-3">
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
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 action-buttons form-section">
                <x-button type="submit" id="btn-submit" icon="fas fa-save">
                    Simpan User
                </x-button>
            </div>
        </form>
    </div>
</div>

