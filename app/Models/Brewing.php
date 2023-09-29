<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brewing extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'method', 'ferment', 'volume', 'efficiency', 'color', 'bitterness', 'alcohol',
    'initial_density', 'final_density', 'density_b_boil', 'carbonation'];

    public function Brewing_steps(): HasMany
    {
        return $this->hasMany(Brewing_Step::class);
    }

    public function recipe(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
