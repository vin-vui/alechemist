<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function ownedBrewing(User $user, array $attributes = []): Brewing
    {
        return Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create($attributes);
    }

    public function test_note_and_boil_time_can_be_updated(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['boil_time' => 60]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['note' => 'Belle mousse', 'boil_time' => 75])
            ->assertOk()
            ->assertJsonPath('data.note', 'Belle mousse')
            ->assertJsonPath('data.boil_time', 75);
    }

    public function test_moving_to_ferment_sets_ferment_start(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['current_step' => 'yeast', 'ferment_start' => null]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'ferment'])
            ->assertOk()
            ->assertJsonPath('data.current_step', 'ferment');

        $this->assertNotNull($brewing->fresh()->ferment_start);
    }

    public function test_moving_to_completed_sets_ferment_end(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['current_step' => 'ferment', 'ferment_end' => null]);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'completed'])
            ->assertOk();

        $this->assertNotNull($brewing->fresh()->ferment_end);
    }

    public function test_an_unknown_step_is_rejected(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user);

        $this->actingAs($user)
            ->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'teleportation'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_step');
    }

    public function test_update_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/brewings/{$brewing->id}", ['note' => 'intrusion'])
            ->assertForbidden();
    }

    public function test_start_boil_stamps_boil_start_server_side(): void
    {
        $user = User::factory()->create();
        $brewing = $this->ownedBrewing($user, ['boil_start' => null]);

        $this->actingAs($user)
            ->postJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertOk();

        $this->assertNotNull($brewing->fresh()->boil_start);
    }

    public function test_start_boil_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertForbidden();
    }
}
