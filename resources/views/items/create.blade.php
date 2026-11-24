@extends('layouts.app')

@section('title', 'Add Item')

@section('content')
    <div class="mb-6">
        <a href="{{ route('items.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Back to inventory</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Add Uniform Item</h1>
    </div>

    <form method="POST" action="{{ route('items.store') }}" class="card max-w-3xl p-6">
        @csrf

        @include('items._form')

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('items.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Item</button>
        </div>
    </form>
@endsection