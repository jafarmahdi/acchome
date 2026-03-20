<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanDueNotification extends Notification
{
    use Queueable;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Loan Payment Due: :name', ['name' => $this->loan->name]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your loan ":name" has a payment due on :date.', [
                'name' => $this->loan->name,
                'date' => $this->loan->next_due_date?->format('M d, Y'),
            ]))
            ->line(__('Monthly payment: :amount', ['amount' => format_currency($this->loan->monthly_actual_payment)]))
            ->line(__('Remaining: :remaining', ['remaining' => format_currency($this->loan->display_remaining_amount)]))
            ->action(__('View Loan'), url('/loans'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'name' => $this->loan->name,
            'amount' => $this->loan->monthly_actual_payment,
            'due_date' => $this->loan->next_due_date?->toDateString(),
        ];
    }
}
