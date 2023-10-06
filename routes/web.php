<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FileController;

use App\Livewire\BrewingController;
use App\Livewire\PreparationController;
use App\Livewire\MashController;
use App\Livewire\BoilController;
use App\Livewire\YeastController;
use App\Livewire\FermentController;

use App\Livewire\RecipesController;
use App\Livewire\RecipeDetailsController;

use App\Livewire\CreateBrewingController;
use App\Livewire\BrewingDetailsController;

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

Route::get('brewing/', BrewingController::class)->name('brewing.index');

Route::get('brewing/new', CreateBrewingController::class)->name('brewing.create');
Route::post('brewing/new', [CreateBrewingController::class, 'store'])->name('brewing.store');

Route::get('brewing/{brewing}/preparation', PreparationController::class)->name('preparation');
Route::get('brewing/{brewing}/mash', MashController::class)->name('mash');
Route::get('brewing/{brewing}/boil', BoilController::class)->name('boil');
Route::get('brewing/{brewing}/yeast', YeastController::class)->name('yeast');
Route::get('brewing/{brewing}/ferment', FermentController::class)->name('ferment');