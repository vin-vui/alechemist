<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_dashboard_expose_compteur_fermentations_et_brassins_en_cours(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $fermenting = Brewing::factory()->for($recipe)->create([
            'current_step' => 'ferment',
            'ferment_start' => '2026-08-01 10:00:00',
        ]);
        BrewingStep::factory()->for($fermenting)->create(['type' => 'Primary', 'time' => 14]);
        $inProgress = Brewing::factory()->for($recipe)->create(['current_step' => 'mash']);
        Brewing::factory()->for($recipe)->create(['current_step' => 'completed']);

        $response = $this->actingAs($user)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.recipe_count', 1)
            ->assertJsonCount(1, 'data.ferment_brewings')
            ->assertJsonPath('data.ferment_brewings.0.id', $fermenting->id)
            ->assertJsonPath('data.ferment_brewings.0.recipe.id', $recipe->id)
            ->assertJsonCount(1, 'data.ferment_brewings.0.steps')
            ->assertJsonCount(1, 'data.in_progress_brewings')
            ->assertJsonPath('data.in_progress_brewings.0.id', $inProgress->id)
            ->assertJsonPath('data.in_progress_brewings.0.recipe.id', $recipe->id);
    }

    public function test_dashboard_never_leaks_someone_elses_data(): void
    {
        $other = Recipe::factory()->create();
        Brewing::factory()->for($other)->create(['current_step' => 'ferment']);
        Brewing::factory()->for($other)->create(['current_step' => 'mash']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.recipe_count', 0)
            ->assertJsonCount(0, 'data.ferment_brewings')
            ->assertJsonCount(0, 'data.in_progress_brewings');
    }
}
