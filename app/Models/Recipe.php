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
    'boil_time', 'initial_density', 'final_density', 'before_boil_density', 'user_id','carbonation'];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }

    public function brewing(): HasMany
    {
        return $this->hasMany(Brewing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
