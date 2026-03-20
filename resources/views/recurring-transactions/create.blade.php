@extends('layouts.app')
@section('title', isset($recurringTransaction) ? __('Edit Recurring Transaction') : __('Add Recurring Transaction'))
@section('page-title', isset($recurringTransaction) ? __('Edit Recurring Transaction') : __('Add Recurring Transaction'))

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ isset($recurringTransaction) ? route('recurring-transactions.update', $recurringTransaction) : route('recurring-transactions.store') }}" class="space-y-5">
        @csrf
        @if(isset($recurringTransaction))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Description') }} *</label>
                <input type="text" name="description" value="{{ old('description', $recurringTransaction->description ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Type') }} *</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="expense" {{ old('type', $recurringTransaction->type ?? 'expense') === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                    <option value="income" {{ old('type', $recurringTransaction->type ?? '') === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Account') }} *</label>
                <select name="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Choose account') }}</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ (string) old('account_id', $recurringTransaction->account_id ?? '') === (string) $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ format_currency($account->balance, $account->currency) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Category') }}</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Uncategorized') }}</option>
                    <optgroup label="{{ __('Expense') }}">
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $recurringTransaction->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->display_name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Income') }}">
                        @foreach($incomeCategories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $recurringTransaction->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->display_name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Amount') }} *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $recurringTransaction->amount ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Frequency') }} *</label>
                <select name="frequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach(['daily','weekly','biweekly','monthly','quarterly','yearly'] as $frequency)
                        <option value="{{ $frequency }}" {{ old('frequency', $recurringTransaction->frequency ?? 'monthly') === $frequency ? 'selected' : '' }}>{{ __(ucfirst($frequency)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Next Due Date') }} *</label>
                <input type="date" name="next_due_date" value="{{ old('next_due_date', isset($recurringTransaction) && $recurringTransaction->next_due_date ? $recurringTransaction->next_due_date->format('Y-m-d') : now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('End Date') }}</label>
                <input type="date" name="end_date" value="{{ old('end_date', isset($recurringTransaction) && $recurringTransaction->end_date ? $recurringTransaction->end_date->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">{{ __('Notes') }}</label>
            <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('notes', $recurringTransaction->notes ?? '') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $recurringTransaction->is_active ?? true) ? 'checked' : '' }}>
                <span>{{ __('Active') }}</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="auto_create" value="1" {{ old('auto_create', $recurringTransaction->auto_create ?? false) ? 'checked' : '' }}>
                <span>{{ __('Auto Create') }}</span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700">
                {{ isset($recurringTransaction) ? __('Update Recurring Transaction') : __('Save Recurring Transaction') }}
            </button>
            <a href="{{ route('recurring-transactions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
