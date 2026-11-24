{{-- Table and pagination for the movement history. Returned on its own for live search requests. --}}
<div class="card overflow-hidden">
    @if ($transactions->isEmpty())
        <p class="px-4 py-12 text-center text-sm text-slate-500">No stock movements match your filters.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="th">Date</th>
                        <th class="th">Item</th>
                        <th class="th">Type</th>
                        <th class="th text-right">Qty</th>
                        <th class="th hidden text-right sm:table-cell">Stock After</th>
                        <th class="th hidden lg:table-cell">Reference / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="td">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            <td class="td min-w-40 whitespace-normal">
                                <a href="{{ route('items.show', $transaction->item) }}" class="font-medium text-slate-900 hover:underline">
                                    {{ $transaction->item->name }}
                                </a>
                                <div class="text-xs text-slate-500">{{ $transaction->item->size }} · {{ $transaction->item->sku }}</div>
                            </td>
                            <td class="td"><x-type-badge :type="$transaction->type" /></td>
                            <td @class(['td text-right font-semibold', 'text-green-600' => $transaction->quantity_changed > 0, 'text-red-600' => $transaction->quantity_changed < 0])>
                                {{ $transaction->quantity_changed > 0 ? '+' : '' }}{{ $transaction->quantity_changed }}
                            </td>
                            <td class="td hidden text-right sm:table-cell">{{ $transaction->stock_after }}</td>
                            <td class="td hidden max-w-xs whitespace-normal lg:table-cell">
                                <span class="font-medium">{{ $transaction->reference }}</span>
                                <span class="text-slate-500">{{ $transaction->notes }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="mt-6">
    {{ $transactions->links() }}
</div>