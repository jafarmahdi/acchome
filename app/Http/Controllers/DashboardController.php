<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Loan;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $familyId = auth()->user()->family_id;
        $familyCurrency = auth()->user()->family->currency ?? 'IQD';

        $balanceAccounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->where('include_in_total', true)
            ->get(['balance', 'currency']);

        $totalBalanceByCurrency = $this->sumByCurrency($balanceAccounts, 'balance', $familyCurrency);
        $totalBalance = (float) $totalBalanceByCurrency->get($familyCurrency, 0);

        $monthlyTransactions = Transaction::where('family_id', $familyId)
            ->thisMonth()
            ->with(['account:id,currency'])
            ->get();

        $monthlyIncomeByCurrency = $this->sumByCurrency(
            $monthlyTransactions->where('type', 'income'),
            'amount',
            $familyCurrency
        );
        $monthlyExpensesByCurrency = $this->sumByCurrency(
            $monthlyTransactions->where('type', 'expense'),
            'amount',
            $familyCurrency
        );
        $monthlyNetByCurrency = $monthlyIncomeByCurrency
            ->keys()
            ->merge($monthlyExpensesByCurrency->keys())
            ->unique()
            ->mapWithKeys(fn ($currency) => [
                $currency => round(
                    (float) $monthlyIncomeByCurrency->get($currency, 0)
                    - (float) $monthlyExpensesByCurrency->get($currency, 0),
                    2
                ),
            ]);

        $monthlyIncome = (float) $monthlyIncomeByCurrency->get($familyCurrency, 0);
        $monthlyExpenses = (float) $monthlyExpensesByCurrency->get($familyCurrency, 0);

        $expensesByCategory = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->thisMonth()
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $recentTransactions = Transaction::where('family_id', $familyId)
            ->with(['account', 'category', 'user'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $budgets = Budget::where('family_id', $familyId)
            ->where('is_active', true)
            ->with('category')
            ->get();

        $accounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->orderByDesc('balance')
            ->get();

        $upcomingLoanPayments = Loan::where('family_id', $familyId)
            ->where('status', 'active')
            ->with('account')
            ->get()
            ->filter(fn ($loan) => $loan->isDueSoon(14))
            ->sortBy('next_due_date');

        $savingsGoals = SavingsGoal::where('family_id', $familyId)
            ->whereIn('status', ['active', 'in_progress'])
            ->orderBy('target_date')
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $income = Transaction::where('family_id', $familyId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');

            $exp = Transaction::where('family_id', $familyId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');

            $monthlyTrend->push([
                'month' => $date->format('M'),
                'income' => round($income, 2),
                'expenses' => round($exp, 2),
            ]);
        }

        return view('dashboard.index', compact(
            'totalBalance',
            'totalBalanceByCurrency',
            'monthlyIncome',
            'monthlyIncomeByCurrency',
            'monthlyExpenses',
            'monthlyExpensesByCurrency',
            'monthlyNetByCurrency',
            'expensesByCategory',
            'recentTransactions',
            'budgets',
            'accounts',
            'upcomingLoanPayments',
            'savingsGoals',
            'monthlyTrend'
        ));
    }

    protected function sumByCurrency(iterable $items, string $amountField, string $defaultCurrency): Collection
    {
        return collect($items)
            ->groupBy(function ($item) use ($defaultCurrency) {
                return $item->currency
                    ?? $item->account?->currency
                    ?? $defaultCurrency;
            })
            ->map(fn ($group) => round((float) $group->sum($amountField), 2))
            ->sortKeys();
    }
}
