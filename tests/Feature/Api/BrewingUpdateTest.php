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
            ->assertNotFound();
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
            ->assertNotFound();
    }

    public function test_un_aller_retour_vers_ferment_ne_reecrit_pas_ferment_start(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'ferment',
            'ferment_start' => '2026-08-01 10:00:00',
        ]);

        $this->actingAs($user)->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'yeast'])->assertOk();
        $this->actingAs($user)->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'ferment'])->assertOk();

        $this->assertSame('2026-08-01 10:00:00', (string) $brewing->fresh()->ferment_start);
    }

    public function test_un_aller_retour_vers_completed_ne_reecrit_pas_ferment_end(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'completed',
            'ferment_start' => '2026-08-01 10:00:00',
            'ferment_end' => '2026-08-10 10:00:00',
        ]);

        $this->actingAs($user)->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'ferment'])->assertOk();
        $this->actingAs($user)->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'completed'])->assertOk();

        $this->assertSame('2026-08-10 10:00:00', (string) $brewing->fresh()->ferment_end);
    }

    public function test_le_retour_arriere_ne_nettoie_aucun_timestamp(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'completed',
            'boil_start' => '2026-07-20 09:00:00',
            'ferment_start' => '2026-08-01 10:00:00',
            'ferment_end' => '2026-08-10 10:00:00',
        ]);

        $this->actingAs($user)->patchJson("/api/brewings/{$brewing->id}", ['current_step' => 'mash'])->assertOk();

        $fresh = $brewing->fresh();
        $this->assertSame('mash', $fresh->current_step);
        $this->assertNotNull($fresh->boil_start);
        $this->assertNotNull($fresh->ferment_start);
        $this->assertNotNull($fresh->ferment_end);
    }

    public function test_start_boil_est_idempotent(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'boil',
            'boil_start' => '2026-07-20 09:00:00',
        ]);

        $this->actingAs($user)->postJson("/api/brewings/{$brewing->id}/start-boil")->assertOk();

        $this->assertSame('2026-07-20 09:00:00', (string) $brewing->fresh()->boil_start);
    }

    public function test_le_chrono_peut_etre_annule_pendant_l_etape_d_ebullition(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'boil',
            'boil_start' => '2026-07-20 09:00:00',
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertOk()
            ->assertJsonPath('data.boil_start', null);

        $this->assertNull($brewing->fresh()->boil_start);
    }

    public function test_le_chrono_ne_peut_plus_etre_annule_une_fois_l_etape_passee(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'current_step' => 'ferment',
            'boil_start' => '2026-07-20 09:00:00',
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertStatus(409);

        $this->assertNotNull($brewing->fresh()->boil_start);
    }

    public function test_cancel_boil_est_refuse_a_un_tiers(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create(['current_step' => 'boil']);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/brewings/{$brewing->id}/start-boil")
            ->assertNotFound();
    }
}
