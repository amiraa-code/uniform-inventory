<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'type' => ['required', Rule::enum(TransactionType::class)],
            // Always entered as a positive number; the controller decides whether it adds or removes stock.
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Choose an item from the suggestions list.',
        ];
    }

    public function attributes(): array
    {
        return [
            'item_id' => 'item',
            'type' => 'movement type',
        ];
    }
}