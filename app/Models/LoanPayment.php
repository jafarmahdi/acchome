<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id', 'user_id', 'account_id', 'amount', 'principal', 'interest',
        'insurance_amount', 'bank_fee', 'payment_date', 'due_date',
        'installment_number', 'reference_number', 'affects_totals', 'status', 'notes', 'receipt_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal' => 'decimal:2',
        'interest' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'bank_fee' => 'decimal:2',
        'payment_date' => 'date',
        'due_date' => 'date',
        'affects_totals' => 'boolean',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date && $this->due_date->isPast();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_image ? asset('storage/' . $this->receipt_image) : null;
    }
}
