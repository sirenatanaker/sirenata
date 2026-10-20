@props([
    'color' => 'slate',
    'text' => null,
])

@php
    $colorClass =
        [
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'green-solid' => 'bg-emerald-600 text-white border-emerald-600 font-semibold',
            'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'success-solid' => 'bg-emerald-600 text-white border-emerald-600 font-semibold',
            'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            'indigo-solid' => 'bg-indigo-600 text-white border-indigo-600 font-semibold',
            'blue' => 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'primary' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            'amber' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'amber-solid' => 'bg-amber-500 text-white border-amber-500 font-semibold',
            'red-solid' => 'bg-rose-600 text-white border-rose-600 font-semibold',
            'danger-solid' => 'bg-rose-600 text-white border-rose-600 font-semibold',
            'blue-solid' => 'bg-blue-600 text-white border-blue-600 font-semibold',
            'yellow' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'red' => 'bg-red-50 text-red-700 border-red-200/60 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
            'danger' => 'bg-red-50 text-red-700 border-red-200/60 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
            'slate' => 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            'slate-solid' => 'bg-slate-600 text-white border-slate-600 font-semibold',
            'gray' => 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            'secondary' => 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        ][$color] ?? 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {$colorClass} transition-colors duration-150"]) }}>
    {{ $text ?? $slot }}
</span>
