{{-- Dipakai di semua halaman Laporan bersama App\Livewire\Concerns\WithDateRangeFilter: partial
     ini tidak isolated (bukan komponen Livewire sendiri), jadi wire:model/wire:click tetap
     terikat ke komponen Livewire pemanggilnya. --}}
<div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 w-full sm:w-auto">
    {{-- Input rentang tanggal dengan ikon dan visual terpadu --}}
    <div class="flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-0.5 shadow-xs dark:shadow-sm h-11 sm:h-[34px]">
        <div class="flex items-center px-2 text-slate-400 shrink-0">
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
        </div>
        <input
            type="date"
            wire:model.live="from"
            aria-label="{{ __('Dari tanggal') }}"
            class="bg-transparent border-0 text-slate-800 dark:text-slate-200 text-xs rounded px-1.5 py-0.5 focus:ring-1 focus:ring-emerald-500 focus:outline-none w-[116px] sm:w-[124px] h-full"
        >
        <span class="text-slate-400 dark:text-slate-500 text-xs px-1 shrink-0">–</span>
        <input
            type="date"
            wire:model.live="to"
            aria-label="{{ __('Sampai tanggal') }}"
            class="bg-transparent border-0 text-slate-800 dark:text-slate-200 text-xs rounded px-1.5 py-0.5 focus:ring-1 focus:ring-emerald-500 focus:outline-none w-[116px] sm:w-[124px] h-full"
        >
    </div>

    {{-- Tombol preset rentang waktu cepat --}}
    <div class="inline-flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-0.5 shadow-xs dark:shadow-sm h-11 sm:h-[34px] gap-0.5">
        <button
            type="button"
            wire:click="presetLast30Days"
            class="text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 px-3 sm:px-2 h-full rounded transition flex items-center justify-center cursor-pointer"
        >
            {{ __('30 Hari') }}
        </button>
        <button
            type="button"
            wire:click="presetThisMonth"
            class="text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 px-3 sm:px-2 h-full rounded transition flex items-center justify-center cursor-pointer"
        >
            {{ __('Bulan Ini') }}
        </button>
        <button
            type="button"
            wire:click="presetThisYear"
            class="text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 px-3 sm:px-2 h-full rounded transition flex items-center justify-center cursor-pointer"
        >
            {{ __('Tahun Ini') }}
        </button>
    </div>
</div>

