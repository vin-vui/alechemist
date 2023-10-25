<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class HomeController extends Component
{
    public $recipe_count, $recipes, $fermentRecipes, $fermentTime;
    public function mount()
    {

        $this->recipes = Recipe::all()->where('user_id', Auth::user()->id);
        $this->recipes_count = recipe::all()->where('user_id', Auth::user()->id)->count();

        $this->fermentRecipes = Recipe::where('user_id', Auth::user()->id)
            ->whereHas('brewing', function ($query) {
                $query->where('current_step', 'ferment');
            })->get();

    }




    public function render()
    {
        return view('home')->layout('layouts.app');
    }
}
