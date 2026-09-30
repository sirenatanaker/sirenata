<x-dashboard::layouts.dashboard title="Persetujuan Proyek Daerah">
    <div class="p-2 sm:p-6">
        <!-- Breadcrumb Profesional khusus Proyek Daerah -->
        <x-breadcrumb :items="[
            ['label' => 'Proyek Daerah', 'url' => route($routePrefix . 'index', ['type' => 'daerah'])],
            ['label' => 'Tinjau & Setujui'],
        ]" />

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 max-w-full mx-auto">
            <!-- Header -->
            <div class="mb-6 border-b border-slate-100 pb-5">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Tinjau & Setujui Proyek
                    Daerah</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Tinjau draft usulan dari daerah dan tentukan aturan
                    kursus prasyarat sebelum disetujui.</p>
                <x-validation-errors class="mt-4" />
            </div>

            <!-- BAGIAN 1: Informasi Proyek (Read-Only) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-8">
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 border-b border-slate-200 pb-2">
                    Detail Usulan Proyek</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama
                            Proyek</span>
                        <span class="text-sm font-bold text-slate-900">{{ $project->name }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe
                            Wilayah</span>
                        <x-badge color="indigo" :text="$project->type" />
                    </div>
                    <div>
                        <span
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Periode
                            Proyek</span>
                        <span class="text-sm font-medium text-slate-700">
                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}
                            s/d
                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Surat
                            Keputusan (SK)</span>
                        @if ($project->sk_document)
                            <a href="{{ asset('storage/' . $project->sk_document) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 shadow-sm text-slate-700 rounded-md text-xs font-bold hover:bg-slate-100 transition">
                                <i class="fas fa-file-pdf text-red-500 text-sm"></i> Buka File SK
                            </a>
                        @else
                            <span
                                class="inline-flex items-center text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-md border border-red-100">
                                Belum Ada SK
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: Form Pengaturan (Prasyarat & Status ACC) -->
            <form action="{{ route($routePrefix . 'update-prerequisite', $project->id) }}" method="POST"
                class="space-y-7">
                @csrf
                @method('PATCH')

                <!-- Atur Prasyarat -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Pengaturan Prasyarat Ketua dan Anggota Tim</h3>
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 sm:p-5">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <div class="pt-0.5">
                                <input type="checkbox" name="is_prerequisite_active" id="is_prerequisite_active"
                                    value="1"
                                    class="w-5 h-5 text-indigo-600 border-indigo-300 rounded focus:ring-indigo-500 transition-colors"
                                    {{ old('is_prerequisite_active', $project->is_prerequisite_active) ? 'checked' : '' }}
                                    onchange="toggleCourseSelection()">
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-slate-900">Wajibkan Prasyarat Kursus
                                    LMS</span>
                                <span class="block text-xs text-slate-600 mt-1 leading-relaxed">
                                    Jika diaktifkan, Admin Daerah hanya dapat menugaskan Ketua atau Anggota Tim yang
                                    telah menyelesaikan <b>100% progress</b> dari semua kursus yang Anda tentukan di
                                    bawah ini.
                                </span>
                            </div>
                        </label>

                        @php
                            $selectedCourseIds = collect(
                                old('prerequisite_course_ids', $project->prerequisiteCourseIds()),
                            )
                                ->map(fn($id) => (string) $id)
                                ->values();
                        @endphp
                        <div id="courseSelectionDiv"
                            class="mt-4 pt-4 border-t border-indigo-100/60 {{ old('is_prerequisite_active', $project->is_prerequisite_active) ? 'block' : 'hidden' }}">
                            <div class="mb-4">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <label class="block text-sm font-semibold text-slate-800">Kursus prasyarat
                                        terpilih</label>
                                    <span id="selectedCourseCount"
                                        class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-2.5 py-1 rounded-full">0
                                        kursus</span>
                                </div>
                                <div id="selectedCoursesList"
                                    class="min-h-12 flex flex-wrap gap-2 p-3 bg-white border border-indigo-100 rounded-xl">
                                    <span id="emptySelectedCourses" class="text-sm text-slate-400 self-center">Belum ada
                                        kursus yang dipilih.</span>
                                </div>
                            </div>

                            <div class="relative" id="coursePicker">
                                <label for="courseSearch" class="block text-sm font-semibold text-slate-800 mb-2">Tambah
                                    kursus prasyarat</label>
                                <button type="button" id="coursePickerButton" aria-expanded="false"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-white border border-slate-300 rounded-xl text-left shadow-sm hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                                    <span class="flex items-center gap-2 text-sm text-slate-500"><i
                                            class="fas fa-plus-circle text-indigo-500"></i> Pilih kursus dari
                                        katalog</span>
                                    <i id="coursePickerChevron"
                                        class="fas fa-chevron-down text-xs text-slate-400 transition-transform"></i>
                                </button>

                                <div id="coursePickerMenu"
                                    class="hidden absolute z-20 w-full mt-2 p-2 bg-white border border-slate-200 rounded-xl shadow-xl shadow-slate-200/60">
                                    <div class="relative mb-2">
                                        <i
                                            class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input type="search" id="courseSearch" placeholder="Cari nama kursus..."
                                            class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                                    </div>
                                    <div id="courseOptions" class="max-h-56 overflow-y-auto space-y-1">
                                        @foreach ($courses as $course)
                                            <label data-course-option data-course-name="{{ strtolower($course->name) }}"
                                                class="flex items-center gap-3 p-3 rounded-lg cursor-pointer hover:bg-indigo-50 transition">
                                                <input type="checkbox" name="prerequisite_course_ids[]"
                                                    value="{{ $course->id }}" data-course-label="{{ $course->name }}"
                                                    class="course-option w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                                                    @checked($selectedCourseIds->contains((string) $course->id))>
                                                <span class="text-sm text-slate-700">{{ $course->name }}</span>
                                            </label>
                                        @endforeach
                                        <p id="noCourseResults" class="hidden p-3 text-sm text-center text-slate-400">
                                            Kursus tidak ditemukan.</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Semua kursus yang dipilih harus mencapai progress
                                100% sebelum Ketua atau Anggota Tim dapat ditambahkan.</p>
                        </div>
                    </div>
                </div>

                <!-- Keputusan ACC -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Keputusan Persetujuan</h3>
                    <x-form.select id="status" name="status" label="Status Proyek" required>
                        <option value="Draft" @selected(old('status', $project->status) == 'Draft')>Draft (Tunda / Perlu Revisi)</option>
                        <option value="Menunggu Tim" @selected(old('status', $project->status) == 'Menunggu Tim')>
                            {{ $project->status === 'Kedaluwarsa' ? 'Buka Kembali (Menunggu Tim)' : 'Setujui (Menunggu Tim)' }}
                        </option>
                    </x-form.select>
                    <p class="text-xs text-slate-500 mt-1.5">
                        <i class="fas fa-info-circle mr-1"></i> Pilih <b>Setujui</b> agar Admin Daerah bisa mulai
                        memasukkan nama Ketua dan Anggota Tim.
                    </p>
                </div>

                <!-- Aksi (Arahkan Batal ke Proyek Daerah) -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-6 border-t border-slate-100">
                    <x-button :href="route($routePrefix . 'index', ['type' => 'daerah', 'status' => 'Draft'])" variant="secondary" class="flex-1">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" class="flex-1">
                        Simpan & Setujui
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const courseSelectionState = {
                isOpen: false,
            };

            function toggleCourseSelection() {
                const isChecked = document.getElementById('is_prerequisite_active').checked;
                const courseDiv = document.getElementById('courseSelectionDiv');

                if (isChecked) {
                    courseDiv.classList.remove('hidden');
                } else {
                    courseDiv.classList.add('hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const picker = document.getElementById('coursePicker');
                const pickerButton = document.getElementById('coursePickerButton');
                const pickerMenu = document.getElementById('coursePickerMenu');
                const pickerChevron = document.getElementById('coursePickerChevron');
                const searchInput = document.getElementById('courseSearch');
                const options = [...document.querySelectorAll('.course-option')];
                const selectedList = document.getElementById('selectedCoursesList');
                const selectedCount = document.getElementById('selectedCourseCount');
                const emptyState = document.getElementById('emptySelectedCourses');
                const noResults = document.getElementById('noCourseResults');

                function closePicker() {
                    courseSelectionState.isOpen = false;
                    pickerMenu.classList.add('hidden');
                    pickerButton.setAttribute('aria-expanded', 'false');
                    pickerChevron.classList.remove('rotate-180');
                }

                function renderSelectedCourses() {
                    selectedList.querySelectorAll('[data-selected-course]').forEach((item) => item.remove());
                    const selectedOptions = options.filter((option) => option.checked);
                    selectedCount.textContent = `${selectedOptions.length} kursus`;
                    emptyState.classList.toggle('hidden', selectedOptions.length > 0);

                    selectedOptions.forEach((option) => {
                        const chip = document.createElement('span');
                        chip.dataset.selectedCourse = option.value;
                        chip.className =
                            'inline-flex items-center gap-2 max-w-full px-3 py-2 bg-indigo-50 border border-indigo-100 rounded-lg text-sm font-medium text-indigo-700';

                        const label = document.createElement('span');
                        label.className = 'truncate';
                        label.textContent = option.dataset.courseLabel;

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className =
                            'w-5 h-5 inline-flex items-center justify-center rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-700 transition';
                        removeButton.setAttribute('aria-label', `Hapus ${option.dataset.courseLabel}`);
                        removeButton.innerHTML = '<i class="fas fa-times text-xs"></i>';
                        removeButton.addEventListener('click', () => {
                            option.checked = false;
                            renderSelectedCourses();
                        });

                        chip.append(label, removeButton);
                        selectedList.appendChild(chip);
                    });
                }

                function filterCourses() {
                    const query = searchInput.value.trim().toLowerCase();
                    let visibleCount = 0;

                    document.querySelectorAll('[data-course-option]').forEach((option) => {
                        const isVisible = option.dataset.courseName.includes(query);
                        option.classList.toggle('hidden', !isVisible);
                        visibleCount += isVisible ? 1 : 0;
                    });

                    noResults.classList.toggle('hidden', visibleCount > 0);
                }
                pickerButton.addEventListener('click', () => {
                    courseSelectionState.isOpen = !courseSelectionState.isOpen;
                    pickerMenu.classList.toggle('hidden', !courseSelectionState.isOpen);
                    pickerButton.setAttribute('aria-expanded', courseSelectionState.isOpen ? 'true' : 'false');
                    pickerChevron.classList.toggle('rotate-180', courseSelectionState.isOpen);
                    if (courseSelectionState.isOpen) searchInput.focus();
                });

                options.forEach((option) => option.addEventListener('change', renderSelectedCourses));
                searchInput.addEventListener('input', filterCourses);
                document.addEventListener('click', (event) => {
                    if (!picker.contains(event.target)) closePicker();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closePicker();
                });

                renderSelectedCourses();
                toggleCourseSelection();
            });
        </script>
    @endpush
</x-dashboard::layouts.dashboard>
