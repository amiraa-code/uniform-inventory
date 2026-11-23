@extends('layouts.app')

@section('title', 'Edit '.$item->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('items.show', $item) }}" class="text-sm text-slate-500 hover:underline">&larr; Back to item</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Edit {{ $item->name }} ({{ $item->size }})</h1>
    </div>

    <form method="POST" action="{{ route('items.update', $item) }}" class="card max-w-3xl p-6">
        @csrf
        {{-- HTML forms only support GET and POST, so Laravel reads the real method from this hidden field. --}}
        @method('PUT')

        @include('items._form')

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('items.show', $item) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Item</button>
        </div>
    </form>
@endsection