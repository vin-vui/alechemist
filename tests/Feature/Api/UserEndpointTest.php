<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_authenticated_user_is_returned_as_a_stable_resource(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.two_factor_enabled', false)
            ->assertJsonMissingPath('data.password');
    }

    public function test_guests_get_401(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_la_reponse_ne_contient_pas_profile_photo_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'two_factor_enabled']])
            ->assertJsonMissingPath('data.profile_photo_url');
    }
}
