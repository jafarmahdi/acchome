<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $transfers = Transaction::where('family_id', $familyId)
            ->where('type', 'transfer')
            ->with(['account', 'transferToAccount', 'user'])
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->account_id, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('account_id', $v)->orWhere('transfer_to_account_id', $v);
                });
            })
            ->when($request->date_from, fn ($q, $v) => $q->where('transaction_date', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->where('transaction_date', '<=', $v))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('transaction_date')->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('transfers.index', compact('transfers', 'accounts'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('transfers.create', compact('accounts'));
    }

    public function edit(Transaction $transfer)
    {
        abort_if($transfer->family_id !== auth()->user()->family_id, 403);
        abort_if($transfer->type !== 'transfer', 404);

        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('transfers.create', compact('transfer', 'accounts'));
    }

    public function store(Request $request)
    {
        // Accept both the legacy field names from the form and the DB-oriented names.
        $request->merge([
            'account_id' => $request->input('account_id', $request->input('from_account_id')),
            'transfer_to_account_id' => $request->input('transfer_to_account_id', $request->input('to_account_id')),
        ]);

        $validated = $request->validate(
            [
                'account_id' => 'required|exists:accounts,id',
                'transfer_to_account_id' => 'required|exists:accounts,id|different:account_id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:100',
            ],
            [],
            [
                'account_id' => __('From Account'),
                'transfer_to_account_id' => __('To Account'),
                'amount' => __('Amount'),
                'description' => __('Description'),
                'transaction_date' => __('Date'),
            ]
        );

        $familyId = auth()->user()->family_id;
        $fromAccount = Account::where('family_id', $familyId)->findOrFail($validated['account_id']);
        $toAccount = Account::where('family_id', $familyId)->findOrFail($validated['transfer_to_account_id']);
        $this->ensureSufficientBalance($fromAccount, (float) $validated['amount']);

        $validated['family_id'] = $familyId;
        $validated['user_id'] = auth()->id();
        $validated['type'] = 'transfer';
        $validated['description'] = $validated['description'] ?: "Transfer: {$fromAccount->name} → {$toAccount->name}";

        $transfer = DB::transaction(function () use ($validated, $fromAccount, $toAccount) {
            $transfer = Transaction::create($validated);
            $fromAccount->adjustBalance($validated['amount'], 'subtract');
            $toAccount->adjustBalance($validated['amount'], 'add');
            return $transfer;
        });

        app(AlertService::class)->checkLowBalance($fromAccount->fresh());
        app(AlertService::class)->createTransactionAlert($transfer->fresh(['account', 'transferToAccount', 'user']));

        AuditLog::record('created', $transfer, null, $transfer->toArray(),
            "Transfer: {$validated['amount']} from {$fromAccount->name} to {$toAccount->name}");

        return redirect()->route('transfers.index')
            ->with('success', __('Transfer completed successfully.'));
    }

    public function update(Request $request, Transaction $transfer)
    {
        abort_if($transfer->family_id !== auth()->user()->family_id, 403);
        abort_if($transfer->type !== 'transfer', 404);

        $request->merge([
            'account_id' => $request->input('account_id', $request->input('from_account_id')),
            'transfer_to_account_id' => $request->input('transfer_to_account_id', $request->input('to_account_id')),
        ]);

        $validated = $request->validate(
            [
                'account_id' => 'required|exists:accounts,id',
                'transfer_to_account_id' => 'required|exists:accounts,id|different:account_id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:100',
            ],
            [],
            [
                'account_id' => __('From Account'),
                'transfer_to_account_id' => __('To Account'),
                'amount' => __('Amount'),
                'description' => __('Description'),
                'transaction_date' => __('Date'),
            ]
        );

        $familyId = auth()->user()->family_id;
        $newFromAccount = Account::where('family_id', $familyId)->findOrFail($validated['account_id']);
        $newToAccount = Account::where('family_id', $familyId)->findOrFail($validated['transfer_to_account_id']);

        $oldValues = $transfer->toArray();
        $oldFromAccount = $transfer->account;
        $oldToAccount = $transfer->transferToAccount;
        $oldAmount = (float) $transfer->amount;
        $availableBalance = $this->calculateAvailableBalanceAfterReversal($newFromAccount, $oldFromAccount, $oldToAccount, $oldAmount);
        $this->ensureSufficientBalance($newFromAccount, (float) $validated['amount'], $availableBalance);

        $validated['description'] = $validated['description'] ?: "Transfer: {$newFromAccount->name} → {$newToAccount->name}";

        DB::transaction(function () use ($transfer, $validated, $oldFromAccount, $oldToAccount, $oldAmount, $newFromAccount, $newToAccount) {
            if ($oldFromAccount) {
                $oldFromAccount->adjustBalance($oldAmount, 'add');
            }
            if ($oldToAccount) {
                $oldToAccount->adjustBalance($oldAmount, 'subtract');
            }

            $transfer->update($validated);

            $newFromAccount->adjustBalance($validated['amount'], 'subtract');
            $newToAccount->adjustBalance($validated['amount'], 'add');
        });

        app(AlertService::class)->checkLowBalance($newFromAccount->fresh());

        AuditLog::record(
            'updated',
            $transfer,
            $oldValues,
            $transfer->fresh()->toArray(),
            "Updated transfer: {$validated['amount']} from {$newFromAccount->name} to {$newToAccount->name}"
        );

        return redirect()->route('transfers.index')
            ->with('success', __('Transfer updated successfully.'));
    }

    public function show(Transaction $transfer)
    {
        abort_if($transfer->family_id !== auth()->user()->family_id, 403);
        abort_if($transfer->type !== 'transfer', 404);

        $transfer->load(['account', 'transferToAccount', 'user']);

        return view('transfers.show', compact('transfer'));
    }

    public function destroy(Transaction $transfer)
    {
        abort_if($transfer->family_id !== auth()->user()->family_id, 403);
        abort_if($transfer->type !== 'transfer', 404);

        $oldValues = $transfer->toArray();

        DB::transaction(function () use ($transfer) {
            if ($transfer->account) {
                $transfer->account->adjustBalance($transfer->amount, 'add');
            }
            if ($transfer->transferToAccount) {
                $transfer->transferToAccount->adjustBalance($transfer->amount, 'subtract');
            }
            $transfer->delete();
        });

        AuditLog::record('deleted', $transfer, $oldValues, null, 'Reversed transfer');

        return redirect()->route('transfers.index')
            ->with('success', __('Transfer reversed and deleted successfully.'));
    }

    protected function ensureSufficientBalance(Account $account, float $amount, ?float $availableBalance = null): void
    {
        $balance = $availableBalance ?? (float) $account->balance;

        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' => __('The source account does not have enough balance for this transfer.'),
            ]);
        }
    }

    protected function calculateAvailableBalanceAfterReversal(
        Account $newFromAccount,
        ?Account $oldFromAccount,
        ?Account $oldToAccount,
        float $oldAmount
    ): float {
        $availableBalance = (float) $newFromAccount->balance;

        if ($oldFromAccount && $oldFromAccount->id === $newFromAccount->id) {
            $availableBalance += $oldAmount;
        }

        if ($oldToAccount && $oldToAccount->id === $newFromAccount->id) {
            $availableBalance -= $oldAmount;
        }

        return $availableBalance;
    }
}
