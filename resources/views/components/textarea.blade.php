@props(['disabled' => false, 'rows' => 3])

<textarea rows="{{ $rows }}" @disabled($disabled) {{ $attributes->merge(['class' => 'w-full min-h-[44px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 disabled:opacity-50 transition custom-scrollbar']) }}>{{ $slot }}</textarea>
