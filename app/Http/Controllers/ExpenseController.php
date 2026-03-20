<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $expenses = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->with(['account', 'category', 'user'])
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%");
            })
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->account_id, fn ($q, $v) => $q->where('account_id', $v))
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->payment_method, fn ($q, $v) => $q->where('payment_method', $v))
            ->when($request->date_from, fn ($q, $v) => $q->where('transaction_date', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->where('transaction_date', '<=', $v))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('transaction_date')->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();
        $members = User::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories', 'accounts', 'members'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->with('children')->whereNull('parent_id')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'transaction_time' => 'nullable|date_format:H:i',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,e_wallet,cheque,other',
            'reference_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'location' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $account = Account::findOrFail($validated['account_id']);
        abort_if($account->family_id !== auth()->user()->family_id, 403);

        $validated['family_id'] = auth()->user()->family_id;
        $validated['user_id'] = auth()->id();
        $validated['type'] = 'expense';

        if ($request->hasFile('receipt_image')) {
            $validated['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . auth()->user()->family_id, 'public');
        }

        $expense = DB::transaction(function () use ($validated, $account) {
            $expense = Transaction::create($validated);
            $account->adjustBalance($validated['amount'], 'subtract');
            return $expense;
        });

        // Recalculate related budgets and check alerts
        $this->recalculateRelatedBudgets($expense);
        $this->checkBudgetAlerts($expense);
        app(AlertService::class)->checkLowBalance($account->fresh());
        app(AlertService::class)->createTransactionAlert($expense->fresh(['account', 'category', 'user']));

        AuditLog::record('created', $expense, null, $expense->toArray(), 'Created expense: ' . $expense->description);

        return redirect()->route('expenses.index')
            ->with('success', __('Expense recorded successfully.'));
    }

    public function show(Transaction $expense)
    {
        abort_if($expense->family_id !== auth()->user()->family_id, 403);
        abort_if($expense->type !== 'expense', 404);

        $expense->load(['account', 'category', 'user']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Transaction $expense)
    {
        abort_if($expense->family_id !== auth()->user()->family_id, 403);
        abort_if($expense->type !== 'expense', 404);

        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->with('children')->whereNull('parent_id')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('expense', 'categories', 'accounts'));
    }

    public function update(Request $request, Transaction $expense)
    {
        abort_if($expense->family_id !== auth()->user()->family_id, 403);
        abort_if($expense->type !== 'expense', 404);

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'transaction_time' => 'nullable|date_format:H:i',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,e_wallet,cheque,other',
            'reference_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'location' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $newAccount = Account::findOrFail($validated['account_id']);
        abort_if($newAccount->family_id !== auth()->user()->family_id, 403);

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $validated['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . auth()->user()->family_id, 'public');
        }

        $oldValues = $expense->toArray();
        $oldAmount = $expense->amount;
        $oldAccountId = $expense->account_id;

        DB::transaction(function () use ($expense, $validated, $oldAmount, $oldAccountId, $newAccount) {
            // Reverse old balance adjustment
            $oldAccount = Account::find($oldAccountId);
            if ($oldAccount) {
                $oldAccount->adjustBalance($oldAmount, 'add');
            }

            $expense->update($validated);

            // Apply new balance adjustment
            $newAccount->adjustBalance($validated['amount'], 'subtract');
        });

        $this->recalculateRelatedBudgets($expense);
        $this->checkBudgetAlerts($expense->fresh());
        app(AlertService::class)->checkLowBalance($newAccount->fresh());

        AuditLog::record('updated', $expense, $oldValues, $expense->fresh()->toArray(), 'Updated expense: ' . $expense->description);

        return redirect()->route('expenses.index')
            ->with('success', __('Expense updated successfully.'));
    }

    public function destroy(Transaction $expense)
    {
        abort_if($expense->family_id !== auth()->user()->family_id, 403);
        abort_if($expense->type !== 'expense', 404);

        $oldValues = $expense->toArray();

        DB::transaction(function () use ($expense) {
            $account = $expense->account;
            if ($account) {
                $account->adjustBalance($expense->amount, 'add');
            }

            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }

            $expense->delete();
        });

        $this->recalculateRelatedBudgets($expense);

        AuditLog::record('deleted', $expense, $oldValues, null, 'Deleted expense: ' . $expense->description);

        return redirect()->route('expenses.index')
            ->with('success', __('Expense deleted successfully.'));
    }

    protected function checkBudgetAlerts(Transaction $expense): void
    {
        $alertService = app(AlertService::class);

        Budget::where('family_id', $expense->family_id)
            ->where('is_active', true)
            ->where('start_date', '<=', $expense->transaction_date)
            ->where('end_date', '>=', $expense->transaction_date)
            ->when($expense->category_id, function ($q) use ($expense) {
                $q->where(function ($q2) use ($expense) {
                    $q2->where('category_id', $expense->category_id)
                        ->orWhereNull('category_id');
                });
            })
            ->each(fn (Budget $budget) => $alertService->checkBudgetAlerts($budget));
    }

    protected function recalculateRelatedBudgets(Transaction $expense): void
    {
        Budget::where('family_id', $expense->family_id)
            ->where('is_active', true)
            ->where('start_date', '<=', $expense->transaction_date)
            ->where('end_date', '>=', $expense->transaction_date)
            ->when($expense->category_id, function ($q) use ($expense) {
                $q->where(function ($q2) use ($expense) {
                    $q2->where('category_id', $expense->category_id)
                        ->orWhereNull('category_id');
                });
            })
            ->each(fn (Budget $budget) => $budget->recalculateSpent());
    }
}
