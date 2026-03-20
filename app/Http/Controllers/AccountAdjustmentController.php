<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountAdjustment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $accounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $adjustments = AccountAdjustment::where('family_id', $familyId)
            ->with(['account', 'user'])
            ->when($request->account_id, fn ($q, $accountId) => $q->where('account_id', $accountId))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('account-adjustments.index', compact('accounts', 'adjustments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'adjustment_type' => 'required|in:set,add,subtract',
            'entered_amount' => 'required|numeric|min:0',
            'adjustment_date' => 'required|date',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ], [], [
            'account_id' => __('Account'),
            'adjustment_type' => __('Adjustment Type'),
            'entered_amount' => __('Amount'),
            'adjustment_date' => __('Date'),
            'reason' => __('Reason'),
            'notes' => __('Notes'),
        ]);

        $account = Account::findOrFail($validated['account_id']);
        abort_if($account->family_id !== auth()->user()->family_id, 403);

        $previousBalance = (float) $account->balance;
        $enteredAmount = (float) $validated['entered_amount'];

        [$newBalance, $difference] = match ($validated['adjustment_type']) {
            'set' => [$enteredAmount, $enteredAmount - $previousBalance],
            'add' => [$previousBalance + $enteredAmount, $enteredAmount],
            'subtract' => [$previousBalance - $enteredAmount, -$enteredAmount],
        };

        $adjustment = DB::transaction(function () use ($validated, $account, $previousBalance, $enteredAmount, $newBalance, $difference) {
            $account->update(['balance' => $newBalance]);

            return AccountAdjustment::create([
                'family_id' => auth()->user()->family_id,
                'account_id' => $account->id,
                'user_id' => auth()->id(),
                'adjustment_type' => $validated['adjustment_type'],
                'entered_amount' => $enteredAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'difference' => $difference,
                'reason' => $validated['reason'],
                'adjustment_date' => $validated['adjustment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        AuditLog::record('account_adjustment', $account, ['balance' => $previousBalance], ['balance' => $newBalance], 'Recorded account adjustment');

        return redirect()->route('account-adjustments.index')
            ->with('success', __('Account adjustment recorded successfully.'));
    }
}
