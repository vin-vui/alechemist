<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;

class RecipeDetailsController extends Component
{
    public $recipe;

    public function mount(Recipe $recipe)
    {

        $this->recipe = $recipe;
    }

    public function delete(Recipe $recipe)
    {
        $this->recipe = recipe::where('id', $recipe->id)->delete();
        $recipe->delete();

        return redirect()->route('recipes.index');

    }

    public function render()
    {
        return view('recipes.recipe')->layout('layouts.app');
    }
}