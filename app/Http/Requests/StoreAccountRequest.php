<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cash,bank,savings,credit_card,loan,rewards,other'],
            'balance' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:30'],
            'icon' => ['nullable', 'string', 'max:50'],
            'low_balance_threshold' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter an account name.',
            'name.max' => 'The account name must not exceed 255 characters.',
            'type.required' => 'Please select an account type.',
            'type.in' => 'The selected account type is invalid.',
            'balance.required' => 'Please enter the account balance.',
            'balance.numeric' => 'The balance must be a valid number.',
            'currency.max' => 'The currency code must not exceed 10 characters.',
            'low_balance_threshold.numeric' => 'The low balance threshold must be a valid number.',
            'low_balance_threshold.min' => 'The low balance threshold cannot be negative.',
        ];
    }
}
