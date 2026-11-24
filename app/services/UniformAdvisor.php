<?php

namespace App\Services;

use App\Models\Item;

/**
 * Turns a weather forecast into stock advice, e.g. "Rain expected: check rain jacket stock",
 * together with how many of those uniforms are in stock and how many sizes are running low.
 */
class UniformAdvisor
{
    public function adviceFor(array $forecast): array
    {
        $days = collect($forecast['days']);
        $advice = [];

        if ($days->max('rain_chance') >= 50 || $days->sum('rain_mm') >= 2) {
            $advice[] = $this->entry('🌧️', 'Rain expected', 'Drivers and yard staff will need waterproofs.', 'Rain Jacket');
        }

        if ($days->min('min') <= 12) {
            $advice[] = $this->entry('🧥', 'Cold mornings', "Lows of {$days->min('min')}°C. Warm layers will be in demand.", 'Fleece');
            $advice[] = $this->entry('🧥', 'Cold mornings', 'Insulated jackets for early shifts and night driving.', 'Bomber');
        }

        if ($days->max('max') >= 30) {
            $advice[] = $this->entry('☀️', 'Hot weather', "Highs of {$days->max('max')}°C. Lightweight uniforms will be requested.", 'Polo');
            $advice[] = $this->entry('☀️', 'Hot weather', 'Work shorts for warehouse and yard teams.', 'Bermuda');
        }

        if ($days->max('wind_max') >= 40) {
            $advice[] = $this->entry('💨', 'Strong winds', "Gusts up to {$days->max('wind_max')} km/h. Helmets with chin straps for yard work.", 'Helmet');
        }

        return $advice;
    }

    /**
     * One piece of advice, with live stock figures for the matching uniforms.
     */
    private function entry(string $icon, string $title, string $reason, string $product): array
    {
        $items = Item::where('name', 'like', "%{$product}%");

        return [
            'icon' => $icon,
            'title' => $title,
            'reason' => $reason,
            'product' => $product,
            'units' => (int) (clone $items)->sum('stock_quantity'),
            'low_sizes' => (clone $items)->lowStock()->count(),
        ];
    }
}