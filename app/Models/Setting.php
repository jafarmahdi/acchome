<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['family_id', 'key', 'value', 'group'];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
