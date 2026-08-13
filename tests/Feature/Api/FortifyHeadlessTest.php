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

    public function test_l_email_de_reset_pointe_vers_le_front(): void
    {
        config(['app.frontend_url' => 'https://front.example']);
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/forgot-password', ['email' => $user->email])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $url = call_user_func($notification::$createUrlCallback, $user, $notification->token);

            return $url === 'https://front.example/reset-password/'.$notification->token.'?email='.urlencode($user->email);
        });
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

    public function test_password_update_is_rejected_when_the_current_password_is_wrong(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_update_is_rejected_when_the_confirmation_does_not_match(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/user/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'wrong-confirmation',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_confirmation_succeeds_with_the_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/user/confirm-password', ['password' => 'password'])
            ->assertCreated();
    }

    public function test_password_confirmation_fails_with_an_incorrect_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/user/confirm-password', ['password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_two_factor_authentication_can_be_enabled_headlessly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication')
            ->assertOk();

        $user = $user->fresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertCount(8, $user->recoveryCodes());
    }

    public function test_two_factor_recovery_codes_can_be_regenerated_headlessly(): void
    {
        $user = User::factory()->create();
        $session = ['auth.password_confirmed_at' => time()];

        $this->actingAs($user)->withSession($session)
            ->postJson('/user/two-factor-authentication')
            ->assertOk();

        $originalCodes = $user->fresh()->recoveryCodes();

        $this->actingAs($user)->withSession($session)
            ->postJson('/user/two-factor-recovery-codes')
            ->assertOk();

        $this->assertCount(8, array_diff($originalCodes, $user->fresh()->recoveryCodes()));
    }

    public function test_two_factor_authentication_can_be_disabled_headlessly(): void
    {
        $user = User::factory()->create();
        $session = ['auth.password_confirmed_at' => time()];

        $this->actingAs($user)->withSession($session)
            ->postJson('/user/two-factor-authentication')
            ->assertOk();

        $this->assertNotNull($user->fresh()->two_factor_secret);

        $this->actingAs($user)->withSession($session)
            ->deleteJson('/user/two-factor-authentication')
            ->assertOk();

        $this->assertNull($user->fresh()->two_factor_secret);
    }
}
