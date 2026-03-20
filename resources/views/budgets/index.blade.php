@extends('layouts.app')
@section('title', __('Budgets'))
@section('page-title', __('Budgets'))

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ url('/budgets/create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('New Budget') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($budgets as $budget)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 {{ $budget->isOverBudget() ? 'border-red-200' : '' }}">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $budget->name }}</h3>
                <p class="text-xs text-gray-400 capitalize">
                    {{ $budget->period }} &middot;
                    {{ !empty($budget->category_names) ? implode('، ', $budget->category_names) : __('All Categories') }}
                </p>
            </div>
            <div class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/budgets/' . $budget->id . '/edit') }}" class="text-gray-400 hover:text-blue-600"><i class="fas fa-edit"></i></a>
                <form action="{{ url('/budgets/' . $budget->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                    @csrf @method('DELETE')
                    <button class="text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        <div class="mb-2">
            <div class="flex justify-between text-sm mb-1">
                <span class="{{ $budget->isOverBudget() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ format_currency($budget->spent) }}</span>
                <span class="text-gray-400">{{ format_currency($budget->amount) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full transition-all {{ $budget->isOverBudget() ? 'bg-red-500' : ($budget->isNearLimit() ? 'bg-yellow-500' : 'bg-orange-500') }}"
                     style="width: {{ min($budget->percent_used, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $budget->percent_used }}% {{ __('used') }} &middot; {{ format_currency($budget->remaining) }} {{ __('remaining') }}</p>
        </div>

        <div class="text-xs text-gray-400">
            {{ $budget->start_date->format('M d') }} - {{ $budget->end_date->format('M d, Y') }}
        </div>

        @if($budget->isOverBudget())
            <div class="mt-3 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-600">
                <i class="fas fa-exclamation-triangle {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Over budget!') }}
            </div>
        @elseif($budget->isNearLimit())
            <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 text-xs text-yellow-700">
                <i class="fas fa-exclamation-circle {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Approaching limit') }}
            </div>
        @endif
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-bullseye text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">{{ __('No budgets set up yet.') }}</p>
    </div>
    @endforelse
</div>
@if($budgets->hasPages())<div class="mt-6">{{ $budgets->withQueryString()->links() }}</div>@endif
@endsection
