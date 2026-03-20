<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
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
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a goal name.',
            'name.max' => 'The goal name must not exceed 255 characters.',
            'target_amount.required' => 'Please enter a target amount.',
            'target_amount.numeric' => 'The target amount must be a valid number.',
            'target_amount.min' => 'The target amount must be at least 0.01.',
            'target_date.date' => 'Please enter a valid target date.',
            'target_date.after' => 'The target date must be in the future.',
            'account_id.exists' => 'The selected account does not exist.',
            'priority.in' => 'The selected priority is invalid.',
            'description.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}
