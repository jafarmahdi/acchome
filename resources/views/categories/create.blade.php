@extends('layouts.app')
@section('title', isset($category) ? __('Edit Category') : __('New Category'))
@section('page-title', isset($category) ? __('Edit Category') : __('New Category'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @php
            $iconOptions = [
                'tag' => 'عام',
                'wallet' => 'محفظة',
                'money-bill-wave' => 'راتب',
                'gift' => 'هدية',
                'briefcase' => 'عمل',
                'house' => 'بيت',
                'cart-shopping' => 'تسوق',
                'basket-shopping' => 'بقالة',
                'utensils' => 'مطعم',
                'car' => 'سيارة',
                'gas-pump' => 'وقود',
                'bolt' => 'كهرباء',
                'faucet-drip' => 'ماء',
                'wifi' => 'إنترنت',
                'phone' => 'هاتف',
                'school' => 'مدرسة',
                'book' => 'كتب',
                'heart-pulse' => 'صحة',
                'capsules' => 'دواء',
                'shirt' => 'ملابس',
                'piggy-bank' => 'ادخار',
                'hand-holding-dollar' => 'قرض',
                'building-columns' => 'بنك',
                'plane' => 'سفر',
                'film' => 'ترفيه',
            ];
            $selectedIcon = old('icon', $category->icon ?? 'tag');
        @endphp
        <form method="POST" action="{{ isset($category) ? url('/categories/' . $category->id) : url('/categories') }}">
            @csrf
            @if(isset($category)) @method('PUT') @endif
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name (English)') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name (Arabic)') }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $category->name_ar ?? '') }}" dir="rtl"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }} *</label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="expense" {{ old('type', $category->type ?? 'expense') === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                        <option value="income" {{ old('type', $category->type ?? '') === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                        <option value="both" {{ old('type', $category->type ?? '') === 'both' ? 'selected' : '' }}>{{ __('Both') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Parent Category') }}</label>
                    <select name="parent_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('None (Top-level)') }}</option>
                        @foreach($parentCategories ?? [] as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id', $category->parent_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-data="{ selectedIcon: '{{ $selectedIcon }}' }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Icon') }}</label>
                    <select name="icon" x-model="selectedIcon"
                            class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach($iconOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }} ({{ $value }})</option>
                        @endforeach
                    </select>
                    <div class="mt-3 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600">
                            <i class="fas" :class="'fa-' + selectedIcon"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ __('Icon') }}</p>
                            <p class="text-xs text-gray-400" x-text="selectedIcon"></p>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Color') }}</label>
                    <input type="color" name="color" value="{{ old('color', $category->color ?? '#6B7280') }}" class="h-10 w-full border border-gray-300 rounded-lg cursor-pointer">
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                <a href="{{ url('/categories') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    {{ isset($category) ? __('Update') : __('Create') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
