<?php

namespace App\Models;

use App\Models\Recipe;
use App\Models\BrewingStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brewing extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'volume', 'alcohol', 'boil_time', 'boil_start', 'initial_density', 'final_density', 'before_boil_density',
    'carbonation', 'date_start', 'current_step', 'note', 'recipe_id'];

    public function BrewingSteps(): HasMany
    {
        return $this->hasMany(BrewingStep::class);
    }

    public function recipe(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
