{{-- One row of an item's stock history. Also returned to fetch() after a new movement is saved. --}}
<tr>
    <td class="td">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
    <td class="td"><x-type-badge :type="$transaction->type" /></td>
    <td @class(['td text-right font-semibold', 'text-green-600' => $transaction->quantity_changed > 0, 'text-red-600' => $transaction->quantity_changed < 0])>
        {{ $transaction->quantity_changed > 0 ? '+' : '' }}{{ $transaction->quantity_changed }}
    </td>
    <td class="td text-right">{{ $transaction->stock_after }}</td>
    <td class="td hidden whitespace-normal md:table-cell">
        <span class="font-medium">{{ $transaction->reference }}</span>
        <span class="text-slate-500">{{ $transaction->notes }}</span>
    </td>
</tr>