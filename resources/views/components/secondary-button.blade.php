@props(['size' => 'md', 'tone' => 'default'])

@php
    $sizeClasses = match ($size) {
        'xs' => 'sm:min-h-[30px] px-2.5 py-1 text-[11px]',
        'sm' => 'sm:min-h-[38px] px-4 py-2 text-xs',
        default => 'px-4 py-2 text-xs',
    };
    $toneClasses = match ($tone) {
        'danger' => 'border-rose-300 dark:border-rose-500/30 text-rose-600 dark:text-rose-400 focus:ring-rose-500 bg-white hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-slate-700',
        'emerald' => 'border-emerald-300 dark:border-emerald-500/30 text-emerald-600 dark:text-emerald-400 focus:ring-emerald-500 bg-white hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-slate-700',
        default => 'border-slate-200 hover:border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:ring-emerald-500 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700',
    };
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex flex-row items-center justify-center gap-2 whitespace-nowrap min-h-[44px] '.$sizeClasses." border {$toneClasses} rounded-lg font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900 disabled:opacity-50 transition ease-in-out duration-150 cursor-pointer shadow-xs dark:shadow-none"]) }}>
    {{ $slot }}
</button>
