<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id', 'user_id', 'account_id', 'category_id', 'type', 'amount',
        'description', 'notes', 'transaction_date', 'transaction_time',
        'reference_number', 'receipt_image', 'payment_method', 'location',
        'tags', 'is_recurring', 'recurring_frequency', 'transfer_to_account_id',
        'loan_id', 'is_approved',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'tags' => 'array',
        'is_recurring' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', 'transfer');
    }

    public function scopeForFamily($query, int $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                     ->whereYear('transaction_date', now()->year);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_image ? asset('storage/' . $this->receipt_image) : null;
    }
}
