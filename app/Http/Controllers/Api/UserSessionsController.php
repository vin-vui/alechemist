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

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_active_at' => Carbon::createFromTimestamp($session->last_activity)->toJSON(),
                'is_current_device' => $session->id === $request->session()->getId(),
            ])
            ->values();

        return response()->json(['data' => $sessions]);
    }

    public function logoutOthers(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        Auth::guard('web')->logoutOtherDevices($request->input('password'));

        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return response()->noContent();
    }
}
