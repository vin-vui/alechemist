<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_is_authorized_on_recipe(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->create();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $recipe), "owner should {$ability}");
            $this->assertFalse(Gate::forUser($other)->allows($ability, $recipe), "other should not {$ability}");
        }
    }

    public function test_brewing_authorization_follows_recipe_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $brewing = Brewing::factory()->for(Recipe::factory()->for($owner))->create();

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $brewing), "owner should {$ability}");
            $this->assertFalse(Gate::forUser($other)->allows($ability, $brewing), "other should not {$ability}");
        }
    }
}
