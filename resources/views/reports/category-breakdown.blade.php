@extends('layouts.app')
@section('title', __('By Category'))
@section('page-title', __('By Category'))

@section('content')
@php
    $chart = [
        'labels' => array_map(fn($item) => $item['label'], array_slice($breakdown, 0, 10)),
        'datasets' => [[
            'data' => array_map(fn($item) => $item['total'], array_slice($breakdown, 0, 10)),
            'backgroundColor' => array_map(fn($item) => $item['color'], array_slice($breakdown, 0, 10)),
            'borderWidth' => 0,
        ]],
    ];
@endphp

<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Type') }}</label>
                <select name="type" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                    <option value="income" {{ $type === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700">{{ __('Apply') }}</button>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-lg font-semibold text-gray-800">{{ __('Totals') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse($totalByCurrency as $currency => $amount)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <p class="text-xs text-gray-400">{{ $currency }}</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ format_currency($amount, $currency) }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
                        {{ __('No transactions yet') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Category') }}</h3>
            <div class="h-[340px]">
                <canvas id="categoryBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                        <th class="py-3 font-medium">{{ __('Category') }}</th>
                        <th class="py-3 font-medium">{{ __('Currency') }}</th>
                        <th class="py-3 font-medium">{{ __('Transactions') }}</th>
                        <th class="py-3 font-medium text-{{ is_rtl() ? 'left' : 'right' }}">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($breakdown as $item)
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-800">{{ $item['name'] }}</td>
                            <td class="py-3 text-gray-500">{{ $item['currency'] }}</td>
                            <td class="py-3 text-gray-500">{{ $item['count'] }}</td>
                            <td class="py-3 text-{{ is_rtl() ? 'left' : 'right' }} font-semibold">{{ format_currency($item['total'], $item['currency']) }}</td>
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
    const canvas = document.getElementById('categoryBreakdownChart');
    if (!canvas || !chart.labels.length) return;

    new Chart(canvas, {
        type: 'doughnut',
        data: chart,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '62%'
        }
    });
});
</script>
@endpush
