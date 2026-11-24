<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Gets a 3-day forecast for a depot from the Open-Meteo API (https://open-meteo.com).
 * Open-Meteo is free and needs no API key. Results are cached for 30 minutes so the
 * API is not called on every page load.
 */
class WeatherService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Returns the forecast, or null if the API could not be reached.
     */
    public function forecast(float $latitude, float $longitude): ?array
    {
        $cacheKey = "weather:{$latitude},{$longitude}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get(self::API_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,weather_code,wind_speed_10m',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max',
                'timezone' => 'Europe/Malta',
                'forecast_days' => 3,
            ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful() || ! isset($response['current'], $response['daily'])) {
            return null;
        }

        $forecast = $this->format($response->json());

        // Only successful responses are cached, so a failed request is retried next time.
        Cache::put($cacheKey, $forecast, now()->addMinutes(30));

        return $forecast;
    }

    /**
     * Returns the depot closest to a position, using the haversine formula for distance on a sphere.
     */
    public function nearestDepot(float $latitude, float $longitude): array
    {
        $nearest = null;

        foreach (config('depots') as $key => $depot) {
            $distance = $this->distanceInKm($latitude, $longitude, $depot['latitude'], $depot['longitude']);

            if ($nearest === null || $distance < $nearest['distance_km']) {
                $nearest = $depot + ['key' => $key, 'distance_km' => $distance];
            }
        }

        return $nearest;
    }

    private function distanceInKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Converts Open-Meteo's response (parallel arrays per field) into one array per day.
     */
    private function format(array $data): array
    {
        $daily = $data['daily'];

        $days = collect($daily['time'])->map(fn (string $date, int $i) => [
            'label' => match ($i) {
                0 => 'Today',
                1 => 'Tomorrow',
                default => Carbon::parse($date)->format('l'),
            },
            'condition' => $this->describe($daily['weather_code'][$i]),
            'max' => round($daily['temperature_2m_max'][$i]),
            'min' => round($daily['temperature_2m_min'][$i]),
            'rain_chance' => $daily['precipitation_probability_max'][$i] ?? 0,
            'rain_mm' => $daily['precipitation_sum'][$i] ?? 0,
            'wind_max' => round($daily['wind_speed_10m_max'][$i]),
        ])->all();

        return [
            'current' => [
                'temperature' => round($data['current']['temperature_2m']),
                'wind' => round($data['current']['wind_speed_10m']),
                'condition' => $this->describe($data['current']['weather_code']),
            ],
            'days' => $days,
        ];
    }

    /**
     * Turns a WMO weather code (used by Open-Meteo) into a description and an icon.
     */
    private function describe(int $code): array
    {
        [$text, $icon] = match (true) {
            $code === 0 => ['Clear sky', '☀️'],
            $code <= 2 => ['Partly cloudy', '⛅'],
            $code === 3 => ['Overcast', '☁️'],
            in_array($code, [45, 48]) => ['Fog', '🌫️'],
            $code >= 51 && $code <= 57 => ['Drizzle', '🌦️'],
            $code >= 61 && $code <= 67 => ['Rain', '🌧️'],
            $code >= 71 && $code <= 77 => ['Snow', '❄️'],
            $code >= 80 && $code <= 82 => ['Rain showers', '🌦️'],
            $code >= 85 && $code <= 86 => ['Snow showers', '❄️'],
            $code >= 95 => ['Thunderstorm', '⛈️'],
            default => ['Unknown', '🌡️'],
        };

        return ['text' => $text, 'icon' => $icon];
    }
}