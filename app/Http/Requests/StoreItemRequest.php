<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the SKU before validation so "ppe-boot-uk9 " and "PPE-BOOT-UK9" are treated as the same.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('sku')) {
            $this->merge(['sku' => strtoupper(trim($this->input('sku')))]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:items,sku'],
            'size' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'minimum_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'opening_stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * Field names used in error messages, e.g. "The category field is required."
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'sku' => 'SKU',
            'minimum_threshold' => 'minimum threshold',
        ];
    }
}