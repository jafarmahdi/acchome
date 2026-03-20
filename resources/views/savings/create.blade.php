@extends('layouts.app')
@section('title', isset($goal) ? __('Edit Savings Goal') : __('New Savings Goal'))
@section('page-title', isset($goal) ? __('Edit Savings Goal') : __('New Savings Goal'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($goal) ? url('/savings-goals/' . $goal->id) : url('/savings-goals') }}">
            @csrf
            @if(isset($goal)) @method('PUT') @endif
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Goal Name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $goal->name ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500" placeholder="{{ __('e.g. New Car Fund') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Target Amount') }} *</label>
                    <input type="number" name="target_amount" step="0.01" min="0.01" value="{{ old('target_amount', $goal->target_amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Target Date') }}</label>
                    <input type="date" name="target_date" value="{{ old('target_date', isset($goal) && $goal->target_date ? $goal->target_date->format('Y-m-d') : '') }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Linked Account') }}</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('None') }}</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id', $goal->account_id ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Priority') }}</label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(['low','medium','high'] as $p)
                            <option value="{{ $p }}" {{ old('priority', $goal->priority ?? 'medium') === $p ? 'selected' : '' }}>{{ __(ucfirst($p)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">{{ old('description', $goal->description ?? '') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/savings-goals') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                    {{ isset($goal) ? __('Update Goal') : __('Create Goal') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
