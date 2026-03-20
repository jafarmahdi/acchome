<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    protected $fillable = [
        'family_id', 'user_id', 'type', 'title', 'message', 'severity',
        'icon', 'action_url', 'alertable_type', 'alertable_id',
        'is_read', 'is_dismissed', 'email_sent', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'email_sent' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function dismiss(): void
    {
        $this->update(['is_dismissed' => true, 'is_read' => true, 'read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false)->where('is_dismissed', false);
    }

    public function scopeForFamily($query, int $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false);
    }
}
