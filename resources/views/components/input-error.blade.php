@props(['messages'])

@if ($messages)
    <ul data-input-error role="alert" {{ $attributes->merge(['class' => 'text-xs text-rose-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
