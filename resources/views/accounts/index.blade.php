@extends('layouts.app')
@section('title', __('Accounts'))
@section('page-title', __('Accounts'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
        <form method="GET" class="flex items-center space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search accounts...') }}"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">{{ __('All Types') }}</option>
                @foreach(['cash','bank','savings','credit_card','loan','rewards','other'] as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_', ' ', $type))) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ url('/account-adjustments') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
            <i class="fas fa-scale-balanced {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Balance Reconciliation') }}
        </a>
        <a href="{{ url('/accounts/create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Add Account') }}
        </a>
    </div>
</div>

<!-- Account Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($accounts as $account)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $account->color }}15">
                    <i class="fas fa-{{ $account->icon ?? 'wallet' }} text-lg" style="color: {{ $account->color }}"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $account->name }}</h3>
                    <p class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $account->type) }} &middot; {{ $account->currency ?? (auth()->user()->family->currency ?? 'IQD') }}</p>
                </div>
            </div>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-1 w-36 bg-white rounded-lg shadow-lg border py-1 z-10">
                    <a href="{{ url('/accounts/' . $account->id . '/edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-edit {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Edit') }}
                    </a>
                    <form action="{{ url('/accounts/' . $account->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="block w-full text-{{ is_rtl() ? 'right' : 'left' }} px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-trash {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-2xl font-bold {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                {{ format_currency($account->balance, $account->currency) }}
            </p>
            @php
                $familyCurrency = auth()->user()->family->currency ?? 'IQD';
                $convertedBalance = $account->currency !== $familyCurrency
                    ? convert_currency((float) $account->balance, $account->currency, $familyCurrency)
                    : null;
            @endphp
            @if($convertedBalance !== null)
                <p class="text-xs text-gray-400 mt-1">{{ __('Approx.') }} {{ format_currency($convertedBalance, $familyCurrency) }}</p>
            @endif
        </div>

        @if($account->user)
            <p class="text-xs text-gray-400"><i class="fas fa-user {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ $account->user->name }}</p>
        @endif

        @if($account->isLowBalance() && $account->low_balance_threshold > 0)
            <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 text-xs text-yellow-700">
                <i class="fas fa-exclamation-triangle {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Low balance alert') }}
            </div>
        @endif

        @if($account->bank_name)
            <p class="text-xs text-gray-400 mt-2"><i class="fas fa-university {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ $account->bank_name }}</p>
        @endif
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-university text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">{{ __('No accounts yet. Create your first account.') }}</p>
    </div>
    @endforelse
</div>

@if($accounts->hasPages())
<div class="mt-6">{{ $accounts->withQueryString()->links() }}</div>
@endif
@endsection
