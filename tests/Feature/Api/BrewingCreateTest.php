<?php

namespace Tests\Feature\Api;

use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_a_brewing_with_copied_recipe_steps(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['volume' => 20, 'boil_time' => 60]);
        Step::factory()->count(3)->for($recipe)->create(['type' => 'Preparation']);

        $response = $this->actingAs($user)
            ->postJson("/api/recipes/{$recipe->id}/brewings", [
                'name' => 'Brassin nº1',
                'note' => null,
                'date_start' => '2026-08-12',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Brassin nº1')
            ->assertJsonPath('data.current_step', 'preparation')
            ->assertJsonPath('data.boil_time', 60)
            ->assertJsonCount(3, 'data.steps');

        // assertEquals (lâche) : volume est une colonne float, SQLite peut renvoyer 20.0
        $this->assertEquals(20, $response->json('data.volume'));

        $brewingId = $response->json('data.id');
        $this->assertSame(3, BrewingStep::where('brewing_id', $brewingId)->count());
        $this->assertSame(0, (int) BrewingStep::where('brewing_id', $brewingId)->where('status', true)->count());
    }

    public function test_store_requires_a_name_and_a_start_date(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->postJson("/api/recipes/{$recipe->id}/brewings", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'date_start']);
    }

    public function test_store_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/recipes/{$recipe->id}/brewings", [
                'name' => 'Intrusion',
                'date_start' => '2026-08-12',
            ])
            ->assertForbidden();
    }
}
