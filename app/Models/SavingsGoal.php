<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id', 'account_id', 'name', 'description', 'target_amount',
        'current_amount', 'target_date', 'icon', 'color', 'status', 'priority',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    public function getProgressAttribute(): float
    {
        return $this->target_amount > 0
            ? round(($this->current_amount / $this->target_amount) * 100, 1)
            : 0;
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    public function addContribution(float $amount, int $userId, int $accountId, ?string $notes = null): SavingsContribution
    {
        return DB::transaction(function () use ($amount, $userId, $accountId, $notes) {
            $account = Account::where('family_id', $this->family_id)->findOrFail($accountId);

            $contribution = $this->contributions()->create([
                'user_id' => $userId,
                'account_id' => $account->id,
                'amount' => $amount,
                'notes' => $notes,
                'contribution_date' => now()->toDateString(),
            ]);

            $account->adjustBalance($amount, 'subtract');
            $this->increment('current_amount', $amount);
            $this->refresh();

            if ($this->current_amount >= $this->target_amount) {
                $this->update(['status' => 'completed']);
            }

            return $contribution;
        });
    }
}
