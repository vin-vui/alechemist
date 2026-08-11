<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => 'American IPA',
            'method' => 'All Grain',
            'ferment' => 'Ale',
            'volume' => 20,
            'efficiency' => '72.0',
            'color' => '8.2',
            'bitterness' => '45.3',
            'alcohol' => '5.6',
            'boil_time' => 60,
            'initial_density' => 1.052,
            'final_density' => 1.010,
            'before_boil_density' => null,
            'carbonation' => 2.4,
            'user_id' => User::factory(),
        ];
    }
}
