<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreStockTransactionRequest;
use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransactionController extends Controller
{
    public function index(Request $request): View|Response
    {
        $search = trim((string) $request->query('search'));
        $type = TransactionType::tryFrom((string) $request->query('type'));

        $transactions = StockTransaction::with(['item', 'user'])
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('item', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return response()
                ->view('transactions._results', compact('transactions'))
                ->header('Cache-Control', 'no-store');
        }

        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request): View
    {
        return view('transactions.create', [
            'items' => Item::orderBy('name')->orderBy('id')->get(),
            'selectedItemId' => $request->integer('item') ?: null,
            'selectedType' => TransactionType::tryFrom((string) $request->query('type'))?->value,
        ]);
    }

    public function store(StoreStockTransactionRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $type = TransactionType::from($data['type']);

        // Stock In adds to stock; Stock Out and Allocated remove from it.
        $change = $type->isOutgoing() ? -$data['quantity'] : $data['quantity'];

        $transaction = DB::transaction(function () use ($data, $type, $change) {
            // lockForUpdate stops two people changing the same item's stock at the same moment.
            $item = Item::lockForUpdate()->findOrFail($data['item_id']);

            if ($item->stock_quantity + $change < 0) {
                // Throwing inside DB::transaction rolls everything back.
                throw ValidationException::withMessages([
                    'quantity' => "Only {$item->stock_quantity} in stock for {$item->name} ({$item->size}).",
                ]);
            }

            $item->stock_quantity += $change;
            $item->save();

            return $item->stockTransactions()->create([
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity_changed' => $change,
                'stock_after' => $item->stock_quantity,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        $item = $transaction->item;

        $message = sprintf(
            '%s: %s%d × %s (%s). Stock is now %d.',
            $type->label(),
            $change > 0 ? '+' : '',
            $change,
            $item->name,
            $item->size,
            $transaction->stock_after,
        );

        // A fetch() request gets JSON so the page can update itself without reloading.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'item' => [
                    'id' => $item->id,
                    'stock_quantity' => $item->stock_quantity,
                    'stock_status' => $item->stock_status,
                    'stock_value' => '€'.number_format($item->stock_value, 2),
                    'badge_html' => Blade::render('<x-stock-badge :item="$item" />', ['item' => $item]),
                ],
                'row_html' => view('items._history_row', compact('transaction'))->render(),
            ], 201);
        }

        return back()->with('success', $message);
    }
}