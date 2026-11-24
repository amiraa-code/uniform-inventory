{{-- Table and pagination for the inventory list. Returned on its own for live search requests. --}}
<p class="mb-3 text-sm text-slate-500" aria-live="polite">{{ $items->total() }} {{ Str::plural('item', $items->total()) }} found</p>

<div class="card overflow-hidden">
    @if ($items->isEmpty())
        <p class="px-4 py-12 text-center text-sm text-slate-500">No items match your filters.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="th">Item</th>
                        <th class="th hidden lg:table-cell">Category</th>
                        <th class="th">Size</th>
                        <th class="th text-right">Stock</th>
                        <th class="th hidden text-right md:table-cell">Min</th>
                        <th class="th hidden text-right lg:table-cell">Unit Price</th>
                        <th class="th hidden sm:table-cell">Status</th>
                        <th class="th"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="td min-w-40 whitespace-normal">
                                <a href="{{ route('items.show', $item) }}" class="font-medium text-slate-900 hover:underline">{{ $item->name }}</a>
                                <div class="text-xs text-slate-500">{{ $item->sku }}</div>
                            </td>
                            <td class="td hidden lg:table-cell">{{ $item->category->name }}</td>
                            <td class="td">{{ $item->size }}</td>
                            <td @class([
                                'td text-right font-semibold',
                                'text-red-600' => $item->stock_status === 'out',
                                'text-amber-600' => $item->stock_status === 'low',
                            ])>{{ $item->stock_quantity }}</td>
                            <td class="td hidden text-right md:table-cell">{{ $item->minimum_threshold }}</td>
                            <td class="td hidden text-right lg:table-cell">€{{ number_format($item->unit_price, 2) }}</td>
                            <td class="td hidden sm:table-cell"><x-stock-badge :item="$item" /></td>
                            <td class="td text-right">
                                <div class="flex justify-end gap-3 text-sm font-medium">
                                    <a href="{{ route('transactions.create', ['item' => $item->id]) }}" class="text-amber-700 hover:underline">Stock</a>
                                    <a href="{{ route('items.edit', $item) }}" class="hidden text-slate-600 hover:underline sm:inline">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="mt-6">
    {{ $items->links() }}
</div>