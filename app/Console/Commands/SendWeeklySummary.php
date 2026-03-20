<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummaryMail;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklySummary extends Command
{
    protected $signature = 'finance:weekly-summary';
    protected $description = 'Send weekly financial summary email to all families';

    public function handle(): void
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        Family::with('users')->each(function (Family $family) use ($startOfWeek, $endOfWeek) {
            $income = Transaction::where('family_id', $family->id)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
                ->sum('amount');

            $expenses = Transaction::where('family_id', $family->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
                ->sum('amount');

            $topExpenses = Transaction::where('family_id', $family->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
                ->orderByDesc('amount')
                ->limit(5)
                ->get()
                ->map(fn($t) => [
                    'description' => $t->description,
                    'category' => $t->category->name ?? '-',
                    'amount' => $t->amount,
                ])->toArray();

            $summaryData = [
                'period' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d, Y'),
                'income' => $income,
                'expenses' => $expenses,
                'top_expenses' => $topExpenses,
                'budget_alerts' => [],
            ];

            foreach ($family->users->where('email_notifications', true) as $user) {
                Mail::to($user->email)->queue(new WeeklySummaryMail($family, $summaryData));
            }
        });

        $this->info('Weekly summaries sent successfully.');
    }
}
