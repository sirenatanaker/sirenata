<div x-data="{ showScrollTop: false }" @scroll.window="showScrollTop = (window.pageYOffset > 400)"
    class="fixed bottom-6 right-6 z-50">
    <button x-show="showScrollTop" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10"
        class="w-12 h-12 flex items-center justify-center bg-[#13416B] text-white rounded-full shadow-lg hover:bg-blue-800 hover:-translate-y-1 transition-all focus:outline-none border-2 border-white/20">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>
