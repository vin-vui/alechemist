<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_splits_ferment_recipes_and_in_progress_brewings(): void
    {
        $user = User::factory()->create();

        $fermenting = Recipe::factory()->for($user)->create(['name' => 'En fermentation']);
        Brewing::factory()->for($fermenting)->create(['current_step' => 'ferment']);

        $inProgress = Recipe::factory()->for($user)->create();
        Brewing::factory()->for($inProgress)->create(['current_step' => 'mash', 'name' => 'Brassin actif']);

        $done = Recipe::factory()->for($user)->create();
        Brewing::factory()->for($done)->create(['current_step' => 'completed']);

        $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(1, 'data.ferment_recipes')
            ->assertJsonPath('data.ferment_recipes.0.name', 'En fermentation')
            ->assertJsonCount(1, 'data.in_progress_brewings')
            ->assertJsonPath('data.in_progress_brewings.0.name', 'Brassin actif')
            ->assertJsonPath('data.in_progress_brewings.0.recipe.id', $inProgress->id);
    }

    public function test_dashboard_never_leaks_someone_elses_data(): void
    {
        $other = Recipe::factory()->create();
        Brewing::factory()->for($other)->create(['current_step' => 'ferment']);
        Brewing::factory()->for($other)->create(['current_step' => 'mash']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'data.ferment_recipes')
            ->assertJsonCount(0, 'data.in_progress_brewings');
    }
}
