<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumSpaTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_cookie_endpoint_is_exposed(): void
    {
        $this->get('/sanctum/csrf-cookie')
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    }

    public function test_a_session_authenticated_frontend_reaches_the_api(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        // Referer stateful (localhost figure dans les domaines stateful par défaut)
        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk();
    }

    public function test_cors_preflight_allows_the_frontend_origin_with_credentials(): void
    {
        $this->options('/api/user', [], [
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'GET',
        ])
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_the_api_group_registers_sanctum_stateful_middleware(): void
    {
        $apiGroup = app(\Illuminate\Routing\Router::class)->getMiddlewareGroups()['api'];

        $this->assertContains(
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            $apiGroup
        );
    }
}
