<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBrewingStepRequest;
use App\Http\Resources\BrewingStepResource;
use App\Models\BrewingStep;

class BrewingStepController extends Controller
{
    public function update(UpdateBrewingStepRequest $request, BrewingStep $brewingStep)
    {
        $brewingStep->update($request->validated());

        return new BrewingStepResource($brewingStep);
    }
}
