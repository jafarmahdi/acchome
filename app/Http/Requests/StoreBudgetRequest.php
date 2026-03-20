<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', 'in:weekly,monthly,quarterly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'alert_threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a budget name.',
            'category_id.exists' => 'The selected category does not exist.',
            'amount.required' => 'Please enter a budget amount.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The budget amount must be at least 0.01.',
            'period.required' => 'Please select a budget period.',
            'period.in' => 'The selected period is invalid.',
            'start_date.required' => 'Please select a start date.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after' => 'The end date must be after the start date.',
            'alert_threshold.numeric' => 'The alert threshold must be a valid number.',
            'alert_threshold.min' => 'The alert threshold cannot be negative.',
            'alert_threshold.max' => 'The alert threshold cannot exceed 100%.',
        ];
    }
}
