<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_before_boil_density(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create(['before_boil_density' => null]);

        $this->actingAs($user)
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 1.048])
            ->assertOk()
            ->assertJsonPath('data.before_boil_density', 1.048);

        $this->assertEquals(1.048, $recipe->fresh()->before_boil_density);
    }

    public function test_update_rejects_a_non_numeric_density(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('before_boil_density');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/recipes/{$recipe->id}", ['before_boil_density' => 1.048])
            ->assertNotFound();
    }

    public function test_owner_can_delete_a_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/recipes/{$recipe->id}")
            ->assertNoContent();

        $this->assertNull(Recipe::find($recipe->id));
    }

    public function test_delete_is_forbidden_for_non_owner(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/recipes/{$recipe->id}")
            ->assertNotFound();

        $this->assertNotNull(Recipe::find($recipe->id));
    }

    public function test_supprimer_une_recette_supprime_steps_brassins_et_brewing_steps(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $step = Step::factory()->for($recipe)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $brewingStep = BrewingStep::factory()->for($brewing)->create();

        $this->actingAs($user)->deleteJson("/api/recipes/{$recipe->id}")->assertNoContent();

        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
        $this->assertDatabaseMissing('steps', ['id' => $step->id]);
        $this->assertDatabaseMissing('brewings', ['id' => $brewing->id]);
        $this->assertDatabaseMissing('brewing_steps', ['id' => $brewingStep->id]);
    }
}
