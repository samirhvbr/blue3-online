<?php

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = ['title', 'detail', 'impact', 'component_id', 'started_at', 'resolved_at'];

    protected function casts(): array
    {
        return [
            'impact' => ComponentStatus::class,
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
