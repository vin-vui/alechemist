<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RecipesController extends Component
{

    public function render()
    {
        return view('recipes.index', [
            'recipes' => Recipe::all()->where('user_id', Auth::user()->id)
        ])->layout('layouts.app');
    }
}
