@props([
    'active' => 'all',
    'total' => 0,
])

<div class="mb-6 sm:mb-8 border-b border-slate-200">
    <nav class="flex space-x-6 overflow-x-auto pb-[-1px]">
        <a href="{{ route('user.course.my-course') }}"
            class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors {{ $active === 'all' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium' }}">
            <span>Semua{{ $active === 'all' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
        <a href="{{ route('user.course.my-course.progress') }}"
            class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors {{ $active === 'progress' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium' }}">
            <span>Belum Selesai{{ $active === 'progress' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
        <a href="{{ route('user.course.my-course.finish') }}"
            class="border-b-2 py-3 px-1 text-sm whitespace-nowrap transition-colors {{ $active === 'finish' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium' }}">
            <span>Selesai{{ $active === 'finish' ? ' (' . ($total ?? 0) . ')' : '' }}</span>
        </a>
    </nav>
</div>
