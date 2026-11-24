<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Creates 90 days of stock movements for each item, then sets the item's
 * stock_quantity to the final total so the two always match.
 */
class StockTransactionSeeder extends Seeder
{
        private const SUPPLIERS = ['Domeman Glove', 'M.Demajo', 'Garmmo', 'Mediterranean Workwear Supplies', 'Arcco Safety Supplies'];

    private const DEPOTS = ['Marsa Depot', 'Luqa Fleet Yard', 'Ħal Far Garage', 'Qormi Hub', 'Mosta Depot', 'Xewkija Depot (Gozo)', 'Head Office, Valletta'];

    private const OUT_REASONS = [
        'Damaged in storage - written off',
        'Faulty stitching - returned to supplier',
        'Failed PPE inspection - disposed',
        'Transferred to partner depot',
    ];

    public function run(): void
    {
        // Skip if transactions already exist, otherwise running db:seed twice would duplicate them.
        if (StockTransaction::exists()) {
            $this->command?->warn('Stock transactions already exist - skipping. Use migrate:fresh --seed to rebuild.');

            return;
        }

        // Fixed seed so the random values are the same on every run.
        mt_srand(2026);

        $userId = User::value('id');
        $poNumber = 4100;

        DB::transaction(function () use ($userId, &$poNumber) {
            Item::query()->orderBy('id')->each(function (Item $item) use ($userId, &$poNumber) {
                $date = Carbon::now()->subDays(90)->setTime(8, 0);
                $stock = 0;

                // Opening delivery from a supplier.
                $opening = $item->minimum_threshold * mt_rand(3, 6);
                $stock = $this->record($item, TransactionType::In, $opening, $stock, $date, $userId,
                    'PO-'.$poNumber++, 'Opening stock from '.$this->pick(self::SUPPLIERS));

                // Random mix of allocations, write-offs and restocks.
                $movements = mt_rand(3, 7);
                for ($i = 0; $i < $movements; $i++) {
                    $date = $date->copy()->addDays(mt_rand(4, 14))->setTime(mt_rand(7, 17), mt_rand(0, 59));
                    if ($date->isFuture()) {
                        break;
                    }

                    $roll = mt_rand(1, 100);

                    if ($roll <= 65 && $stock > 0) {
                        // Issue uniforms to an employee.
                        $qty = min($stock, mt_rand(1, max(2, intdiv($item->minimum_threshold, 2) + 2)));
                        $stock = $this->record($item, TransactionType::Allocated, -$qty, $stock, $date, $userId,
                            sprintf('EMP-%04d', mt_rand(1001, 1450)),
                            'Issued to staff at '.$this->pick(self::DEPOTS));
                    } elseif ($roll <= 80 && $stock > 0) {
                        // Remove damaged or returned stock.
                        $qty = min($stock, mt_rand(1, 3));
                        $stock = $this->record($item, TransactionType::Out, -$qty, $stock, $date, $userId,
                            'ADJ-'.mt_rand(100, 999), $this->pick(self::OUT_REASONS));
                    } elseif ($stock <= $item->minimum_threshold * 2) {
                        // Reorder when stock is running low.
                        $qty = $item->minimum_threshold * mt_rand(2, 4);
                        $stock = $this->record($item, TransactionType::In, $qty, $stock, $date, $userId,
                            'PO-'.$poNumber++, 'Restock delivery from '.$this->pick(self::SUPPLIERS));
                    }
                }

                // About 15% of items get a large recent issue that takes them below their threshold.
                if (mt_rand(1, 100) <= 15 && $stock > 0) {
                    $date = Carbon::now()->subDays(mt_rand(1, 3))->setTime(mt_rand(8, 16), mt_rand(0, 59));
                    $qty = $stock - mt_rand(0, max(0, $item->minimum_threshold - 1));
                    if ($qty > 0) {
                        $stock = $this->record($item, TransactionType::Allocated, -$qty, $stock, $date, $userId,
                            'BULK-'.mt_rand(10, 99), 'Bulk issue for new-starter induction');
                    }
                }

                $item->update(['stock_quantity' => $stock]);
            });
        });
    }

    /**
     * Saves one stock movement and returns the new stock level.
     */
    private function record(
        Item $item,
        TransactionType $type,
        int $quantity,
        int $stock,
        Carbon $date,
        ?int $userId,
        string $reference,
        string $notes,
    ): int {
        $stockAfter = $stock + $quantity;

        $transaction = new StockTransaction([
            'item_id' => $item->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity_changed' => $quantity,
            'stock_after' => $stockAfter,
            'reference' => $reference,
            'notes' => $notes,
        ]);

        // Set timestamps manually so the movement is dated in the past.
        $transaction->created_at = $date;
        $transaction->updated_at = $date;
        $transaction->save();

        return $stockAfter;
    }

    private function pick(array $options): string
    {
        return $options[mt_rand(0, count($options) - 1)];
    }
}