<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Loan;
use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    protected $signature = 'finance:check-alerts';
    protected $description = 'Check and generate alerts for budgets, low balances, and loan due dates';

    public function handle(AlertService $alertService): void
    {
        // Check over-budget alerts
        Budget::where('is_active', true)->each(function (Budget $budget) use ($alertService) {
            $budget->recalculateSpent();
            $alertService->checkBudgetAlerts($budget);
        });

        // Check low balance alerts
        Account::where('is_active', true)
            ->where('low_balance_threshold', '>', 0)
            ->each(function (Account $account) use ($alertService) {
                $alertService->checkLowBalance($account);
            });

        // Check loan due date alerts
        Loan::where('status', 'active')
            ->whereNotNull('due_day')
            ->each(function (Loan $loan) use ($alertService) {
                $alertService->checkLoanDue($loan);
            });

        $this->info('Alert check completed.');
    }
}
