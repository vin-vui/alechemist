<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrewingRequest;
use App\Http\Resources\BrewingResource;
use App\Models\Brewing;
use App\Models\BrewingStep;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreBrewingRequest $request, Recipe $recipe)
    {
        $brewing = DB::transaction(function () use ($request, $recipe) {
            $brewing = Brewing::create([
                'name' => $request->validated('name'),
                'note' => $request->validated('note'),
                'date_start' => $request->validated('date_start'),
                'volume' => $recipe->volume,
                'alcohol' => $recipe->alcohol,
                'initial_density' => $recipe->initial_density,
                'final_density' => $recipe->final_density,
                'boil_time' => $recipe->boil_time,
                'before_boil_density' => $recipe->before_boil_density,
                'carbonation' => $recipe->carbonation,
                'recipe_id' => $recipe->id,
                'current_step' => 'preparation',
            ]);

            foreach ($recipe->steps as $step) {
                BrewingStep::create([
                    'quantity' => $step->quantity,
                    'unit' => $step->unit,
                    'field' => $step->field,
                    'date' => null, // parité web : Step n'a pas de colonne date
                    'time' => $step->time,
                    'type' => $step->type,
                    'brewing_id' => $brewing->id,
                ]);
            }

            return $brewing;
        });

        return (new BrewingResource($brewing->load('brewingSteps')))
            ->response()
            ->setStatusCode(201);
    }
}
