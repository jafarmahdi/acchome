<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'borrowed',
        'lent',
        'installment',
        'salary_advance',
        'mortgage',
        'apartment_installment',
    ];

    protected $fillable = [
        'family_id', 'user_id', 'account_id', 'name', 'description', 'type',
        'lender_borrower_name', 'original_amount', 'down_payment', 'remaining_amount',
        'interest_rate', 'monthly_payment', 'installment_interest',
        'installment_insurance', 'installment_bank_fee', 'total_installments',
        'paid_installments', 'start_date', 'end_date', 'due_day',
        'status', 'notes',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'installment_interest' => 'decimal:2',
        'installment_insurance' => 'decimal:2',
        'installment_bank_fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getProgressAttribute(): float
    {
        return $this->total_installments > 0
            ? round(($this->paid_installments / $this->total_installments) * 100, 1)
            : 0;
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->original_amount - $this->remaining_amount;
    }

    public function getMonthlyContractAmountAttribute(): float
    {
        return (float) $this->monthly_payment
            + (float) $this->installment_interest
            + (float) $this->installment_insurance;
    }

    public function getMonthlyActualPaymentAttribute(): float
    {
        return $this->monthly_contract_amount + (float) $this->installment_bank_fee;
    }

    public function getTotalCostWithInterestAttribute(): float
    {
        return (float) $this->down_payment + ($this->monthly_contract_amount * max(0, (int) $this->total_installments));
    }

    public function getTotalCostWithFeesAttribute(): float
    {
        return $this->total_cost_with_interest + ((float) $this->installment_bank_fee * max(0, (int) $this->total_installments));
    }

    public function getUsesContractTotalsAttribute(): bool
    {
        return $this->type === 'apartment_installment'
            && ($this->down_payment > 0 || $this->installment_interest > 0 || $this->installment_insurance > 0);
    }

    public function getDisplayOriginalAmountAttribute(): float
    {
        return $this->uses_contract_totals
            ? $this->total_cost_with_interest
            : (float) $this->original_amount;
    }

    public function getRemainingContractAmountAttribute(): float
    {
        $remainingInstallments = max(0, (int) $this->total_installments - (int) $this->paid_installments);

        return $remainingInstallments * $this->monthly_contract_amount;
    }

    public function getDisplayRemainingAmountAttribute(): float
    {
        return $this->uses_contract_totals
            ? $this->remaining_contract_amount
            : (float) $this->remaining_amount;
    }

    public function getNextDueDateAttribute(): ?\Carbon\Carbon
    {
        if (!$this->due_day || $this->status !== 'active') return null;
        $date = now()->day($this->due_day);
        if ($date->isPast()) $date->addMonth();
        return $date;
    }

    public function isDueSoon(int $days = 7): bool
    {
        $nextDue = $this->next_due_date;
        return $nextDue && $nextDue->diffInDays(now()) <= $days;
    }

    public function makePayment(float $amount, int $userId, ?int $accountId = null, array $breakdown = []): LoanPayment
    {
        $principal = (float) ($breakdown['principal'] ?? $this->monthly_payment ?? $amount);
        $interest = (float) ($breakdown['interest'] ?? $this->installment_interest ?? 0);
        $insuranceAmount = (float) ($breakdown['insurance_amount'] ?? $this->installment_insurance ?? 0);
        $bankFee = (float) ($breakdown['bank_fee'] ?? $this->installment_bank_fee ?? 0);
        $paymentDate = $breakdown['payment_date'] ?? now()->toDateString();

        $payment = $this->payments()->create([
            'user_id' => $userId,
            'account_id' => $accountId ?? $this->account_id,
            'amount' => $amount,
            'principal' => $principal,
            'interest' => $interest,
            'insurance_amount' => $insuranceAmount,
            'bank_fee' => $bankFee,
            'payment_date' => $paymentDate,
            'installment_number' => $this->paid_installments + 1,
            'status' => 'paid',
        ]);

        $this->decrement('remaining_amount', $principal);
        $this->increment('paid_installments');

        $this->refresh();

        if ($this->remaining_amount <= 0) {
            $this->update(['status' => 'paid_off', 'remaining_amount' => 0]);
        }

        return $payment;
    }
}
