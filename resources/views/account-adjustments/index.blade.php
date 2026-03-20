@extends('layouts.app')
@section('title', __('Account Adjustments'))
@section('page-title', __('Account Adjustments'))

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Balance Reconciliation') }}</h2>
        <form method="POST" action="{{ route('account-adjustments.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Account') }} *</label>
                <select name="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Choose account') }}</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ (string) old('account_id', request('account_id')) === (string) $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ format_currency($account->balance, $account->currency) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Adjustment Type') }} *</label>
                <select name="adjustment_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="set" {{ old('adjustment_type', 'set') === 'set' ? 'selected' : '' }}>{{ __('Set Actual Balance') }}</option>
                    <option value="add" {{ old('adjustment_type') === 'add' ? 'selected' : '' }}>{{ __('Add Difference') }}</option>
                    <option value="subtract" {{ old('adjustment_type') === 'subtract' ? 'selected' : '' }}>{{ __('Subtract Difference') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Amount') }} *</label>
                <input type="number" step="0.01" min="0" name="entered_amount" value="{{ old('entered_amount') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Date') }} *</label>
                <input type="date" name="adjustment_date" value="{{ old('adjustment_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Reason') }} *</label>
                <input type="text" name="reason" value="{{ old('reason') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="{{ __('Wallet count, bank check, opening balance...') }}">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800">
                <i class="fas fa-check {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Save Adjustment') }}
            </button>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('Adjustment History') }}</h2>
            <form method="GET">
                <select name="account_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('All Accounts') }}</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ (string) request('account_id') === (string) $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-{{ is_rtl() ? 'right' : 'left' }} text-gray-500">
                        <th class="pb-3 font-medium">{{ __('Date') }}</th>
                        <th class="pb-3 font-medium">{{ __('Account') }}</th>
                        <th class="pb-3 font-medium">{{ __('Type') }}</th>
                        <th class="pb-3 font-medium">{{ __('Previous') }}</th>
                        <th class="pb-3 font-medium">{{ __('Difference') }}</th>
                        <th class="pb-3 font-medium">{{ __('New Balance') }}</th>
                        <th class="pb-3 font-medium">{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        @php
                            $typeLabel = match ($adjustment->adjustment_type) {
                                'set' => __('Set Actual Balance'),
                                'add' => __('Add Difference'),
                                'subtract' => __('Subtract Difference'),
                                default => $adjustment->adjustment_type,
                            };
                        @endphp
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-600">{{ $adjustment->adjustment_date?->format('Y-m-d') }}</td>
                            <td class="py-3 font-medium text-gray-800">{{ $adjustment->account?->name ?? '-' }}</td>
                            <td class="py-3 text-gray-600">{{ $typeLabel }}</td>
                            <td class="py-3 text-gray-600">{{ format_currency($adjustment->previous_balance, $adjustment->account?->currency) }}</td>
                            <td class="py-3 {{ $adjustment->difference >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-semibold">{{ format_currency($adjustment->difference, $adjustment->account?->currency) }}</td>
                            <td class="py-3 font-semibold text-gray-800">{{ format_currency($adjustment->new_balance, $adjustment->account?->currency) }}</td>
                            <td class="py-3 text-gray-500">{{ $adjustment->reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">{{ __('No balance adjustments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($adjustments->hasPages())
            <div class="mt-6">{{ $adjustments->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
