<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Industrial Safety Gear',
                'description' => 'Certified PPE for depot, yard and roadside staff: safety footwear, high-visibility garments and head protection.',
            ],
            [
                'name' => 'Weatherproof Outerwear',
                'description' => 'Rain jackets and fleece layers for drivers and outdoor operatives working in all conditions.',
            ],
            [
                'name' => 'Industrial Workwear',
                'description' => 'Durable everyday garments for mechanics, loaders and warehouse teams.',
            ],
            [
                'name' => 'Office Wear',
                'description' => 'Branded corporate uniform for head-office, customer-service and reception staff.',
            ],
        ];

        foreach ($categories as $category) {
            // The slug is created by the Category model's saving event.
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}