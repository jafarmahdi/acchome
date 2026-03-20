@extends('layouts.app')
@section('title', isset($account) ? __('Edit Account') : __('New Account'))
@section('page-title', isset($account) ? __('Edit Account') : __('New Account'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($account) ? url('/accounts/' . $account->id) : url('/accounts') }}">
            @csrf
            @if(isset($account)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $account->name ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('e.g. Main Bank Account') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }} *</label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(['cash','bank','savings','credit_card','loan','rewards','other'] as $type)
                            <option value="{{ $type }}" {{ old('type', $account->type ?? '') === $type ? 'selected' : '' }}>
                                {{ __(ucfirst(str_replace('_', ' ', $type))) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Balance') }} *</label>
                    <input type="number" name="balance" step="0.01" value="{{ old('balance', $account->balance ?? 0) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                    <select name="currency" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @php $selectedCurrency = old('currency', $account->currency ?? (auth()->user()->family->currency ?? 'IQD')); @endphp
                        @foreach(['IQD', 'USD', 'EUR', 'GBP', 'SAR', 'AED'] as $currency)
                            <option value="{{ $currency }}" {{ $selectedCurrency === $currency ? 'selected' : '' }}>
                                {{ $currency }} ({{ currency_symbol($currency) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Owner') }}</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Family Shared') }}</option>
                        @foreach($members ?? [] as $member)
                            <option value="{{ $member->id }}" {{ old('user_id', $account->user_id ?? '') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bank Name') }}</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Number') }}</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $account->account_number ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Color') }}</label>
                    <input type="color" name="color" value="{{ old('color', $account->color ?? '#3B82F6') }}"
                           class="h-10 w-full border border-gray-300 rounded-lg cursor-pointer">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Low Balance Alert') }}</label>
                    <input type="number" name="low_balance_threshold" step="0.01" value="{{ old('low_balance_threshold', $account->low_balance_threshold ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $account->notes ?? '') }}</textarea>
                </div>

                <div class="sm:col-span-2 flex items-center space-x-4 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                    <label class="flex items-center">
                        <input type="checkbox" name="include_in_total" value="1" {{ old('include_in_total', $account->include_in_total ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600 {{ is_rtl() ? 'mr-2' : 'ml-2' }}">{{ __('Include in total balance') }}</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/accounts') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ isset($account) ? __('Update Account') : __('Create Account') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
