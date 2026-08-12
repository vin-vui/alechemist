<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_brewing_step_factory_creates_full_chain(): void
    {
        $brewingStep = BrewingStep::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('brewings', 1);
        $this->assertDatabaseCount('brewing_steps', 1);
        $this->assertNotNull(Brewing::find($brewingStep->brewing_id));
    }

    public function test_step_factory_belongs_to_a_recipe(): void
    {
        $step = Step::factory()->create();

        $this->assertInstanceOf(Recipe::class, $step->recipe);
        $this->assertNotNull($step->recipe->user_id);
    }

    public function test_brewing_step_belongs_to_its_brewing(): void
    {
        $brewingStep = BrewingStep::factory()->create();

        $this->assertInstanceOf(Brewing::class, $brewingStep->brewing);
        $this->assertSame($brewingStep->brewing_id, $brewingStep->brewing->id);
    }
}
