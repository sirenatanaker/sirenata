<x-dashboard::layouts.dashboard title="User Management Detail">
    <div class="p-2 sm:p-6">
        <!-- Breadcrumb Navigation -->
        <x-breadcrumb :items="[['label' => 'Manajemen Admin', 'url' => route('super-admin.user-management.index')], ['label' => 'Detail User']]" />

        <!-- Profile Section -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">

                <!-- Avatar -->
                <div class="relative shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&length=2&background=6366f1&color=fff&size=128&rounded=true&uppercase=true"
                        class="h-32 w-32 rounded-full shadow" alt="Avatar">
                </div>

                <!-- Profile Info -->
                <div class="flex-1 text-center sm:text-left">
                    <h2 id="admin-name" class="text-xl sm:text-2xl font-semibold text-slate-900">
                        {{ $user->name }}
                    </h2>

                    <p id="admin-role" class="text-sm text-indigo-600 font-medium mt-1">
                        {{ $user->roles->first()->name }}
                    </p>

                    <p id="last-updated" class="text-xs text-slate-400 mt-4">
                        Terakhir diperbarui:
                        <span id="last-updated-date"
                            class="font-medium text-slate-500">{{ $user->updated_at->diffForHumans() }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Informasi Pribadi -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Dasar -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 card-hover">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Dasar</h3>
                        <span class="text-xs text-gray-500">
                            Terakhir diperbarui: <span
                                id="last-updated">{{ $user->updated_at->diffForHumans() ?? '-' }}</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kolom kiri -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p id="admin-email" class="font-medium text-gray-900">{{ $user->email }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-1">Nomor Telepon</p>
                                <p id="admin-phone" class="font-medium text-gray-900">{{ $user->phone }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-1">Tanggal Bergabung</p>
                                <p id="join-date" class="font-medium text-gray-900">
                                    {{ $user->created_at->format('d M Y') }}</p>
                            </div>
                        </div>

                        <!-- Kolom kanan -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Jabatan</p>
                                <p id="last-login" class="font-medium text-gray-900">{{ $user->profile->jabatan }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-1">Provinsi</p>
                                <p id="last-ip" class="font-medium text-gray-900">
                                    {{ $user->scopeArea->province_name ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-1">Kab/Kota</p>
                                <p id="last-device" class="font-medium text-gray-900">
                                    {{ $user->scopeArea->regency_name ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-1">Surat Tugas</p>
                                @if ($user->profile?->surat_tugas)
                                    <a href="{{ Storage::url($user->profile->surat_tugas) }}" target="_blank" class="font-medium text-indigo-600 hover:underline">
                                        <i class="fas fa-file-pdf mr-1"></i> Lihat Dokumen
                                    </a>
                                @else
                                    <p class="font-medium text-gray-900">-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Statistik Aktivitas -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 card-hover">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Statistik Aktivitas</h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 stats-grid">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600 mr-3">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Login</p>
                                    <p id="total-login" class="font-bold text-gray-900 text-xl">-</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">30 hari terakhir</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600 mr-3">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Aksi Dilakukan</p>
                                    <p id="total-actions" class="font-bold text-gray-900 text-xl">-</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Hari ini</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="p-2 rounded-lg bg-amber-50 text-amber-600 mr-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Rata-rata Waktu</p>
                                    <p id="avg-time" class="font-bold text-gray-900 text-xl">-</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Per sesi</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="p-2 rounded-lg bg-blue-50 text-blue-600 mr-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Aktivitas Terakhir</p>
                                    <p id="last-activity" class="font-bold text-gray-900 text-xl">-</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Menit yang lalu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Sistem -->
            <div class="space-y-6">
                <!-- Status Sistem -->


                <!-- Log Aktivitas Terbaru -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:p-6 card-hover">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Log Aktivitas</h3>
                        <a href="#" class="text-sm text-indigo-600 hover:underline">Lihat Semua</a>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg">
                            <div class="p-2 rounded-lg bg-blue-100 text-blue-600">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Login Berhasil</p>
                                <p class="text-sm text-gray-600">Dari Jakarta, Chrome v120</p>
                                <p class="text-xs text-gray-500 mt-1">Hari ini, 08:45</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg">
                            <div class="p-2 rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fas fa-file-edit"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Update Data Pengguna</p>
                                <p class="text-sm text-gray-600">Mengupdate profile user</p>
                                <p class="text-xs text-gray-500 mt-1">Kemarin, 14:20</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard::layouts.dashboard>
