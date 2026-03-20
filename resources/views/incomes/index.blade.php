@extends('layouts.app')
@section('title', __('Income'))
@section('page-title', __('Income'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="bg-gray-800 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-900">
            <i class="fas fa-filter {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Filter') }}
        </button>
    </form>
</div>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
    <p class="text-sm text-gray-500">{{ __('Total') }}: <span class="font-semibold text-green-600">{{ format_currency($totalIncome ?? 0) }}</span></p>
    <a href="{{ url('/incomes/create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Add Income') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-{{ is_rtl() ? 'right' : 'left' }}">
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Description') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Category') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Account') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('By') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-{{ is_rtl() ? 'left' : 'right' }}">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($incomes as $income)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $income->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $income->description }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $income->category->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $income->account->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $income->user->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-{{ is_rtl() ? 'left' : 'right' }} font-semibold text-green-600">+{{ format_currency($income->amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ url('/incomes/' . $income->id . '/edit') }}" class="text-gray-400 hover:text-blue-600 mx-1"><i class="fas fa-edit"></i></a>
                        <form action="{{ url('/incomes/' . $income->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-600 mx-1"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">{{ __('No income recorded yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($incomes->hasPages())<div class="mt-6">{{ $incomes->withQueryString()->links() }}</div>@endif
@endsection
