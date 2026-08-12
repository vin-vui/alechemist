<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'method' => $this->method,
            'ferment' => $this->ferment,
            'volume' => $this->volume,
            'efficiency' => $this->efficiency,
            'color' => $this->color,
            'bitterness' => $this->bitterness,
            'alcohol' => $this->alcohol,
            'boil_time' => $this->boil_time,
            'initial_density' => $this->initial_density,
            'final_density' => $this->final_density,
            'before_boil_density' => $this->before_boil_density,
            'carbonation' => $this->carbonation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // La relation HasMany s'appelle historiquement brewing() (singulier)
            'brewings_count' => $this->whenCounted('brewing'),
            'steps' => StepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
