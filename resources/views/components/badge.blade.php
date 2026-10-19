@props(['color' => 'slate'])

@php
    // DESIGN.md "Palet Warna": emerald/rose/amber tetap warna fungsional (status nyata),
    // sky dipakai seperlunya untuk status netral-informatif seperti "Disetujui".
    $palette = [
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
        'rose' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30',
        'sky' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30',
    ][in_array($color, ['indigo', 'purple', 'violet', 'blue'], true) ? 'sky' : $color] ?? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold border {$palette}"]) }}>
    {{ $slot }}
</span>
