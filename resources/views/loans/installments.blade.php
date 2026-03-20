@extends('layouts.app')
@section('title', __('Installments') . ' - ' . $loan->name)
@section('page-title', __('Installments') . ' - ' . $loan->name)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-bold text-gray-800">{{ $loan->name }}</h2>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ __(ucfirst(str_replace('_', ' ', $loan->type))) }}</span>
                    <span class="text-xs px-2 py-1 rounded-full {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ __(ucfirst(str_replace('_', ' ', $loan->status))) }}</span>
                </div>
                <p class="text-sm text-gray-400 mt-2">{{ __('Manage receipt numbers, old installments, and uploaded files from one place.') }}</p>
            </div>
            <a href="{{ route('loans.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200">{{ __('Back to Loans') }}</a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
            <div class="rounded-2xl border border-gray-100 p-4">
                <p class="text-xs text-gray-400">{{ __('Recorded Installments') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['count'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
                <p class="text-xs text-emerald-700">{{ __('Receipts Uploaded') }}</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">{{ $stats['receipts_count'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                <p class="text-xs text-amber-700">{{ __('Archived Installments') }}</p>
                <p class="text-2xl font-bold text-amber-800 mt-1">{{ $stats['archived_count'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 p-4">
                <p class="text-xs text-gray-400">{{ __('Remaining') }}</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ format_currency($loan->display_remaining_amount) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-lg font-semibold text-gray-800">{{ __('Add Previous Installment') }}</h3>
            <p class="text-sm text-gray-400 mt-1">{{ __('Simple entry only. The installment amount and breakdown are filled automatically from the loan settings.') }}</p>

            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm text-blue-900 space-y-1">
                <p class="font-semibold">{{ __('Automatic installment details') }}</p>
                <p>{{ __('Total') }}: {{ format_currency($loan->monthly_actual_payment) }}</p>
                <p>{{ __('Principal') }}: {{ format_currency($loan->monthly_payment) }}</p>
                <p>{{ __('Interest') }}: {{ format_currency($loan->installment_interest) }}</p>
                <p>{{ __('Insurance') }}: {{ format_currency($loan->installment_insurance) }}</p>
                <p>{{ __('Bank Fee') }}: {{ format_currency($loan->installment_bank_fee) }}</p>
            </div>

            <form method="POST" action="{{ route('loans.installments.store', $loan) }}" enctype="multipart/form-data" class="space-y-4 mt-5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">{{ __('Installment Number') }} *</label>
                        <input type="number" name="installment_number" min="1" value="{{ old('installment_number', $loan->paid_installments + 1) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">{{ __('Payment Date') }} *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">{{ __('From Account') }}</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="">{{ __('Choose account') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ (string) old('account_id', $loan->account_id ?? '') === (string) $account->id ? 'selected' : '' }}>
                                {{ $account->name }} ({{ format_currency($account->balance, $account->currency) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">{{ __('Receipt Number') }}</label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">{{ __('Installment Receipt') }}</label>
                    <input type="file" name="receipt_image" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf" class="w-full text-sm text-gray-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>

                <label class="flex items-start gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="affects_totals" value="1" {{ old('affects_totals') ? 'checked' : '' }} class="mt-1">
                    <span>{{ __('Affect loan totals and deduct from the selected account') }}</span>
                </label>

                <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700">
                    {{ __('Save Installment') }}
                </button>
            </form>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">{{ __('Search') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by receipt number, installment number, account, notes...') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">{{ __('Apply') }}</button>
                        <a href="{{ route('loans.installments.index', $loan) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                                <th class="py-3 font-medium">{{ __('No.') }}</th>
                                <th class="py-3 font-medium">{{ __('Payment Date') }}</th>
                                <th class="py-3 font-medium">{{ __('Receipt Number') }}</th>
                                <th class="py-3 font-medium">{{ __('Account') }}</th>
                                <th class="py-3 font-medium">{{ __('Amount') }}</th>
                                <th class="py-3 font-medium">{{ __('Receipt') }}</th>
                                <th class="py-3 font-medium">{{ __('Status') }}</th>
                                <th class="py-3 font-medium">{{ __('Update') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr class="border-b border-gray-50 align-top">
                                    <td class="py-3 font-semibold text-gray-800">{{ $payment->installment_number ?: '-' }}</td>
                                    <td class="py-3 text-gray-600">{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="py-3 text-gray-600">{{ $payment->reference_number ?: '-' }}</td>
                                    <td class="py-3 text-gray-600">{{ $payment->account?->name ?? '-' }}</td>
                                    <td class="py-3">
                                        <p class="font-semibold text-gray-800">{{ format_currency($payment->amount) }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ __('Principal') }}: {{ format_currency($payment->principal) }}
                                            /
                                            {{ __('Interest') }}: {{ format_currency($payment->interest) }}
                                        </p>
                                    </td>
                                    <td class="py-3">
                                        @if($payment->receipt_url)
                                            <a href="{{ $payment->receipt_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-paperclip text-[11px]"></i>{{ __('Open') }}
                                            </a>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <span class="text-[11px] px-2 py-1 rounded-full {{ $payment->affects_totals ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $payment->affects_totals ? __('Affects Totals') : __('Archived Only') }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <details class="min-w-[220px]">
                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800 text-xs font-medium">{{ __('Update Installment') }}</summary>
                                            <form method="POST" action="{{ route('loans.installments.update', [$loan, $payment]) }}" enctype="multipart/form-data" class="space-y-2 mt-3">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="installment_number" min="1" value="{{ $payment->installment_number }}" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-xs">
                                                <input type="date" name="payment_date" value="{{ $payment->payment_date?->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-xs">
                                                <input type="text" name="reference_number" value="{{ $payment->reference_number }}" placeholder="{{ __('Receipt Number') }}" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-xs">
                                                <select name="account_id" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-xs">
                                                    <option value="">{{ __('Choose account') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" {{ (string) $payment->account_id === (string) $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="notes" rows="2" placeholder="{{ __('Notes') }}" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-xs">{{ $payment->notes }}</textarea>
                                                <input type="file" name="receipt_image" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf" class="w-full text-xs text-gray-500">
                                                <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">{{ __('Update Installment') }}</button>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-gray-400">{{ __('No installments recorded yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                    <div class="mt-6">{{ $payments->withQueryString()->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
