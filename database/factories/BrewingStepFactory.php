<?php

namespace Database\Factories;

use App\Models\Brewing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BrewingStep>
 */
class BrewingStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => 5000,
            'unit' => 'g',
            'field' => 'Pale Ale Malt',
            'date' => null,
            'time' => null,
            'type' => 'Preparation',
            'status' => false,
            'brewing_id' => Brewing::factory(),
        ];
    }
}
