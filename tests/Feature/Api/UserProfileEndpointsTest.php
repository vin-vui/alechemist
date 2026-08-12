<?php

namespace Tests\Feature\Api;

use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserProfileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_des_sessions_ne_montre_que_celles_de_l_utilisateur(): void
    {
        // phpunit.xml force SESSION_DRIVER=array pour les tests ; ce test vérifie la
        // lecture de la table `sessions`, on force donc le driver database ici.
        config(['session.driver' => 'database']);

        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->insertSession('sess-a', $user->id);
        $this->insertSession('sess-b', $user->id);
        $this->insertSession('sess-c', $other->id);

        // Referer stateful : le contrôleur lit $request->session(), qui n'est
        // disponible que si Sanctum reconnaît la requête comme provenant du front
        // (cf. EnsureFrontendRequestsAreStateful::fromFrontend, déjà exploité par
        // SanctumSpaTest::test_a_session_authenticated_frontend_reaches_the_api).
        $response = $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost')
            ->getJson('/api/user/sessions');

        $response->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['ip_address', 'user_agent', 'last_active_at', 'is_current_device']]]);
    }

    public function test_logout_others_supprime_les_autres_sessions_et_exige_le_mot_de_passe(): void
    {
        // Idem : la suppression physique des autres sessions par le contrôleur est
        // conditionnée à `config('session.driver') === 'database'`.
        config(['session.driver' => 'database']);

        $user = User::factory()->create(); // password factory = "password"
        $this->insertSession('sess-a', $user->id);
        $this->insertSession('sess-b', $user->id);

        $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost')
            ->postJson('/api/user/sessions/logout-others', ['password' => 'wrong'])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost')
            ->postJson('/api/user/sessions/logout-others', ['password' => 'password'])
            ->assertNoContent();

        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)
            ->whereIn('id', ['sess-a', 'sess-b'])->count());
    }

    public function test_supprimer_le_compte_supprime_l_utilisateur_et_toutes_ses_donnees(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->for($user)->create();
        $step = Step::factory()->for($recipe)->create();
        $brewing = Brewing::factory()->for($recipe)->create();
        $brewingStep = BrewingStep::factory()->for($brewing)->create();
        $user->createToken('test-token');

        $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost')
            ->deleteJson('/api/user', ['password' => 'wrong'])
            ->assertUnprocessable();
        $this->actingAs($user)
            ->withHeader('Referer', 'http://localhost')
            ->deleteJson('/api/user', ['password' => 'password'])
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
        $this->assertDatabaseMissing('steps', ['id' => $step->id]);
        $this->assertDatabaseMissing('brewings', ['id' => $brewing->id]);
        $this->assertDatabaseMissing('brewing_steps', ['id' => $brewingStep->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    private function insertSession(string $id, int $userId): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode('a:0:{}'),
            'last_activity' => now()->getTimestamp(),
        ]);
    }
}
