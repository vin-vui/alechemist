<?php

namespace App\Models;

use App\Models\Brewing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrewingStep extends Model
{
    use HasFactory;
    protected $fillable = ['quantity', 'unit', 'field', 'date', 'time', 'type', 'status', 'started_at', 'brewing_id'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    public function brewing(): BelongsTo
    {
        return $this->belongsTo(Brewing::class);
    }
}
