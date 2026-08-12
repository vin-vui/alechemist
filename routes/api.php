<?php

use App\Http\Controllers\Api\BrewingController;
use App\Http\Controllers\Api\BrewingStepController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\RecipeImportController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => new UserResource($request->user()));

    Route::get('/dashboard', DashboardController::class);

    Route::post('/recipes/import', RecipeImportController::class);
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
    Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);

    Route::get('/recipes/{recipe}/brewings', [BrewingController::class, 'index']);
    Route::post('/recipes/{recipe}/brewings', [BrewingController::class, 'store']);
    Route::get('/brewings/{brewing}', [BrewingController::class, 'show']);
    Route::patch('/brewings/{brewing}', [BrewingController::class, 'update']);
    Route::post('/brewings/{brewing}/start-boil', [BrewingController::class, 'startBoil']);
    Route::delete('/brewings/{brewing}', [BrewingController::class, 'destroy']);

    Route::patch('/brewing-steps/{brewingStep}', [BrewingStepController::class, 'update']);
});
