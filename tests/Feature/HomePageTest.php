<?php

namespace Tests\Feature;

use App\Models\Brewing;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_own_in_progress_brewings_only(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $ownBrewing = Brewing::factory()
            ->for(Recipe::factory()->for($user))
            ->create(['name' => 'MON-BRASSIN-EN-COURS', 'current_step' => 'mash']);
        $foreignBrewing = Brewing::factory()
            ->create(['name' => 'BRASSIN-ETRANGER', 'current_step' => 'mash']);

        $this->actingAs($user)
            ->get(route('alechemist.home'))
            ->assertOk()
            ->assertSee('MON-BRASSIN-EN-COURS')
            ->assertDontSee('BRASSIN-ETRANGER');
    }
}
