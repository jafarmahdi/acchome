<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['nullable', 'date_format:H:i'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque,online,other'],
            'notes' => ['nullable', 'string'],
            'receipt_image' => ['nullable', 'image', 'max:2048'],
            'location' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'account_id.required' => 'Please select an account.',
            'account_id.exists' => 'The selected account does not exist.',
            'category_id.exists' => 'The selected category does not exist.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'description.required' => 'Please enter a description.',
            'description.max' => 'The description must not exceed 255 characters.',
            'transaction_date.required' => 'Please select a date.',
            'transaction_date.date' => 'Please enter a valid date.',
            'transaction_time.date_format' => 'The time must be in HH:MM format.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'receipt_image.image' => 'The receipt must be an image file.',
            'receipt_image.max' => 'The receipt image must not exceed 2MB.',
        ];
    }
}
