@extends('layouts.app')
@section('title', __('Reports & Analytics'))
@section('page-title', __('Reports & Analytics'))

@section('content')
@php
    $dateQuery = [
        'from' => $from->format('Y-m-d'),
        'to' => $to->format('Y-m-d'),
    ];
@endphp

<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl text-white p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">{{ __('Reports & Analytics') }}</p>
                <h2 class="text-2xl sm:text-3xl font-bold mt-2">{{ __('Financial overview for the selected period') }}</h2>
                <p class="text-sm text-slate-300 mt-2">{{ $from->format('Y-m-d') }} <span class="px-1">→</span> {{ $to->format('Y-m-d') }}</p>
            </div>
            <a href="{{ route('reports.export', $dateQuery) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white/10 border border-white/15 text-white text-sm font-medium rounded-xl hover:bg-white/15 transition-colors">
                <i class="fas fa-file-csv {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Export CSV') }}
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $dateQuery['from'] }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $dateQuery['to'] }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700">{{ __('Apply') }}</button>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mt-5">
            <a href="{{ route('reports.income-vs-expense', $dateQuery) }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-blue-50 hover:border-blue-200 transition-colors p-4 text-center">
                <i class="fas fa-scale-balanced text-blue-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('Income vs Expense') }}</p>
            </a>
            <a href="{{ route('reports.category-breakdown', $dateQuery) }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-purple-50 hover:border-purple-200 transition-colors p-4 text-center">
                <i class="fas fa-chart-pie text-purple-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('By Category') }}</p>
            </a>
            <a href="{{ route('reports.monthly-trend') }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 transition-colors p-4 text-center">
                <i class="fas fa-chart-line text-emerald-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('Monthly Trend') }}</p>
            </a>
            <a href="{{ route('reports.member-spending', $dateQuery) }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-orange-50 hover:border-orange-200 transition-colors p-4 text-center">
                <i class="fas fa-users text-orange-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('Member Spending') }}</p>
            </a>
            <a href="{{ route('reports.account-summary') }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-teal-50 hover:border-teal-200 transition-colors p-4 text-center">
                <i class="fas fa-building-columns text-teal-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('Account Summary') }}</p>
            </a>
            <a href="{{ route('reports.export', $dateQuery) }}" class="rounded-2xl border border-gray-100 bg-gray-50 hover:bg-rose-50 hover:border-rose-200 transition-colors p-4 text-center">
                <i class="fas fa-download text-rose-600 text-xl mb-2"></i>
                <p class="text-xs font-semibold text-gray-700">{{ __('Export CSV') }}</p>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Total Income') }}</p>
            <div class="mt-3 space-y-2">
                @forelse($reportData['total_income_by_currency'] as $currency => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $currency }}</span>
                        <span class="font-bold text-emerald-600">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Total Expenses') }}</p>
            <div class="mt-3 space-y-2">
                @forelse($reportData['total_expenses_by_currency'] as $currency => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $currency }}</span>
                        <span class="font-bold text-red-600">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Net') }}</p>
            <div class="mt-3 space-y-2">
                @forelse($reportData['net_by_currency'] as $currency => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $currency }}</span>
                        <span class="font-bold {{ $amount >= 0 ? 'text-teal-600' : 'text-red-600' }}">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Transactions') }}</p>
            <p class="text-3xl font-bold text-gray-800 mt-3">{{ $reportData['transaction_count'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ __('Count of all income and expense movements in the selected period.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Income vs Expenses') }}</h3>
                    <p class="text-xs text-gray-400">{{ __('Separated by currency to avoid mixing dollar and dinar totals.') }}</p>
                </div>
            </div>
            <div class="h-[340px]">
                <canvas id="reportMonthlyOverviewChart"></canvas>
            </div>
        </div>

        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Top Expense Categories') }}</h3>
                    <p class="text-xs text-gray-400">{{ __('Category totals are shown by currency.') }}</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse(array_slice($reportData['category_breakdown'], 0, 8) as $item)
                    <div class="rounded-2xl border border-gray-100 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $item['count'] }} {{ __('Transactions') }} · {{ $item['currency'] }}</p>
                            </div>
                            <span class="text-sm font-bold text-gray-800">{{ format_currency($item['total'], $item['currency']) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
                        {{ __('No transactions yet') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Top Expenses') }}</h3>
                <p class="text-xs text-gray-400">{{ __('Largest expense entries in the selected period.') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500 border-b">
                        <th class="pb-3 font-medium">{{ __('Description') }}</th>
                        <th class="pb-3 font-medium">{{ __('Category') }}</th>
                        <th class="pb-3 font-medium">{{ __('By') }}</th>
                        <th class="pb-3 font-medium">{{ __('Account') }}</th>
                        <th class="pb-3 font-medium">{{ __('Date') }}</th>
                        <th class="pb-3 font-medium text-{{ is_rtl() ? 'left' : 'right' }}">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData['top_expenses'] as $exp)
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-800">{{ $exp->description }}</td>
                            <td class="py-3 text-gray-500">{{ $exp->category?->display_name ?? __('Uncategorized') }}</td>
                            <td class="py-3 text-gray-500">{{ $exp->user?->name ?? '-' }}</td>
                            <td class="py-3 text-gray-500">{{ $exp->account?->name ?? '-' }}</td>
                            <td class="py-3 text-gray-500">{{ $exp->transaction_date?->format('Y-m-d') }}</td>
                            <td class="py-3 text-{{ is_rtl() ? 'left' : 'right' }} font-semibold text-red-600">
                                {{ format_currency($exp->amount, $exp->account?->currency) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">{{ __('No transactions yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chart = @json($reportData['monthly_chart'] ?? ['labels' => [], 'datasets' => []]);
    const canvas = document.getElementById('reportMonthlyOverviewChart');

    if (!canvas || !chart.labels.length) {
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: chart,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
