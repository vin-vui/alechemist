<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrewingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'volume' => $this->volume,
            'alcohol' => $this->alcohol,
            'boil_time' => $this->boil_time,
            'boil_start' => $this->boil_start,
            'ferment_start' => $this->ferment_start,
            'ferment_end' => $this->ferment_end,
            'initial_density' => $this->initial_density,
            'final_density' => $this->final_density,
            'before_boil_density' => $this->before_boil_density,
            'carbonation' => $this->carbonation,
            'date_start' => $this->date_start,
            'current_step' => $this->current_step,
            'note' => $this->note,
            'recipe_id' => $this->recipe_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'steps' => BrewingStepResource::collection($this->whenLoaded('brewingSteps')),
            'recipe' => new RecipeResource($this->whenLoaded('recipe')),
        ];
    }
}
