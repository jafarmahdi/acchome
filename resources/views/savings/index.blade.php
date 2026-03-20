@extends('layouts.app')
@section('title', __('Savings Goals'))
@section('page-title', __('Savings Goals'))

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ url('/savings-goals/create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('New Goal') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($goals as $goal)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $goal->color }}15">
                    <i class="fas fa-{{ $goal->icon ?? 'piggy-bank' }} text-lg" style="color: {{ $goal->color }}"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $goal->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $goal->status === 'completed' ? 'bg-green-100 text-green-700' : ($goal->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ __(ucfirst($goal->status)) }}
                    </span>
                </div>
            </div>
            <div class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/savings-goals/' . $goal->id . '/edit') }}" class="text-gray-400 hover:text-blue-600"><i class="fas fa-edit"></i></a>
            </div>
        </div>

        <div class="mb-3">
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600 font-semibold">{{ format_currency($goal->current_amount) }}</span>
                <span class="text-gray-400">{{ format_currency($goal->target_amount) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-emerald-500 transition-all" style="width: {{ min($goal->progress, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $goal->progress }}% &middot; {{ format_currency($goal->remaining) }} {{ __('to go') }}</p>
        </div>

        @if($goal->target_date)
            <p class="text-xs text-gray-400 mb-3"><i class="fas fa-calendar {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __('Target') }}: {{ $goal->target_date->format('M d, Y') }}</p>
        @endif

        @if($goal->status === 'active')
        <form method="POST" action="{{ url('/savings-goals/' . $goal->id . '/contribute') }}" class="space-y-2">
            @csrf
            <div class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <input type="number" name="amount" step="0.01" min="0.01" placeholder="{{ __('Amount') }}" required
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="px-3 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <select name="account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">{{ __('From Account') }}</option>
                @foreach($accounts ?? [] as $account)
                    <option value="{{ $account->id }}" {{ (string) ($goal->account_id ?? '') === (string) $account->id ? 'selected' : '' }}>
                        {{ $account->name }} ({{ format_currency($account->balance, $account->currency) }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-piggy-bank text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">{{ __('No savings goals yet.') }}</p>
    </div>
    @endforelse
</div>
@if($goals->hasPages())<div class="mt-6">{{ $goals->withQueryString()->links() }}</div>@endif
@endsection
