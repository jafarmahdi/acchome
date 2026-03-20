<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Loan;

class StoreLoanRequest extends FormRequest
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
            'type' => ['required', 'in:' . implode(',', Loan::TYPES)],
            'lender_borrower_name' => ['required', 'string', 'max:255'],
            'original_amount' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monthly_payment' => ['nullable', 'numeric', 'min:0'],
            'total_installments' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a loan name.',
            'type.required' => 'Please select a loan type.',
            'type.in' => 'The selected loan type is invalid.',
            'lender_borrower_name.required' => 'Please enter the lender or borrower name.',
            'original_amount.required' => 'Please enter the loan amount.',
            'original_amount.numeric' => 'The loan amount must be a valid number.',
            'original_amount.min' => 'The loan amount must be at least 0.01.',
            'interest_rate.numeric' => 'The interest rate must be a valid number.',
            'interest_rate.min' => 'The interest rate cannot be negative.',
            'interest_rate.max' => 'The interest rate cannot exceed 100%.',
            'monthly_payment.numeric' => 'The monthly payment must be a valid number.',
            'total_installments.integer' => 'The total installments must be a whole number.',
            'total_installments.min' => 'There must be at least 1 installment.',
            'start_date.required' => 'Please select a start date.',
            'end_date.after' => 'The end date must be after the start date.',
            'due_day.min' => 'The due day must be between 1 and 31.',
            'due_day.max' => 'The due day must be between 1 and 31.',
            'account_id.exists' => 'The selected account does not exist.',
        ];
    }
}
