@extends('layouts.app')

@section('title', $item->name)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('items.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Back to inventory</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $item->name }}</h1>
            <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <span>{{ $item->sku }}</span>
                <span>&middot;</span>
                <span>Size {{ $item->size }}</span>
                <span data-item-badge="{{ $item->id }}"><x-stock-badge :item="$item" /></span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-open-modal="movement-modal" class="btn-accent">Record Movement</button>
            <a href="{{ route('items.edit', $item) }}" class="btn-secondary">Edit</a>
            <form method="POST" action="{{ route('items.destroy', $item) }}"
                  data-confirm="Delete {{ $item->name }} ({{ $item->size }}) and all of its stock history?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="card p-5">
            <h2 class="font-semibold text-slate-900">Details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Category</dt>
                    <dd class="text-right font-medium">{{ $item->category->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Current stock</dt>
                    <dd data-item-stock="{{ $item->id }}" data-stock-colour @class(['text-right text-lg font-bold', 'text-red-600' => $item->stock_status === 'out', 'text-amber-600' => $item->stock_status === 'low'])>{{ $item->stock_quantity }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Minimum threshold</dt>
                    <dd class="text-right font-medium">{{ $item->minimum_threshold }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Unit price</dt>
                    <dd class="text-right font-medium">€{{ number_format($item->unit_price, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Stock value</dt>
                    <dd data-item-value="{{ $item->id }}" class="text-right font-medium">€{{ number_format($item->stock_value, 2) }}</dd>
                </div>
            </dl>
            @if ($item->description)
                <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $item->description }}</p>
            @endif
        </section>

        <section class="card overflow-hidden lg:col-span-2">
            <h2 class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-900">Stock History</h2>

            {{-- The table is always rendered so a movement saved with fetch() has somewhere to appear. --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="th">Date</th>
                            <th class="th">Type</th>
                            <th class="th text-right">Qty</th>
                            <th class="th text-right">Stock After</th>
                            <th class="th hidden md:table-cell">Reference / Notes</th>
                        </tr>
                    </thead>
                    <tbody data-history-rows="{{ $item->id }}" class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            @include('items._history_row')
                        @empty
                            <tr data-empty-row>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No stock movements recorded for this item yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- Stock movement modal. If the form fails validation, data-open-on-load re-opens it to show the errors. --}}
    <dialog id="movement-modal" @if ($errors->any()) data-open-on-load @endif
            class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-lg p-0 shadow-xl backdrop:bg-slate-900/60">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h2 class="font-semibold text-slate-900">Record Stock Movement</h2>
                <p class="text-sm text-slate-500">{{ $item->name }} ({{ $item->size }}) &middot; <span data-item-stock="{{ $item->id }}">{{ $item->stock_quantity }}</span> in stock</p>
            </div>
            <button type="button" data-close-modal class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6">
            @include('transactions._form', ['item' => $item, 'inModal' => true])
        </div>
    </dialog>
@endsection