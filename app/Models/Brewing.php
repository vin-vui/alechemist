<?php

namespace App\Models;

use App\Models\Recipe;
use App\Models\Brewing_Step;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brewing extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'volume', 'alcohol', 'initial_density', 'final_density', 'before_boil_density',
    'carbonation', 'date_start', 'note', 'recipe_id'];

    public function Brewing_steps(): HasMany
    {
        return $this->hasMany(Brewing_Step::class);
    }

    public function recipe(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
