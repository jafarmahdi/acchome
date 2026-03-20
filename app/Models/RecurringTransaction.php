<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'family_id', 'user_id', 'account_id', 'category_id', 'type', 'amount',
        'description', 'frequency', 'next_due_date', 'end_date', 'is_active', 'auto_create',
        'notes', 'last_generated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_due_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'auto_create' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    public function family(): BelongsTo { return $this->belongsTo(Family::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_due_date->lte(now());
    }

    public function advanceNextDueDate(): void
    {
        $next = match($this->frequency) {
            'daily' => $this->next_due_date->addDay(),
            'weekly' => $this->next_due_date->addWeek(),
            'biweekly' => $this->next_due_date->addWeeks(2),
            'monthly' => $this->next_due_date->addMonth(),
            'quarterly' => $this->next_due_date->addMonths(3),
            'yearly' => $this->next_due_date->addYear(),
        };

        if ($this->end_date && $next->gt($this->end_date)) {
            $this->update(['is_active' => false]);
        } else {
            $this->update(['next_due_date' => $next]);
        }
    }

    public function frequencyLabel(): string
    {
        return __(ucfirst($this->frequency));
    }
}
