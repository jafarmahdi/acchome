@extends('layouts.app')
@section('title', __('Audit Logs'))
@section('page-title', __('Audit Logs'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <select name="user_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('All Users') }}</option>
            @foreach($members ?? [] as $m)
                <option value="{{ $m->id }}" {{ request('user_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
            @endforeach
        </select>
        <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('All Actions') }}</option>
            @foreach(['created','updated','deleted','login','logout'] as $a)
                <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ __(ucfirst($a)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="bg-gray-800 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-900">{{ __('Filter') }}</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-{{ is_rtl() ? 'right' : 'left' }}">
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Date/Time') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('User') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Action') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Resource') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('Description') }}</th>
                    <th class="px-4 py-3 font-medium text-gray-600">{{ __('IP') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $log->user->name ?? __('System') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $log->action === 'created' ? 'bg-green-100 text-green-700' : ($log->action === 'deleted' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ __(ucfirst($log->action)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ class_basename($log->model_type ?? '') }} #{{ $log->model_id }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ \Illuminate\Support\Str::limit($log->description, 60) }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">{{ __('No audit logs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($logs->hasPages())<div class="mt-6">{{ $logs->withQueryString()->links() }}</div>@endif
@endsection
