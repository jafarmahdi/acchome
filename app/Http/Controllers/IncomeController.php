<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $incomes = Transaction::where('family_id', $familyId)
            ->where('type', 'income')
            ->with(['account', 'category', 'user'])
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
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

        $categories = Category::where('family_id', $familyId)->where('type', 'income')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();
        $members = User::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('incomes.index', compact('incomes', 'categories', 'accounts', 'members'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'income')->with('children')->whereNull('parent_id')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('incomes.create', compact('categories', 'accounts'));
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
            'location' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $account = Account::findOrFail($validated['account_id']);
        abort_if($account->family_id !== auth()->user()->family_id, 403);

        $validated['family_id'] = auth()->user()->family_id;
        $validated['user_id'] = auth()->id();
        $validated['type'] = 'income';

        $income = DB::transaction(function () use ($validated, $account) {
            $income = Transaction::create($validated);
            $account->adjustBalance($validated['amount'], 'add');
            return $income;
        });

        app(AlertService::class)->createTransactionAlert($income->fresh(['account', 'category', 'user']));

        AuditLog::record('created', $income, null, $income->toArray(), 'Created income: ' . $income->description);

        return redirect()->route('incomes.index')
            ->with('success', __('Income recorded successfully.'));
    }

    public function show(Transaction $income)
    {
        abort_if($income->family_id !== auth()->user()->family_id, 403);
        abort_if($income->type !== 'income', 404);

        $income->load(['account', 'category', 'user']);

        return view('incomes.show', compact('income'));
    }

    public function edit(Transaction $income)
    {
        abort_if($income->family_id !== auth()->user()->family_id, 403);
        abort_if($income->type !== 'income', 404);

        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'income')->with('children')->whereNull('parent_id')->orderBy('name')->get();
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('incomes.create', compact('income', 'categories', 'accounts'));
    }

    public function update(Request $request, Transaction $income)
    {
        abort_if($income->family_id !== auth()->user()->family_id, 403);
        abort_if($income->type !== 'income', 404);

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
            'location' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $newAccount = Account::findOrFail($validated['account_id']);
        abort_if($newAccount->family_id !== auth()->user()->family_id, 403);

        $oldValues = $income->toArray();
        $oldAmount = $income->amount;
        $oldAccountId = $income->account_id;

        DB::transaction(function () use ($income, $validated, $oldAmount, $oldAccountId, $newAccount) {
            $oldAccount = Account::find($oldAccountId);
            if ($oldAccount) {
                $oldAccount->adjustBalance($oldAmount, 'subtract');
            }

            $income->update($validated);
            $newAccount->adjustBalance($validated['amount'], 'add');
        });

        AuditLog::record('updated', $income, $oldValues, $income->fresh()->toArray(), 'Updated income: ' . $income->description);

        return redirect()->route('incomes.index')
            ->with('success', __('Income updated successfully.'));
    }

    public function destroy(Transaction $income)
    {
        abort_if($income->family_id !== auth()->user()->family_id, 403);
        abort_if($income->type !== 'income', 404);

        $oldValues = $income->toArray();

        DB::transaction(function () use ($income) {
            $account = $income->account;
            if ($account) {
                $account->adjustBalance($income->amount, 'subtract');
            }
            $income->delete();
        });

        AuditLog::record('deleted', $income, $oldValues, null, 'Deleted income: ' . $income->description);

        return redirect()->route('incomes.index')
            ->with('success', __('Income deleted successfully.'));
    }
}
