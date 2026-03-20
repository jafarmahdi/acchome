@extends('layouts.app')
@section('title', __('Recurring Transactions'))
@section('page-title', __('Recurring Transactions'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Types') }}</option>
            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
        </select>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Status') }}</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
        </select>
    </form>
    <a href="{{ route('recurring-transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Add Recurring Transaction') }}
    </a>
</div>

<div class="space-y-4">
    @forelse($recurringTransactions as $item)
        <div class="bg-white rounded-xl shadow-sm border {{ $item->isDue() ? 'border-cyan-300' : 'border-gray-100' }} p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <h3 class="font-semibold text-gray-800">{{ $item->description }}</h3>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $item->type === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ __($item->type === 'income' ? 'Income' : 'Expense') }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $item->is_active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ __($item->is_active ? 'Active' : 'Inactive') }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $item->frequencyLabel() }}</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div>
                            <p class="text-gray-400 text-xs">{{ __('Amount') }}</p>
                            <p class="font-semibold text-gray-800">{{ format_currency($item->amount, $item->account?->currency) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs">{{ __('Next Due Date') }}</p>
                            <p class="font-semibold text-gray-800">{{ $item->next_due_date?->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs">{{ __('Account') }}</p>
                            <p class="font-semibold text-gray-800">{{ $item->account?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs">{{ __('Category') }}</p>
                            <p class="font-semibold text-gray-800">{{ $item->category?->display_name ?? __('Uncategorized') }}</p>
                        </div>
                    </div>

                    @if($item->notes)
                        <p class="text-xs text-gray-500 mt-3">{{ $item->notes }}</p>
                    @endif

                    @if($item->isDue())
                        <div class="mt-3 bg-cyan-50 border border-cyan-200 rounded-lg px-3 py-2 text-xs text-cyan-700">
                            <i class="fas fa-bell {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Due now') }}
                        </div>
                    @elseif($item->next_due_date && $item->next_due_date->isTomorrow())
                        <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 text-xs text-yellow-700">
                            <i class="fas fa-clock {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Due tomorrow') }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if($item->is_active)
                        <form method="POST" action="{{ route('recurring-transactions.process', $item) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-emerald-600 text-white text-xs rounded-lg hover:bg-emerald-700">{{ __('Record Now') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('recurring-transactions.edit', $item) }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-xs rounded-lg hover:bg-gray-200">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('recurring-transactions.destroy', $item) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 text-xs rounded-lg hover:bg-red-100">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
            <i class="fas fa-calendar-check text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">{{ __('No recurring transactions yet.') }}</p>
        </div>
    @endforelse
</div>

@if($recurringTransactions->hasPages())
    <div class="mt-6">{{ $recurringTransactions->withQueryString()->links() }}</div>
@endif
@endsection
