@extends('layouts.app')
@section('title', __('Expenses'))
@section('page-title', __('Expenses'))

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        <select name="account_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('All Accounts') }}</option>
            @foreach($accounts ?? [] as $acc)
                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">

        <div class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
            <button type="submit" class="flex-1 bg-gray-800 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-900">
                <i class="fas fa-filter {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Filter') }}
            </button>
            <a href="{{ url('/expenses') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Header with Add button and total -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
    <div>
        <p class="text-sm text-gray-500">{{ __('Total') }}: <span class="font-semibold text-red-600">{{ format_currency($totalExpenses ?? 0) }}</span></p>
    </div>
    <a href="{{ url('/expenses/create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Add Expense') }}
    </a>
</div>

<!-- Expenses Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-{{ is_rtl() ? 'right' : 'left' }}">
                    <th class="px-4 py-3 font-medium text-gray-600">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'transaction_date', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">
                            {{ __('Date') }} @if(request('sort') === 'transaction_date') <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Description') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Category') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Account') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('By') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Method') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-{{ is_rtl() ? 'left' : 'right' }}">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">
                            {{ __('Amount') }} @if(request('sort') === 'amount') <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        {{ $expense->transaction_date->format('M d, Y') }}
                        @if($expense->transaction_time)
                            <span class="text-xs text-gray-400">{{ $expense->transaction_time }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-gray-800 font-medium">{{ $expense->description }}</p>
                        @if($expense->notes)
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $expense->notes }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($expense->category)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" style="background-color: {{ $expense->category->color }}15; color: {{ $expense->category->color }}">
                                {{ $expense->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $expense->account->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                            <img src="{{ $expense->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($expense->user?->name ?? 'Unknown') . '&background=CBD5E1&color=334155' }}" class="w-6 h-6 rounded-full">
                            <span class="text-gray-600 text-xs">{{ $expense->user?->name ?? __('Unknown user') }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 capitalize text-xs">{{ str_replace('_', ' ', $expense->payment_method) }}</td>
                    <td class="px-4 py-3 text-{{ is_rtl() ? 'left' : 'right' }} font-semibold text-red-600 whitespace-nowrap">
                        {{ format_currency($expense->amount) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                            @if($expense->receipt_image)
                                <a href="{{ $expense->receipt_url }}" target="_blank" class="text-blue-500 hover:text-blue-700" title="{{ __('Receipt') }}">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                            @endif
                            <a href="{{ url('/expenses/' . $expense->id . '/edit') }}" class="text-gray-400 hover:text-blue-600">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ url('/expenses/' . $expense->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete this expense?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="fas fa-receipt text-4xl mb-3"></i>
                        <p>{{ __('No expenses recorded yet.') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($expenses->hasPages())
<div class="mt-6">{{ $expenses->withQueryString()->links() }}</div>
@endif
@endsection
