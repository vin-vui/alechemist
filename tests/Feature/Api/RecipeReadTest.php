<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_the_users_recipes(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->count(2)->for($user)->create();
        Recipe::factory()->create(); // autre user

        $this->actingAs($user)
            ->getJson('/api/recipes')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_the_recipe_with_steps_and_brewings_count(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['name' => 'Ma Stout']);
        Step::factory()->count(3)->for($recipe)->create();

        $this->actingAs($user)
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Ma Stout')
            ->assertJsonPath('data.brewings_count', 0)
            ->assertJsonCount(3, 'data.steps');
    }

    public function test_show_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertNotFound();
    }

    public function test_show_returns_404_for_a_missing_recipe(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/recipes/999999')
            ->assertNotFound();
    }

    public function test_guests_get_401(): void
    {
        $this->getJson('/api/recipes')->assertUnauthorized();
    }
}
