<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrewingResource;
use App\Models\Brewing;
use App\Models\Recipe;

class BrewingController extends Controller
{
    public function index(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        return BrewingResource::collection($recipe->brewing()->get());
    }

    public function show(Brewing $brewing)
    {
        $this->authorize('view', $brewing);

        return new BrewingResource($brewing->load('brewingSteps'));
    }
}
