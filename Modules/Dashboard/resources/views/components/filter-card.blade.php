@props([
    'title',
    'total',
    'action' => null,
    'resetUrl' => null,
    'hasRequest' => count(request()->except(['page', '_token'])) > 0,
    'defaultOpen' => false,
    'density' => 'normal',
])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ showFilter: {{ ($hasRequest || $defaultOpen) ? 'true' : 'false' }} }">
    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-slate-800">{{ $title }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                Total: <span class="font-medium text-slate-700">{{ $total }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(isset($filter_inputs))
            <button type="button" @click="showFilter = !showFilter"
                class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 text-slate-700 bg-white text-sm font-medium rounded-md hover:bg-slate-50 transition-colors cursor-pointer"
                :class="showFilter ? 'bg-slate-100 border-slate-400 font-semibold' : ''">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            @endif
            
            {{ $actions ?? '' }}
        </div>
    </div>

    @if(isset($filter_inputs))
    <!-- Filter Form -->
    <form method="GET" action="{{ $action ?? url()->current() }}" class="p-5 border-b border-slate-200 bg-slate-50/50" x-show="showFilter" x-transition @if(!$hasRequest && !$defaultOpen) style="display: none;" @endif>
        <div class="flex flex-col sm:flex-row flex-wrap gap-4 items-end">
            
            <!-- Filter Inputs Slot -->
            {{ $filter_inputs }}

            <!-- Buttons -->
            <div class="flex gap-2 w-full sm:w-auto justify-end mt-4 sm:mt-0 shrink-0">
                <button type="submit" title="Cari" class="{{ $density === 'compact' ? 'w-9 h-9 rounded-md' : 'w-[42px] h-[42px] rounded-lg' }} inline-flex justify-center items-center bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ $resetUrl ?? url()->current() }}" title="Reset" class="{{ $density === 'compact' ? 'w-9 h-9 rounded-md' : 'w-[42px] h-[42px] rounded-lg' }} inline-flex justify-center items-center border border-slate-300 text-slate-600 text-sm font-medium hover:bg-slate-100 transition">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>
    @endif
    
    {{ $slot }}
</div>
