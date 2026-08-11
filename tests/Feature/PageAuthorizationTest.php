<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function pageUrls(Recipe $recipe, Brewing $brewing): array
    {
        return [
            route('recipes.show', $recipe),
            route('brewing.index', $recipe),
            route('brewing.create', $recipe),
            route('preparation', [$recipe, $brewing]),
            route('mash', [$recipe, $brewing]),
            route('boil', [$recipe, $brewing]),
            route('yeast', [$recipe, $brewing]),
            route('ferment', [$recipe, $brewing]),
            route('completed', [$recipe, $brewing]),
            route('note', [$recipe, $brewing]),
        ];
    }

    public function test_all_pages_are_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $other = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        foreach ($this->pageUrls($recipe, $brewing) as $url) {
            $this->actingAs($other)->get($url)->assertForbidden();
        }
    }

    public function test_all_pages_are_accessible_for_owner(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        foreach ($this->pageUrls($recipe, $brewing) as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_brewing_not_belonging_to_route_recipe_is_not_found(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $otherRecipe = Recipe::factory()->for($owner)->create();
        $brewing = Brewing::factory()->for($recipe)->create();

        $this->actingAs($owner)
            ->get(route('mash', [$otherRecipe, $brewing]))
            ->assertNotFound();
    }
}
