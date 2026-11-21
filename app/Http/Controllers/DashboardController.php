<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_items' => Item::count(),
            'total_units' => (int) Item::sum('stock_quantity'),
            'stock_value' => (float) Item::sum(DB::raw('stock_quantity * unit_price')),
            'low_stock' => Item::lowStock()->count(),
            'out_of_stock' => Item::outOfStock()->count(),
        ];

        // Lowest stock first, so the most urgent items are at the top.
        $lowStockItems = Item::with('category')
            ->lowStock()
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $recentTransactions = StockTransaction::with('item')
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'lowStockItems', 'recentTransactions'));
    }
}