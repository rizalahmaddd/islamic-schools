@props(['icon', 'label', 'tone' => 'default'])

@php
    $toneClasses = match ($tone) {
        'edit' => 'text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10',
        'danger' => 'text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10',
        default => 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800',
    };
@endphp

<button {{ $attributes->merge([
    'type' => 'button',
    'aria-label' => $label,
    'title' => $label,
    'class' => "inline-flex items-center justify-center shrink-0 w-11 h-11 sm:w-8 sm:h-8 rounded-lg {$toneClasses} focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 disabled:opacity-50 disabled:pointer-events-none transition cursor-pointer",
]) }}>
    <i data-lucide="{{ $icon }}" aria-hidden="true" class="w-4 h-4"></i>
</button>
