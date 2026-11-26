@props([
    'courses',
    'hasMore' => false,
    'nextPage' => null,
    'fetchUrl' => null,
])

@php
    $baseUrl = $fetchUrl ?? url()->current();
@endphp

<div
    x-data="{
        hasMore: @js((bool) $hasMore),
        nextPage: @js($nextPage),
        loadingMore: false,
        loadError: false,
        baseUrl: @js($baseUrl),
        observer: null,
        init() {
            this.$nextTick(() => this.setupObserver());
        },
        setupObserver() {
            if (!this.hasMore || !this.$refs.scrollSentinel) return;
            if (this.observer) this.observer.disconnect();
            this.observer = new IntersectionObserver((entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    this.loadMore();
                }
            }, { rootMargin: '250px 0px' });
            this.observer.observe(this.$refs.scrollSentinel);
        },
        async loadMore() {
            if (this.loadingMore || !this.hasMore || !this.nextPage) return;
            this.loadingMore = true;
            this.loadError = false;
            try {
                const url = new URL(this.baseUrl, window.location.origin);
                const currentParams = new URLSearchParams(window.location.search);
                currentParams.forEach((v, k) => {
                    if (k !== 'page' && k !== 'partial') url.searchParams.set(k, v);
                });
                url.searchParams.set('page', String(this.nextPage));
                url.searchParams.set('partial', '1');

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();

                if (data.html && this.$refs.courseGrid) {
                    this.$refs.courseGrid.insertAdjacentHTML('beforeend', data.html);
                }

                this.hasMore = Boolean(data.hasMore);
                this.nextPage = data.nextPage ?? null;

                if (!this.hasMore && this.observer) {
                    this.observer.disconnect();
                } else if (this.hasMore) {
                    this.$nextTick(() => {
                        const sentinel = this.$refs.scrollSentinel;
                        if (sentinel && sentinel.getBoundingClientRect().top <= window.innerHeight + 250) {
                            this.loadMore();
                        }
                    });
                }
            } catch (e) {
                this.loadError = true;
            } finally {
                this.loadingMore = false;
            }
        }
    }"
>
    @if (!empty($courses) && count($courses) > 0)
        <div x-ref="courseGrid" class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-y-8 gap-x-3 sm:gap-6 items-stretch">
            @include('lms::user.course.partials.course-cards', ['courses' => $courses])
        </div>

        {{-- Sentinel & Indikator Infinite Scroll --}}
        <div x-ref="scrollSentinel" class="mt-8 flex flex-col items-center justify-center min-h-[2.5rem]">
            <div x-show="loadingMore" x-cloak class="inline-flex items-center gap-2.5 text-xs sm:text-sm font-medium text-slate-500">
                <svg class="animate-spin w-4 h-4 text-[#13416B]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>Memuat pelatihan lainnya...</span>
            </div>

            <div x-show="loadError && hasMore && !loadingMore" x-cloak class="text-center">
                <button type="button" @click="loadMore()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-semibold text-[#13416B] bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors cursor-pointer">
                    <i class="fas fa-rotate-right text-xs"></i>
                    <span>Gagal memuat. Coba lagi</span>
                </button>
            </div>
        </div>
    @else
        {{ $empty }}
    @endif
</div>
