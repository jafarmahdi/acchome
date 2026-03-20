<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsContribution extends Model
{
    protected $fillable = ['savings_goal_id', 'user_id', 'account_id', 'amount', 'notes', 'contribution_date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'contribution_date' => 'date',
    ];

    public function savingsGoal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }
}
