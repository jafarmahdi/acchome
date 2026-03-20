@extends('layouts.app')
@section('title', __('Monthly Trend'))
@section('page-title', __('Monthly Trend'))

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Period') }}</label>
                <select name="months" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    @foreach([3, 6, 12, 18, 24] as $option)
                        <option value="{{ $option }}" {{ (int) $months === $option ? 'selected' : '' }}>{{ $option }} {{ __('Months') }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700">{{ __('Apply') }}</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Monthly Trend') }}</h3>
                <p class="text-xs text-gray-400">{{ __('Each currency is drawn separately so totals stay meaningful.') }}</p>
            </div>
        </div>
        <div class="h-[360px]">
            <canvas id="monthlyTrendDetailedChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($trends as $trend)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $trend['month'] }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $trend['transaction_count'] }} {{ __('Transactions') }}</span>
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs mb-2">{{ __('Income') }}</p>
                        @forelse($trend['income_by_currency'] as $currency => $amount)
                            <div class="font-semibold text-green-600">{{ format_currency($amount, $currency) }}</div>
                        @empty
                            <div class="text-gray-300">-</div>
                        @endforelse
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-2">{{ __('Expenses') }}</p>
                        @forelse($trend['expense_by_currency'] as $currency => $amount)
                            <div class="font-semibold text-red-600">{{ format_currency($amount, $currency) }}</div>
                        @empty
                            <div class="text-gray-300">-</div>
                        @endforelse
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-2">{{ __('Net') }}</p>
                        @forelse($trend['net_by_currency'] as $currency => $amount)
                            <div class="font-semibold {{ $amount >= 0 ? 'text-teal-600' : 'text-red-600' }}">{{ format_currency($amount, $currency) }}</div>
                        @empty
                            <div class="text-gray-300">-</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-400">
                {{ __('No transactions yet') }}
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chart = @json($chart);
    const canvas = document.getElementById('monthlyTrendDetailedChart');
    if (!canvas || !chart.labels.length) return;

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
