<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\SavingsGoal;
use App\Services\AlertService;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $goals = SavingsGoal::where('family_id', $familyId)
            ->with('account')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderBy('target_date'))
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('family_id', $familyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('savings.index', compact('goals', 'accounts'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        return view('savings.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'account_id' => 'nullable|exists:accounts,id',
            'target_amount' => 'required|numeric|min:0.01',
            'target_date' => 'nullable|date|after:today',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        if (isset($validated['account_id'])) {
            $account = Account::where('family_id', auth()->user()->family_id)->findOrFail($validated['account_id']);
        }

        $validated['family_id'] = auth()->user()->family_id;
        $validated['current_amount'] = 0;
        $validated['status'] = 'active';

        $goal = SavingsGoal::create($validated);

        AuditLog::record('created', $goal, null, $goal->toArray(), 'Created savings goal: ' . $goal->name);

        return redirect()->route('savings-goals.index')
            ->with('success', __('Savings goal created successfully.'));
    }

    public function show(SavingsGoal $savingsGoal)
    {
        abort_if($savingsGoal->family_id !== auth()->user()->family_id, 403);

        $savingsGoal->load(['account', 'contributions' => function ($q) {
            $q->with('user')->orderByDesc('contribution_date');
        }]);

        return redirect()
            ->route('savings-goals.index')
            ->with('info', __('Savings goal details are shown in the list for now.'));
    }

    public function edit(SavingsGoal $savingsGoal)
    {
        abort_if($savingsGoal->family_id !== auth()->user()->family_id, 403);

        $familyId = auth()->user()->family_id;
        $accounts = Account::where('family_id', $familyId)->where('is_active', true)->orderBy('name')->get();

        $goal = $savingsGoal;

        return view('savings.create', compact('goal', 'accounts'));
    }

    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        abort_if($savingsGoal->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'account_id' => 'nullable|exists:accounts,id',
            'target_amount' => 'required|numeric|min:0.01',
            'target_date' => 'nullable|date',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:active,paused,completed,cancelled',
        ]);

        $oldValues = $savingsGoal->toArray();
        $savingsGoal->update($validated);

        AuditLog::record('updated', $savingsGoal, $oldValues, $savingsGoal->fresh()->toArray(), 'Updated savings goal: ' . $savingsGoal->name);

        return redirect()->route('savings-goals.index')
            ->with('success', __('Savings goal updated successfully.'));
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        abort_if($savingsGoal->family_id !== auth()->user()->family_id, 403);

        $oldValues = $savingsGoal->toArray();
        $savingsGoal->delete();

        AuditLog::record('deleted', $savingsGoal, $oldValues, null, 'Deleted savings goal: ' . $savingsGoal->name);

        return redirect()->route('savings-goals.index')
            ->with('success', __('Savings goal deleted successfully.'));
    }

    public function addContribution(Request $request, SavingsGoal $savingsGoal)
    {
        abort_if($savingsGoal->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string|max:500',
        ], [], [
            'amount' => __('Amount'),
            'account_id' => __('From Account'),
            'notes' => __('Notes'),
        ]);

        $oldAmount = $savingsGoal->current_amount;
        $account = Account::where('family_id', auth()->user()->family_id)
            ->findOrFail($validated['account_id']);

        $contribution = $savingsGoal->addContribution(
            $validated['amount'],
            auth()->id(),
            $account->id,
            $validated['notes'] ?? null
        );

        app(AlertService::class)->checkLowBalance($account->fresh());

        AuditLog::record(
            'contribution',
            $savingsGoal,
            ['current_amount' => $oldAmount],
            ['current_amount' => $savingsGoal->fresh()->current_amount],
            "Added contribution of {$validated['amount']} to {$savingsGoal->name}"
        );

        return redirect()->route('savings-goals.index')
            ->with('success', __('Contribution added successfully.'));
    }

    public function contribute(Request $request, SavingsGoal $goal)
    {
        return $this->addContribution($request, $goal);
    }
}
