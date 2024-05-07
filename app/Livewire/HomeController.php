<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
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
        $this->recipes = Recipe::all()->where('user_id', Auth::user()->id)->pluck('id');
        $this->fermentRecipes = Recipe::where('user_id', Auth::user()->id)
            ->whereHas('brewing', function ($query) {
                $query->where('current_step', 'ferment');
            })->get();
        $this->progressBrewings = Brewing::whereIn('recipe_id', $this->recipes)->where('current_step', '<>', 'ferment')->where('current_step', '<>', 'completed')->oldest('updated_at')->get();
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
