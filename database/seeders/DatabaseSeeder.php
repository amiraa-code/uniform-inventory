<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The password is hashed automatically by the 'hashed' cast on the User model.
        User::updateOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Stores Manager', 'password' => 'password', 'email_verified_at' => now()],
        );

        // Categories must exist before items, and items before transactions.
        $this->call([
            CategorySeeder::class,
            ItemSeeder::class,
            StockTransactionSeeder::class,
        ]);
    }
}