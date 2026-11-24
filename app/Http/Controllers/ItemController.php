<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Enums\TransactionType;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View|Response
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $items = Item::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->query('category')))
            ->when($status === 'low', fn ($query) => $query->lowStock())
            ->when($status === 'out', fn ($query) => $query->outOfStock())
            ->when($status === 'ok', fn ($query) => $query->whereColumn('stock_quantity', '>', 'minimum_threshold'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString(); // keeps the filters in the pagination links

        // Live search sends an AJAX request, so only the results table is returned.
        // no-store stops the browser caching this partial page under the full page's URL.
        if ($request->ajax()) {
            return response()
                ->view('items._results', compact('items'))
                ->header('Cache-Control', 'no-store');
        }

        $categories = Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }
    /**
     * Returns up to 8 items as JSON for the autocomplete on the Record Movement form.
     * Every word typed must match the name, SKU or size, so "boot uk 9" finds the UK 9 boots.
     */
    public function search(Request $request): JsonResponse
    {
        $words = preg_split('/\s+/', trim((string) $request->query('q')), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === []) {
            return response()->json([]);
    }

        $items = Item::query()
            ->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    $query->where(function ($query) use ($word) {
                        $query->where('name', 'like', "%{$word}%")
                            ->orWhere('sku', 'like', "%{$word}%")
                            ->orWhere('size', 'like', "%{$word}%");
                    });
                }
            })
            ->orderBy('name')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'label' => "{$item->name} – {$item->size} ({$item->sku})",
                'stock_quantity' => $item->stock_quantity,
                'stock_status' => $item->stock_status,
            ]);

        return response()->json($items);
    }
    /**
     * Tells the item form, while the user is typing, whether a SKU is free to use.
     * "ignore" is the id of the item being edited, so it doesn't clash with its own SKU.
     */
    public function checkSku(Request $request): JsonResponse
    {
        $sku = strtoupper(trim((string) $request->query('sku')));

        if (! preg_match('/^[A-Z0-9_-]+$/', $sku)) {
            return response()->json([
                'available' => false,
                'message' => 'Use only letters, numbers, dashes and underscores.',
            ]);
        }

        $existing = Item::where('sku', $sku)
            ->when($request->filled('ignore'), fn ($query) => $query->whereKeyNot($request->integer('ignore')))
            ->first();

        return response()->json([
            'available' => $existing === null,
            'message' => $existing
                ? "Already used by {$existing->name} ({$existing->size})."
                : "{$sku} is available.",
        ]);
    }

    public function create(): View
    {
        return view('items.create', [
            'item' => new Item,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $openingStock = (int) ($data['opening_stock'] ?? 0);
        unset($data['opening_stock']);

        // Create the item and its opening stock movement together, so neither is saved without the other.
        $item = DB::transaction(function () use ($data, $openingStock) {
            $item = Item::create($data + ['stock_quantity' => $openingStock]);

            if ($openingStock > 0) {
                $item->stockTransactions()->create([
                    'user_id' => auth()->id(),
                    'type' => TransactionType::In,
                    'quantity_changed' => $openingStock,
                    'stock_after' => $openingStock,
                    'reference' => 'OPENING',
                    'notes' => 'Opening stock when item was created',
                ]);
            }

            return $item;
        });

        return redirect()
            ->route('items.show', $item)
            ->with('success', "{$item->name} ({$item->size}) was added to the inventory.");
    }

    public function show(Item $item): View
    {
        $item->load('category');

        $transactions = $item->stockTransactions()
            ->with('user')
            ->paginate(10);

        return view('items.show', compact('item', 'transactions'));
    }

    public function edit(Item $item): View
    {
        return view('items.edit', [
            'item' => $item,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($request->validated());

        return redirect()
            ->route('items.show', $item)
            ->with('success', "{$item->name} ({$item->size}) was updated.");
    }

    public function destroy(Item $item): RedirectResponse
    {
        // Its stock transactions are removed too (cascadeOnDelete on the foreign key).
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', "{$item->name} ({$item->size}) was deleted.");
    }
}