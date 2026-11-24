{{-- Weather forecast and uniform advice for one depot. Loaded into the dashboard with fetch(). --}}
<div data-depot-key="{{ $depot['key'] }}">
    @isset($depot['distance_km'])
        <p class="mb-3 text-xs text-slate-500">
            📍 Nearest depot to you: <span class="font-medium text-slate-700">{{ $depot['name'] }}</span>
            ({{ number_format($depot['distance_km'], 1) }} km away)
        </p>
    @endisset

    @if (! $forecast)
        <p class="py-6 text-center text-sm text-slate-500">The weather forecast is unavailable right now. Please try again later.</p>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Current conditions and the next 3 days --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-3">
                    <span class="text-4xl" aria-hidden="true">{{ $forecast['current']['condition']['icon'] }}</span>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ $forecast['current']['temperature'] }}°C</p>
                        <p class="text-sm text-slate-500">{{ $forecast['current']['condition']['text'] }} &middot; wind {{ $forecast['current']['wind'] }} km/h</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ($forecast['days'] as $day)
                        <div class="rounded-md bg-slate-50 p-3 text-center ring-1 ring-slate-200 ring-inset">
                            <p class="text-xs font-semibold text-slate-600">{{ $day['label'] }}</p>
                            <p class="my-1 text-2xl" title="{{ $day['condition']['text'] }}">{{ $day['condition']['icon'] }}</p>
                            <p class="text-sm font-medium text-slate-900">{{ $day['max'] }}° <span class="text-slate-400">/ {{ $day['min'] }}°</span></p>
                            <p class="text-xs text-blue-700">💧 {{ $day['rain_chance'] }}%</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Advice based on the forecast, with live stock figures --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Uniform advice</h3>
                <ul class="mt-2 space-y-2">
                    @forelse ($advice as $tip)
                        <li class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm">
                            <p class="font-medium text-slate-900">{{ $tip['icon'] }} {{ $tip['title'] }}</p>
                            <p class="text-xs text-slate-600">{{ $tip['reason'] }}</p>
                            <a href="{{ route('items.index', ['search' => $tip['product']]) }}" class="mt-1 inline-block text-xs font-medium text-amber-700 hover:underline">
                                {{ $tip['product'] }}: {{ $tip['units'] }} in stock
                                @if ($tip['low_sizes'] > 0)
                                    &middot; <span class="text-red-600">{{ $tip['low_sizes'] }} {{ Str::plural('size', $tip['low_sizes']) }} low</span>
                                @endif
                                &rarr;
                            </a>
                        </li>
                    @empty
                        <li class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                            ✓ Mild weather ahead. No weather-related stock actions needed.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endif
</div>