<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_the_brewings_of_a_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        Brewing::factory()->count(2)->for($recipe)->create();

        $this->actingAs($user)
            ->getJson("/api/recipes/{$recipe->id}/brewings")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_is_forbidden_on_someone_elses_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/recipes/{$recipe->id}/brewings")
            ->assertNotFound();
    }

    public function test_show_returns_the_brewing_with_all_its_steps(): void
    {
        $user = User::factory()->create();
        $brewing = Brewing::factory()
            ->for(Recipe::factory()->for($user)->create(['name' => 'American IPA']))
            ->create(['current_step' => 'mash']);
        BrewingStep::factory()->count(3)->create(['brewing_id' => $brewing->id]);

        $this->actingAs($user)
            ->getJson("/api/brewings/{$brewing->id}")
            ->assertOk()
            ->assertJsonPath('data.current_step', 'mash')
            ->assertJsonCount(3, 'data.steps')
            // L'en-tete d'un ecran de brassin nomme sa recette dans le lien de retour.
            ->assertJsonPath('data.recipe.name', 'American IPA');
    }

    public function test_show_is_forbidden_on_someone_elses_brewing(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/brewings/{$brewing->id}")
            ->assertNotFound();
    }

    public function test_les_timestamps_d_etapes_sont_serialises_en_iso_8601(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $brewing = Brewing::factory()->for($recipe)->create([
            'boil_start' => '2026-07-20 09:00:00',
            'date_start' => '2026-07-20',
        ]);

        $response = $this->actingAs($user)->getJson("/api/brewings/{$brewing->id}");

        $response->assertOk()
            ->assertJsonPath('data.boil_start', '2026-07-20T09:00:00.000000Z')
            ->assertJsonPath('data.date_start', '2026-07-20');
    }
}
