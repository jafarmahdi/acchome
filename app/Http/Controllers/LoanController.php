<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $loans = Loan::where('family_id', $familyId)
            ->with([
                'account',
                'user',
                'payments' => fn ($q) => $q->with(['account', 'user'])->orderByDesc('payment_date')->orderByDesc('id'),
            ])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('loans.index', compact('loans', 'accounts'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('loans.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(Loan::TYPES)],
            'account_id' => 'nullable|exists:accounts,id',
            'lender_borrower_name' => 'nullable|string|max:255',
            'original_amount' => 'required|numeric|min:0.01',
            'down_payment' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable|numeric|min:0',
            'installment_interest' => 'nullable|numeric|min:0',
            'installment_insurance' => 'nullable|numeric|min:0',
            'installment_bank_fee' => 'nullable|numeric|min:0',
            'total_installments' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['account_id'])) {
            $account = Account::where('family_id', auth()->user()->family_id)->findOrFail($validated['account_id']);
        }

        $validated['family_id'] = auth()->user()->family_id;
        $validated['user_id'] = auth()->id();
        $validated['remaining_amount'] = $validated['original_amount'];
        $validated['paid_installments'] = 0;
        $validated['status'] = 'active';

        $loan = Loan::create($validated);

        AuditLog::record('created', $loan, null, $loan->toArray(), 'Created loan: ' . $loan->name);

        return redirect()->route('loans.index')
            ->with('success', __('Loan created successfully.'));
    }

    public function show(Loan $loan)
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);

        return redirect()
            ->route('loans.index')
            ->with('info', __('Loan details are shown in the list for now.'));
    }

    public function edit(Loan $loan)
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);

        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('loans.create', compact('loan', 'accounts'));
    }

    public function update(Request $request, Loan $loan)
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(Loan::TYPES)],
            'account_id' => 'nullable|exists:accounts,id',
            'lender_borrower_name' => 'nullable|string|max:255',
            'down_payment' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable|numeric|min:0',
            'installment_interest' => 'nullable|numeric|min:0',
            'installment_insurance' => 'nullable|numeric|min:0',
            'installment_bank_fee' => 'nullable|numeric|min:0',
            'total_installments' => 'nullable|integer|min:1',
            'end_date' => 'nullable|date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'status' => 'nullable|in:active,paid_off,defaulted,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $loan->toArray();
        $loan->update($validated);

        AuditLog::record('updated', $loan, $oldValues, $loan->fresh()->toArray(), 'Updated loan: ' . $loan->name);

        return redirect()->route('loans.index')
            ->with('success', __('Loan updated successfully.'));
    }

    public function destroy(Loan $loan)
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);

        $oldValues = $loan->toArray();
        $loan->delete();

        AuditLog::record('deleted', $loan, $oldValues, null, 'Deleted loan: ' . $loan->name);

        return redirect()->route('loans.index')
            ->with('success', __('Loan deleted successfully.'));
    }

    public function makePayment(Request $request, Loan $loan)
    {
        abort_if($loan->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'principal' => 'nullable|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
            'insurance_amount' => 'nullable|numeric|min:0',
            'bank_fee' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'receipt_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [], [
            'amount' => __('Amount'),
            'account_id' => __('From Account'),
            'principal' => __('Principal'),
            'interest' => __('Interest'),
            'insurance_amount' => __('Insurance'),
            'bank_fee' => __('Bank Fee'),
            'payment_date' => __('Payment Date'),
            'notes' => __('Notes'),
            'receipt_image' => __('Installment Receipt'),
        ]);

        $principal = array_key_exists('principal', $validated) && $validated['principal'] !== null
            ? (float) $validated['principal']
            : (float) ($loan->monthly_payment ?: ($validated['amount'] ?? 0));
        $interest = (float) ($validated['interest'] ?? 0);
        $insuranceAmount = (float) ($validated['insurance_amount'] ?? 0);
        $bankFee = (float) ($validated['bank_fee'] ?? 0);
        $amount = (float) ($validated['amount'] ?? 0);

        if ($interest === 0.0 && $insuranceAmount === 0.0 && $bankFee === 0.0) {
            $interest = (float) ($loan->installment_interest ?? 0);
            $insuranceAmount = (float) ($loan->installment_insurance ?? 0);
            $bankFee = (float) ($loan->installment_bank_fee ?? 0);
        }

        if ($amount <= 0) {
            $amount = $principal + $interest + $insuranceAmount + $bankFee;
        }

        if ($principal > $loan->remaining_amount) {
            return back()
                ->withErrors(['principal' => __('Principal cannot be greater than the remaining amount.')])
                ->withInput();
        }

        if (($principal + $interest + $insuranceAmount + $bankFee) > ($amount + 0.0001)) {
            return back()
                ->withErrors(['amount' => __('The total paid amount must cover principal, interest, insurance, and bank fee.')])
                ->withInput();
        }

        $oldValues = [
            'remaining_amount' => $loan->remaining_amount,
            'paid_installments' => $loan->paid_installments,
        ];

        $accountId = $validated['account_id'];

        $payment = null;

        DB::transaction(function () use ($loan, $validated, $accountId, $principal, $interest, $insuranceAmount, $bankFee, $amount, &$payment) {
            $payment = $loan->makePayment(
                $amount,
                auth()->id(),
                $accountId,
                [
                    'principal' => $principal,
                    'interest' => $interest,
                    'insurance_amount' => $insuranceAmount,
                    'bank_fee' => $bankFee,
                    'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
                ]
            );

            if ((float) $payment->amount !== $amount) {
                $payment->update(['amount' => $amount]);
            }

            if ($validated['notes'] ?? null) {
                $payment->update(['notes' => $validated['notes']]);
            }

            // Deduct from account
            if ($accountId) {
                $account = Account::find($accountId);
                if ($account) {
                    $account->adjustBalance($amount, 'subtract');
                    app(AlertService::class)->checkLowBalance($account->fresh());
                }
            }
        });

        if ($request->hasFile('receipt_image') && $payment) {
            $receiptPath = $request->file('receipt_image')
                ->store('loan-payments/' . auth()->user()->family_id, 'public');

            $payment->update([
                'receipt_image' => $receiptPath,
            ]);
        }

        AuditLog::record(
            'loan_payment',
            $loan,
            $oldValues,
            [
                'remaining_amount' => $loan->fresh()->remaining_amount,
                'paid_installments' => $loan->fresh()->paid_installments,
            ],
            "Loan payment of {$amount} for {$loan->name}"
        );

        app(AlertService::class)->createLoanPaymentAlert($loan->fresh(), $amount);

        return redirect()->route('loans.index')
            ->with('success', __('Payment recorded successfully.'));
    }

    public function payment(Request $request, Loan $loan)
    {
        return $this->makePayment($request, $loan);
    }
}
