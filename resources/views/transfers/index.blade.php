@extends('layouts.app')
@section('title', __('Transfers'))
@section('page-title', __('Transfers'))

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ url('/transfers/create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
        <i class="fas fa-exchange-alt {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('New Transfer') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-{{ is_rtl() ? 'right' : 'left' }}">
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('From') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-center"><i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }}"></i></th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('To') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Description') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('By') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-{{ is_rtl() ? 'left' : 'right' }}">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600 text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transfers as $transfer)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $transfer->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 font-medium text-gray-700">{{ $transfer->account->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-purple-400"><i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }}"></i></td>
                    <td class="px-4 py-3 font-medium text-gray-700">{{ $transfer->transferToAccount->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $transfer->description }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $transfer->user->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-{{ is_rtl() ? 'left' : 'right' }} font-semibold text-purple-600">{{ format_currency($transfer->amount, $transfer->account?->currency) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ url('/transfers/' . $transfer->id . '/edit') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-100">
                                {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ url('/transfers/' . $transfer->id) }}" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100">
                                    {{ __('Cancel') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">{{ __('No transfers yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($transfers->hasPages())<div class="mt-6">{{ $transfers->withQueryString()->links() }}</div>@endif
@endsection
