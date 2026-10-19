@props(['active' => true, 'onLabel' => 'Aktif', 'offLabel' => 'Nonaktif'])

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border '
        . ($active
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30'
            : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700')]) }}
>
    {{ $active ? $onLabel : $offLabel }}
</span>
