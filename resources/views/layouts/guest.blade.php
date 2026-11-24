<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Layout for pages shown before logging in: no navigation, just a centred card. --}}
<body class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-4 py-10 font-sans text-slate-800 antialiased">
    <div class="mb-6 flex items-center gap-2 text-lg font-bold tracking-tight text-slate-900">
        <span class="flex h-9 w-9 items-center justify-center rounded bg-amber-400 text-slate-900">UI</span>
        <span>{{ config('app.name') }}</span>
    </div>

    <main class="w-full max-w-sm">
        <x-flash />

        @yield('content')
    </main>
</body>
</html>