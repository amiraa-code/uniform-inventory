@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Overview of uniform stock across all depots in Malta and Gozo.</p>
        </div>
        <p class="flex items-center gap-2 text-xs text-slate-500">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
            </span>
            Live &middot; updated <span data-refresh-time>{{ now()->format('H:i:s') }}</span>
        </p>
    </div>

    {{-- Weather for the chosen depot, loaded with fetch() from the Open-Meteo API through WeatherController. --}}
    <section class="card mb-6 p-5" data-weather data-weather-url="{{ route('weather') }}">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-slate-900">Depot Weather &amp; Uniform Advice</h2>
            <div class="flex flex-wrap items-center gap-2">
                <label for="depot" class="sr-only">Depot</label>
                <select id="depot" data-weather-depot class="form-input mt-0 w-auto">
                    @foreach (config('depots') as $key => $depot)
                        <option value="{{ $key }}">{{ $depot['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div data-weather-body aria-live="polite">
            <p class="py-6 text-center text-sm text-slate-500">Loading forecast…</p>
        </div>

        <p class="mt-4 text-right text-xs text-slate-400">
            Weather data by <a href="https://open-meteo.com/" target="_blank" rel="noopener" class="underline">Open-Meteo.com</a>
        </p>
    </section>

    {{-- data-auto-refresh: JavaScript reloads this area from the server every 30 seconds. --}}
    <div data-auto-refresh="30" data-refresh-url="{{ route('dashboard') }}">
        @include('dashboard._panels')
    </div>
@endsection