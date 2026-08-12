<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\BrewingStep;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        return RecipeResource::collection(
            Recipe::where('user_id', $request->user()->id)
                ->withCount('brewing')
                ->get()
        );
    }

    public function show(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        return new RecipeResource($recipe->load('steps')->loadCount('brewing'));
    }

    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $recipe->update($request->validated());

        return new RecipeResource($recipe);
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        DB::transaction(function () use ($recipe) {
            BrewingStep::whereIn('brewing_id', $recipe->brewing()->pluck('id'))->delete();
            $recipe->brewing()->delete();
            $recipe->steps()->delete();
            $recipe->delete();
        });

        return response()->noContent();
    }
}
