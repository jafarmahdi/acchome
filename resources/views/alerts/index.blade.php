@extends('layouts.app')
@section('title', __('Alerts'))
@section('page-title', __('Alerts'))

@section('content')
@php
    $severityStyles = [
        'danger' => [
            'card' => 'border-red-200 bg-red-50/80',
            'icon' => 'bg-red-100 text-red-600',
            'badge' => 'bg-red-100 text-red-700',
        ],
        'warning' => [
            'card' => 'border-amber-200 bg-amber-50/80',
            'icon' => 'bg-amber-100 text-amber-700',
            'badge' => 'bg-amber-100 text-amber-700',
        ],
        'success' => [
            'card' => 'border-emerald-200 bg-emerald-50/80',
            'icon' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ],
        'info' => [
            'card' => 'border-blue-200 bg-blue-50/80',
            'icon' => 'bg-blue-100 text-blue-700',
            'badge' => 'bg-blue-100 text-blue-700',
        ],
    ];
@endphp

<div class="space-y-6">
    <div class="bg-gradient-to-r from-red-900 via-slate-900 to-slate-800 rounded-3xl text-white p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-red-200">{{ __('Strong Alerts') }}</p>
                <h2 class="text-2xl sm:text-3xl font-bold mt-2">{{ __('Budgets, installments, and important movements in one place') }}</h2>
                <p class="text-sm text-slate-300 mt-2">{{ __('Critical items stay visible until you read or dismiss them.') }}</p>
            </div>
            @if($alerts->total() > 0)
                <form method="POST" action="{{ url('/alerts/read-all') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white/10 border border-white/15 text-white text-sm font-medium hover:bg-white/15">
                        <i class="fas fa-check-double {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>{{ __('Mark all as read') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500">{{ __('All Alerts') }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-red-600">{{ __('Critical') }}</p>
            <p class="text-3xl font-bold text-red-700 mt-2">{{ $summary['danger'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-amber-600">{{ __('Warnings') }}</p>
            <p class="text-3xl font-bold text-amber-700 mt-2">{{ $summary['warning'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-blue-600">{{ __('Unread') }}</p>
            <p class="text-3xl font-bold text-blue-700 mt-2">{{ $summary['unread'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Type') }}</label>
                <select name="type" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ __(ucwords(str_replace('_', ' ', $type))) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Severity') }}</label>
                <select name="severity" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    <option value="">{{ __('All Severity') }}</option>
                    @foreach(['danger', 'warning', 'info', 'success'] as $severity)
                        <option value="{{ $severity }}" {{ request('severity') === $severity ? 'selected' : '' }}>{{ __(ucfirst($severity)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Status') }}</label>
                <select name="status" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>{{ __('Unread') }}</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>{{ __('Read') }}</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>{{ __('Dismissed') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Sort') }}</label>
                <select name="sort" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                    <option value="">{{ __('Newest') }}</option>
                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>{{ __('Date') }}</option>
                    <option value="severity" {{ request('sort') === 'severity' ? 'selected' : '' }}>{{ __('Severity') }}</option>
                    <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>{{ __('Type') }}</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">{{ __('Apply') }}</button>
                <a href="{{ url('/alerts') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($alerts as $alert)
            @php
                $style = $severityStyles[$alert->severity] ?? $severityStyles['info'];
            @endphp
            <div class="rounded-2xl border shadow-sm p-4 sm:p-5 {{ $style['card'] }} {{ !$alert->is_read ? 'ring-2 ring-offset-2 ring-offset-transparent ' . ($alert->severity === 'danger' ? 'ring-red-200' : ($alert->severity === 'warning' ? 'ring-amber-200' : 'ring-blue-200')) : '' }}">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $style['icon'] }}">
                        <i class="fas fa-{{ $alert->icon ?? 'bell' }} text-lg"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-gray-900">{{ $alert->title }}</h3>
                                    <span class="text-[11px] font-semibold px-2 py-1 rounded-full {{ $style['badge'] }}">{{ __(ucfirst($alert->severity)) }}</span>
                                    @if(!$alert->is_read)
                                        <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-white/80 text-gray-700">{{ __('Unread') }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 mt-2 leading-6">{{ $alert->message }}</p>
                                <div class="flex items-center gap-3 flex-wrap mt-3 text-xs text-gray-500">
                                    <span><i class="fas fa-clock {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ $alert->created_at->diffForHumans() }}</span>
                                    <span><i class="fas fa-tag {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>{{ __(ucwords(str_replace('_', ' ', $alert->type))) }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap lg:justify-end">
                                @if($alert->action_url)
                                    <a href="{{ $alert->action_url }}" class="px-3 py-2 bg-white/80 text-sm font-medium text-gray-800 rounded-xl border border-white hover:bg-white">
                                        {{ __('Open') }}
                                    </a>
                                @endif
                                @if(!$alert->is_read)
                                    <form method="POST" action="{{ url('/alerts/' . $alert->id . '/read') }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700">
                                            {{ __('Mark as read') }}
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ url('/alerts/' . $alert->id . '/dismiss') }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-black">
                                        {{ __('Dismiss') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
                <p class="text-lg font-semibold text-gray-700">{{ __('No alerts.') }}</p>
                <p class="text-sm text-gray-400 mt-2">{{ __('When budgets, installments, or important movements need your attention, they will appear here.') }}</p>
            </div>
        @endforelse
    </div>

    @if($alerts->hasPages())
        <div>{{ $alerts->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
