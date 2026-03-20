@extends('layouts.app')
@section('title', __('Member Spending'))
@section('page-title', __('Member Spending'))

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700">{{ __('Apply') }}</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($members as $entry)
            @php
                $member = $entry['member'];
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $member->name }}</h3>
                        <p class="text-xs text-gray-400">{{ $member->email }}</p>
                    </div>
                    <div class="text-{{ is_rtl() ? 'left' : 'right' }}">
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ __(ucfirst($member->relation ?? 'other')) }}</span>
                        <p class="text-xs text-gray-400 mt-2">{{ $entry['transaction_count'] }} {{ __('Transactions') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4">
                        <p class="text-xs font-semibold text-emerald-700">{{ __('Income') }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($entry['income_by_currency'] as $currency => $amount)
                                <div class="font-semibold text-emerald-700">{{ format_currency($amount, $currency) }}</div>
                            @empty
                                <div class="text-sm text-emerald-300">-</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="rounded-2xl bg-red-50 border border-red-100 p-4">
                        <p class="text-xs font-semibold text-red-700">{{ __('Expenses') }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($entry['expenses_by_currency'] as $currency => $amount)
                                <div class="font-semibold text-red-700">{{ format_currency($amount, $currency) }}</div>
                            @empty
                                <div class="text-sm text-red-300">-</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs font-semibold text-slate-700">{{ __('Net') }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($entry['net_by_currency'] as $currency => $amount)
                                <div class="font-semibold {{ $amount >= 0 ? 'text-teal-700' : 'text-red-700' }}">{{ format_currency($amount, $currency) }}</div>
                            @empty
                                <div class="text-sm text-slate-300">-</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-3">{{ __('Top Expense Categories') }}</p>
                    <div class="space-y-2">
                        @forelse($entry['top_categories'] as $category)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="min-w-0">
                                    <span class="text-gray-700 truncate block">{{ $category['name'] }}</span>
                                    <span class="text-xs text-gray-400">{{ $category['currency'] }}</span>
                                </div>
                                <span class="font-semibold text-gray-800">{{ format_currency($category['total'], $category['currency']) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-400">
                {{ __('No family members found.') }}
            </div>
        @endforelse
    </div>
</div>
@endsection
