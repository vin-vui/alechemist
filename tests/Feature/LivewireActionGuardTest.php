<?php

namespace Tests\Feature;

use App\Livewire\BrewingController;
use App\Livewire\PreparationController;
use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireActionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_change_rejects_step_of_another_brewing(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $foreignStep = BrewingStep::factory()->create(); // autre brewing, autre user

        Livewire::actingAs($user)
            ->test(PreparationController::class, ['recipe' => $recipe, 'brewing' => $brewing])
            ->call('statusChange', $foreignStep->id)
            ->assertStatus(403);

        $this->assertFalse((bool) $foreignStep->fresh()->status);
    }

    public function test_status_change_toggles_own_step(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $step = BrewingStep::factory()->create(['brewing_id' => $brewing->id]);

        Livewire::actingAs($user)
            ->test(PreparationController::class, ['recipe' => $recipe, 'brewing' => $brewing])
            ->call('statusChange', $step->id);

        $this->assertTrue((bool) $step->fresh()->status);
    }

    public function test_brewing_delete_rejects_brewing_of_another_recipe(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $foreignBrewing = Brewing::factory()->create(); // autre recipe, autre user

        Livewire::actingAs($user)
            ->test(BrewingController::class, ['recipe' => $recipe])
            ->call('delete', $foreignBrewing->id)
            ->assertStatus(403);

        $this->assertNotNull($foreignBrewing->fresh());
    }
}
