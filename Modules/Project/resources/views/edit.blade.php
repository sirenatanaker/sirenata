<x-dashboard::layouts.dashboard title="Edit Proyek - E-Learning">
    @push('styles')
        @include('project::partials.create-styles')
    @endpush

    @php
        $placeholderPrefix = str_contains($routePrefix, 'pusat') ? 'RTKN' : 'RTKD';

        // Fetch data kursus prasyarat
        $prerequisiteIds = $project->prerequisiteCourseIds();
        $courseModelClass = match (true) {
            class_exists('Modules\LMS\Models\Course') => 'Modules\LMS\Models\Course',
            class_exists('Modules\Course\Models\Course') => 'Modules\Course\Models\Course',
            class_exists('Modules\Lms\Models\Course') => 'Modules\Lms\Models\Course',
            class_exists('App\Models\Course') => 'App\Models\Course',
            default => null,
        };
        $prerequisiteCourses =
            $courseModelClass && !empty($prerequisiteIds)
                ? $courseModelClass::whereIn('id', $prerequisiteIds)->get()
                : collect();

        // Mendapatkan data anggota tim yang sudah terpilih sebelumnya
        $selectedMembers =
            old('teamMembers') ??
            (is_array($project->team_members)
                ? $project->team_members
                : json_decode($project->team_members ?? '[]', true) ?? []);
    @endphp

    <!-- Inisialisasi Alpine.js x-data dengan membawa $users dan $selectedMembers -->
    <div class="p-2 sm:p-6" x-data="projectEditForm({{ json_encode($users ?? []) }}, {{ json_encode($selectedMembers) }})">
        <x-breadcrumb :items="[['label' => 'Proyek', 'url' => route($routePrefix . 'index')], ['label' => 'Edit Proyek']]" />

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 sm:p-8 max-w-full mx-auto">
            <div class="mb-6 sm:mb-8 border-b border-slate-100 pb-5 sm:pb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Edit Proyek</h1>
                    <p class="text-sm text-slate-500 mt-1">Perbarui data proyek dan Dokumen SK.</p>
                </div>
                @php
                    $statusColor = match ($project->status) {
                        'Menunggu Tim' => 'amber-solid',
                        'On Progress' => 'blue-solid',
                        'Completed' => 'green-solid',
                        'Kedaluwarsa' => 'red-solid',
                        default => 'slate-solid',
                    };
                @endphp
                <x-badge :color="$statusColor" :text="$project->status === 'Completed' ? 'Selesai' : ($project->status ?? 'Draft')" />
            </div>

            <x-validation-errors class="mb-6" />

            <form action="{{ route($routePrefix . 'update', $project->id) }}" method="POST"
                enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')
                <x-form.input name="proyekName" label="Nama Proyek" required
                    value="{{ old('proyekName', $project->name) }}"
                    placeholder="Contoh: {{ $placeholderPrefix }} Sektor Industri Manufaktur 2025" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <x-form.input type="date" id="startDate" name="startDate" label="Tanggal Mulai" required
                        value="{{ old('startDate', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}"
                        onchange="calculateDuration()" />
                    <x-form.input type="date" id="endDate" name="endDate" label="Tanggal Selesai" required
                        value="{{ old('endDate', $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}"
                        onchange="calculateDuration()" />
                </div>

                <input type="hidden" id="duration" name="duration" value="{{ old('duration', $project->duration) }}">
                <div
                    class="bg-indigo-50/50 border border-indigo-100 rounded-lg p-3 text-sm text-slate-700 font-medium flex items-center">
                    <i class="fas fa-clock text-indigo-500 mr-2 text-lg"></i>
                    Estimasi Durasi Proyek: &nbsp;<span id="durationText"
                        class="font-extrabold text-indigo-700 text-base">0 Hari</span>
                </div>

                <!-- Input Ubah Dokumen SK -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Dokumen SK Proyek</label>
                    @if ($project->sk_document)
                        <div class="mb-3">
                            <a href="{{ asset('storage/' . $project->sk_document) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-700 rounded-md text-xs font-bold hover:bg-slate-200 transition border border-slate-200">
                                <i class="fas fa-file-pdf text-red-500"></i> Lihat SK Saat Ini
                            </a>
                        </div>
                    @endif
                    <input type="file" name="sk_document" accept=".pdf"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-300 rounded-lg p-1.5 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <p class="text-xs text-slate-500 mt-1.5">Abaikan jika Anda tidak ingin mengubah/mengganti dokumen SK
                        yang ada.</p>
                </div>

                <!-- PENGATURAN TIM PROYEK (Ditampilkan saat status 'Menunggu Tim', 'On Progress', atau 'Completed') -->
                @if (in_array($project->status, ['Menunggu Tim', 'On Progress', 'Completed']))
                    <div class="border-t border-slate-200 pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">Pengaturan Tim Proyek</h3>
                            @if ($project->status === 'Menunggu Tim')
                                <span class="inline-flex items-center text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200">
                                    <i class="fas fa-clock mr-1.5"></i> Batas Penentuan Tim: Sisa {{ $project->sisa_hari_penentuan_tim }} Hari
                                </span>
                            @endif
                        </div>

                        @if ($project->is_prerequisite_active)
                            <div
                                class="bg-indigo-50 border border-indigo-100 text-indigo-800 p-4 rounded-lg text-sm mb-5 space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <i class="fas fa-graduation-cap text-indigo-600 mt-0.5 text-base"></i>
                                    <div>
                                        <span class="font-bold">Pusat mewajibkan prasyarat kursus.</span>
                                        <p class="text-xs text-indigo-600 mt-0.5">Hanya pengguna yang lulus kursus di
                                            bawah ini yang muncul dalam daftar pilihan tim.</p>
                                    </div>
                                </div>

                                @if ($prerequisiteCourses->count() > 0)
                                    <div class="pt-2 border-t border-indigo-100">
                                        <span
                                            class="block text-xs font-semibold text-indigo-900 uppercase tracking-wider mb-2">Daftar
                                            Kursus Prasyarat:</span>
                                        <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto pr-1">
                                            @foreach ($prerequisiteCourses as $course)
                                                <span
                                                    class="inline-block text-xs font-medium text-indigo-800 bg-white border border-indigo-200 px-3 py-1 rounded-full shadow-sm">
                                                    {{ $course->name ?? ($course->title ?? 'Kursus #' . $course->id) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="space-y-6">
                            <x-form.select id="teamLeader" name="teamLeader" label="Ketua Tim" required>
                                <option value="">Pilih Ketua Tim</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((old('teamLeader') ?? $project->team_leader) == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </x-form.select>

                            <!-- Dropdown Multi-Select Anggota Tim -->
                            <div class="relative" @click.outside="isMemberDropdownOpen = false">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-medium text-slate-700">Anggota Tim
                                        (Opsional)</label>
                                    <span class="text-sm text-slate-500"
                                        x-text="selectedMembers.length + ' Anggota Terpilih'"></span>
                                </div>

                                <!-- Badges Anggota Terpilih -->
                                <div class="flex flex-wrap gap-2 my-2.5" x-show="selectedMembers.length > 0">
                                    <template x-for="user in selectedMemberObjects" :key="user.id">
                                        <span
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <span x-text="user.name"></span>
                                            <button type="button" @click="removeMember(user.id)"
                                                class="text-indigo-400 hover:text-indigo-700 focus:outline-none">
                                                <i class="fas fa-times text-sm"></i>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <!-- Dropdown Trigger Button -->
                                <button type="button" @click="isMemberDropdownOpen = !isMemberDropdownOpen"
                                    class="w-full flex items-center justify-between bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <span
                                        x-text="selectedMembers.length > 0 ? selectedMembers.length + ' Anggota Terpilih' : '-- Pilih Anggota Tim --'"
                                        :class="selectedMembers.length > 0 ? 'text-slate-900 font-medium' : 'text-slate-400'"></span>
                                    <i class="fas fa-chevron-down text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': isMemberDropdownOpen }"></i>
                                </button>

                                <!-- Dropdown Menu Box -->
                                <div x-show="isMemberDropdownOpen" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute z-20 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg p-3.5 space-y-3">

                                    <!-- Searchbar di Dalam Dropdown -->
                                    <div class="relative">
                                        <input type="text" x-model.debounce.300ms="searchMember"
                                            placeholder="Cari nama anggota tim..."
                                            class="w-full text-sm rounded-md border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 pr-9">
                                        <i class="fas fa-search absolute right-3 top-3 text-slate-400 text-sm"></i>
                                    </div>

                                    <!-- List Checkbox Anggota Tim -->
                                    <div class="max-h-56 overflow-y-auto space-y-1 p-1">
                                        <template x-for="user in filteredMembersList" :key="user.id">
                                            <label
                                                class="flex items-center gap-3 p-2.5 rounded-md hover:bg-slate-50 cursor-pointer text-sm font-medium text-slate-700 transition">
                                                <input type="checkbox" name="teamMembers[]" :value="String(user.id)"
                                                    x-model="selectedMembers"
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4.5 h-4.5 cursor-pointer">
                                                <span class="line-clamp-1" x-text="user.name"></span>
                                            </label>
                                        </template>
                                        <div x-show="filteredMembersList.length === 0"
                                            class="text-sm text-slate-500 text-center py-4">
                                            Tidak ada anggota yang cocok.
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @elseif ($project->status === 'Kedaluwarsa')
                    <!-- TAMPILAN JIKA KEDALUWARSA -->
                    <div class="border-t border-slate-200 pt-6 mt-6">
                        <div class="bg-red-50 border border-red-200 p-4 rounded-lg flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                            <div class="text-sm text-red-800 leading-relaxed">
                                <strong>Penentuan Tim Kedaluwarsa</strong><br>
                                Batas waktu penentuan tim (14 hari) telah habis. Silakan hubungi Admin Pusat untuk membuka kembali akses penentuan tim proyek ini.
                            </div>
                        </div>
                    </div>
                @else
                    <!-- TAMPILAN JIKA MASIH DRAFT -->
                    <div class="border-t border-slate-200 pt-6 mt-6">
                        <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg flex items-start gap-3">
                            <i class="fas fa-clock text-amber-600 mt-0.5"></i>
                            <div class="text-sm text-amber-800 leading-relaxed">
                                <strong>Menunggu Persetujuan Pusat</strong><br>
                                Form pemilihan Ketua dan Anggota Tim dikunci dan akan muncul otomatis pada halaman ini
                                setelah Admin Pusat menyetujui dan mengaktifkan prasyarat proyek ini.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-6 border-t border-slate-100">
                    <x-button :href="route($routePrefix . 'index')" variant="secondary" class="flex-1">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" class="flex-1" :disabled="$project->status === 'Kedaluwarsa'">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('project::partials.create-scripts')
        <script>
            // Alpine JS Logic khusus halaman Edit
            document.addEventListener('alpine:init', () => {
                Alpine.data('projectEditForm', (allUsers, initialSelectedMembers) => ({
                    allUsers: allUsers,
                    // Konversi semua ID yang sudah terpilih ke string agar sinkron dengan input checkbox
                    selectedMembers: initialSelectedMembers.map(id => String(id)),
                    searchMember: '',
                    isMemberDropdownOpen: false,

                    get filteredMembersList() {
                        if (!this.searchMember.trim()) return this.allUsers;
                        return this.allUsers.filter(u =>
                            u.name.toLowerCase().includes(this.searchMember.toLowerCase())
                        );
                    },

                    get selectedMemberObjects() {
                        return this.allUsers.filter(u => this.selectedMembers.includes(String(u.id)));
                    },

                    removeMember(userId) {
                        this.selectedMembers = this.selectedMembers.filter(id => String(id) !== String(
                            userId));
                    }
                }));
            });

            // Native JS untuk Kalkulasi Durasi
            function calculateDuration() {
                const start = document.getElementById('startDate').value;
                const end = document.getElementById('endDate').value;
                const durationInput = document.getElementById('duration');
                const durationText = document.getElementById('durationText');

                if (start && end) {
                    const startDate = new Date(start);
                    const endDate = new Date(end);
                    const diffTime = endDate - startDate;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const days = diffDays < 0 ? 0 : diffDays;

                    durationInput.value = days;
                    durationText.innerText = days + ' Hari';
                } else {
                    durationInput.value = '0';
                    durationText.innerText = '0 Hari';
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                calculateDuration();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>