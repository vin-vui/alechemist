<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;

class RecipesController extends Component
{

    public function render()
    {
        return view('recipes.index', [
            'recipes' => Recipe::all()
        ])->layout('layouts.app');
    }
}
