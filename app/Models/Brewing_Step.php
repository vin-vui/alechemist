<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brewing_Step extends Model
{
    use HasFactory;
    protected $fillable = ['quantity', 'unit', 'field', 'date', 'time', 'type', 'status', 'brewing_id'];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Brewing::class);
    }
}
