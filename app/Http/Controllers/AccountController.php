<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $accounts = Account::where('family_id', $familyId)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status !== null, function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('bank_name', 'like', "%{$search}%")
                       ->orWhere('account_number', 'like', "%{$search}%");
                });
            })
            ->when($request->sort, function ($q) use ($request) {
                $direction = $request->direction === 'asc' ? 'asc' : 'desc';
                $q->orderBy($request->sort, $direction);
            }, fn ($q) => $q->orderBy('name'))
            ->paginate(15)
            ->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $members = \App\Models\User::where('family_id', auth()->user()->family_id)->orderBy('name')->get();

        return view('accounts.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,savings,credit_card,loan,rewards,other',
            'balance' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'user_id' => 'nullable|exists:users,id',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'low_balance_threshold' => 'nullable|numeric|min:0',
            'include_in_total' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['user_id'])) {
            \App\Models\User::where('family_id', auth()->user()->family_id)
                ->findOrFail($validated['user_id']);
        }

        $validated['family_id'] = auth()->user()->family_id;
        $validated['user_id'] = $validated['user_id'] ?? auth()->id();
        $validated['initial_balance'] = $validated['balance'];
        $validated['currency'] = $validated['currency'] ?? (auth()->user()->family->currency ?? 'IQD');
        $validated['is_active'] = true;
        $validated['include_in_total'] = $request->has('include_in_total');

        $account = Account::create($validated);

        AuditLog::record('created', $account, null, $account->toArray(), 'Created account: ' . $account->name);

        return redirect()->route('accounts.index')
            ->with('success', __('Account created successfully.'));
    }

    public function show(Account $account)
    {
        $this->authorizeFamily($account);

        $account->load(['transactions' => function ($q) {
            $q->with(['category', 'user'])->orderByDesc('transaction_date')->limit(20);
        }]);

        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        $this->authorizeFamily($account);

        $members = \App\Models\User::where('family_id', auth()->user()->family_id)->orderBy('name')->get();

        return view('accounts.create', compact('account', 'members'));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorizeFamily($account);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,savings,credit_card,loan,rewards,other',
            'currency' => 'nullable|string|max:10',
            'user_id' => 'nullable|exists:users,id',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'low_balance_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'include_in_total' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['user_id'])) {
            \App\Models\User::where('family_id', auth()->user()->family_id)
                ->findOrFail($validated['user_id']);
        }

        $validated['user_id'] = $validated['user_id'] ?? null;
        $validated['currency'] = $validated['currency'] ?? ($account->currency ?: (auth()->user()->family->currency ?? 'IQD'));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['include_in_total'] = $request->has('include_in_total');

        $oldValues = $account->toArray();
        $account->update($validated);

        AuditLog::record('updated', $account, $oldValues, $account->fresh()->toArray(), 'Updated account: ' . $account->name);

        return redirect()->route('accounts.index')
            ->with('success', __('Account updated successfully.'));
    }

    public function destroy(Account $account)
    {
        $this->authorizeFamily($account);

        $oldValues = $account->toArray();
        $account->delete();

        AuditLog::record('deleted', $account, $oldValues, null, 'Deleted account: ' . $account->name);

        return redirect()->route('accounts.index')
            ->with('success', __('Account deleted successfully.'));
    }

    public function adjustBalance(Request $request, Account $account)
    {
        $this->authorizeFamily($account);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'operation' => 'required|in:add,subtract',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldBalance = $account->balance;
        $account->adjustBalance($validated['amount'], $validated['operation']);

        AuditLog::record(
            'adjusted_balance',
            $account,
            ['balance' => $oldBalance],
            ['balance' => $account->fresh()->balance],
            "Adjusted balance: {$validated['operation']} {$validated['amount']}. " . ($validated['notes'] ?? '')
        );

        return redirect()->route('accounts.show', $account)
            ->with('success', __('Account balance adjusted successfully.'));
    }

    protected function authorizeFamily(Account $account): void
    {
        abort_if($account->family_id !== auth()->user()->family_id, 403);
    }
}
