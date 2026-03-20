<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id', 'user_id', 'name', 'type', 'balance', 'initial_balance',
        'currency', 'bank_name', 'account_number', 'color', 'icon',
        'low_balance_threshold', 'is_active', 'include_in_total', 'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'low_balance_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'include_in_total' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(AccountAdjustment::class);
    }

    public function isLowBalance(): bool
    {
        return $this->balance <= $this->low_balance_threshold;
    }

    public function adjustBalance(float $amount, string $operation = 'subtract'): void
    {
        if ($operation === 'add') {
            $this->increment('balance', $amount);
        } else {
            $this->decrement('balance', $amount);
        }
    }
}
