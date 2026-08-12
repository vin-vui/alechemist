<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrewingDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_deletes_the_brewing_and_its_steps(): void
    {
        $user = User::factory()->create();
        $brewing = Brewing::factory()->for(Recipe::factory()->for($user))->create();
        BrewingStep::factory()->count(2)->create(['brewing_id' => $brewing->id]);

        $this->actingAs($user)
            ->deleteJson("/api/brewings/{$brewing->id}")
            ->assertNoContent();

        $this->assertNull(Brewing::find($brewing->id));
        $this->assertSame(0, BrewingStep::where('brewing_id', $brewing->id)->count());
    }

    public function test_delete_is_forbidden_for_non_owner(): void
    {
        $brewing = Brewing::factory()->for(Recipe::factory())->create();
        BrewingStep::factory()->count(2)->create(['brewing_id' => $brewing->id]);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/brewings/{$brewing->id}")
            ->assertNotFound();

        $this->assertNotNull(Brewing::find($brewing->id));
        $this->assertSame(2, BrewingStep::where('brewing_id', $brewing->id)->count());
    }
}
