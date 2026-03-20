<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetOverspentNotification extends Notification
{
    use Queueable;

    public function __construct(public Budget $budget) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Budget Alert: :name', ['name' => $this->budget->name]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your budget ":name" has reached :percent% of its limit.', [
                'name' => $this->budget->name,
                'percent' => $this->budget->percent_used,
            ]))
            ->line(__('Spent: :spent / :total', [
                'spent' => format_currency($this->budget->spent),
                'total' => format_currency($this->budget->amount),
            ]))
            ->action(__('View Budget'), url('/budgets'))
            ->line(__('Consider reducing spending in this category.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'name' => $this->budget->name,
            'spent' => $this->budget->spent,
            'amount' => $this->budget->amount,
            'percent' => $this->budget->percent_used,
        ];
    }
}
