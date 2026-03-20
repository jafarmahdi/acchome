<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Budget;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSummary(int $familyId): array
    {
        $totalBalance = Account::where('family_id', $familyId)->sum('balance');

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyIncome = Transaction::where('family_id', $familyId)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpenses = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return [
            'total_balance' => round($totalBalance, 2),
            'monthly_income' => round($monthlyIncome, 2),
            'monthly_expenses' => round($monthlyExpenses, 2),
            'monthly_savings' => round($monthlyIncome - $monthlyExpenses, 2),
        ];
    }

    public function getExpensesByCategory(int $familyId): Collection
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return Transaction::where('transactions.family_id', $familyId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$startOfMonth, $endOfMonth])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                'categories.icon',
                'categories.color',
                DB::raw('SUM(transactions.amount) as total'),
            )
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->orderByDesc('total')
            ->get();
    }

    public function getMonthlyTrend(int $familyId, int $months = 6): Collection
    {
        $startDate = now()->subMonths($months - 1)->startOfMonth();

        return Transaction::where('family_id', $familyId)
            ->where('date', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses"),
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->savings = round($item->income - $item->expenses, 2);
                $item->month_label = Carbon::createFromFormat('Y-m', $item->month)->format('M Y');

                return $item;
            });
    }

    public function getRecentTransactions(int $familyId, int $limit = 10): Collection
    {
        return Transaction::where('family_id', $familyId)
            ->with(['category', 'account', 'user'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getBudgetOverview(int $familyId): Collection
    {
        $now = now();

        return Budget::where('family_id', $familyId)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->with(['category', 'categories'])
            ->get()
            ->map(function (Budget $budget) {
                $categoryIds = $budget->selected_category_ids;

                $spent = Transaction::where('family_id', $budget->family_id)
                    ->where('type', 'expense')
                    ->when(!empty($categoryIds), fn ($q) => $q->whereIn('category_id', $categoryIds))
                    ->whereBetween('date', [$budget->start_date, $budget->end_date])
                    ->sum('amount');

                $budget->spent = round($spent, 2);
                $budget->remaining = round(max($budget->amount - $spent, 0), 2);
                $budget->percentage = $budget->amount > 0
                    ? round(min(($spent / $budget->amount) * 100, 100), 1)
                    : 0;

                return $budget;
            });
    }

    public function getUpcomingPayments(int $familyId): Collection
    {
        $upcoming = collect();

        // Recurring transactions due in the next 30 days
        $recurringPayments = RecurringTransaction::where('family_id', $familyId)
            ->where('is_active', true)
            ->where('next_due_date', '<=', now()->addDays(30))
            ->where('next_due_date', '>=', now())
            ->orderBy('next_due_date')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->description,
                'amount' => $item->amount,
                'type' => $item->type,
                'due_date' => $item->next_due_date,
                'source' => 'recurring',
                'days_until_due' => now()->startOfDay()->diffInDays(Carbon::parse($item->next_due_date)->startOfDay(), false),
            ]);

        return $upcoming->merge($recurringPayments)->sortBy('due_date')->values();
    }
}
