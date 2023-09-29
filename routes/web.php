<?php


use App\Livewire\BrewingController;
use App\Livewire\RecipesController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Livewire\RecipeDetailsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/uploadFile',[FileController::class,'index'])->name('fileUpload');
Route::post('/uploadFile',[FileController::class,'store'])->name('upload.store');
Route::get('/recipes', RecipesController::class)->name('recipes.index');
Route::get('/recipes/{recipeId}', RecipeDetailsController::class)->name('recipes.show');
Route::get('/recipes/{recipe}/brewing', BrewingController::class)->name('recipe.brewing');
