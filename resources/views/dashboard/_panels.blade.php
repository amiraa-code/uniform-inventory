{{-- KPI cards and tables. Also returned on their own when the dashboard refreshes itself. --}}
{{-- KPI cards: 1 column on phones, 2 on tablets, 4 on desktops --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card p-5">
        <p class="text-sm font-medium text-slate-500">Uniform SKUs</p>
        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['total_items']) }}</p>
        <p class="mt-1 text-xs text-slate-500">Distinct item and size combinations</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-slate-500">Units in stock</p>
        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['total_units']) }}</p>
        <p class="mt-1 text-xs text-slate-500">Total pieces across all items</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-slate-500">Stock value</p>
        <p class="mt-2 text-3xl font-bold text-slate-900">€{{ number_format($stats['stock_value'], 2) }}</p>
        <p class="mt-1 text-xs text-slate-500">Quantity × unit price</p>
    </div>
    <a href="{{ route('items.index', ['status' => 'low']) }}"
       @class(['card block p-5 transition hover:ring-amber-300', 'bg-amber-50 ring-amber-200' => $stats['low_stock'] > 0])>
        <p class="text-sm font-medium text-slate-500">Low stock alerts</p>
        <p @class(['mt-2 text-3xl font-bold', 'text-amber-600' => $stats['low_stock'] > 0, 'text-slate-900' => $stats['low_stock'] === 0])>
            {{ number_format($stats['low_stock']) }}
        </p>
        <p class="mt-1 text-xs text-slate-500">{{ $stats['out_of_stock'] }} completely out of stock</p>
    </a>
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Low stock table --}}
    <section class="card overflow-hidden lg:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Low Stock Alerts</h2>
            <a href="{{ route('items.index', ['status' => 'low']) }}" class="text-sm font-medium text-amber-700 hover:underline">View all</a>
        </div>

        @if ($lowStockItems->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-slate-500">All items are above their minimum threshold.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="th">Item</th>
                            <th class="th">Size</th>
                            <th class="th text-right">Stock / Min</th>
                            <th class="th hidden sm:table-cell">Status</th>
                            <th class="th"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($lowStockItems as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="td min-w-40 whitespace-normal">
                                    <a href="{{ route('items.show', $item) }}" class="font-medium text-slate-900 hover:underline">{{ $item->name }}</a>
                                    <div class="text-xs text-slate-500">{{ $item->sku }}</div>
                                </td>
                                <td class="td">{{ $item->size }}</td>
                                <td class="td text-right">
                                    <span @class(['font-semibold', 'text-red-600' => $item->stock_quantity === 0, 'text-amber-600' => $item->stock_quantity > 0])>{{ $item->stock_quantity }}</span>
                                    <span class="text-slate-400">/ {{ $item->minimum_threshold }}</span>
                                </td>
                                <td class="td hidden sm:table-cell"><x-stock-badge :item="$item" /></td>
                                <td class="td text-right">
                                    <a href="{{ route('transactions.create', ['item' => $item->id, 'type' => 'in']) }}"
                                       class="text-sm font-medium text-amber-700 hover:underline">Restock</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Recent movements --}}
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold text-slate-900">Recent Movements</h2>
            <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-amber-700 hover:underline">View all</a>
        </div>

        <ul class="divide-y divide-slate-100">
            @forelse ($recentTransactions as $transaction)
                <li class="px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('items.show', $transaction->item) }}" class="block truncate text-sm font-medium text-slate-900 hover:underline">
                                {{ $transaction->item->name }} ({{ $transaction->item->size }})
                            </a>
                            <p class="truncate text-xs text-slate-500">{{ $transaction->notes ?? $transaction->reference }}</p>
                        </div>
                        <span @class(['shrink-0 text-sm font-semibold', 'text-green-600' => $transaction->quantity_changed > 0, 'text-red-600' => $transaction->quantity_changed < 0])>
                            {{ $transaction->quantity_changed > 0 ? '+' : '' }}{{ $transaction->quantity_changed }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                        <x-type-badge :type="$transaction->type" />
                        <span>{{ $transaction->created_at->diffForHumans() }}</span>
                    </div>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-slate-500">No stock movements recorded yet.</li>
            @endforelse
        </ul>
    </section>
</div>