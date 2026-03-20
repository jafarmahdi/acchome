<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAdjustment extends Model
{
    protected $fillable = [
        'family_id',
        'account_id',
        'user_id',
        'adjustment_type',
        'entered_amount',
        'previous_balance',
        'new_balance',
        'difference',
        'reason',
        'adjustment_date',
        'notes',
    ];

    protected $casts = [
        'entered_amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'adjustment_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
