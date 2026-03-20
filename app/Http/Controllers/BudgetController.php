<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $budgets = Budget::where('family_id', $familyId)
            ->with(['category', 'categories'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->category_id, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('category_id', $v)
                        ->orWhereHas('categories', fn ($q3) => $q3->where('categories.id', $v));
                });
            })
            ->when($request->period, fn ($q, $v) => $q->where('period', $v))
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'over_budget') {
                    $q->where('is_active', true)->whereRaw('spent > amount');
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();

        return view('budgets.index', compact('budgets', 'categories'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();

        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:weekly,monthly,quarterly,yearly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'alert_threshold' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['family_id'] = auth()->user()->family_id;
        $validated['is_active'] = true;
        $validated['spent'] = 0;
        $validated['alert_threshold'] = $validated['alert_threshold'] ?? 80;
        $categoryIds = collect($request->input('category_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $validated['category_id'] = !empty($categoryIds)
            ? $categoryIds[0]
            : ($validated['category_id'] ?? null);

        $budget = Budget::create($validated);
        $budget->syncCategories($categoryIds);
        $budget->recalculateSpent();

        AuditLog::record('created', $budget, null, $budget->toArray(), 'Created budget: ' . $budget->name);

        return redirect()->route('budgets.index')
            ->with('success', __('Budget created successfully.'));
    }

    public function show(Budget $budget)
    {
        abort_if($budget->family_id !== auth()->user()->family_id, 403);

        $budget->load(['category', 'categories']);

        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        abort_if($budget->family_id !== auth()->user()->family_id, 403);

        $familyId = auth()->user()->family_id;
        $categories = Category::where('family_id', $familyId)->where('type', 'expense')->orderBy('name')->get();

        return view('budgets.create', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        abort_if($budget->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:weekly,monthly,quarterly,yearly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'alert_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $categoryIds = collect($request->input('category_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $validated['category_id'] = !empty($categoryIds)
            ? $categoryIds[0]
            : ($validated['category_id'] ?? null);

        $oldValues = $budget->toArray();
        $budget->update($validated);
        $budget->syncCategories($categoryIds);
        $budget->recalculateSpent();

        AuditLog::record('updated', $budget, $oldValues, $budget->fresh()->toArray(), 'Updated budget: ' . $budget->name);

        return redirect()->route('budgets.index')
            ->with('success', __('Budget updated successfully.'));
    }

    public function destroy(Budget $budget)
    {
        abort_if($budget->family_id !== auth()->user()->family_id, 403);

        $oldValues = $budget->toArray();
        $budget->delete();

        AuditLog::record('deleted', $budget, $oldValues, null, 'Deleted budget: ' . $budget->name);

        return redirect()->route('budgets.index')
            ->with('success', __('Budget deleted successfully.'));
    }

    public function recalculate(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $budgets = Budget::where('family_id', $familyId)
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($budgets as $budget) {
            $budget->recalculateSpent();
            $count++;
        }

        return redirect()->route('budgets.index')
            ->with('success', __(':count budgets recalculated successfully.', ['count' => $count]));
    }
}
