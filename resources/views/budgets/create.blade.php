@extends('layouts.app')
@section('title', isset($budget) ? __('Edit Budget') : __('New Budget'))
@section('page-title', isset($budget) ? __('Edit Budget') : __('New Budget'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ isset($budget) ? url('/budgets/' . $budget->id) : url('/budgets') }}">
            @csrf
            @if(isset($budget)) @method('PUT') @endif
            @php
                $selectedCategoryIds = collect(old('category_ids', $budget->selected_category_ids ?? []))
                    ->map(fn ($id) => (string) $id)
                    ->all();
            @endphp
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Budget Name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $budget->name ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500" placeholder="{{ __('e.g. Monthly Groceries') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Categories') }}</label>
                    <div class="rounded-lg border border-gray-300 p-3 space-y-2 max-h-64 overflow-y-auto">
                        @foreach($categories ?? [] as $cat)
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                       {{ in_array((string) $cat->id, $selectedCategoryIds, true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <span class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $cat->color ?? '#6B7280' }}"></span>
                                    {{ $cat->display_name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-2">{{ __('Leave all categories unchecked to apply this budget to all expense categories.') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Budget Amount') }} *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $budget->amount ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Period') }} *</label>
                    <select name="period" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(['weekly','monthly','quarterly','yearly'] as $p)
                            <option value="{{ $p }}" {{ old('period', $budget->period ?? 'monthly') === $p ? 'selected' : '' }}>{{ __(ucfirst($p)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Date') }} *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', isset($budget) ? $budget->start_date->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Date') }} *</label>
                        <input type="date" name="end_date" value="{{ old('end_date', isset($budget) ? $budget->end_date->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alert at (%)') }}</label>
                    <input type="number" name="alert_threshold" min="0" max="100" value="{{ old('alert_threshold', $budget->alert_threshold ?? 80) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/budgets') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                    {{ isset($budget) ? __('Update Budget') : __('Create Budget') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
