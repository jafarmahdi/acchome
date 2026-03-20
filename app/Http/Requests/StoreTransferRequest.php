<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'exists:accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Please select the source account.',
            'from_account_id.exists' => 'The selected source account does not exist.',
            'from_account_id.different' => 'The source and destination accounts must be different.',
            'to_account_id.required' => 'Please select the destination account.',
            'to_account_id.exists' => 'The selected destination account does not exist.',
            'amount.required' => 'Please enter a transfer amount.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The transfer amount must be at least 0.01.',
            'description.max' => 'The description must not exceed 255 characters.',
            'transaction_date.required' => 'Please select a transfer date.',
            'transaction_date.date' => 'Please enter a valid date.',
        ];
    }
}
