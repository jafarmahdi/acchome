@extends('layouts.app')
@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))
@section('page-subtitle', __('Welcome back') . ', ' . auth()->user()->name)

@section('content')
@php
    $totalBal = $totalBalance ?? 0;
    $income = $monthlyIncome ?? 0;
    $expenses = $monthlyExpenses ?? 0;
    $savings = $income - $expenses;
    $balanceByCurrency = collect($totalBalanceByCurrency ?? []);
    $incomeByCurrency = collect($monthlyIncomeByCurrency ?? []);
    $expensesByCurrency = collect($monthlyExpensesByCurrency ?? []);
    $netByCurrency = collect($monthlyNetByCurrency ?? []);
    $singleCurrencyMode = $incomeByCurrency->count() <= 1 && $expensesByCurrency->count() <= 1 && $balanceByCurrency->count() <= 1;
    $savingsRate = $singleCurrencyMode && $income > 0 ? round(($savings / $income) * 100) : null;
    $budgetsList = collect($budgets ?? []);
    $urgentBudgets = $budgetsList->filter(fn ($budget) => $budget->isNearLimit() || $budget->isOverBudget());
    $upcomingLoanPayments = collect($upcomingLoanPayments ?? $upcomingPayments ?? []);
    $todayHighlights = [
        ['label' => __('Unread Alerts'), 'value' => (int) ($unreadAlerts ?? 0), 'url' => url('/alerts'), 'icon' => 'bell', 'tone' => 'red'],
        ['label' => __('Upcoming Payments'), 'value' => $upcomingLoanPayments->count(), 'url' => url('/loans'), 'icon' => 'calendar-check', 'tone' => 'orange'],
        ['label' => __('Budgets Need Attention'), 'value' => $urgentBudgets->count(), 'url' => url('/budgets'), 'icon' => 'chart-pie', 'tone' => 'amber'],
    ];
    $quickActions = [
        ['label' => __('Add Expense'), 'hint' => __('Daily spending'), 'url' => url('/expenses/create'), 'icon' => 'receipt', 'tone' => 'rose'],
        ['label' => __('Add Income'), 'hint' => __('Salary, gift, or transfer in'), 'url' => url('/incomes/create'), 'icon' => 'coins', 'tone' => 'emerald'],
        ['label' => __('Transfer Money'), 'hint' => __('Move money between accounts'), 'url' => url('/transfers/create'), 'icon' => 'right-left', 'tone' => 'violet'],
        ['label' => __('Pay Installment'), 'hint' => __('Record a loan payment'), 'url' => url('/loans'), 'icon' => 'hand-holding-dollar', 'tone' => 'orange'],
    ];
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-5 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ __('Daily Use') }}</h2>
            <p class="text-sm text-gray-400 mt-1">{{ __('The simplest actions for family members are here first.') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto lg:min-w-[420px]">
            @foreach($todayHighlights as $item)
            @php
                $toneClasses = [
                    'red' => 'bg-red-50 text-red-700 border-red-100',
                    'orange' => 'bg-orange-50 text-orange-700 border-orange-100',
                    'amber' => 'bg-amber-50 text-amber-700 border-amber-100',
                ][$item['tone']];
            @endphp
            <a href="{{ $item['url'] }}" class="rounded-2xl border p-4 {{ $toneClasses }}">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold">{{ $item['label'] }}</p>
                        <p class="text-2xl font-extrabold mt-1">{{ $item['value'] }}</p>
                    </div>
                    <i class="fas fa-{{ $item['icon'] }} text-lg opacity-80"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mt-5">
        @foreach($quickActions as $action)
        @php
            $actionClasses = [
                'rose' => 'bg-rose-50 border-rose-100 text-rose-700',
                'emerald' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
                'violet' => 'bg-violet-50 border-violet-100 text-violet-700',
                'orange' => 'bg-orange-50 border-orange-100 text-orange-700',
            ][$action['tone']];
        @endphp
        <a href="{{ $action['url'] }}" class="rounded-2xl border p-4 {{ $actionClasses }} hover:shadow-sm transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/80 flex items-center justify-center">
                    <i class="fas fa-{{ $action['icon'] }} text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-bold">{{ $action['label'] }}</p>
                    <p class="text-xs opacity-80 mt-0.5">{{ $action['hint'] }}</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>

<!-- Hero Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <!-- Total Balance -->
    <div class="stat-card relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-2xl p-6 text-white shadow-xl shadow-blue-500/20">
        <div class="absolute top-0 {{ is_rtl() ? 'left-0' : 'right-0' }} w-32 h-32 bg-white/5 rounded-full -mt-10 {{ is_rtl() ? '-ml-10' : '-mr-10' }}"></div>
        <div class="absolute bottom-0 {{ is_rtl() ? 'right-0' : 'left-0' }} w-20 h-20 bg-white/5 rounded-full -mb-8 {{ is_rtl() ? '-mr-8' : '-ml-8' }}"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-vault text-xl"></i>
                </div>
                <span class="text-xs font-medium bg-white/15 px-3 py-1 rounded-full backdrop-blur-sm">{{ __('All Accounts') }}</span>
            </div>
            <p class="text-blue-100 text-sm font-medium mb-1">{{ __('Total Balance') }}</p>
            @if($balanceByCurrency->count() > 1)
                <div class="space-y-1.5 mt-2">
                    @foreach($balanceByCurrency as $currency => $amount)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-blue-100/80">{{ $currency }}</span>
                            <span class="text-xl font-extrabold tracking-tight">{{ format_currency($amount, $currency) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-3xl font-extrabold tracking-tight">{{ format_currency($totalBal) }}</p>
            @endif
        </div>
    </div>

    <!-- Monthly Income -->
    <div class="stat-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100/80">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
                <i class="fas fa-arrow-trend-up text-xl text-emerald-500"></i>
            </div>
            <div class="flex items-center gap-1 text-emerald-600 text-xs font-semibold bg-emerald-50 px-2.5 py-1 rounded-full">
                <i class="fas fa-arrow-up text-[10px]"></i>
                {{ __('Income') }}
            </div>
        </div>
        <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Monthly Income') }}</p>
        @if($incomeByCurrency->count() > 1)
            <div class="space-y-1.5 mt-2">
                @foreach($incomeByCurrency as $currency => $amount)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-gray-400">{{ $currency }}</span>
                        <span class="text-lg font-extrabold text-gray-800 tracking-tight">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-2xl font-extrabold text-gray-800 tracking-tight">{{ format_currency($income) }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-2">{{ now()->format('F Y') }}</p>
    </div>

    <!-- Monthly Expenses -->
    <div class="stat-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100/80">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center">
                <i class="fas fa-arrow-trend-down text-xl text-rose-500"></i>
            </div>
            <div class="flex items-center gap-1 text-rose-600 text-xs font-semibold bg-rose-50 px-2.5 py-1 rounded-full">
                <i class="fas fa-arrow-down text-[10px]"></i>
                {{ __('Spent') }}
            </div>
        </div>
        <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Monthly Expenses') }}</p>
        @if($expensesByCurrency->count() > 1)
            <div class="space-y-1.5 mt-2">
                @foreach($expensesByCurrency as $currency => $amount)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-gray-400">{{ $currency }}</span>
                        <span class="text-lg font-extrabold text-gray-800 tracking-tight">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-2xl font-extrabold text-gray-800 tracking-tight">{{ format_currency($expenses) }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-2">{{ now()->format('F Y') }}</p>
    </div>

    <!-- Net Savings -->
    <div class="stat-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100/80">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 {{ $savings >= 0 ? 'bg-teal-50' : 'bg-red-50' }} rounded-2xl flex items-center justify-center">
                <i class="fas fa-piggy-bank text-xl {{ $savings >= 0 ? 'text-teal-500' : 'text-red-500' }}"></i>
            </div>
            @if($savingsRate !== null && $income > 0)
            <div class="flex items-center gap-1 {{ $savings >= 0 ? 'text-teal-600 bg-teal-50' : 'text-red-600 bg-red-50' }} text-xs font-semibold px-2.5 py-1 rounded-full">
                {{ $savingsRate }}%
            </div>
            @endif
        </div>
        <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Net Savings') }}</p>
        @if($netByCurrency->count() > 1)
            <div class="space-y-1.5 mt-2">
                @foreach($netByCurrency as $currency => $amount)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-gray-400">{{ $currency }}</span>
                        <span class="text-lg font-extrabold {{ $amount >= 0 ? 'text-teal-600' : 'text-red-600' }} tracking-tight">{{ format_currency($amount, $currency) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-2xl font-extrabold {{ $savings >= 0 ? 'text-teal-600' : 'text-red-600' }} tracking-tight">{{ format_currency($savings) }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-2">{{ __('Income - Expenses') }}</p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <!-- Monthly Trend -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ __('Monthly Trend') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Income vs Expenses over time') }}</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-400"></span>{{ __('Income') }}</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span>{{ __('Expenses') }}</span>
            </div>
        </div>
        <div class="h-[280px]">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800">{{ __('Expenses by Category') }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('F Y') }}</p>
        </div>
        @if(!empty($expensesByCategory) && count($expensesByCategory) > 0)
        <div class="h-[180px] flex items-center justify-center">
            <canvas id="categoryChart"></canvas>
        </div>
        <div class="mt-5 space-y-2.5 max-h-[120px] overflow-y-auto">
            @foreach($expensesByCategory as $cat)
            <div class="flex items-center justify-between group">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $cat->category->color ?? '#6B7280' }}"></span>
                    <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">{{ $cat->category->name ?? __('Uncategorized') }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-800">{{ format_currency($cat->total) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="h-[280px] flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-pie text-2xl text-gray-300"></i>
                </div>
                <p class="text-sm text-gray-400">{{ __('No expenses this month') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Middle Row -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Budget Status -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ __('Budget Status') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Track your spending limits') }}</p>
            </div>
            <a href="{{ url('/budgets') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">{{ __('View All') }}</a>
        </div>
        @forelse($budgets ?? [] as $budget)
        @php
            $pct = min($budget->percent_used, 100);
            $barColor = $budget->isOverBudget() ? 'from-red-500 to-red-400' : ($budget->isNearLimit() ? 'from-amber-500 to-amber-400' : 'from-blue-500 to-blue-400');
            $bgColor = $budget->isOverBudget() ? 'bg-red-50' : ($budget->isNearLimit() ? 'bg-amber-50' : 'bg-blue-50');
        @endphp
        <div class="mb-5 last:mb-0">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gradient-to-r {{ $barColor }}"></span>
                    <span class="text-sm font-semibold text-gray-700">{{ $budget->name }}</span>
                </div>
                <span class="text-xs font-bold {{ $budget->isOverBudget() ? 'text-red-600' : 'text-gray-500' }}">
                    {{ format_currency($budget->spent) }} / {{ format_currency($budget->amount) }}
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r {{ $barColor }} transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-1">
                <span class="text-[11px] text-gray-400">{{ round($pct) }}% {{ __('used') }}</span>
                <span class="text-[11px] {{ $budget->isOverBudget() ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                    {{ $budget->isOverBudget() ? __('Over budget!') : format_currency($budget->remaining) . ' ' . __('remaining') }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-chart-pie text-2xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400 mb-3">{{ __('No budgets set up yet') }}</p>
            <a href="{{ url('/budgets/create') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                <i class="fas fa-plus text-xs"></i>{{ __('Create Budget') }}
            </a>
        </div>
        @endforelse
    </div>

    <!-- Accounts -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ __('Accounts') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Your financial accounts') }}</p>
            </div>
            <a href="{{ url('/accounts') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">{{ __('View All') }}</a>
        </div>
        @forelse($accounts ?? [] as $account)
        <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }} group hover:bg-gray-50/50 -mx-3 px-3 rounded-xl transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm" style="background: {{ $account->color }}15; border: 1px solid {{ $account->color }}25;">
                    <i class="fas fa-{{ $account->icon ?? 'wallet' }} text-base" style="color: {{ $account->color }}"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $account->name }}</p>
                    <p class="text-[11px] text-gray-400 capitalize">{{ str_replace('_', ' ', $account->type) }}</p>
                </div>
            </div>
            <div class="text-{{ is_rtl() ? 'left' : 'right' }}">
                <p class="text-sm font-bold {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ format_currency($account->balance, $account->currency) }}</p>
                @if($account->isLowBalance())
                <p class="text-[10px] text-amber-500 font-medium"><i class="fas fa-exclamation-triangle text-[8px]"></i> {{ __('Low') }}</p>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-layer-group text-2xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400 mb-3">{{ __('No accounts yet') }}</p>
            <a href="{{ url('/accounts/create') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                <i class="fas fa-plus text-xs"></i>{{ __('Add Account') }}
            </a>
        </div>
        @endforelse
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-gray-800">{{ __('Recent Transactions') }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ __('Latest financial activity') }}</p>
        </div>
        <a href="{{ url('/expenses') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">{{ __('View All') }}</a>
    </div>
    <div class="overflow-x-auto -mx-6">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-{{ is_rtl() ? 'right' : 'left' }} text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3 px-6">{{ __('Transaction') }}</th>
                    <th class="text-{{ is_rtl() ? 'right' : 'left' }} text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">{{ __('Category') }}</th>
                    <th class="text-{{ is_rtl() ? 'right' : 'left' }} text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">{{ __('Account') }}</th>
                    <th class="text-{{ is_rtl() ? 'right' : 'left' }} text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">{{ __('Date') }}</th>
                    <th class="text-{{ is_rtl() ? 'left' : 'right' }} text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3 px-6">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $txn)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            @php
                                $iconBg = $txn->type === 'income' ? 'bg-emerald-50' : ($txn->type === 'expense' ? 'bg-rose-50' : 'bg-violet-50');
                                $iconColor = $txn->type === 'income' ? 'text-emerald-500' : ($txn->type === 'expense' ? 'text-rose-500' : 'text-violet-500');
                                $iconName = $txn->type === 'income' ? 'arrow-down' : ($txn->type === 'expense' ? 'arrow-up' : 'right-left');
                            @endphp
                            <div class="w-9 h-9 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-{{ $iconName }} text-sm {{ $iconColor }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $txn->description }}</p>
                                <p class="text-[11px] text-gray-400">{{ $txn->user->name ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5">
                        @if($txn->category)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-gray-50 px-2.5 py-1 rounded-lg">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $txn->category->color ?? '#6B7280' }}"></span>
                            {{ $txn->category->name }}
                        </span>
                        @else
                        <span class="text-xs text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="py-3.5 text-sm text-gray-500">{{ $txn->account->name ?? '-' }}</td>
                    <td class="py-3.5 text-sm text-gray-400">{{ $txn->transaction_date->format('M d') }}</td>
                    <td class="py-3.5 px-6 text-{{ is_rtl() ? 'left' : 'right' }}">
                        <span class="text-sm font-bold {{ $txn->type === 'income' ? 'text-emerald-600' : ($txn->type === 'expense' ? 'text-rose-600' : 'text-violet-600') }}">
                            {{ $txn->type === 'income' ? '+' : ($txn->type === 'expense' ? '-' : '') }}{{ format_currency($txn->amount, $txn->account?->currency) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-receipt text-2xl text-gray-300"></i>
                        </div>
                        <p class="text-sm text-gray-400 mb-1">{{ __('No transactions yet') }}</p>
                        <p class="text-xs text-gray-300">{{ __('Start by adding an expense or income') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bottom Row -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Upcoming Payments -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ __('Upcoming Payments') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Due in the next 14 days') }}</p>
            </div>
            <a href="{{ url('/loans') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">{{ __('View All') }}</a>
        </div>
        @forelse($upcomingLoanPayments ?? $upcomingPayments ?? [] as $loan)
        <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }} -mx-3 px-3 rounded-xl hover:bg-gray-50/50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-orange-50 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-orange-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $loan->name }}</p>
                    <p class="text-[11px] text-gray-400">
                        <i class="far fa-clock text-[10px]"></i>
                        {{ __('Due') }}: {{ $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : '-' }}
                    </p>
                </div>
            </div>
            <span class="text-sm font-bold text-orange-600">{{ format_currency($loan->monthly_actual_payment) }}</span>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-calendar-check text-2xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400">{{ __('No upcoming payments') }}</p>
        </div>
        @endforelse
    </div>

    <!-- Savings Goals -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ __('Savings Goals') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Track your savings progress') }}</p>
            </div>
            <a href="{{ url('/savings-goals') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors">{{ __('View All') }}</a>
        </div>
        @forelse($savingsGoals ?? [] as $goal)
        @php $goalPct = min($goal->progress, 100); @endphp
        <div class="mb-5 last:mb-0">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-teal-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bullseye text-teal-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">{{ $goal->name }}</span>
                </div>
                <span class="text-xs font-bold text-teal-600">{{ round($goalPct) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400 transition-all duration-500" style="width: {{ $goalPct }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-[11px] text-gray-400">{{ format_currency($goal->current_amount) }}</span>
                <span class="text-[11px] text-gray-400">{{ format_currency($goal->target_amount) }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-piggy-bank text-2xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400 mb-3">{{ __('No savings goals yet') }}</p>
            <a href="{{ url('/savings-goals/create') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                <i class="fas fa-plus text-xs"></i>{{ __('Create Goal') }}
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Inter', 'Noto Sans Arabic', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Monthly Trend Chart
    const trendData = @json($monthlyTrend ?? []);
    if (trendData.length > 0) {
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        const incomeGradient = ctx.createLinearGradient(0, 0, 0, 280);
        incomeGradient.addColorStop(0, 'rgba(16, 185, 129, 0.15)');
        incomeGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
        const expenseGradient = ctx.createLinearGradient(0, 0, 0, 280);
        expenseGradient.addColorStop(0, 'rgba(244, 63, 94, 0.15)');
        expenseGradient.addColorStop(1, 'rgba(244, 63, 94, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [
                    {
                        label: '{{ __("Income") }}',
                        data: trendData.map(d => d.income),
                        borderColor: '#10b981',
                        backgroundColor: incomeGradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: '{{ __("Expenses") }}',
                        data: trendData.map(d => d.expenses),
                        borderColor: '#f43f5e',
                        backgroundColor: expenseGradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f43f5e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                        boxPadding: 6,
                        callbacks: { label: ctx => ctx.dataset.label + ': {{ currency_symbol() }}' + ctx.parsed.y.toLocaleString() }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: 500 } } },
                    y: {
                        grid: { color: 'rgba(148,163,184,0.08)', drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 11 }, callback: v => '{{ currency_symbol() }}' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) }
                    }
                }
            }
        });
    }

    // Category Doughnut
    const catData = @json($expensesByCategory ?? []);
    if (catData.length > 0 && document.getElementById('categoryChart')) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catData.map(c => c.category ? c.category.name : '{{ __("Other") }}'),
                datasets: [{
                    data: catData.map(c => c.total),
                    backgroundColor: catData.map((c, i) => c.category?.color || ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'][i % 7]),
                    borderWidth: 0,
                    spacing: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 12,
                        callbacks: { label: ctx => ctx.label + ': {{ currency_symbol() }}' + ctx.parsed.toLocaleString() }
                    }
                }
            }
        });
    }
});
</script>
@endpush
