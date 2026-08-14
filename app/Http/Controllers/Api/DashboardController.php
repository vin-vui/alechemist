<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrewingResource;
use App\Models\Brewing;
use App\Models\Recipe;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $recipeIds = Recipe::where('user_id', $request->user()->id)->pluck('id');

        return response()->json([
            'data' => [
                'recipe_count' => $recipeIds->count(),
                'ferment_brewings' => BrewingResource::collection(
                    Brewing::whereIn('recipe_id', $recipeIds)
                        ->where('current_step', 'ferment')
                        ->with(['recipe', 'brewingSteps'])
                        ->get()
                ),
                'in_progress_brewings' => BrewingResource::collection(
                    Brewing::whereIn('recipe_id', $recipeIds)
                        ->whereNotIn('current_step', ['ferment', 'completed'])
                        ->whereNotNull('current_step')
                        ->with('recipe')
                        ->oldest('updated_at')
                        ->get()
                ),
                // L'archive, du plus récent au plus ancien. Elle vivait sur une page
                // « Brassins » à part, qui redisait ce que le tableau de bord montrait
                // déjà : elle est repliée en bas de cet écran.
                'finished_brewings' => BrewingResource::collection(
                    Brewing::whereIn('recipe_id', $recipeIds)
                        ->where('current_step', 'completed')
                        ->with('recipe')
                        ->orderByDesc('date_start')
                        ->orderByDesc('id')
                        ->get()
                ),
            ],
        ]);
    }
}
