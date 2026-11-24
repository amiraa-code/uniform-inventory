{{--
    Stock movement form, used on the "Record Movement" page and inside the modal on an item's page.
    Pass $item to fix the item (hidden field), or $items to show a dropdown.
--}}
@php
    $typeOptions = [
        'in' => ['Stock In', 'Restock from supplier'],
        'allocated' => ['Issue Uniform', 'Issue to driver or staff'],
        'out' => ['Stock Out', 'Write-off or return'],
    ];
    $currentType = old('type', $selectedType ?? 'allocated');
@endphp

{{-- data-ajax-form: JavaScript sends this form with fetch(). Without JavaScript it still works as a normal POST. --}}
<form method="POST" action="{{ route('transactions.store') }}" data-ajax-form class="flex flex-col gap-5">
    @csrf

    @isset($item)
        <input type="hidden" name="item_id" value="{{ $item->id }}">
    @else
        <div>
            <label for="item_id" class="form-label">Uniform item</label>
            <select id="item_id" name="item_id" required class="form-input">
                <option value="">Select an item</option>
                @foreach ($items as $option)
                    <option value="{{ $option->id }}" @selected(old('item_id', $selectedItemId ?? null) == $option->id)>
                        {{ $option->name }} – {{ $option->size }} ({{ $option->sku }}) · {{ $option->stock_quantity }} in stock
                    </option>
                @endforeach
            </select>
            @error('item_id') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    @endisset

    <fieldset>
        <legend class="form-label">Movement type</legend>
        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
            @foreach ($typeOptions as $value => [$label, $hint])
                <label class="flex cursor-pointer items-start gap-2 rounded-md border border-slate-300 p-3 text-sm has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="type" value="{{ $value }}" @checked($currentType === $value) class="mt-0.5 accent-amber-500">
                    <span>
                        <span class="block font-medium text-slate-900">{{ $label }}</span>
                        <span class="block text-xs text-slate-500">{{ $hint }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('type') <p class="form-error">{{ $message }}</p> @enderror
    </fieldset>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" required min="1" step="1"
                   inputmode="numeric" class="form-input">
            @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="reference" class="form-label">Reference <span class="font-normal text-slate-400">(optional)</span></label>
            <input type="text" id="reference" name="reference" value="{{ old('reference') }}" maxlength="100"
                   placeholder="e.g. EMP-0402 or PO-4120" class="form-input">
            @error('reference') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="notes" class="form-label">Notes <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea id="notes" name="notes" rows="2" maxlength="500" placeholder="e.g. Issued to Driver #402 - Lagos Depot"
                  class="form-input">{{ old('notes') }}</textarea>
        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        @if ($inModal ?? false)
            <button type="button" data-close-modal class="btn-secondary">Cancel</button>
        @else
            <a href="{{ url()->previous() }}" class="btn-secondary">Cancel</a>
        @endif
        <button type="submit" class="btn-primary">Save Movement</button>
    </div>
</form>