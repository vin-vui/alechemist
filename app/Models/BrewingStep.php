<?php

namespace App\Models;

use App\Models\Brewing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrewingStep extends Model
{
    use HasFactory;
    protected $fillable = ['quantity', 'unit', 'field', 'date', 'time', 'type', 'status', 'brewing_id'];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Brewing::class);
    }
}
