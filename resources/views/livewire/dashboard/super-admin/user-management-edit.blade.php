<div>
    <div class="my-3">
        <x-validation-errors />
    </div>
    <x-flash-message />
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 mb-6 card-hover">
        <form wire:submit="save">
            <!-- Informasi Pribadi -->
            <div class="mb-8">
                <div class="mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Pribadi</h3>
                        <p class="text-sm text-slate-500">Data identitas pengguna</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 form-grid">
                    @if (! $isSsoMode)
                        <x-form.input name="full_name" label="Nama Lengkap" wire:model.lazy="full_name" required placeholder="Masukkan nama lengkap" />
                    @endif

                    <x-form.input type="email" name="email" label="Email" wire:model.lazy="email" disabled placeholder="contoh@kemnaker.go.id" />

                    @if (! $isSsoMode)
                        <x-form.input type="tel" name="phone" label="Nomor Telepon" wire:model.lazy="phone" required placeholder="+62 812-3456-7890" inputmode="numeric" />
                    @endif

                    <x-form.input name="instansi" label="Instansi" wire:model.lazy="instansi" required placeholder="Contoh: Dinas Tenaga Kerja Prov. Jawa Barat" />

                    <x-form.input type="date" name="join_date" label="Tanggal Bergabung" wire:model.lazy="join_date" disabled />

                    <x-form.select name="province" label="Provinsi" wire:model.live="province" helper="Pilih provinsi untuk memfilter kabupaten/kota">
                        <option value="">Pilih provinsi</option>
                        @foreach ($this->provinces as $prov)
                            <option value="{{ $prov->code }}">{{ $prov->name }}</option>
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
                                @foreach ($this->regencies as $reg)
                                    <option value="{{ $reg->code }}">{{ $reg->name }}</option>
                                @endforeach
                            </x-form.select>
                        @endif
                        <p wire:loading wire:target="province" class="mt-1 text-xs text-indigo-600">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat kabupaten/kota...
                        </p>
                    </div>

                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status Akun <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <div class="flex items-center">
                                <input type="radio" id="status_aktif" name="status" value="aktif"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <label for="status_aktif" class="ml-2 block text-sm text-gray-700">
                                    Aktif
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" id="status_nonaktif" name="status" value="nonaktif"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <label for="status_nonaktif" class="ml-2 block text-sm text-gray-700">
                                    Nonaktif
                                </label>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>

            <!-- Informasi Akun -->

            <!-- Pengaturan Role -->
            <div class="mb-8">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Role dan Izin Akses</h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @forelse ($roles as $role)
                                <div class="relative">
                                    <input type="checkbox" id="role_{{ $role->uuid }}" wire:model="roleIds"
                                        value="{{ $role->uuid }}" class="hidden peer"
                                        :disabled="$wire.roleIds.length >= 2 && !$wire.roleIds.includes('{{ $role->uuid }}')">

                                    <label for="role_{{ $role->uuid }}"
                                        class="flex flex-col h-full p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed transition">
                                        <div class="flex items-center mb-2">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 mr-3">
                                                <i class="fas fa-user-cog"></i>
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

                        @error('roleIds')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pengaturan Notifikasi -->
            <div class="mb-8">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Pengaturan Notifikasi</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">Email Notifikasi</p>
                            <p class="text-xs text-gray-500">Kirim notifikasi via email</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" id="email_notifications" name="email_notifications"
                                class="sr-only">
                            <label for="email_notifications"
                                class="block h-6 rounded-full bg-gray-300 cursor-pointer transition-colors">
                                <span
                                    class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-transform duration-300"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 action-buttons form-section">
                <x-button type="submit" id="btn-submit" icon="fas fa-save">
                    Simpan Perubahan
                </x-button>

                <x-button href="{{ route('super-admin.user-management.index') }}" variant="secondary" icon="fas fa-times">
                    Batal
                </x-button>
            </div>
        </form>
    </div>
</div>
