<?php

namespace App\Http\Controllers;

use App\Services\UniformAdvisor;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class WeatherController extends Controller
{
    /**
     * Returns the weather card for a depot, chosen either by its key (?depot=marsa)
     * or by the user's position (?latitude=..&longitude=..), which picks the nearest depot.
     * Called with fetch() from the dashboard.
     */
    public function __invoke(Request $request, WeatherService $weather, UniformAdvisor $advisor): Response
    {
        $validated = $request->validate([
            'depot' => ['nullable', Rule::in(array_keys(config('depots')))],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        if (isset($validated['latitude'])) {
            $depot = $weather->nearestDepot($validated['latitude'], $validated['longitude']);
        } else {
            $key = $validated['depot'] ?? array_key_first(config('depots'));
            $depot = config("depots.{$key}") + ['key' => $key];
        }

        $forecast = $weather->forecast($depot['latitude'], $depot['longitude']);
        $advice = $forecast ? $advisor->adviceFor($forecast) : [];

        return response()
            ->view('weather._card', compact('depot', 'forecast', 'advice'))
            ->header('Cache-Control', 'no-store');
    }
}