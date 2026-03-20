<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $categories = Category::where('family_id', $familyId)
            ->with(['parent', 'children'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->boolean('parents_only'), fn ($q) => $q->whereNull('parent_id'))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderBy('sort_order')->orderBy('name'))
            ->paginate(15)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $familyId = auth()->user()->family_id;
        $parentCategories = Category::where('family_id', $familyId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:expense,income',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['parent_id'])) {
            $parent = Category::where('family_id', auth()->user()->family_id)->findOrFail($validated['parent_id']);
        }

        $validated['family_id'] = auth()->user()->family_id;
        $validated['is_active'] = true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = Category::create($validated);

        AuditLog::record('created', $category, null, $category->toArray(), 'Created category: ' . $category->name);

        return redirect()->route('categories.index')
            ->with('success', __('Category created successfully.'));
    }

    public function show(Category $category)
    {
        abort_if($category->family_id !== auth()->user()->family_id, 403);

        $category->load(['parent', 'children', 'transactions' => function ($q) {
            $q->orderByDesc('transaction_date')->limit(10);
        }]);

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        abort_if($category->family_id !== auth()->user()->family_id, 403);

        $familyId = auth()->user()->family_id;
        $parentCategories = Category::where('family_id', $familyId)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('categories.create', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->family_id !== auth()->user()->family_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:expense,income',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => __('A category cannot be its own parent.')]);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $category->toArray();
        $category->update($validated);

        AuditLog::record('updated', $category, $oldValues, $category->fresh()->toArray(), 'Updated category: ' . $category->name);

        return redirect()->route('categories.index')
            ->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category)
    {
        abort_if($category->family_id !== auth()->user()->family_id, 403);

        // Check for children
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => __('Cannot delete a category with subcategories. Remove subcategories first.')]);
        }

        // Check for transactions
        if ($category->transactions()->count() > 0) {
            return back()->withErrors(['error' => __('Cannot delete a category with existing transactions. Reassign transactions first.')]);
        }

        $oldValues = $category->toArray();
        $category->delete();

        AuditLog::record('deleted', $category, $oldValues, null, 'Deleted category: ' . $category->name);

        return redirect()->route('categories.index')
            ->with('success', __('Category deleted successfully.'));
    }
}
