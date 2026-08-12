<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class FortifyHeadlessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_json_and_opens_an_api_session(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJson(['two_factor' => false]);

        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_invalid_credentials_return_422(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    public function test_logout_returns_204_and_closes_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/logout')->assertNoContent();

        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_registration_works_headlessly(): void
    {
        $this->postJson('/register', [
            'name' => 'Brewer',
            'email' => 'brewer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ])->assertCreated();

        $this->assertNotNull(User::firstWhere('email', 'brewer@example.com'));
    }

    public function test_two_factor_login_challenge_accepts_a_recovery_code(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('base32secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['valid-recovery-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJson(['two_factor' => true]);

        $this->postJson('/two-factor-challenge', ['recovery_code' => 'valid-recovery-code'])
            ->assertNoContent();

        $this->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.two_factor_enabled', true);
    }

    public function test_password_reset_flow_works_in_json(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/forgot-password', ['email' => $user->email])->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'new-password'])
            ->assertOk();
    }

    public function test_profile_information_updates_headlessly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/profile-information', [
                'name' => 'Nouveau Nom',
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertSame('Nouveau Nom', $user->fresh()->name);
    }

    public function test_password_updates_headlessly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
