@extends('layouts.app')
@section('title', isset($transfer) ? __('Edit Transfer') : __('New Transfer'))
@section('page-title', isset($transfer) ? __('Edit Transfer') : __('New Transfer'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($transfer) ? url('/transfers/' . $transfer->id) : url('/transfers') }}">
            @csrf
            @if(isset($transfer)) @method('PUT') @endif
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('From Account') }} *</label>
                    <select name="account_id" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id', old('from_account_id', $transfer->account_id ?? '')) == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ format_currency($acc->balance, $acc->currency) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center"><i class="fas fa-arrow-down text-2xl text-purple-400"></i></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('To Account') }} *</label>
                    <select name="transfer_to_account_id" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('transfer_to_account_id', old('to_account_id', $transfer->transfer_to_account_id ?? '')) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $transfer->amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <input type="text" name="description" value="{{ old('description', $transfer->description ?? __('Transfer between accounts')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }} *</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', isset($transfer) ? $transfer->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/transfers') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">{{ isset($transfer) ? __('Update Transfer') : __('Transfer') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
