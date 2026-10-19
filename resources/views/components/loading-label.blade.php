@props(['target', 'loading' => 'Memproses...', 'label' => null])

<span wire:loading.remove wire:target="{{ $target }}" {{ $attributes }}>{{ $slot->isEmpty() ? $label : $slot }}</span>
<span wire:loading wire:target="{{ $target }}" class="inline-flex items-center gap-1.5">
    <x-spinner />
    <span>{{ $loading }}</span>
</span>
