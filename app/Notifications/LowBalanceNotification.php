<?php

namespace App\Notifications;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification
{
    use Queueable;

    public function __construct(public Account $account) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Low Balance Alert: :name', ['name' => $this->account->name]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your account ":name" has a low balance.', ['name' => $this->account->name]))
            ->line(__('Current balance: :balance', ['balance' => format_currency($this->account->balance)]))
            ->line(__('Threshold: :threshold', ['threshold' => format_currency($this->account->low_balance_threshold)]))
            ->action(__('View Account'), url('/accounts'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'account_id' => $this->account->id,
            'name' => $this->account->name,
            'balance' => $this->account->balance,
        ];
    }
}
