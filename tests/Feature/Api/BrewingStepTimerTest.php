<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingStepTimerTest extends TestCase
{
    use RefreshDatabase;

    private function mashBrewing(User $user, array $attributes = []): Brewing
    {
        return Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create($attributes + ['current_step' => 'mash']);
    }

    private function mashStep(Brewing $brewing, array $attributes = []): BrewingStep
    {
        return BrewingStep::factory()->create($attributes + [
            'brewing_id' => $brewing->id,
            'type' => 'Mash',
            'time' => 60,
            'started_at' => null,
        ]);
    }

    /**
     * Brassin arrivé à l'ébullition, dont le chrono de feu est écoulé : c'est
     * l'instant du flame-out, celui où les aromatiques entrent dans la cuve.
     */
    private function boilBrewing(User $user, array $attributes = []): Brewing
    {
        return Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create($attributes + [
                'current_step' => 'boil',
                'boil_time' => 60,
                'boil_start' => now()->subMinutes(61),
            ]);
    }

    private function aromaStep(Brewing $brewing, array $attributes = []): BrewingStep
    {
        return BrewingStep::factory()->create($attributes + [
            'brewing_id' => $brewing->id,
            'type' => 'Aroma',
            'time' => 10,
            'started_at' => null,
        ]);
    }

    public function test_le_chrono_d_un_palier_est_horodate_cote_serveur(): void
    {
        $user = User::factory()->create();
        $step = $this->mashStep($this->mashBrewing($user));

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk()
            ->assertJsonPath('data.id', $step->id);

        $this->assertNotNull($step->fresh()->started_at);
    }

    public function test_l_horodatage_du_chrono_est_expose_par_l_api(): void
    {
        $user = User::factory()->create();
        $brewing = $this->mashBrewing($user);
        $this->mashStep($brewing, ['started_at' => '2026-08-17 09:00:00']);

        $this->actingAs($user)
            ->getJson("/api/brewings/{$brewing->id}")
            ->assertOk()
            ->assertJsonPath('data.steps.0.started_at', '2026-08-17T09:00:00.000000Z');
    }

    /**
     * Un brasseur ne tient pas deux paliers a la fois : demarrer le suivant arrete
     * le precedent, sinon deux chronos tourneraient cote a cote sur le meme brassin.
     */
    public function test_demarrer_un_palier_arrete_le_chrono_des_autres(): void
    {
        $user = User::factory()->create();
        $brewing = $this->mashBrewing($user);
        $premier = $this->mashStep($brewing, ['started_at' => '2026-08-17 09:00:00']);
        $second = $this->mashStep($brewing);

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$second->id}/start")
            ->assertOk();

        $this->assertNull($premier->fresh()->started_at);
        $this->assertNotNull($second->fresh()->started_at);
    }

    public function test_le_chrono_d_un_autre_brassin_n_est_pas_arrete(): void
    {
        $user = User::factory()->create();
        $autreBrassin = $this->mashBrewing($user);
        $temoin = $this->mashStep($autreBrassin, ['started_at' => '2026-08-17 09:00:00']);
        $step = $this->mashStep($this->mashBrewing($user));

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk();

        $this->assertNotNull($temoin->fresh()->started_at);
    }

    public function test_un_chrono_deja_lance_n_est_pas_reecrit(): void
    {
        $user = User::factory()->create();
        $step = $this->mashStep($this->mashBrewing($user), ['started_at' => '2026-08-17 09:00:00']);

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk();

        $this->assertSame('2026-08-17 09:00:00', (string) $step->fresh()->started_at);
    }

    /**
     * L'ebullition a son propre chrono, porte par le brassin : un palier de houblon
     * n'a rien a demarrer.
     */
    public function test_le_chrono_ne_se_lance_que_sur_un_palier_d_empatage(): void
    {
        $user = User::factory()->create();
        $step = $this->mashStep($this->mashBrewing($user), ['type' => 'Boil']);

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertUnprocessable();

        $this->assertNull($step->fresh()->started_at);
    }

    public function test_le_chrono_d_un_palier_peut_etre_annule(): void
    {
        $user = User::factory()->create();
        $step = $this->mashStep($this->mashBrewing($user), ['started_at' => '2026-08-17 09:00:00']);

        $this->actingAs($user)
            ->deleteJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk()
            ->assertJsonPath('data.started_at', null);

        $this->assertNull($step->fresh()->started_at);
    }

    /**
     * Meme regle que le chrono d'ebullition : une fois l'etape passee, l'horodatage
     * est definitif, une consultation en arriere ne reecrit pas l'historique.
     */
    public function test_le_chrono_ne_peut_plus_etre_annule_une_fois_l_empatage_passe(): void
    {
        $user = User::factory()->create();
        $brewing = $this->mashBrewing($user, ['current_step' => 'boil']);
        $step = $this->mashStep($brewing, ['started_at' => '2026-08-17 09:00:00']);

        $this->actingAs($user)
            ->deleteJson("/api/brewing-steps/{$step->id}/start")
            ->assertStatus(409);

        $this->assertNotNull($step->fresh()->started_at);
    }

    public function test_le_chrono_d_un_aromatique_demarre_apres_l_arret_du_feu(): void
    {
        $user = User::factory()->create();
        $step = $this->aromaStep($this->boilBrewing($user));

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk();

        $this->assertNotNull($step->fresh()->started_at);
    }

    /**
     * Une infusion hors flamme ne peut pas commencer avant l'arrêt du feu. Le
     * bouton ne s'affiche pas tant que le chrono tourne, mais la règle ne peut pas
     * ne vivre que dans l'écran.
     */
    public function test_le_chrono_d_un_aromatique_est_refuse_tant_que_l_ebullition_tourne(): void
    {
        $user = User::factory()->create();
        $brewing = $this->boilBrewing($user, ['boil_start' => now()->subMinutes(10)]);
        $step = $this->aromaStep($brewing);

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertStatus(409);

        $this->assertNull($step->fresh()->started_at);
    }

    public function test_le_chrono_d_un_aromatique_est_refuse_si_l_ebullition_n_a_pas_demarre(): void
    {
        $user = User::factory()->create();
        $step = $this->aromaStep($this->boilBrewing($user, ['boil_start' => null]));

        $this->actingAs($user)
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertStatus(409);

        $this->assertNull($step->fresh()->started_at);
    }

    public function test_le_chrono_d_un_aromatique_peut_etre_annule_pendant_l_ebullition(): void
    {
        $user = User::factory()->create();
        $step = $this->aromaStep($this->boilBrewing($user), ['started_at' => now()->subMinutes(2)]);

        $this->actingAs($user)
            ->deleteJson("/api/brewing-steps/{$step->id}/start")
            ->assertOk()
            ->assertJsonPath('data.started_at', null);

        $this->assertNull($step->fresh()->started_at);
    }

    public function test_le_chrono_d_un_aromatique_ne_peut_plus_etre_annule_une_fois_l_ebullition_passee(): void
    {
        $user = User::factory()->create();
        $brewing = $this->boilBrewing($user, ['current_step' => 'yeast']);
        $step = $this->aromaStep($brewing, ['started_at' => now()->subMinutes(2)]);

        $this->actingAs($user)
            ->deleteJson("/api/brewing-steps/{$step->id}/start")
            ->assertStatus(409);

        $this->assertNotNull($step->fresh()->started_at);
    }

    public function test_le_demarrage_est_refuse_a_un_tiers(): void
    {
        $step = $this->mashStep($this->mashBrewing(User::factory()->create()));

        $this->actingAs(User::factory()->create())
            ->postJson("/api/brewing-steps/{$step->id}/start")
            ->assertNotFound();

        $this->assertNull($step->fresh()->started_at);
    }

    public function test_l_annulation_est_refusee_a_un_tiers(): void
    {
        $step = $this->mashStep(
            $this->mashBrewing(User::factory()->create()),
            ['started_at' => '2026-08-17 09:00:00'],
        );

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/brewing-steps/{$step->id}/start")
            ->assertNotFound();

        $this->assertNotNull($step->fresh()->started_at);
    }
}
