@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Uniform Inventory</h1>
            <p class="mt-1 text-sm text-slate-500">Search and filter uniform stock by item, category or status.</p>
        </div>
        <a href="{{ route('items.create') }}" class="btn-primary">+ Add Item</a>
    </div>

    {{-- Filters are sent as a GET request so the URL can be bookmarked or shared.
         data-live-filter makes JavaScript reload only #item-results as you type. --}}
    <form method="GET" action="{{ route('items.index') }}" data-live-filter="#item-results" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="search" class="form-label">Search</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Name or SKU" class="form-input">
        </div>
        <div>
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-input">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="form-label">Stock status</label>
            <select id="status" name="status" class="form-input">
                <option value="">Any status</option>
                <option value="ok" @selected(request('status') === 'ok')>In stock</option>
                <option value="low" @selected(request('status') === 'low')>Low stock (incl. out)</option>
                <option value="out" @selected(request('status') === 'out')>Out of stock</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">Filter</button>
            <a href="{{ route('items.index') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div id="item-results">
        @include('items._results')
    </div>
@endsection