<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Same rules as creating an item, except the SKU may keep its current value
 * and stock cannot be set here (it only changes through stock movements).
 */
class UpdateItemRequest extends StoreItemRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['sku'] = [
            'required', 'string', 'max:50', 'alpha_dash',
            Rule::unique('items', 'sku')->ignore($this->route('item')),
        ];

        unset($rules['opening_stock']);

        return $rules;
    }
}