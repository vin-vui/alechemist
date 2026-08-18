<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrewingStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'field' => $this->field,
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'status' => (bool) $this->status,
            'started_at' => $this->started_at,
        ];
    }
}
