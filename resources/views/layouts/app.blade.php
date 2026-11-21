<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    @php
        $navLinks = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
            ['label' => 'Inventory', 'route' => 'items.index', 'active' => 'items.*'],
            ['label' => 'Stock Movements', 'route' => 'transactions.index', 'active' => 'transactions.*'],
        ];
    @endphp

    <nav class="bg-slate-900 text-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold tracking-tight">
                <span class="flex h-8 w-8 items-center justify-center rounded bg-amber-400 text-slate-900">UI</span>
                <span>{{ config('app.name') }}</span>
            </a>

            {{-- Desktop links --}}
            <div class="hidden items-center gap-1 md:flex">
                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       @class([
                           'rounded-md px-3 py-2 text-sm font-medium',
                           'bg-slate-700 text-white' => request()->routeIs($link['active']),
                           'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($link['active']),
                       ])>
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('transactions.create') }}" class="btn-accent ml-3">Record Movement</a>
            </div>

            {{-- Mobile hamburger button --}}
            <button type="button" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false"
                    class="rounded-md p-2 text-slate-300 hover:bg-slate-800 hover:text-white md:hidden">
                <span class="sr-only">Open menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>

        {{-- Mobile links, hidden until the hamburger is pressed --}}
        <div id="mobile-menu" class="hidden border-t border-slate-800 px-4 py-3 md:hidden">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'block rounded-md px-3 py-2 text-base font-medium',
                       'bg-slate-700 text-white' => request()->routeIs($link['active']),
                       'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($link['active']),
                   ])>
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('transactions.create') }}" class="btn-accent mt-3 w-full">Record Movement</a>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <x-flash />

        @yield('content')
    </main>
</body>
</html>