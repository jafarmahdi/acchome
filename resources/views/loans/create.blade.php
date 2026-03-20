@extends('layouts.app')
@section('title', isset($loan) ? __('Edit Loan') : __('New Loan'))
@section('page-title', isset($loan) ? __('Edit Loan') : __('New Loan'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($loan) ? url('/loans/' . $loan->id) : url('/loans') }}">
            @csrf
            @if(isset($loan)) @method('PUT') @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Loan Name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $loan->name ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500" placeholder="{{ __('e.g. Car Loan') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }} *</label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(\App\Models\Loan::TYPES as $t)
                            <option value="{{ $t }}" {{ old('type', $loan->type ?? '') === $t ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_',' ',$t))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Lender/Borrower Name') }}</label>
                    <input type="text" name="lender_borrower_name" value="{{ old('lender_borrower_name', $loan->lender_borrower_name ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Original Amount') }} *</label>
                    <input type="number" name="original_amount" step="0.01" value="{{ old('original_amount', $loan->original_amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Down Payment') }}</label>
                    <input type="number" name="down_payment" step="0.01" min="0" value="{{ old('down_payment', $loan->down_payment ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Monthly Payment') }} *</label>
                    <input type="number" name="monthly_payment" step="0.01" value="{{ old('monthly_payment', $loan->monthly_payment ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Interest') }}</label>
                    <input type="number" name="installment_interest" step="0.01" min="0" value="{{ old('installment_interest', $loan->installment_interest ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Insurance') }}</label>
                    <input type="number" name="installment_insurance" step="0.01" min="0" value="{{ old('installment_insurance', $loan->installment_insurance ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bank Fee') }}</label>
                    <input type="number" name="installment_bank_fee" step="0.01" min="0" value="{{ old('installment_bank_fee', $loan->installment_bank_fee ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Total Installments') }} *</label>
                    <input type="number" name="total_installments" min="1" value="{{ old('total_installments', $loan->total_installments ?? 12) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Interest Rate (%)') }}</label>
                    <input type="number" name="interest_rate" step="0.01" min="0" value="{{ old('interest_rate', $loan->interest_rate ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Due Day (1-31)') }}</label>
                    <input type="number" name="due_day" min="1" max="31" value="{{ old('due_day', $loan->due_day ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Date') }} *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', isset($loan) ? $loan->start_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Date') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date', isset($loan) && $loan->end_date ? $loan->end_date->format('Y-m-d') : '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account') }}</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('None') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id', $loan->account_id ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">{{ old('notes', $loan->notes ?? '') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/loans') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                    {{ isset($loan) ? __('Update Loan') : __('Add Loan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
