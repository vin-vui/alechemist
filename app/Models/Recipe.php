<?php

namespace App\Models;

use App\Models\Step;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'method', 'ferment', 'volume', 'efficiency', 'color', 'bitterness', 'alcohol',
    'initial_density', 'final_density', 'before_boil_density', 'carbonation'];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }

    public function brewing(): BelongsTo
    {
        return $this->belongsTo(brewing::class);
    }
}
