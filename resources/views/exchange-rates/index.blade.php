@extends('layouts.app')
@section('title', __('Exchange Rates'))
@section('page-title', __('Exchange Rates'))

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="xl:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Add Exchange Rate') }}</h2>
        <form method="POST" action="{{ route('exchange-rates.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">{{ __('From Currency') }}</label>
                    <select name="from_currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['IQD','USD','EUR','GBP','SAR','AED'] as $currency)
                            <option value="{{ $currency }}" {{ old('from_currency', 'USD') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">{{ __('To Currency') }}</label>
                    <select name="to_currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['IQD','USD','EUR','GBP','SAR','AED'] as $currency)
                            <option value="{{ $currency }}" {{ old('to_currency', 'IQD') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Rate') }}</label>
                <input type="number" step="0.000001" min="0.000001" name="rate" value="{{ old('rate') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Effective Date') }}</label>
                <input type="date" name="effective_date" value="{{ old('effective_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="{{ __('Optional notes...') }}">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class="fas fa-save {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Save Rate') }}
            </button>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('Currency Converter') }}</h2>
            <p class="text-xs text-gray-400">{{ __('Uses the latest saved rate up to the selected date.') }}</p>
        </div>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Amount') }}</label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ request('amount') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('From Currency') }}</label>
                <select name="from_currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach(['IQD','USD','EUR','GBP','SAR','AED'] as $currency)
                        <option value="{{ $currency }}" {{ request('from_currency', 'USD') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('To Currency') }}</label>
                <select name="to_currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach(['IQD','USD','EUR','GBP','SAR','AED'] as $currency)
                        <option value="{{ $currency }}" {{ request('to_currency', 'IQD') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Date') }}</label>
                <input type="date" name="conversion_date" value="{{ request('conversion_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800">{{ __('Convert') }}</button>
        </form>

        @if($conversion)
            <div class="mt-5 rounded-xl border {{ $conversion['converted_amount'] !== null ? 'border-emerald-200 bg-emerald-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
                @if($conversion['converted_amount'] !== null)
                    <p class="text-sm text-gray-600">{{ __('Rate used') }}: <span class="font-semibold text-gray-800">{{ number_format($conversion['rate'], 6) }}</span></p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">
                        {{ format_currency($conversion['amount'], $conversion['from_currency']) }}
                        <span class="text-gray-400 text-lg">→</span>
                        {{ format_currency($conversion['converted_amount'], $conversion['to_currency']) }}
                    </p>
                @else
                    <p class="text-sm text-yellow-700">{{ __('No exchange rate found for the selected currencies and date.') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('Saved Rates') }}</h2>
        <p class="text-xs text-gray-400">{{ __('Latest first') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                    <th class="pb-3 font-medium">{{ __('Pair') }}</th>
                    <th class="pb-3 font-medium">{{ __('Rate') }}</th>
                    <th class="pb-3 font-medium">{{ __('Effective Date') }}</th>
                    <th class="pb-3 font-medium">{{ __('Notes') }}</th>
                    <th class="pb-3 font-medium text-{{ is_rtl() ? 'left' : 'right' }}">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rates as $rate)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-medium text-gray-800">{{ $rate->from_currency }} / {{ $rate->to_currency }}</td>
                        <td class="py-3 text-gray-600">{{ number_format((float) $rate->rate, 6) }}</td>
                        <td class="py-3 text-gray-600">{{ $rate->effective_date?->format('Y-m-d') }}</td>
                        <td class="py-3 text-gray-500">{{ $rate->notes ?: '-' }}</td>
                        <td class="py-3 text-{{ is_rtl() ? 'left' : 'right' }}">
                            <form method="POST" action="{{ route('exchange-rates.destroy', $rate) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">{{ __('No exchange rates saved yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rates->hasPages())
        <div class="mt-6">{{ $rates->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
