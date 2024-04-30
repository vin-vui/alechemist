<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class RecipeDetailsController extends Component
{
    public $recipe;

    public function mount(Recipe $recipe)
    {
        $this->recipe = $recipe;
        $this->inProgressCount = Brewing::where('recipe_id', $this->recipe->id)->count();
    }

    public function render()
    {
        return view('recipes.recipe')->layout('layouts.app');
    }

    public function back()
    {
        return redirect()->route('recipes.index');
    }

    public function delete(Recipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('recipes.index');
    }

}