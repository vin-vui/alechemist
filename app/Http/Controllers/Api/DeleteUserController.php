<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteUserController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            $recipeIds = Recipe::where('user_id', $user->id)->pluck('id');
            $brewingIds = Brewing::whereIn('recipe_id', $recipeIds)->pluck('id');

            BrewingStep::whereIn('brewing_id', $brewingIds)->delete();
            Brewing::whereIn('id', $brewingIds)->delete();
            Step::whereIn('recipe_id', $recipeIds)->delete();
            Recipe::whereIn('id', $recipeIds)->delete();

            $user->tokens()->delete();

            // Sessions résiduelles (IP, user agent, payload) : donnée personnelle qui
            // survivrait sinon à la suppression du compte. Gardé par le driver : la
            // table `sessions` n'existe/n'est pertinente qu'avec SESSION_DRIVER=database.
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            // On supprime une instance fraîche (et non $user, mise en cache par le
            // guard) : Eloquent::delete() met exists=false sur l'instance supprimée ;
            // si c'était $user, le cycleRememberToken() de Auth::logout() ci-dessous
            // ferait un save() -> INSERT (résurrection de la ligne) au lieu d'un
            // simple no-op UPDATE. Voir vendor/laravel/jetstream DeleteUserForm qui
            // applique le même contournement via Auth::user()->fresh().
            $user->fresh()->delete();
        });

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Le guard resolu par defaut pour cette requete API (Sanctum, via
        // auth:sanctum) est un RequestGuard qui met en cache l'utilisateur resolu
        // (par $request->user() plus haut) pour toute la duree de la requete et n'a
        // pas de notion de logout() : Auth::guard('web')->logout() ne l'affecte pas.
        // Sans les deux lignes suivantes, la persistance de session en fin de
        // requete (Illuminate\Session\Middleware\StartSession::terminate) re-ecrirait
        // une ligne `sessions` avec le user_id du compte pourtant deja supprime
        // (contournant le nettoyage juste au-dessus). forgetGuards() vide le cache
        // interne d'AuthManager, mais le conteneur a lui-meme deja mis en cache
        // l'instance singleton 'auth.driver' (alias de Guard::class) : sans
        // forgetInstance(), une future resolution de Guard::class renverrait encore
        // l'ancienne instance figee. Les deux ensemble forcent une re-resolution a
        // vide, la session etant deja invalidee et l'utilisateur deja supprime.
        Auth::forgetGuards();
        app()->forgetInstance('auth.driver');

        return response()->noContent();
    }
}
