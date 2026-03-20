<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'family_id',
        'created_by',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
