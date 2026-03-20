@extends('layouts.app')
@section('title', __('Loans & Installments'))
@section('page-title', __('Loans & Installments'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <form method="GET" class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Types') }}</option>
            @foreach(\App\Models\Loan::TYPES as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_',' ',$t))) }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Status') }}</option>
            @foreach(['active','paid_off','defaulted'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_',' ',$s))) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ url('/loans/create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Add Loan') }}
    </a>
</div>

<div class="space-y-4">
    @forelse($loans as $loan)
    <div x-data="{ showPaymentForm: false, showInstallments: false }" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 {{ $loan->isDueSoon() ? 'border-yellow-300' : '' }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }} mb-2">
                    <h3 class="font-semibold text-gray-800">{{ $loan->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-700' : ($loan->status === 'paid_off' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                        {{ __(ucfirst(str_replace('_',' ',$loan->status))) }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ __(ucfirst(str_replace('_',' ',$loan->type))) }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">{{ $loan->uses_contract_totals ? __('Total Cost with Interest') : __('Original') }}</p>
                        <p class="font-semibold text-gray-700">{{ format_currency($loan->display_original_amount) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">{{ __('Remaining') }}</p>
                        <p class="font-semibold text-red-600">{{ format_currency($loan->display_remaining_amount) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">{{ __('Monthly') }}</p>
                        <p class="font-semibold text-gray-700">{{ format_currency($loan->monthly_actual_payment) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">{{ __('Progress') }}</p>
                        <p class="font-semibold text-gray-700">{{ $loan->paid_installments }}/{{ $loan->total_installments }}</p>
                    </div>
                </div>
                @if($loan->down_payment)
                    <p class="text-xs text-gray-400 mt-2">{{ __('Down Payment') }}: {{ format_currency($loan->down_payment) }}</p>
                @endif
                @if($loan->installment_interest || $loan->installment_insurance || $loan->installment_bank_fee)
                    <div class="mt-2 text-xs text-gray-500 space-y-1">
                        <p>{{ __('Principal') }}: {{ format_currency($loan->monthly_payment) }}</p>
                        <p>{{ __('Interest') }}: {{ format_currency($loan->installment_interest) }}</p>
                        <p>{{ __('Insurance') }}: {{ format_currency($loan->installment_insurance) }}</p>
                        @if($loan->installment_bank_fee)
                            <p>{{ __('Bank Fee') }}: {{ format_currency($loan->installment_bank_fee) }}</p>
                        @endif
                        <p>{{ __('Original') }}: {{ format_currency($loan->original_amount) }}</p>
                        <p class="font-semibold text-gray-700">{{ __('Remaining') }}: {{ format_currency($loan->display_remaining_amount) }}</p>
                    </div>
                @endif
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-yellow-500" style="width: {{ $loan->progress }}%"></div>
                </div>
            </div>
            <div class="flex sm:flex-col space-x-2 sm:space-x-0 sm:space-y-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                @if($loan->status === 'active')
                <form method="POST" action="{{ url('/loans/' . $loan->id . '/payment') }}" class="inline" enctype="multipart/form-data">
                    @csrf
                    <button type="button" @click="showPaymentForm = !showPaymentForm" class="px-3 py-2 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700">
                        <i class="fas fa-money-bill"></i> {{ __('Pay') }}
                    </button>
                    <div x-show="showPaymentForm" class="mt-2 space-y-2">
                        <div class="flex space-x-1 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                            <input type="number" name="amount" step="0.01" value="{{ $loan->monthly_actual_payment }}" class="w-28 border rounded px-2 py-1 text-xs">
                            <button type="submit" class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700"><i class="fas fa-check"></i></button>
                        </div>
                        <input type="date" name="payment_date" class="block w-full border rounded px-2 py-1 text-xs">
                        <select name="account_id" class="block w-full border rounded px-2 py-1 text-xs">
                            <option value="">{{ __('From Account') }}</option>
                            @foreach($accounts ?? [] as $account)
                                <option value="{{ $account->id }}" {{ (string) ($loan->account_id ?? '') === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ format_currency($account->balance, $account->currency) }})
                                </option>
                            @endforeach
                        </select>
                        <input type="file" name="receipt_image" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf" class="block w-full text-xs text-gray-500">
                    </div>
                </form>
                @endif
                <a href="{{ route('loans.installments.index', $loan) }}" class="px-3 py-2 bg-indigo-50 text-indigo-700 text-xs rounded-lg hover:bg-indigo-100">
                    <i class="fas fa-folder-open"></i> {{ __('Manage Installments') }}
                </a>
                <button type="button" @click="showInstallments = !showInstallments" class="px-3 py-2 bg-blue-50 text-blue-700 text-xs rounded-lg hover:bg-blue-100">
                    <i class="fas fa-list-ul"></i> {{ __('Installments') }} ({{ $loan->payments->count() }})
                </button>
                <a href="{{ url('/loans/' . $loan->id . '/edit') }}" class="px-3 py-2 bg-gray-100 text-gray-600 text-xs rounded-lg hover:bg-gray-200"><i class="fas fa-edit"></i></a>
            </div>
        </div>
        @if($loan->lender_borrower_name)
            <p class="text-xs text-gray-400 mt-2"><i class="fas fa-user {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ $loan->lender_borrower_name }}</p>
        @endif
        @if($loan->isDueSoon())
            <div class="mt-2 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 text-xs text-yellow-700">
                <i class="fas fa-clock {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Payment due soon') }}: {{ $loan->next_due_date?->format('M d, Y') }}
            </div>
        @endif

        <div x-show="showInstallments" class="mt-4 border-t border-gray-100 pt-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-800">{{ __('Installment History') }}</h4>
                    <p class="text-xs text-gray-400">{{ __('Every recorded installment and its receipt file.') }}</p>
                </div>
                <span class="text-xs text-gray-500">{{ $loan->payments->count() }} {{ __('Installments') }}</span>
            </div>

            @if($loan->payments->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                                <th class="py-2 font-medium">{{ __('No.') }}</th>
                                <th class="py-2 font-medium">{{ __('Date') }}</th>
                                <th class="py-2 font-medium">{{ __('Account') }}</th>
                                <th class="py-2 font-medium">{{ __('Amount') }}</th>
                                <th class="py-2 font-medium">{{ __('Principal') }}</th>
                                <th class="py-2 font-medium">{{ __('Interest') }}</th>
                                <th class="py-2 font-medium">{{ __('Insurance') }}</th>
                                <th class="py-2 font-medium">{{ __('Bank Fee') }}</th>
                                <th class="py-2 font-medium">{{ __('Receipt') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->payments as $payment)
                                <tr class="border-b border-gray-50">
                                    <td class="py-2 font-semibold text-gray-700">{{ $payment->installment_number ?: '-' }}</td>
                                    <td class="py-2 text-gray-600">{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="py-2 text-gray-600">{{ $payment->account?->name ?? '-' }}</td>
                                    <td class="py-2 font-semibold text-gray-800">{{ format_currency($payment->amount) }}</td>
                                    <td class="py-2 text-gray-600">{{ format_currency($payment->principal) }}</td>
                                    <td class="py-2 text-gray-600">{{ format_currency($payment->interest) }}</td>
                                    <td class="py-2 text-gray-600">{{ format_currency($payment->insurance_amount) }}</td>
                                    <td class="py-2 text-gray-600">{{ format_currency($payment->bank_fee) }}</td>
                                    <td class="py-2">
                                        @if($payment->receipt_url)
                                            <a href="{{ $payment->receipt_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-paperclip text-[11px]"></i>{{ __('Open') }}
                                            </a>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($payment->notes)
                                    <tr class="border-b border-gray-50 bg-gray-50/70">
                                        <td colspan="9" class="py-2 text-xs text-gray-500">
                                            <span class="font-semibold text-gray-600">{{ __('Notes') }}:</span> {{ $payment->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-400">
                    {{ __('No installments recorded yet.') }}
                </div>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
        <i class="fas fa-hand-holding-usd text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">{{ __('No loans tracked yet.') }}</p>
    </div>
    @endforelse
</div>
@if($loans->hasPages())<div class="mt-6">{{ $loans->withQueryString()->links() }}</div>@endif
@endsection
