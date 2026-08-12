<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

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
}
