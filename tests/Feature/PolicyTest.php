<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use App\Policies\BrewingPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_is_authorized_on_recipe(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->create();
        $policy = new RecipePolicy();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue($policy->$ability($owner, $recipe)->allowed(), "owner should {$ability}");
            $this->assertTrue($policy->$ability($other, $recipe)->denied(), "other should not {$ability}");
        }
    }

    public function test_brewing_authorization_follows_recipe_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $brewing = Brewing::factory()->for(Recipe::factory()->for($owner))->create();
        $policy = new BrewingPolicy();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue($policy->$ability($owner, $brewing)->allowed(), "owner should {$ability}");
            $this->assertTrue($policy->$ability($other, $brewing)->denied(), "other should not {$ability}");
        }
    }
}
