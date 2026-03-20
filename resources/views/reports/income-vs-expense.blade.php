@extends('layouts.app')
@section('title', __('Income vs Expense'))
@section('page-title', __('Income vs Expense'))

@section('content')
@php
    $chart = [
        'labels' => array_map(fn($row) => $row['month'], $monthlyRows),
        'datasets' => [],
    ];
    $currencies = collect($monthlyRows)
        ->flatMap(fn($row) => array_unique(array_merge(array_keys($row['income_by_currency']), array_keys($row['expense_by_currency']))))
        ->unique()
        ->values();
    $palette = [
        'IQD' => ['income' => '#0f766e', 'expense' => '#b91c1c'],
        'USD' => ['income' => '#2563eb', 'expense' => '#7c3aed'],
    ];
    foreach ($currencies as $currency) {
        $colors = $palette[$currency] ?? ['income' => '#16a34a', 'expense' => '#ef4444'];
        $chart['datasets'][] = [
            'label' => __('Income') . ' (' . $currency . ')',
            'data' => array_map(fn($row) => (float) ($row['income_by_currency'][$currency] ?? 0), $monthlyRows),
            'backgroundColor' => $colors['income'],
        ];
        $chart['datasets'][] = [
            'label' => __('Expenses') . ' (' . $currency . ')',
            'data' => array_map(fn($row) => (float) ($row['expense_by_currency'][$currency] ?? 0), $monthlyRows),
            'backgroundColor' => $colors['expense'],
        ];
    }
@endphp

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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Total Income') }}</p>
            <div class="mt-3 space-y-2">
                @forelse($incomeByCurrency as $currency => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $currency }}</span>
                        <span class="font-bold text-green-600">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">{{ __('Total Expenses') }}</p>
            <div class="mt-3 space-y-2">
                @forelse($expensesByCurrency as $currency => $amount)
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
                @forelse($netByCurrency as $currency => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $currency }}</span>
                        <span class="font-bold {{ $amount >= 0 ? 'text-teal-600' : 'text-red-600' }}">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('No transactions yet') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Income vs Expenses') }}</h3>
                <p class="text-xs text-gray-400">{{ __('Monthly comparison separated by currency.') }}</p>
            </div>
        </div>
        <div class="h-[360px]">
            <canvas id="incomeExpenseDetailedChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                        <th class="py-3 font-medium">{{ __('Month') }}</th>
                        <th class="py-3 font-medium">{{ __('Income') }}</th>
                        <th class="py-3 font-medium">{{ __('Expenses') }}</th>
                        <th class="py-3 font-medium">{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRows as $row)
                        <tr class="border-b border-gray-50 align-top">
                            <td class="py-3 font-medium text-gray-800">{{ $row['month'] }}</td>
                            <td class="py-3">
                                @forelse($row['income_by_currency'] as $currency => $amount)
                                    <div class="text-green-600 font-semibold">{{ format_currency($amount, $currency) }}</div>
                                @empty
                                    <span class="text-gray-300">-</span>
                                @endforelse
                            </td>
                            <td class="py-3">
                                @forelse($row['expense_by_currency'] as $currency => $amount)
                                    <div class="text-red-600 font-semibold">{{ format_currency($amount, $currency) }}</div>
                                @empty
                                    <span class="text-gray-300">-</span>
                                @endforelse
                            </td>
                            <td class="py-3">
                                @forelse($row['net_by_currency'] as $currency => $amount)
                                    <div class="font-semibold {{ $amount >= 0 ? 'text-teal-600' : 'text-red-600' }}">{{ format_currency($amount, $currency) }}</div>
                                @empty
                                    <span class="text-gray-300">-</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400">{{ __('No transactions yet') }}</td>
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
    const chart = @json($chart);
    const canvas = document.getElementById('incomeExpenseDetailedChart');
    if (!canvas || !chart.labels.length) return;

    new Chart(canvas, {
        type: 'bar',
        data: chart,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
