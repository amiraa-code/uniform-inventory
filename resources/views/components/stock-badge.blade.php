@props(['item'])

@php
    [$label, $classes] = match ($item->stock_status) {
        'out' => ['Out of stock', 'bg-red-100 text-red-800 ring-red-200'],
        'low' => ['Low stock', 'bg-amber-100 text-amber-800 ring-amber-200'],
        default => ['In stock', 'bg-green-100 text-green-800 ring-green-200'],
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', $classes]) }}>
    {{ $label }}
</span>