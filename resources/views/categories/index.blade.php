@extends('layouts.app')
@section('title', __('Categories'))
@section('page-title', __('Categories'))

@section('content')
<div class="flex items-center justify-between mb-6">
    <form method="GET" class="flex space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Types') }}</option>
            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
        </select>
    </form>
    <a href="{{ url('/categories/create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('New Category') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-{{ is_rtl() ? 'right' : 'left' }}">
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Category') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Arabic Name') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Type') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Parent') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                            <span class="w-4 h-4 rounded-full" style="background-color: {{ $cat->color }}"></span>
                            <i class="fas fa-{{ $cat->icon ?? 'tag' }} text-gray-400"></i>
                            <span class="font-medium text-gray-800">{{ $cat->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500" dir="rtl">{{ $cat->name_ar ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $cat->type === 'expense' ? 'bg-red-100 text-red-700' : ($cat->type === 'income' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ __(ucfirst($cat->type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $cat->parent->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ url('/categories/' . $cat->id . '/edit') }}" class="text-gray-400 hover:text-blue-600 mx-1"><i class="fas fa-edit"></i></a>
                        <form action="{{ url('/categories/' . $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-600 mx-1"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">{{ __('No categories.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
