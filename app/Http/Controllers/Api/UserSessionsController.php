<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserSessionsController extends Controller
{
    public function index(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return response()->json(['data' => []]);
        }

        // $request->session() n'est peuplé que si Sanctum a reconnu la requête
        // comme provenant du front (en-tête Referer/Origin stateful, cf.
        // EnsureFrontendRequestsAreStateful::fromFrontend). Un client API "jeton
        // pur" sans un tel en-tête n'a pas de session : on ne casse pas la liste
        // pour autant, is_current_device sera simplement toujours false.
        $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_active_at' => Carbon::createFromTimestamp($session->last_activity)->toJSON(),
                'is_current_device' => $session->id === $currentSessionId,
            ])
            ->values();

        return response()->json(['data' => $sessions]);
    }

    public function logoutOthers(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        Auth::guard('web')->logoutOtherDevices($request->input('password'));

        if (config('session.driver') === 'database') {
            $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;

            // where('id', '!=', null) ne matcherait aucune ligne (comparaison à NULL
            // toujours fausse en SQL) : sans session courante identifiable, on
            // supprime toutes les sessions de l'utilisateur plutôt que de ne rien
            // faire silencieusement.
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->when($currentSessionId !== null, fn ($query) => $query->where('id', '!=', $currentSessionId))
                ->delete();
        }

        return response()->noContent();
    }
}
