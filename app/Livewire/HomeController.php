<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\progress;

class HomeController extends Component
{
    public $recipe_count, $brewings, $recipes, $fermentRecipes;
    public function mount()
    {

        $this->brewings = Brewing::all();
        $this->recipes = Recipe::all()->where('user_id', Auth::user()->id);
        $this->recipes_count = recipe::all()->where('user_id', Auth::user()->id)->count();

        $this->fermentRecipes = Recipe::where('user_id', Auth::user()->id)
            ->whereHas('brewing', function ($query) {
                $query->where('current_step', 'ferment');
            })->get();
    }

    public function getProgressProperty()
    {
        $totalTime = $this->brewing->ferment_start + $this->brewing->BrewingSteps->time;
        $elapsedTime = now()->diffInMinutes($this->brewing->ferment_start);
        return $elapsedTime / $totalTime * 100;
    }

    public function render()
    {
        return view('home')->layout('layouts.app');
    }
}
