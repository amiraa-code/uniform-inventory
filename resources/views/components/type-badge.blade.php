@props(['type'])

@php
    $classes = match ($type) {
        \App\Enums\TransactionType::In => 'bg-green-100 text-green-800',
        \App\Enums\TransactionType::Allocated => 'bg-blue-100 text-blue-800',
        \App\Enums\TransactionType::Out => 'bg-slate-200 text-slate-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex rounded px-2 py-0.5 text-xs font-medium', $classes]) }}>
    {{ $type->label() }}
</span>