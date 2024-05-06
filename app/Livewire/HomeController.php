<?php

namespace App\Livewire;

use App\Models\Recipe;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Component
{
    public $recipe_count, $recipes, $fermentRecipes, $fermentTime;

    /**
     * Mount the component.
     * @return void
     */
    public function mount(): void
    {
        $this->recipes = Recipe::all()->where('user_id', Auth::user()->id);
        $this->recipes_count = recipe::all()->where('user_id', Auth::user()->id)->count();
        $this->fermentRecipes = Recipe::where('user_id', Auth::user()->id)
            ->whereHas('brewing', function ($query) {
                $query->where('current_step', 'ferment');
            })->get();
        $this->progressRecipes = Recipe::where('user_id', Auth::user()->id)
            ->whereHas('brewing', function ($query) {
                $query->whereNotIn('current_step', ['ferment', 'completed']);
            })->get();
    }

    /**
     * Render the component.
     * @return View
     */
    public function render(): View
    {
        return view('home')->layout('layouts.app');
    }
}
