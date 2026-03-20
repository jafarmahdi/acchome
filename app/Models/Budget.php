<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id', 'category_id', 'name', 'amount', 'spent', 'period',
        'start_date', 'end_date', 'alert_threshold', 'is_active', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'alert_threshold' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'budget_category')
            ->withTimestamps()
            ->orderBy('categories.name');
    }

    public function getSelectedCategoryIdsAttribute(): array
    {
        $ids = $this->relationLoaded('categories')
            ? $this->categories->pluck('id')->all()
            : $this->categories()->pluck('categories.id')->all();

        if (!empty($ids)) {
            return $ids;
        }

        return $this->category_id ? [(int) $this->category_id] : [];
    }

    public function getCategoryNamesAttribute(): array
    {
        $names = $this->relationLoaded('categories')
            ? $this->categories->pluck('display_name')->filter()->values()->all()
            : $this->categories()->get()->pluck('display_name')->filter()->values()->all();

        if (!empty($names)) {
            return $names;
        }

        return $this->category ? [$this->category->display_name] : [];
    }

    public function getPercentUsedAttribute(): float
    {
        return $this->amount > 0 ? round(($this->spent / $this->amount) * 100, 1) : 0;
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->spent);
    }

    public function isOverBudget(): bool
    {
        return $this->spent > $this->amount;
    }

    public function isNearLimit(): bool
    {
        return $this->percent_used >= $this->alert_threshold && !$this->isOverBudget();
    }

    public function recalculateSpent(): void
    {
        $categoryIds = $this->selected_category_ids;

        $spent = Transaction::where('family_id', $this->family_id)
            ->where('type', 'expense')
            ->when(!empty($categoryIds), fn($q) => $q->whereIn('category_id', $categoryIds))
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        $this->update(['spent' => $spent]);
    }

    public function syncCategories(array $categoryIds = []): void
    {
        $cleanIds = collect($categoryIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->categories()->sync($cleanIds);
    }
}
