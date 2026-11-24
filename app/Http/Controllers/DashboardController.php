<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|Response
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

        // The automatic refresh only needs the cards and tables, not the whole page.
        if ($request->ajax()) {
            return response()
                ->view('dashboard._panels', compact('stats', 'lowStockItems', 'recentTransactions'))
                ->header('Cache-Control', 'no-store');
        }

        return view('dashboard', compact('stats', 'lowStockItems', 'recentTransactions'));
    }
}