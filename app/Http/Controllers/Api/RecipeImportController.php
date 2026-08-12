<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidBeerXmlException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Services\BeerXmlImporter;
use Illuminate\Validation\ValidationException;

class RecipeImportController extends Controller
{
    public function __invoke(ImportRecipeRequest $request, BeerXmlImporter $importer)
    {
        try {
            $recipe = $importer->import($request->file('file')->get(), $request->user());
        } catch (InvalidBeerXmlException) {
            throw ValidationException::withMessages(['file' => __('Invalid BeerXML file')]);
        }

        return (new RecipeResource($recipe->load('steps')))
            ->response()
            ->setStatusCode(201);
    }
}
