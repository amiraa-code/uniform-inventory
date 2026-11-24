@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="card flex flex-col gap-5 p-6">
        @csrf

        <div>
            <h1 class="text-xl font-bold text-slate-900">Staff log in</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to manage uniform stock.</p>
        </div>

        <div>
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="form-input">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" class="form-input">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="accent-amber-500">
            Keep me logged in on this device
        </label>

        <button type="submit" class="btn-primary w-full">Log in</button>
    </form>
@endsection