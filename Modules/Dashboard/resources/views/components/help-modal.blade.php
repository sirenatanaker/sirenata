{{-- Modal Pusat Bantuan global (pengganti halaman bantuan per role).
    Dibuka lewat event: $dispatch('open-help-modal'). Data FAQ diambil
    sekali saat pertama dibuka dari endpoint help.faqs. --}}
<div x-data="helpModal()" @open-help-modal.window="open = true; load()" x-show="open" x-cloak
    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-slate-900/60" @click="open = false" aria-hidden="true"></div>

    <div class="relative bg-white w-full sm:max-w-2xl max-h-[92dvh] sm:max-h-[85vh] rounded-t-2xl sm:rounded-2xl shadow-xl flex flex-col overflow-hidden"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#13416B]/10 text-[#13416B]">
                    <i class="fas fa-question-circle text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">Pusat Bantuan</h3>
                    <p class="text-xs text-slate-500">Jawaban cepat tanpa pindah halaman</p>
                </div>
            </div>
            <button type="button" @click="open = false"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="px-5 pt-4 shrink-0">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" x-model.debounce.200ms="query" placeholder="Cari bantuan atau pertanyaan..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm placeholder:text-slate-400 placeholder:font-normal focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <a href="mailto:support@sirenata.go.id"
                class="mt-3 flex items-center gap-3 p-3 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100/70 transition-colors">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-amber-600 border border-amber-100">
                    <i class="fas fa-envelope"></i>
                </span>
                <span>
                    <span class="block text-sm font-bold text-slate-800">Email Support</span>
                    <span class="block text-xs font-semibold text-amber-700">support@sirenata.go.id</span>
                </span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            <div x-show="loading" class="py-10 text-center text-sm text-slate-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Memuat bantuan...
            </div>

            <div x-show="!loading && filtered.length === 0" class="py-10 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-3 border border-slate-100">
                    <i class="fas fa-search text-xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ditemukan hasil.</p>
                <p class="text-xs text-slate-500 mt-1">Coba kata kunci lain atau hubungi email support.</p>
            </div>

            <div class="space-y-2.5" x-show="!loading && filtered.length > 0">
                <template x-for="faq in filtered" :key="faq.id">
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white"
                        :class="expanded === faq.id ? 'border-[#13416B]/30 ring-1 ring-[#13416B]/10' : ''">
                        <button type="button" @click="expanded = expanded === faq.id ? null : faq.id"
                            class="w-full px-4 py-3 flex items-center justify-between gap-3 text-left bg-slate-50/50 hover:bg-slate-50 transition-colors">
                            <span class="font-semibold text-sm text-slate-700" :class="expanded === faq.id ? 'text-[#13416B]' : ''" x-text="faq.question"></span>
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md transition-colors"
                                :class="expanded === faq.id ? 'bg-[#13416B]/10 text-[#13416B]' : 'text-slate-400'">
                                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="expanded === faq.id ? 'rotate-180' : ''"></i>
                            </span>
                        </button>
                        <div x-show="expanded === faq.id" x-collapse>
                            <div class="px-4 pb-4 pt-2 text-sm text-slate-600 leading-relaxed border-t border-slate-100" x-text="faq.answer"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function helpModal() {
        return {
            open: false,
            loading: false,
            loaded: false,
            query: '',
            faqs: [],
            expanded: null,
            get filtered() {
                const q = this.query.toLowerCase().trim();
                if (!q) return this.faqs;
                return this.faqs.filter((f) =>
                    (f.question || '').toLowerCase().includes(q) ||
                    (f.answer || '').toLowerCase().includes(q)
                );
            },
            async load() {
                if (this.loaded || this.loading) return;
                this.loading = true;
                try {
                    const res = await fetch('{{ route('help.faqs') }}', {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.faqs = json.data;
                        this.loaded = true;
                    }
                } catch (e) {}
                this.loading = false;
            },
        };
    }
</script>
