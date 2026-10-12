@props([
    'variant' => 'primary',
    'size' => 'md',
    'rounded' => null,
    'icon' => null,
    'type' => 'button',
    'href' => null
])

@php
    $baseClass = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
    ][$size] ?? 'px-4 py-2.5 text-sm gap-2';

    $defaultRadius = [
        'sm' => 'rounded-md',
        'md' => 'rounded-lg',
        'lg' => 'rounded-xl',
    ][$size] ?? 'rounded-lg';

    $radiusClass = $rounded ? "rounded-{$rounded}" : $defaultRadius;

    $variantClasses = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 shadow-sm shadow-indigo-100 border border-transparent',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-200 border border-transparent',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 shadow-sm shadow-emerald-100 border border-transparent',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm shadow-red-100 border border-transparent',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-400 shadow-sm shadow-amber-100 border border-transparent',
        'white' => 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 focus:ring-slate-100 shadow-sm shadow-slate-50',
    ][$variant] ?? 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 shadow-sm shadow-indigo-100 border border-transparent';

    $classes = "{$baseClass} {$sizeClasses} {$radiusClass} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} shrink-0"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} shrink-0"></i>
        @endif
        {{ $slot }}
    </button>
@endif
