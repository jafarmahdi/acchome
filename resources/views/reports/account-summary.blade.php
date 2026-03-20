@extends('layouts.app')
@section('title', __('Account Summary'))
@section('page-title', __('Account Summary'))

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Account Summary') }}</h2>
        <p class="text-sm text-gray-400 mt-1">{{ __('Current balances with this month activity and the latest movements for each account.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($accounts as $entry)
            @php
                $account = $entry['account'];
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $account->name }}</h3>
                        <p class="text-xs text-gray-400">{{ strtoupper($account->currency ?? (auth()->user()->family->currency ?? 'IQD')) }} · {{ __(ucfirst(str_replace('_', ' ', $account->type))) }}</p>
                    </div>
                    <div class="text-{{ is_rtl() ? 'left' : 'right' }}">
                        <span class="text-xl font-bold {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                            {{ format_currency($account->balance, $account->currency) }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $entry['transaction_count'] }} {{ __('Transactions') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm mb-4">
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4">
                        <p class="text-xs text-emerald-700">{{ __('Income') }}</p>
                        <p class="font-semibold text-emerald-700 mt-1">{{ format_currency($entry['monthly_income'], $account->currency) }}</p>
                    </div>
                    <div class="rounded-2xl bg-red-50 border border-red-100 p-4">
                        <p class="text-xs text-red-700">{{ __('Expenses') }}</p>
                        <p class="font-semibold text-red-700 mt-1">{{ format_currency($entry['monthly_expenses'], $account->currency) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-700">{{ __('Net') }}</p>
                        <p class="font-semibold mt-1 {{ $entry['net'] >= 0 ? 'text-teal-700' : 'text-red-700' }}">{{ format_currency($entry['net'], $account->currency) }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Recent Transactions') }}</p>
                    <div class="space-y-2">
                        @forelse($entry['recent_transactions'] as $txn)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2 text-sm">
                                <div class="min-w-0">
                                    <p class="text-gray-700 truncate">{{ $txn->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $txn->transaction_date?->format('Y-m-d') }} · {{ $txn->category?->display_name ?? __('Uncategorized') }}</p>
                                </div>
                                <span class="font-semibold {{ $txn->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $txn->type === 'income' ? '+' : '-' }}{{ format_currency($txn->amount, $account->currency) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-400">
                {{ __('No accounts yet') }}
            </div>
        @endforelse
    </div>
</div>
@endsection
