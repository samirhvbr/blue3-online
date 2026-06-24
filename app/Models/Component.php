<?php

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends Model
{
    protected $fillable = ['component_group_id', 'name', 'status', 'position'];

    protected function casts(): array
    {
        return ['status' => ComponentStatus::class];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ComponentGroup::class, 'component_group_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
