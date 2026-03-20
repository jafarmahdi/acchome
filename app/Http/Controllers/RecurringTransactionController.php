<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $recurringTransactions = RecurringTransaction::where('family_id', $familyId)
            ->with(['account', 'category', 'user'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('next_due_date')
            ->orderBy('description')
            ->paginate(15)
            ->withQueryString();

        return view('recurring-transactions.index', compact('recurringTransactions'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();
        $expenseCategories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();
        $incomeCategories = Category::where('family_id', $familyId)->where('type', 'income')->orderBy('name')->get();

        return view('recurring-transactions.create', compact('accounts', 'expenseCategories', 'incomeCategories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecurring($request);
        $validated['family_id'] = auth()->user()->family_id;
        $validated['user_id'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['auto_create'] = $request->boolean('auto_create', false);

        $recurringTransaction = RecurringTransaction::create($validated);

        AuditLog::record('created', $recurringTransaction, null, $recurringTransaction->toArray(), 'Created recurring transaction');

        return redirect()->route('recurring-transactions.index')
            ->with('success', __('Recurring transaction created successfully.'));
    }

    public function edit(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeFamily($recurringTransaction);

        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();
        $expenseCategories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();
        $incomeCategories = Category::where('family_id', $familyId)->where('type', 'income')->orderBy('name')->get();

        return view('recurring-transactions.create', compact('recurringTransaction', 'accounts', 'expenseCategories', 'incomeCategories'));
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        $this->authorizeFamily($recurringTransaction);

        $validated = $this->validateRecurring($request);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['auto_create'] = $request->boolean('auto_create', false);

        $oldValues = $recurringTransaction->toArray();
        $recurringTransaction->update($validated);

        AuditLog::record('updated', $recurringTransaction, $oldValues, $recurringTransaction->fresh()->toArray(), 'Updated recurring transaction');

        return redirect()->route('recurring-transactions.index')
            ->with('success', __('Recurring transaction updated successfully.'));
    }

    public function destroy(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeFamily($recurringTransaction);

        $oldValues = $recurringTransaction->toArray();
        $recurringTransaction->delete();

        AuditLog::record('deleted', $recurringTransaction, $oldValues, null, 'Deleted recurring transaction');

        return redirect()->route('recurring-transactions.index')
            ->with('success', __('Recurring transaction deleted successfully.'));
    }

    public function process(RecurringTransaction $recurringTransaction)
    {
        $this->authorizeFamily($recurringTransaction);

        if (!$recurringTransaction->is_active) {
            return redirect()->route('recurring-transactions.index')
                ->with('error', __('This recurring transaction is inactive.'));
        }

        $account = $recurringTransaction->account;
        abort_if(!$account || $account->family_id !== auth()->user()->family_id, 403);

        DB::transaction(function () use ($recurringTransaction, $account) {
            Transaction::create([
                'family_id' => $recurringTransaction->family_id,
                'user_id' => auth()->id(),
                'account_id' => $recurringTransaction->account_id,
                'category_id' => $recurringTransaction->category_id,
                'type' => $recurringTransaction->type,
                'amount' => $recurringTransaction->amount,
                'description' => $recurringTransaction->description,
                'notes' => trim(($recurringTransaction->notes ?? '') . "\n" . __('Generated from recurring transaction')),
                'transaction_date' => $recurringTransaction->next_due_date,
                'payment_method' => 'other',
                'is_recurring' => true,
                'recurring_frequency' => $recurringTransaction->frequency,
            ]);

            $operation = $recurringTransaction->type === 'income' ? 'add' : 'subtract';
            $account->adjustBalance((float) $recurringTransaction->amount, $operation);

            $recurringTransaction->forceFill([
                'last_generated_at' => now(),
            ])->save();

            $recurringTransaction->advanceNextDueDate();
        });

        AuditLog::record('processed', $recurringTransaction, null, ['next_due_date' => $recurringTransaction->fresh()->next_due_date?->toDateString()], 'Processed recurring transaction');

        return redirect()->route('recurring-transactions.index')
            ->with('success', __('Recurring transaction recorded successfully.'));
    }

    protected function validateRecurring(Request $request): array
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'next_due_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:next_due_date',
        ]);

        $account = Account::findOrFail($validated['account_id']);
        abort_if($account->family_id !== auth()->user()->family_id, 403);

        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            abort_if($category->family_id !== auth()->user()->family_id, 403);
        }

        return $validated;
    }

    protected function authorizeFamily(RecurringTransaction $recurringTransaction): void
    {
        abort_if($recurringTransaction->family_id !== auth()->user()->family_id, 403);
    }
}
