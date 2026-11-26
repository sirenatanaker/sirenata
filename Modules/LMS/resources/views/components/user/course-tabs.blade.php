@props([
    'active' => 'all',
    'total' => 0,
])

<div class="mb-4 sm:mb-8 border-b border-slate-200 dark:border-slate-700/80">
    <nav class="flex space-x-4 sm:space-x-6 overflow-x-auto -mb-px">
        <a href="{{ route('user.course.my-course') }}"
            class="border-b-2 py-2 sm:py-3 px-0.5 sm:px-1 text-xs sm:text-sm whitespace-nowrap transition-colors {{ $active === 'all' ? 'border-[#13416B] text-[#13416B] dark:border-white dark:text-white font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 font-medium' }}">
            <span>Semua{{ $active === 'all' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
        <a href="{{ route('user.course.my-course.progress') }}"
            class="border-b-2 py-2 sm:py-3 px-0.5 sm:px-1 text-xs sm:text-sm whitespace-nowrap transition-colors {{ $active === 'progress' ? 'border-[#13416B] text-[#13416B] dark:border-white dark:text-white font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 font-medium' }}">
            <span>Sedang Berlangsung{{ $active === 'progress' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
        <a href="{{ route('user.course.my-course.finish') }}"
            class="border-b-2 py-2 sm:py-3 px-0.5 sm:px-1 text-xs sm:text-sm whitespace-nowrap transition-colors {{ $active === 'finish' ? 'border-[#13416B] text-[#13416B] dark:border-white dark:text-white font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600 font-medium' }}">
            <span>Selesai{{ $active === 'finish' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
    </nav>
</div>
