<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    private const APPAREL = ['S', 'M', 'L', 'XL', 'XXL'];

    private const SHOES = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12'];

    private const WAIST = ['30"', '32"', '34"', '36"', '38"', '40"'];

    private const DRESS = ['UK 8', 'UK 10', 'UK 12', 'UK 14', 'UK 16', 'UK 18'];

    private const ONE_SIZE = ['One Size'];

    /**
     * Each product is created once per size in its size list.
     * Format: [name, SKU prefix, sizes, unit price, minimum threshold, description]
     */
    private function catalogue(): array
    {
        return [
            'industrial-safety-gear' => [
                ['Steel-toe Safety Boots (S3)', 'PPE-BOOT', self::SHOES, 54.99, 8,
                    'EN ISO 20345 S3 rated leather boot with steel toe cap and midsole, oil and slip resistant.'],
                ['Steel-toe Safety Trainers (S1P)', 'PPE-TRNR', self::SHOES, 39.50, 6,
                    'Lightweight S1P safety trainer for warehouse and loading-bay staff.'],
                ['Hi-Vis Reflective Vest (Class 2)', 'PPE-VEST', self::APPAREL, 4.75, 25,
                    'EN ISO 20471 Class 2 yellow vest with two 50mm retro-reflective bands and hook-and-loop fastening.'],
                ['Hi-Vis Long-Sleeve Polo (Class 2)', 'PPE-POLO', self::APPAREL, 14.20, 15,
                    'Breathable orange hi-vis polo shirt with reflective tape, for summer depot work.'],
                ['Safety Helmet with Ratchet Harness', 'PPE-HELM', self::ONE_SIZE, 12.40, 20,
                    'EN 397 vented hard hat with adjustable ratchet harness and chin-strap anchor points.'],
            ],
            'weatherproof-outerwear' => [
                ['Heavy-Duty Hi-Vis Rain Jacket', 'OUT-RAIN', self::APPAREL, 36.90, 10,
                    'Waterproof taped-seam PU jacket with concealed hood and Class 3 reflective banding.'],
                ['Fleece Jacket (Navy, Embroidered Logo)', 'OUT-FLCE', self::APPAREL, 22.50, 10,
                    '300gsm anti-pill fleece with full zip, two zipped pockets and embroidered company logo.'],
                ['Insulated Bomber Jacket', 'OUT-BMBR', self::APPAREL, 44.00, 6,
                    'Quilted, water-repellent bomber jacket for winter driving and yard duties.'],
            ],
            'industrial-workwear' => [
                ['Work Bermuda Shorts', 'WRK-BERM', self::WAIST, 18.95, 8,
                    'Poly-cotton work shorts with cargo and tool pockets, for summer warehouse use.'],
                ['Cargo Work Trousers', 'WRK-TROU', self::WAIST, 24.60, 8,
                    'Hard-wearing cargo trousers with knee-pad pockets and triple-stitched seams.'],
                ['Mechanic Coverall (Navy)', 'WRK-COVR', self::APPAREL, 29.99, 5,
                    'Two-way zip boiler suit for fleet maintenance technicians.'],
            ],
            'office-wear' => [
                ['Corporate Office Shirt (White, Long Sleeve)', 'OFF-SHRT', self::APPAREL, 19.50, 12,
                    'Easy-care white shirt with embroidered company logo on the chest.'],
                ['Corporate Blouse (Light Blue)', 'OFF-BLSE', self::APPAREL, 19.50, 8,
                    'Fitted easy-care blouse in corporate light blue.'],
                ['Office Trousers (Charcoal)', 'OFF-TROU', self::WAIST, 27.00, 8,
                    'Flat-front tailored trousers in charcoal grey.'],
                ['Corporate Shift Dress (Navy)', 'OFF-DRSS', self::DRESS, 34.00, 4,
                    'Knee-length navy shift dress for customer-facing office staff.'],
            ],
        ];
    }

    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        foreach ($this->catalogue() as $categorySlug => $products) {
            foreach ($products as [$name, $skuPrefix, $sizes, $price, $threshold, $description]) {
                foreach ($sizes as $size) {
                    // stock_quantity is left at its default of 0; StockTransactionSeeder sets it.
                    Item::updateOrCreate(
                        ['sku' => $skuPrefix.'-'.$this->sizeCode($size)],
                        [
                            'category_id' => $categories[$categorySlug],
                            'name' => $name,
                            'size' => $size,
                            'description' => $description,
                            'minimum_threshold' => $threshold,
                            'unit_price' => number_format($price, 2, '.', ''),
                        ],
                    );
                }
            }
        }
    }

    /**
     * Converts a size into an SKU code: 'UK 9' => 'UK9', '32"' => 'W32', 'One Size' => 'OS'.
     */
    private function sizeCode(string $size): string
    {
        return match (true) {
            $size === 'One Size' => 'OS',
            str_ends_with($size, '"') => 'W'.rtrim($size, '"'),
            default => str_replace(' ', '', $size),
        };
    }
}