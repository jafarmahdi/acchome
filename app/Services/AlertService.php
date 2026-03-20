<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Alert;
use App\Models\Budget;
use App\Models\Loan;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlertService
{
    public function refreshTimedAlertsForFamily(int $familyId): void
    {
        $family = \App\Models\Family::with('settings')->find($familyId);
        if (!$family) {
            return;
        }

        $currentMarker = now()->format('Y-m-d-H');
        $lastMarker = $family->getSetting('alerts_last_refresh_marker');

        if ($lastMarker === $currentMarker) {
            return;
        }

        Budget::where('family_id', $familyId)
            ->where('is_active', true)
            ->get()
            ->each(function (Budget $budget) {
                $budget->recalculateSpent();
                $this->checkBudgetAlerts($budget);
            });

        Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->where('low_balance_threshold', '>', 0)
            ->get()
            ->each(fn (Account $account) => $this->checkLowBalance($account));

        Loan::where('family_id', $familyId)
            ->where('status', 'active')
            ->whereNotNull('due_day')
            ->get()
            ->each(fn (Loan $loan) => $this->checkLoanDue($loan));

        RecurringTransaction::where('family_id', $familyId)
            ->where('is_active', true)
            ->whereDate('next_due_date', '<=', now()->addDays(3))
            ->get()
            ->each(fn (RecurringTransaction $transaction) => $this->checkRecurringDue($transaction));

        $this->checkUnusualSpending($familyId);

        $family->setSetting('alerts_last_refresh_marker', $currentMarker, 'alerts');
    }

    public function checkBudgetAlerts(Budget $budget): void
    {
        $categoryIds = $budget->selected_category_ids;

        $spent = Transaction::where('family_id', $budget->family_id)
            ->where('type', 'expense')
            ->when(!empty($categoryIds), fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->where('transaction_date', '>=', $budget->start_date)
            ->where('transaction_date', '<=', $budget->end_date)
            ->sum('amount');

        $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
        $budgetName = $budget->name;
        $actionUrl = url('/budgets');

        if ($percentage >= 100) {
            $this->createAlert([
                'family_id' => $budget->family_id,
                'type' => 'budget_exceeded',
                'title' => __('Budget Exceeded'),
                'message' => __(':name budget is over the limit. Spent :spent of :amount (:percent%).', [
                    'name' => $budgetName,
                    'spent' => format_currency((float) $spent),
                    'amount' => format_currency((float) $budget->amount),
                    'percent' => number_format($percentage, 0),
                ]),
                'severity' => 'danger',
                'icon' => 'triangle-exclamation',
                'action_url' => $actionUrl,
                'alertable_type' => Budget::class,
                'alertable_id' => $budget->id,
            ]);
        } elseif ($percentage >= 95) {
            $this->createAlert([
                'family_id' => $budget->family_id,
                'type' => 'budget_critical',
                'title' => __('Budget Critical'),
                'message' => __(':name budget reached :percent%. Only :remaining left.', [
                    'name' => $budgetName,
                    'percent' => number_format($percentage, 0),
                    'remaining' => format_currency(max(0, (float) $budget->amount - (float) $spent)),
                ]),
                'severity' => 'danger',
                'icon' => 'triangle-exclamation',
                'action_url' => $actionUrl,
                'alertable_type' => Budget::class,
                'alertable_id' => $budget->id,
            ]);
        } elseif ($percentage >= max(1, (float) $budget->alert_threshold)) {
            $this->createAlert([
                'family_id' => $budget->family_id,
                'type' => 'budget_warning',
                'title' => __('Budget Warning'),
                'message' => __(':name budget is at :percent% usage.', [
                    'name' => $budgetName,
                    'percent' => number_format($percentage, 0),
                ]),
                'severity' => 'warning',
                'icon' => 'gauge-high',
                'action_url' => $actionUrl,
                'alertable_type' => Budget::class,
                'alertable_id' => $budget->id,
            ]);
        }
    }

    public function checkLowBalance(Account $account): void
    {
        $threshold = $account->low_balance_threshold ?? 100;

        if ($account->balance < $threshold) {
            $this->createAlert([
                'family_id' => $account->family_id,
                'type' => 'low_balance',
                'title' => __('Low Balance Alert'),
                'message' => __(':name account balance is :balance, below threshold of :threshold.', [
                    'name' => $account->name,
                    'balance' => format_currency((float) $account->balance, $account->currency),
                    'threshold' => format_currency((float) $threshold, $account->currency),
                ]),
                'severity' => 'warning',
                'icon' => 'wallet',
                'action_url' => url('/accounts'),
                'alertable_type' => Account::class,
                'alertable_id' => $account->id,
            ]);
        }
    }

    public function checkLoanDue(Loan $loan): void
    {
        if ($loan->status !== 'active') {
            return;
        }

        $nextDueDate = Carbon::parse($loan->next_due_date);
        $daysUntilDue = (int) now()->startOfDay()->diffInDays($nextDueDate->startOfDay(), false);
        $amount = format_currency((float) $loan->monthly_actual_payment);

        if ($daysUntilDue < 0) {
            $this->createAlert([
                'family_id' => $loan->family_id,
                'type' => 'loan_overdue',
                'title' => __('Loan Overdue'),
                'message' => __('Payment for :name is overdue by :days day(s). Amount due: :amount.', [
                    'name' => $loan->name,
                    'days' => abs($daysUntilDue),
                    'amount' => $amount,
                ]),
                'severity' => 'danger',
                'icon' => 'calendar-xmark',
                'action_url' => url('/loans'),
                'alertable_type' => Loan::class,
                'alertable_id' => $loan->id,
            ]);
        } elseif ($daysUntilDue === 0) {
            $this->createAlert([
                'family_id' => $loan->family_id,
                'type' => 'loan_due_today',
                'title' => __('Installment Due Today'),
                'message' => __('Installment for :name is due today. Amount: :amount.', [
                    'name' => $loan->name,
                    'amount' => $amount,
                ]),
                'severity' => 'danger',
                'icon' => 'calendar-day',
                'action_url' => url('/loans'),
                'alertable_type' => Loan::class,
                'alertable_id' => $loan->id,
            ]);
        } elseif ($daysUntilDue <= 7) {
            $this->createAlert([
                'family_id' => $loan->family_id,
                'type' => 'loan_due_soon',
                'title' => __('Loan Due Soon'),
                'message' => __('Payment for :name is due in :days day(s). Amount: :amount.', [
                    'name' => $loan->name,
                    'days' => $daysUntilDue,
                    'amount' => $amount,
                ]),
                'severity' => $daysUntilDue <= 3 ? 'warning' : 'info',
                'icon' => 'bell',
                'action_url' => url('/loans'),
                'alertable_type' => Loan::class,
                'alertable_id' => $loan->id,
            ]);
        }
    }

    public function checkRecurringDue(RecurringTransaction $transaction): void
    {
        $daysUntilDue = now()->startOfDay()->diffInDays(Carbon::parse($transaction->next_due_date)->startOfDay(), false);

        if ($daysUntilDue > 3) {
            return;
        }

        $this->createAlert([
            'family_id' => $transaction->family_id,
            'type' => 'recurring_due',
            'title' => $daysUntilDue <= 0 ? __('Recurring payment due today') : __('Recurring payment due soon'),
            'message' => __(':name is scheduled for :date with amount :amount.', [
                'name' => $transaction->description,
                'date' => Carbon::parse($transaction->next_due_date)->format('Y-m-d'),
                'amount' => format_currency((float) $transaction->amount, $transaction->account?->currency),
            ]),
            'severity' => $daysUntilDue <= 0 ? 'warning' : 'info',
            'icon' => 'repeat',
            'action_url' => url('/recurring-transactions'),
            'alertable_type' => RecurringTransaction::class,
            'alertable_id' => $transaction->id,
        ]);
    }

    public function checkUnusualSpending(int $familyId): void
    {
        $currentMonth = now()->startOfMonth();
        $lookbackMonths = 3;

        $currentSpending = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $currentMonth)
            ->sum('amount');

        $avgSpending = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $currentMonth->copy()->subMonths($lookbackMonths))
            ->where('transaction_date', '<', $currentMonth)
            ->sum('amount') / max($lookbackMonths, 1);

        if ($avgSpending > 0 && $currentSpending > ($avgSpending * 1.5)) {
            $increase = (($currentSpending - $avgSpending) / $avgSpending) * 100;

            $this->createAlert([
                'family_id' => $familyId,
                'type' => 'unusual_spending',
                'title' => __('Unusual Spending Detected'),
                'message' => __('Your spending this month (:current) is :percent% higher than your :months-month average of :average.', [
                    'current' => format_currency((float) $currentSpending),
                    'percent' => number_format($increase, 0),
                    'months' => $lookbackMonths,
                    'average' => format_currency((float) $avgSpending),
                ]),
                'severity' => 'warning',
                'icon' => 'chart-line',
                'action_url' => url('/reports'),
            ]);
        }
    }

    public function createTransactionAlert(Transaction $transaction): void
    {
        $transaction->loadMissing(['account', 'category', 'user']);

        $amount = format_currency((float) $transaction->amount, $transaction->account?->currency);
        $baseUrl = match ($transaction->type) {
            'income' => url('/incomes'),
            'expense' => url('/expenses'),
            'transfer' => url('/transfers'),
            default => url('/dashboard'),
        };

        if ($transaction->type === 'income') {
            $this->createAlert([
                'family_id' => $transaction->family_id,
                'type' => 'income_received',
                'title' => __('Income Received'),
                'message' => __('Income ":description" was added for :amount to :account.', [
                    'description' => $transaction->description,
                    'amount' => $amount,
                    'account' => $transaction->account?->name ?? __('Account'),
                ]),
                'severity' => 'success',
                'icon' => 'circle-arrow-down',
                'action_url' => $baseUrl,
                'alertable_type' => Transaction::class,
                'alertable_id' => $transaction->id,
            ]);
            return;
        }

        if ($transaction->type === 'transfer') {
            $this->createAlert([
                'family_id' => $transaction->family_id,
                'type' => 'transfer_activity',
                'title' => __('Transfer Completed'),
                'message' => __('Transferred :amount from :from to :to.', [
                    'amount' => $amount,
                    'from' => $transaction->account?->name ?? __('Account'),
                    'to' => $transaction->transferToAccount?->name ?? __('Account'),
                ]),
                'severity' => 'info',
                'icon' => 'right-left',
                'action_url' => $baseUrl,
                'alertable_type' => Transaction::class,
                'alertable_id' => $transaction->id,
            ]);
            return;
        }

        $this->createAlert([
            'family_id' => $transaction->family_id,
            'type' => 'transaction_activity',
            'title' => __('Expense Recorded'),
            'message' => __('Expense ":description" was recorded for :amount from :account.', [
                'description' => $transaction->description,
                'amount' => $amount,
                'account' => $transaction->account?->name ?? __('Account'),
            ]),
            'severity' => 'info',
            'icon' => 'receipt',
            'action_url' => $baseUrl,
            'alertable_type' => Transaction::class,
            'alertable_id' => $transaction->id,
        ]);

        $threshold = $this->largeTransactionThreshold($transaction->account?->currency);
        if ((float) $transaction->amount >= $threshold) {
            $this->createAlert([
                'family_id' => $transaction->family_id,
                'type' => 'large_expense',
                'title' => __('Large Expense Alert'),
                'message' => __('Large expense detected: :description for :amount.', [
                    'description' => $transaction->description,
                    'amount' => $amount,
                ]),
                'severity' => 'warning',
                'icon' => 'fire',
                'action_url' => $baseUrl,
                'alertable_type' => Transaction::class,
                'alertable_id' => $transaction->id,
            ]);
        }
    }

    public function createLoanPaymentAlert(Loan $loan, float $amount): void
    {
        $this->createAlert([
            'family_id' => $loan->family_id,
            'type' => 'loan_payment_recorded',
            'title' => __('Installment Recorded'),
            'message' => __('A payment of :amount was recorded for :name.', [
                'amount' => format_currency($amount),
                'name' => $loan->name,
            ]),
            'severity' => 'success',
            'icon' => 'circle-check',
            'action_url' => url('/loans'),
            'alertable_type' => Loan::class,
            'alertable_id' => $loan->id,
        ]);
    }

    protected function largeTransactionThreshold(?string $currency): float
    {
        $currency = strtoupper((string) $currency ?: 'IQD');

        return match ($currency) {
            'USD' => 200,
            'EUR' => 200,
            default => 250000,
        };
    }

    public function createAlert(array $data): Alert
    {
        // Prevent duplicate unread alerts of the same type for the same resource
        $query = Alert::where('family_id', $data['family_id'])
            ->where('type', $data['type'])
            ->where('is_read', false);

        if (isset($data['alertable_type'], $data['alertable_id'])) {
            $query->where('alertable_type', $data['alertable_type'])
                ->where('alertable_id', $data['alertable_id']);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->update([
                'title' => $data['title'] ?? $existing->title,
                'message' => $data['message'],
                'severity' => $data['severity'] ?? $existing->severity,
                'updated_at' => now(),
            ]);

            return $existing;
        }

        return Alert::create(array_merge([
            'is_read' => false,
            'icon' => 'bell',
        ], $data));
    }
}
