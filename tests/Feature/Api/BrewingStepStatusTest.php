<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingStepStatusTest extends TestCase
{
    use RefreshDatabase;

    private function ownedStep(User $user, array $attributes = []): BrewingStep
    {
        $brewing = Brewing::factory()->for(Recipe::factory()->for($user))->create();

        return BrewingStep::factory()->create(['brewing_id' => $brewing->id] + $attributes);
    }

    public function test_owner_checks_and_unchecks_a_step(): void
    {
        $user = User::factory()->create();
        $step = $this->ownedStep($user, ['status' => false]);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => true])
            ->assertOk()
            ->assertJsonPath('data.status', true);

        $this->assertTrue((bool) $step->fresh()->status);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => false])
            ->assertOk()
            ->assertJsonPath('data.status', false);

        $this->assertFalse((bool) $step->fresh()->status);
    }

    public function test_status_is_required_and_boolean(): void
    {
        $user = User::factory()->create();
        $step = $this->ownedStep($user);

        $this->actingAs($user)
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => 'maybe'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $step = $this->ownedStep($owner, ['status' => false]);

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/brewing-steps/{$step->id}", ['status' => true])
            ->assertNotFound();

        $this->assertFalse((bool) $step->fresh()->status);
    }
}
