<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;

class RecipeDetailsController extends Component
{
    public $recipe;

    public function mount($recipeId)
    {

    $this->recipe = Recipe::find($recipeId);
    }

    public function render()
    {
        return view('recipes.recipe')->layout('layouts.app');
    }
}
