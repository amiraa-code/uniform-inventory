<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'size',
        'description',
        'stock_quantity',
        'minimum_threshold',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'minimum_threshold' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    // Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class)->latest();
    }

    // Query scopes

    /**
     * Items at or below their re-order threshold. Usage: Item::lowStock()->get()
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_threshold');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', 0);
    }

    // Accessors

    /**
     * $item->is_low_stock
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::get(fn () => $this->stock_quantity <= $this->minimum_threshold);
    }

    /**
     * $item->stock_value (quantity x unit price)
     */
    protected function stockValue(): Attribute
    {
        return Attribute::get(fn () => round($this->stock_quantity * (float) $this->unit_price, 2));
    }
}