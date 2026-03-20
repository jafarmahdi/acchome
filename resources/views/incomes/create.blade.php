@extends('layouts.app')
@section('title', isset($income) ? __('Edit Income') : __('New Income'))
@section('page-title', isset($income) ? __('Edit Income') : __('New Income'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($income) ? url('/incomes/' . $income->id) : url('/incomes') }}">
            @csrf
            @if(isset($income)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }} *</label>
                    <input type="text" name="description" value="{{ old('description', $income->description ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('e.g. Monthly Salary') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $income->amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account') }} *</label>
                    <select name="account_id" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id', $income->account_id ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('No Category') }}</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $income->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }} *</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', isset($income) ? $income->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Method') }}</label>
                    <select name="payment_method" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(['cash','bank_transfer','cheque','online','other'] as $m)
                            <option value="{{ $m }}" {{ old('payment_method', $income->payment_method ?? 'bank_transfer') === $m ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_',' ',$m))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">{{ old('notes', $income->notes ?? '') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/incomes') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    {{ isset($income) ? __('Update Income') : __('Add Income') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
