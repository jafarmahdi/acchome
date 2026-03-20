@extends('layouts.app')
@section('title', isset($expense) ? __('Edit Expense') : __('New Expense'))
@section('page-title', isset($expense) ? __('Edit Expense') : __('New Expense'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($expense) ? url('/expenses/' . $expense->id) : url('/expenses') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($expense)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }} *</label>
                    <input type="text" name="description" value="{{ old('description', $expense->description ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('What was this expense for?') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $expense->amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account') }} *</label>
                    <select name="account_id" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id', $expense->account_id ?? '') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} ({{ format_currency($acc->balance, $acc->currency) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('No Category') }}</option>
                        @foreach($categories ?? [] as $cat)
                            @if(!$cat->parent_id)
                                <optgroup label="{{ $cat->name }}">
                                    <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @foreach($cat->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id', $expense->category_id ?? '') == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;{{ $child->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }} *</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', isset($expense) ? $expense->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Time') }}</label>
                    <input type="time" name="transaction_time" value="{{ old('transaction_time', $expense->transaction_time ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Method') }} *</label>
                    <select name="payment_method" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(['cash','card','bank_transfer','cheque','online','other'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', $expense->payment_method ?? 'cash') === $method ? 'selected' : '' }}>
                                {{ __(ucfirst(str_replace('_', ' ', $method))) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                    <input type="text" name="location" value="{{ old('location', $expense->location ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('Where?') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Receipt Image') }}</label>
                    <input type="file" name="receipt_image" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700">
                    @if(isset($expense) && $expense->receipt_image)
                        <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle"></i> {{ __('Receipt attached') }}</p>
                    @endif
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="{{ __('Additional details...') }}">{{ old('notes', $expense->notes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/expenses') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    {{ isset($expense) ? __('Update Expense') : __('Add Expense') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
