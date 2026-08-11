<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Brewing>
 */
class BrewingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'volume' => 20,
            'alcohol' => '5.6',
            'initial_density' => 1.052,
            'final_density' => 1.010,
            'boil_time' => 60,
            'boil_start' => null,
            'ferment_start' => null,
            'ferment_end' => null,
            'before_boil_density' => null,
            'carbonation' => 2.4,
            'date_start' => now()->toDateString(),
            'current_step' => 'preparation',
            'note' => null,
            'recipe_id' => Recipe::factory(),
        ];
    }
}
