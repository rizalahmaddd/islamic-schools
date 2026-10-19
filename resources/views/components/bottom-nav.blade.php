{{-- Navigasi bawah khusus mobile: sampai empat tujuan bertanda 'mobile' di App\Support\Navigation yang lolos izin,
     plus tombol Menu untuk sisanya (drawer sidebar). Tinggi bar + safe-area iOS sudah dicadangkan lewat
     padding bawah <main> di layouts/app. --}}
@php
    $destinations = collect(\App\Support\Navigation::linksForUser(auth()->user()))
        ->filter(fn (array $link) => $link['mobile'] ?? false)
        ->take(4)
        ->map(fn (array $link) => ['label' => $link['mobile_label'] ?? $link['label'], 'icon' => $link['icon'], 'route' => $link['route'], 'active' => $link['active'] ?? $link['route']])
        ->values()
        ->all();

    $count = count($destinations);
    $gridColumns = [0 => 'grid-cols-1', 1 => 'grid-cols-2', 2 => 'grid-cols-3', 3 => 'grid-cols-4', 4 => 'grid-cols-5'][$count] ?? 'grid-cols-2';
@endphp

<nav x-data aria-label="{{ __('Navigasi utama') }}"
    class="md:hidden fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 dark:border-slate-800/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md pb-[env(safe-area-inset-bottom)] shadow-lg dark:shadow-none">
    <div class="grid h-16 {{ $gridColumns }}">
        @foreach ($destinations as $destination)
            @php $isActive = request()->routeIs($destination['active']); @endphp
            <a href="{{ route($destination['route']) }}" wire:navigate
                @if ($isActive) aria-current="page" @endif
                @class([
                    'flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition active:bg-slate-100 dark:active:bg-slate-800/60',
                    'text-emerald-600 dark:text-emerald-400 font-semibold' => $isActive,
                    'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' => ! $isActive,
                ])>
                <i data-lucide="{{ $destination['icon'] }}" class="w-5 h-5"></i>
                <span class="truncate max-w-full px-1">{{ $destination['label'] }}</span>
            </a>
        @endforeach

        <button type="button" @click="$dispatch('open-mobile-nav')"
            class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition active:bg-slate-100 dark:active:bg-slate-800/60">
            <i data-lucide="layout-grid" class="w-5 h-5"></i>
            <span>{{ __('Menu') }}</span>
        </button>
    </div>
</nav>
