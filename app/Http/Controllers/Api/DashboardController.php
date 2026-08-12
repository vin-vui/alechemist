<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrewingResource;
use App\Http\Resources\RecipeResource;
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
                'ferment_recipes' => RecipeResource::collection(
                    Recipe::where('user_id', $request->user()->id)
                        ->whereHas('brewing', fn ($query) => $query->where('current_step', 'ferment'))
                        ->get()
                ),
                'in_progress_brewings' => BrewingResource::collection(
                    Brewing::whereIn('recipe_id', $recipeIds)
                        ->whereNotIn('current_step', ['ferment', 'completed'])
                        ->oldest('updated_at')
                        ->with('recipe')
                        ->get()
                ),
            ],
        ]);
    }
}
