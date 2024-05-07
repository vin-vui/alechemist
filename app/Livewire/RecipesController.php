<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RecipesController extends Component
{
    public $recipes;

    public function mount()
    {
        $this->recipes = Recipe::where('user_id', Auth::id())->get();
    }

    public function render()
    {
        return view('recipes.index')->layout('layouts.app');
    }

}
