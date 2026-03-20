<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();
        $transactions = $this->transactionsForRange($familyId, $from, $to);
        $expenses = $transactions->where('type', 'expense')->values();
        $income = $transactions->where('type', 'income')->values();

        $monthlyRows = $this->buildMonthlyRows($transactions, $from, $to);

        $reportData = [
            'total_income_by_currency' => $this->totalsByCurrency($income),
            'total_expenses_by_currency' => $this->totalsByCurrency($expenses),
            'net_by_currency' => $this->netByCurrency(
                $this->totalsByCurrency($income),
                $this->totalsByCurrency($expenses)
            ),
            'transaction_count' => $transactions->count(),
            'top_expenses' => $expenses->sortByDesc('amount')->take(10)->values(),
            'category_breakdown' => $this->categoryBreakdownRows($expenses),
            'monthly_rows' => $monthlyRows,
            'monthly_chart' => $this->buildMonthlyChart($monthlyRows),
        ];

        return view('reports.index', compact('reportData', 'from', 'to'));
    }

    public function incomeVsExpense(Request $request)
    {
        $familyId = auth()->user()->family_id;
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfYear();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();
        $transactions = $this->transactionsForRange($familyId, $from, $to);
        $incomeTransactions = $transactions->where('type', 'income')->values();
        $expenseTransactions = $transactions->where('type', 'expense')->values();
        $monthlyRows = $this->buildMonthlyRows($transactions, $from, $to);

        $incomeByCurrency = $this->totalsByCurrency($incomeTransactions);
        $expensesByCurrency = $this->totalsByCurrency($expenseTransactions);
        $netByCurrency = $this->netByCurrency($incomeByCurrency, $expensesByCurrency);

        return view('reports.income-vs-expense', compact(
            'incomeByCurrency',
            'expensesByCurrency',
            'netByCurrency',
            'monthlyRows',
            'from',
            'to'
        ));
    }

    public function categoryBreakdown(Request $request)
    {
        $familyId = auth()->user()->family_id;
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();
        $type = $request->type ?? 'expense';
        $transactions = $this->transactionsForRange($familyId, $from, $to, $type);
        $breakdown = $this->categoryBreakdownRows($transactions);
        $totalByCurrency = $this->totalsByCurrency($transactions);

        return view('reports.category-breakdown', compact('breakdown', 'totalByCurrency', 'from', 'to', 'type'));
    }

    public function monthlyTrend(Request $request)
    {
        $familyId = auth()->user()->family_id;
        $months = $request->months ?? 12;
        $startDate = now()->subMonths($months - 1)->startOfMonth();
        $endDate = now()->endOfMonth();
        $transactions = $this->transactionsForRange($familyId, $startDate, $endDate);
        $trends = $this->buildMonthlyRows($transactions, $startDate, $endDate);
        $chart = $this->buildMonthlyChart($trends);

        return view('reports.monthly-trend', compact('trends', 'chart', 'months'));
    }

    public function memberSpending(Request $request)
    {
        $familyId = auth()->user()->family_id;
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();
        $transactions = $this->transactionsForRange($familyId, $from, $to);
        $transactionsByUser = $transactions->groupBy('user_id');

        $members = User::where('family_id', $familyId)->where('is_active', true)->get()
            ->map(function ($member) use ($transactionsByUser) {
                $memberTransactions = $transactionsByUser->get($member->id, collect());
                $income = $memberTransactions->where('type', 'income')->values();
                $expenses = $memberTransactions->where('type', 'expense')->values();

                return [
                    'member' => $member,
                    'income_by_currency' => $this->totalsByCurrency($income),
                    'expenses_by_currency' => $this->totalsByCurrency($expenses),
                    'net_by_currency' => $this->netByCurrency(
                        $this->totalsByCurrency($income),
                        $this->totalsByCurrency($expenses)
                    ),
                    'top_categories' => array_slice($this->categoryBreakdownRows($expenses), 0, 5),
                    'transaction_count' => $memberTransactions->count(),
                ];
            });

        return view('reports.member-spending', compact('members', 'from', 'to'));
    }

    public function accountSummary()
    {
        $familyId = auth()->user()->family_id;
        $monthTransactions = Transaction::where('family_id', $familyId)
            ->thisMonth()
            ->with(['account', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('account_id');

        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->get()
            ->map(function ($account) use ($monthTransactions) {
                $transactions = $monthTransactions->get($account->id, collect());
                $monthlyIncome = (float) $transactions->where('type', 'income')->sum('amount');
                $monthlyExpenses = (float) $transactions->where('type', 'expense')->sum('amount');
                $recentTransactions = $transactions->take(5)->values();

                return [
                    'account' => $account,
                    'monthly_income' => $monthlyIncome,
                    'monthly_expenses' => $monthlyExpenses,
                    'net' => $monthlyIncome - $monthlyExpenses,
                    'recent_transactions' => $recentTransactions,
                    'transaction_count' => $transactions->count(),
                ];
            });

        return view('reports.account-summary', compact('accounts'));
    }

    protected function transactionsForRange(int $familyId, Carbon $from, Carbon $to, ?string $type = null): Collection
    {
        return Transaction::where('family_id', $familyId)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->dateRange($from, $to)
            ->with(['account', 'category', 'user'])
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();
    }

    protected function totalsByCurrency(Collection $transactions): array
    {
        return $transactions
            ->groupBy(fn ($transaction) => $this->resolveCurrency($transaction))
            ->map(fn ($items) => round((float) $items->sum('amount'), 2))
            ->sortKeys()
            ->all();
    }

    protected function netByCurrency(array $incomeByCurrency, array $expensesByCurrency): array
    {
        $currencies = collect(array_keys($incomeByCurrency))
            ->merge(array_keys($expensesByCurrency))
            ->unique()
            ->sort()
            ->values();

        $net = [];

        foreach ($currencies as $currency) {
            $net[$currency] = round(
                (float) ($incomeByCurrency[$currency] ?? 0) - (float) ($expensesByCurrency[$currency] ?? 0),
                2
            );
        }

        return $net;
    }

    protected function buildMonthlyRows(Collection $transactions, Carbon $from, Carbon $to): array
    {
        $rows = [];
        $cursor = $from->copy()->startOfMonth();

        while ($cursor->lte($to)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();
            $monthTransactions = $transactions->filter(function ($transaction) use ($monthStart, $monthEnd) {
                return $transaction->transaction_date
                    && $transaction->transaction_date->between($monthStart, $monthEnd);
            })->values();

            $incomeByCurrency = $this->totalsByCurrency($monthTransactions->where('type', 'income')->values());
            $expenseByCurrency = $this->totalsByCurrency($monthTransactions->where('type', 'expense')->values());

            $rows[] = [
                'month' => $cursor->format('M Y'),
                'income_by_currency' => $incomeByCurrency,
                'expense_by_currency' => $expenseByCurrency,
                'net_by_currency' => $this->netByCurrency($incomeByCurrency, $expenseByCurrency),
                'transaction_count' => $monthTransactions->count(),
            ];

            $cursor->addMonth();
        }

        return $rows;
    }

    protected function buildMonthlyChart(array $rows): array
    {
        $currencies = collect($rows)
            ->flatMap(function ($row) {
                return array_unique(array_merge(
                    array_keys($row['income_by_currency']),
                    array_keys($row['expense_by_currency'])
                ));
            })
            ->unique()
            ->sort()
            ->values();

        $palette = [
            'IQD' => ['income' => '#0f766e', 'expense' => '#b91c1c'],
            'USD' => ['income' => '#2563eb', 'expense' => '#7c3aed'],
            'EUR' => ['income' => '#0891b2', 'expense' => '#dc2626'],
        ];

        $datasets = [];

        foreach ($currencies as $currency) {
            $colors = $palette[$currency] ?? ['income' => '#16a34a', 'expense' => '#ef4444'];

            $datasets[] = [
                'label' => __('Income') . ' (' . $currency . ')',
                'data' => array_map(fn ($row) => (float) ($row['income_by_currency'][$currency] ?? 0), $rows),
                'borderColor' => $colors['income'],
                'backgroundColor' => $this->hexToRgba($colors['income'], 0.14),
                'fill' => true,
                'tension' => 0.3,
            ];

            $datasets[] = [
                'label' => __('Expenses') . ' (' . $currency . ')',
                'data' => array_map(fn ($row) => (float) ($row['expense_by_currency'][$currency] ?? 0), $rows),
                'borderColor' => $colors['expense'],
                'backgroundColor' => $this->hexToRgba($colors['expense'], 0.10),
                'fill' => true,
                'tension' => 0.3,
            ];
        }

        return [
            'labels' => array_map(fn ($row) => $row['month'], $rows),
            'datasets' => $datasets,
        ];
    }

    protected function categoryBreakdownRows(Collection $transactions): array
    {
        return $transactions
            ->groupBy(function ($transaction) {
                return ($transaction->category_id ?: 'uncategorized') . '|' . $this->resolveCurrency($transaction);
            })
            ->map(function (Collection $group) {
                $first = $group->first();
                $currency = $this->resolveCurrency($first);
                $name = $first->category?->display_name ?? __('Uncategorized');

                return [
                    'name' => $name,
                    'currency' => $currency,
                    'label' => $name . ' (' . $currency . ')',
                    'total' => round((float) $group->sum('amount'), 2),
                    'count' => $group->count(),
                    'color' => $first->category?->color ?? '#6B7280',
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    protected function resolveCurrency($transaction): string
    {
        return strtoupper($transaction->account?->currency ?? (auth()->user()->family->currency ?? 'IQD'));
    }

    protected function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            return 'rgba(107,114,128,' . $alpha . ')';
        }

        return sprintf(
            'rgba(%d,%d,%d,%.2f)',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            $alpha
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $familyId = auth()->user()->family_id;
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();
        $type = $request->type;

        $transactions = Transaction::where('family_id', $familyId)
            ->when($type, fn ($q, $v) => $q->where('type', $v))
            ->dateRange($from, $to)
            ->with(['account', 'category', 'user'])
            ->orderBy('transaction_date')->get();

        $filename = 'transactions_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Description', 'Amount', 'Category', 'Account', 'User', 'Payment Method', 'Reference', 'Notes']);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date->format('Y-m-d'), ucfirst($t->type), $t->description, $t->amount,
                    $t->category?->name ?? '', $t->account?->name ?? '', $t->user?->name ?? '',
                    $t->payment_method ?? '', $t->reference_number ?? '', $t->notes ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
