@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Stock Movements</h1>
            <p class="mt-1 text-sm text-slate-500">Full history of stock received, issued and written off.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn-accent">Record Movement</a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" data-live-filter="#transaction-results" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-3">
        <div>
            <label for="search" class="form-label">Search</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Item, SKU, reference or notes" class="form-input">
        </div>
        <div>
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-input">
                <option value="">All types</option>
                @foreach (\App\Enums\TransactionType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">Filter</button>
            <a href="{{ route('transactions.index') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div id="transaction-results">
        @include('transactions._results')
    </div>
@endsection