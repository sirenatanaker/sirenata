<x-dashboard::layouts.dashboard title="FAQ - E-Learning">
    <div class="p-2 sm:p-6">
        <x-breadcrumb :home="route('super-admin.dashboard')" :items="[
            ['label' => 'FAQ']
        ]" />

        @php
            $roleLabels = [];
            foreach ($targetRoles as $targetRole) {
                $roleLabels[$targetRole] = ucwords(str_replace(['-', '_'], ' ', $targetRole));
            }
            $roleBadgeColors = ['slate', 'indigo', 'amber', 'emerald', 'blue', 'red'];
        @endphp

        <x-dashboard::filter-card
            title="Daftar FAQ"
            :total="$faqs->total() . ' FAQ'"
            :resetUrl="route($routePrefix . 'index')">

            <x-slot name="actions">
                <x-button :href="route($routePrefix . 'export') . '?' . http_build_query(request()->only(['search', 'role']))" variant="success" icon="fas fa-download" title="Ekspor Data">
                    <span class="hidden sm:inline">Ekspor</span>
                </x-button>
                <x-button x-data x-on:click="$dispatch('open-modal', 'create-faq')" variant="primary" icon="fas fa-plus">
                    Tambah FAQ
                </x-button>
            </x-slot>

            <x-slot name="filter_inputs">
                <!-- Role target -->
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Filter Role
                    </label>
                    <div class="relative">
                        <i class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <select name="role" class="pl-9 pr-3 py-2.5 w-full rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Role</option>
                            @foreach ($targetRoles as $targetRole)
                                <option value="{{ $targetRole }}" @selected(request('role') === $targetRole)>{{ $roleLabels[$targetRole] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Per Page -->
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Data per Halaman
                    </label>
                    <select name="per_page" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ([10, 20, 50, 100] as $page)
                            <option value="{{ $page }}" {{ request('per_page') == $page ? 'selected' : '' }}>
                                {{ $page }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[240px] w-full">
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Pencarian
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari FAQ..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </x-slot>

            <x-table.table plain>
                <thead>
                    <tr>
                        <x-table.th align="left">No.</x-table.th>
                        <x-table.th>Pertanyaan</x-table.th>
                        <x-table.th>Ditujukan Untuk</x-table.th>
                        <x-table.th>Dibuat Oleh</x-table.th>
                        <x-table.th align="center">Aksi</x-table.th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($faqs as $key => $faq)
                        <tr class="hover:bg-slate-50 transition">
                            <x-table.td>
                                {{ $key + $faqs->firstItem() }}
                            </x-table.td>
                            <x-table.td class="font-medium">
                                {{ Str::limit($faq->question, 60) }}
                            </x-table.td>
                            <x-table.td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($faq->roles as $index => $faqRole)
                                        <x-badge :color="$roleBadgeColors[$index % count($roleBadgeColors)]" :text="$roleLabels[$faqRole->name] ?? $faqRole->name" />
                                    @empty
                                        <span class="text-xs text-slate-400">-</span>
                                    @endforelse
                                </div>
                            </x-table.td>
                            <x-table.td>
                                {{ $faq->creator->name ?? 'Sistem' }}
                            </x-table.td>
                            <x-table.td align="center">
                                <x-table.action>
                                    <li>
                                        <button x-data x-on:click="$dispatch('open-modal', 'show-faq-{{ $faq->id }}')"
                                            class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-slate-700 text-xs">Detail</button>
                                    </li>
                                    <li>
                                        <button x-data x-on:click="$dispatch('open-modal', 'edit-faq-{{ $faq->id }}')"
                                            class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-slate-700 text-xs">Ubah</button>
                                    </li>
                                    <li>
                                        <div class="inline-flex items-center w-full p-2 hover:bg-slate-100 rounded text-slate-700 text-xs">
                                            <x-modal-delete :id="'delete-faq-' . $faq->id" message="Apakah Anda yakin ingin menghapus FAQ ini?"
                                                :item-name="Str::limit($faq->question, 40)" buttonText="Hapus" buttonClass="w-full text-left text-red-600 outline-none cursor-pointer" :route="route($routePrefix . 'destroy', $faq->id)" />
                                        </div>
                                    </li>
                                </x-table.action>
                            </x-table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-table.td colspan="5" align="center" class="py-12">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <i class="fas fa-question-circle text-xl"></i>
                                    </div>
                                    <p class="text-base font-medium text-slate-700">Tidak ada FAQ yang ditemukan.</p>
                                </div>
                            </x-table.td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table.table>

            <div class="px-5 py-4 border-t border-slate-200">
                {{ $faqs->links('pagination::tailwind') }}
            </div>
        </x-dashboard::filter-card>
    </div>

    <x-modal name="create-faq" title="Tambah FAQ Baru">
        <form action="{{ route($routePrefix . 'store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.input name="question" label="Pertanyaan" :required="true" />

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                    Ditujukan Untuk (boleh lebih dari satu)
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach ($targetRoles as $targetRole)
                        <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-slate-300 cursor-pointer transition-all hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="roles[]" value="{{ $targetRole }}"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                                @checked(in_array($targetRole, old('roles', [$targetRoles[0] ?? 'user'])))>
                            {{ $roleLabels[$targetRole] }}
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <x-form.textarea name="answer" label="Jawaban" :rows="5" :required="true" />

            <div class="flex justify-end gap-3 pt-2">
                <x-button type="button" x-on:click="$dispatch('close-modal', 'create-faq')" variant="white">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    Simpan FAQ
                </x-button>
            </div>
        </form>
    </x-modal>

    @foreach($faqs as $faq)
        <x-modal name="show-faq-{{ $faq->id }}" title="Detail FAQ" maxWidth="sm:max-w-2xl">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Pertanyaan</h4>
                    <p class="text-gray-900 font-semibold">{{ $faq->question }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Ditujukan Untuk</h4>
                    <div class="flex flex-wrap gap-1">
                        @forelse ($faq->roles as $index => $faqRole)
                            <x-badge :color="$roleBadgeColors[$index % count($roleBadgeColors)]" :text="$roleLabels[$faqRole->name] ?? $faqRole->name" />
                        @empty
                            <span class="text-xs text-slate-400">-</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Jawaban</h4>
                    <div class="prose max-w-none text-gray-800 text-sm">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
                <div class="text-xs text-gray-400 pt-2 border-t">
                    Dibuat oleh: {{ $faq->creator->name ?? 'Sistem' }} &middot; {{ $faq->created_at->format('d M Y H:i') }}
                </div>
            </div>
        </x-modal>
    @endforeach

    @foreach($faqs as $faq)
        <x-modal name="edit-faq-{{ $faq->id }}" title="Edit FAQ">
            <form action="{{ route($routePrefix . 'update', $faq->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form.input name="question" label="Pertanyaan" :required="true" :value="old('question', $faq->question)" />

                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                        Ditujukan Untuk (boleh lebih dari satu)
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        @foreach ($targetRoles as $targetRole)
                            <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-slate-300 cursor-pointer transition-all hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="roles[]" value="{{ $targetRole }}"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                                    @checked(in_array($targetRole, old('roles', $faq->roles->pluck('name')->all())))>
                                {{ $roleLabels[$targetRole] }}
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-form.textarea name="answer" label="Jawaban" :rows="5" :required="true" :value="old('answer', $faq->answer)" />

                <div class="flex justify-end gap-3 pt-2">
                    <x-button type="button" x-on:click="$dispatch('close-modal', 'edit-faq-{{ $faq->id }}')" variant="white">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Perbarui FAQ
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-dashboard::layouts.dashboard>
