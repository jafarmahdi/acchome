<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE alerts
            MODIFY type ENUM(
                'over_budget',
                'low_balance',
                'loan_due',
                'goal_milestone',
                'unusual_spending',
                'recurring_due',
                'bill_reminder',
                'custom',
                'budget_warning',
                'budget_critical',
                'budget_exceeded',
                'loan_due_soon',
                'loan_due_today',
                'loan_overdue',
                'transaction_activity',
                'large_expense',
                'income_received',
                'transfer_activity',
                'loan_payment_recorded'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE alerts
            MODIFY type ENUM(
                'over_budget',
                'low_balance',
                'loan_due',
                'goal_milestone',
                'unusual_spending',
                'recurring_due',
                'bill_reminder',
                'custom'
            ) NOT NULL
        ");
    }
};
