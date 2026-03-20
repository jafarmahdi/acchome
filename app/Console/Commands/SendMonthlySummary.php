<?php

namespace App\Console\Commands;

use App\Mail\MonthlySummaryMail;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlySummary extends Command
{
    protected $signature = 'finance:monthly-summary';
    protected $description = 'Send monthly financial summary email to all families';

    public function handle(): void
    {
        $month = now()->subMonth();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        Family::with(['users', 'accounts'])->each(function (Family $family) use ($startOfMonth, $endOfMonth, $month) {
            $income = Transaction::where('family_id', $family->id)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $expenses = Transaction::where('family_id', $family->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $categoryBreakdown = Transaction::where('family_id', $family->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->whereNotNull('category_id')
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->with('category')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn($t) => [
                    'name' => $t->category->name ?? '-',
                    'total' => $t->total,
                    'percent' => $expenses > 0 ? round(($t->total / $expenses) * 100, 1) : 0,
                    'color' => $t->category->color ?? '#6B7280',
                ])->toArray();

            $accounts = $family->accounts->where('is_active', true)->map(fn($a) => [
                'name' => $a->name,
                'balance' => $a->balance,
            ])->toArray();

            $summaryData = [
                'month' => $month->format('F Y'),
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $family->totalBalance(),
                'category_breakdown' => $categoryBreakdown,
                'accounts' => $accounts,
            ];

            foreach ($family->users->where('email_notifications', true) as $user) {
                Mail::to($user->email)->queue(new MonthlySummaryMail($family, $summaryData));
            }
        });

        $this->info('Monthly summaries sent successfully.');
    }
}
