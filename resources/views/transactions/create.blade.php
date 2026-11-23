@extends('layouts.app')

@section('title', 'Record Movement')

@section('content')
    <div class="mb-6">
        <a href="{{ route('transactions.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Back to stock movements</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Record Stock Movement</h1>
        <p class="mt-1 text-sm text-slate-500">Log a delivery, issue uniforms to staff, or write off damaged stock.</p>
    </div>

    <div class="card max-w-3xl p-6">
        @include('transactions._form')
    </div>
@endsection