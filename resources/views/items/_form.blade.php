{{-- Shared fields for the create and edit forms. old() refills the fields after a validation error. --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="form-label">Item name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" required maxlength="150"
               placeholder="e.g. Hi-Vis Reflective Vest (Class 2)" class="form-input">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="category_id" class="form-label">Category</label>
        <select id="category_id" name="category_id" required class="form-input">
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="sku" class="form-label">SKU</label>
        <input type="text" id="sku" name="sku" value="{{ old('sku', $item->sku) }}" required maxlength="50"
               placeholder="e.g. PPE-VEST-XL" class="form-input uppercase">
        @error('sku') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="size" class="form-label">Size</label>
        <input type="text" id="size" name="size" value="{{ old('size', $item->size) }}" required maxlength="20"
               list="size-options" placeholder="e.g. XL, UK 9, 32&quot;" class="form-input">
        <datalist id="size-options">
            @foreach (['S', 'M', 'L', 'XL', 'XXL', 'One Size'] as $size)
                <option value="{{ $size }}">
            @endforeach
        </datalist>
        @error('size') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="unit_price" class="form-label">Unit price (£)</label>
        <input type="number" id="unit_price" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}" required
               min="0" step="0.01" inputmode="decimal" class="form-input">
        @error('unit_price') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="minimum_threshold" class="form-label">Minimum threshold</label>
        <input type="number" id="minimum_threshold" name="minimum_threshold" value="{{ old('minimum_threshold', $item->minimum_threshold ?? 10) }}"
               required min="0" step="1" inputmode="numeric" class="form-input">
        <p class="mt-1 text-xs text-slate-500">The item is flagged as low stock at or below this quantity.</p>
        @error('minimum_threshold') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Opening stock is only set when creating. After that, stock only changes through stock movements. --}}
    @if (! $item->exists)
        <div>
            <label for="opening_stock" class="form-label">Opening stock</label>
            <input type="number" id="opening_stock" name="opening_stock" value="{{ old('opening_stock', 0) }}"
                   min="0" step="1" inputmode="numeric" class="form-input">
            <p class="mt-1 text-xs text-slate-500">Recorded as a "Stock In" movement.</p>
            @error('opening_stock') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    @else
        <div>
            <span class="form-label">Current stock</span>
            <p class="mt-1 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-700 ring-1 ring-inset ring-slate-200">
                {{ $item->stock_quantity }} units
                <a href="{{ route('transactions.create', ['item' => $item->id]) }}" class="ml-2 font-medium text-amber-700 hover:underline">Record movement</a>
            </p>
        </div>
    @endif

    <div class="sm:col-span-2">
        <label for="description" class="form-label">Description <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea id="description" name="description" rows="3" maxlength="1000" class="form-input">{{ old('description', $item->description) }}</textarea>
        @error('description') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>