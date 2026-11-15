<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Individual stock-keeping units (one row per product + size).
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // cannot delete a category that still has items
            $table->string('name', 150);
            $table->string('sku', 50)->unique();
            $table->string('size', 20);
            $table->text('description')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('minimum_threshold')->default(10);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index('name');
            $table->index(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};