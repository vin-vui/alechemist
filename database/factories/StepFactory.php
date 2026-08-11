<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Step>
 */
class StepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => 5000,
            'unit' => 'g',
            'field' => 'Pale Ale Malt',
            'time' => null,
            'type' => 'Preparation',
            'recipe_id' => Recipe::factory(),
        ];
    }
}
