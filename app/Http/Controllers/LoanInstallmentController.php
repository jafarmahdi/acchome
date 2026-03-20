<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LoanInstallmentController extends Controller
{
    public function index(Request $request, Loan $loan)
    {
        $this->authorizeLoan($loan);

        $familyId = auth()->user()->family_id;
        $payments = $loan->payments()
            ->with(['account', 'user'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('installment_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('payment_date', 'like', "%{$search}%")
                        ->orWhereHas('account', fn ($accountQuery) => $accountQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->has_receipt === 'yes', fn ($q) => $q->whereNotNull('receipt_image'))
            ->when($request->has_receipt === 'no', fn ($q) => $q->whereNull('receipt_image'))
            ->orderByDesc('installment_number')
            ->orderByDesc('payment_date')
            ->paginate(20)
            ->withQueryString();

        $accounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'count' => $loan->payments()->count(),
            'receipts_count' => $loan->payments()->whereNotNull('receipt_image')->count(),
            'archived_count' => $loan->payments()->where('affects_totals', false)->count(),
        ];

        return view('loans.installments', compact('loan', 'payments', 'accounts', 'stats'));
    }

    public function store(Request $request, Loan $loan)
    {
        $this->authorizeLoan($loan);

        $validated = $this->validateInstallment($request, $loan);
        $breakdown = $this->resolveInstallmentBreakdown($validated, $loan);
        $affectsTotals = $request->boolean('affects_totals', false);

        $payment = DB::transaction(function () use ($loan, $validated, $breakdown, $affectsTotals) {
            if ($affectsTotals) {
                $payment = $loan->makePayment(
                    $breakdown['amount'],
                    auth()->id(),
                    $validated['account_id'] ?? null,
                    [
                        'principal' => $breakdown['principal'],
                        'interest' => $breakdown['interest'],
                        'insurance_amount' => $breakdown['insurance_amount'],
                        'bank_fee' => $breakdown['bank_fee'],
                        'payment_date' => $validated['payment_date'],
                    ]
                );

                $payment->update([
                    'installment_number' => $validated['installment_number'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'affects_totals' => true,
                ]);

                if (!empty($validated['account_id'])) {
                    $account = Account::where('family_id', auth()->user()->family_id)->find($validated['account_id']);
                    if ($account) {
                        $account->adjustBalance($breakdown['amount'], 'subtract');
                        app(AlertService::class)->checkLowBalance($account->fresh());
                    }
                }

                app(AlertService::class)->createLoanPaymentAlert($loan->fresh(), $breakdown['amount']);

                return $payment;
            }

            return $loan->payments()->create([
                'user_id' => auth()->id(),
                'account_id' => $validated['account_id'] ?? null,
                'amount' => $breakdown['amount'],
                'principal' => $breakdown['principal'],
                'interest' => $breakdown['interest'],
                'insurance_amount' => $breakdown['insurance_amount'],
                'bank_fee' => $breakdown['bank_fee'],
                'payment_date' => $validated['payment_date'],
                'installment_number' => $validated['installment_number'],
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'paid',
                'notes' => $validated['notes'] ?? null,
                'affects_totals' => false,
            ]);
        });

        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')
                ->store('loan-payments/' . auth()->user()->family_id, 'public');

            $payment->update([
                'receipt_image' => $receiptPath,
            ]);
        }

        AuditLog::record(
            'loan_installment_created',
            $loan,
            null,
            $payment->toArray(),
            'Added loan installment record'
        );

        return redirect()->route('loans.installments.index', $loan)
            ->with('success', __('Installment saved successfully.'));
    }

    public function update(Request $request, Loan $loan, LoanPayment $payment)
    {
        $this->authorizeLoan($loan);
        $this->authorizePayment($loan, $payment);

        $validated = $request->validate([
            'installment_number' => 'required|integer|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'nullable|exists:accounts,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [], [
            'installment_number' => __('Installment Number'),
            'payment_date' => __('Payment Date'),
            'account_id' => __('From Account'),
            'reference_number' => __('Receipt Number'),
            'notes' => __('Notes'),
            'receipt_image' => __('Installment Receipt'),
        ]);

        if (!empty($validated['account_id'])) {
            Account::where('family_id', auth()->user()->family_id)->findOrFail($validated['account_id']);
        }

        $oldValues = $payment->toArray();

        if ($request->hasFile('receipt_image')) {
            if ($payment->receipt_image) {
                Storage::disk('public')->delete($payment->receipt_image);
            }

            $validated['receipt_image'] = $request->file('receipt_image')
                ->store('loan-payments/' . auth()->user()->family_id, 'public');
        }

        $payment->update($validated);

        AuditLog::record(
            'loan_installment_updated',
            $loan,
            $oldValues,
            $payment->fresh()->toArray(),
            'Updated loan installment record'
        );

        return redirect()->route('loans.installments.index', $loan)
            ->with('success', __('Installment updated successfully.'));
    }

    protected function validateInstallment(Request $request, Loan $loan): array
    {
        $validated = $request->validate([
            'installment_number' => 'required|integer|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'nullable|numeric|min:0.01',
            'principal' => 'nullable|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
            'insurance_amount' => 'nullable|numeric|min:0',
            'bank_fee' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [], [
            'installment_number' => __('Installment Number'),
            'payment_date' => __('Payment Date'),
            'account_id' => __('From Account'),
            'amount' => __('Amount'),
            'principal' => __('Principal'),
            'interest' => __('Interest'),
            'insurance_amount' => __('Insurance'),
            'bank_fee' => __('Bank Fee'),
            'reference_number' => __('Receipt Number'),
            'notes' => __('Notes'),
            'receipt_image' => __('Installment Receipt'),
        ]);

        if (!empty($validated['account_id'])) {
            Account::where('family_id', auth()->user()->family_id)->findOrFail($validated['account_id']);
        }

        $principal = (float) ($validated['principal'] ?? $loan->monthly_payment ?? 0);

        if ($principal > (float) $loan->remaining_amount && $request->boolean('affects_totals', false)) {
            return back()
                ->withErrors(['principal' => __('Principal cannot be greater than the remaining amount.')])
                ->withInput()
                ->throwResponse();
        }

        return $validated;
    }

    protected function resolveInstallmentBreakdown(array $validated, Loan $loan): array
    {
        $principal = (float) ($validated['principal'] ?? $loan->monthly_payment ?? 0);
        $interest = (float) ($validated['interest'] ?? $loan->installment_interest ?? 0);
        $insuranceAmount = (float) ($validated['insurance_amount'] ?? $loan->installment_insurance ?? 0);
        $bankFee = (float) ($validated['bank_fee'] ?? $loan->installment_bank_fee ?? 0);

        $computedAmount = $principal + $interest + $insuranceAmount + $bankFee;
        $fallbackAmount = max(
            $computedAmount,
            (float) ($loan->monthly_actual_payment ?? 0),
            (float) ($loan->monthly_payment ?? 0)
        );
        $amount = (float) ($validated['amount'] ?? $fallbackAmount);

        return [
            'amount' => $amount,
            'principal' => $principal,
            'interest' => $interest,
            'insurance_amount' => $insuranceAmount,
            'bank_fee' => $bankFee,
        ];
    }

    protected function authorizeLoan(Loan $loan): void
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);
    }

    protected function authorizePayment(Loan $loan, LoanPayment $payment): void
    {
        abort_if($payment->loan_id !== $loan->id, 404);
    }
}
