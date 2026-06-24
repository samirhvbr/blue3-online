<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentGroup extends Model
{
    protected $fillable = ['name', 'position'];

    public function components(): HasMany
    {
        return $this->hasMany(Component::class)->orderBy('position');
    }
}
