<div
    class="course-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(99,102,241,0.3)] hover:border-indigo-500"
>
    <img
        src="{{ $course->thumbnail_url }}"
        alt="{{ $course->name }}"
        loading="lazy"
        class="w-full h-24 sm:h-40 object-cover"
    />

    <div class="p-3 sm:p-5">
        <div class="flex items-start justify-between mb-2">

            {{-- Badge --}}
            @if ($course->status === 'completed')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-600">
                    Selesai
                </span>
            @elseif ($course->status === 'in_progress')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">
                    Sedang Berjalan
                </span>
            @else
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                    Terdaftar
                </span>
            @endif

            <span class="text-xs text-gray-500">
                {{ $course->progress }}%
            </span>
        </div>

        {{-- Title --}}
        <h3 x-data="{
                isShrinking: false,
                init() {
                    if (typeof ResizeObserver !== 'undefined') {
                        const observer = new ResizeObserver(() => {
                            if (!this.isShrinking) {
                                this.shrink();
                            }
                        });
                        observer.observe(this.$el);
                    } else {
                        window.addEventListener('resize', () => this.shrink());
                    }
                },
                shrink() {
                    this.isShrinking = true;
                    const el = this.$el;
                    el.style.fontSize = '';
                    let fontSize = parseFloat(window.getComputedStyle(el).fontSize);
                    if (el.clientWidth > 0) {
                        while (el.scrollWidth > el.clientWidth && fontSize > 8) {
                            fontSize -= 0.5;
                            el.style.fontSize = fontSize + 'px';
                        }
                    }
                    setTimeout(() => {
                        this.isShrinking = false;
                    }, 0);
                }
            }"
            class="font-bold text-gray-900 mb-1 sm:mb-2 text-sm sm:text-lg whitespace-nowrap overflow-hidden text-ellipsis"
        >
            {{ $course->name }}
        </h3>

        {{-- Category --}}
        <p class="text-xs text-gray-400 mb-2 sm:mb-3">
            {{ $course->category }}
        </p>

        {{-- Progress --}}
        <div class="mb-3 sm:mb-4">
            <div class="w-full bg-gray-200 rounded-full h-1.5 sm:h-2">
                <div
                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300 {{ $course->status === 'completed' ? 'bg-emerald-600' : 'bg-amber-500' }}"
                    style="width: {{ $course->progress }}%"
                ></div>
            </div>
        </div>

        {{-- Button --}}
        @if ($course->status !== 'completed')
            <a
                href="{{ route('user.course.my-course.detail', ['slug' => $course->slug]) }}"
                class="block w-full bg-amber-500 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-amber-600 transition-colors text-center text-xs sm:text-sm"
            >
                Lanjutkan Belajar
            </a>
        @else
            <div class="flex items-center gap-2 w-full">
                <a
                    href="{{ route('user.course.my-course.detail', ['slug' => $course->slug]) }}"
                    class="w-1/2 bg-emerald-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-emerald-700 transition-colors text-center text-xs sm:text-sm"
                >
                    Detail
                </a>
                @if (!empty($course->certificate_file))
                    <a href="{{ $course->certificate_file }}" target="_blank" download
                        class="w-1/2 bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-1.5 text-center text-xs sm:text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="truncate">Unduh Sertifikat</span>
                    </a>
                @else
                    <a href="#"
                        onclick="event.preventDefault(); document.getElementById('gen-cert-form-{{ $course->slug }}').submit();"
                        class="w-1/2 bg-indigo-600 text-white py-2 sm:py-2.5 rounded-lg font-medium sm:font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-1.5 text-center text-xs sm:text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="truncate">Unduh Sertifikat</span>
                    </a>
                    <form id="gen-cert-form-{{ $course->slug }}" action="{{ route('user.course.my-course.generate-certificate', ['slug' => $course->slug]) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>