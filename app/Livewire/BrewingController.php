<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use App\Models\BrewingStep;
use Livewire\Component;

class BrewingController extends Component
{
    public $recipe, $brewings, $brewingSteps;

    public function mount (Recipe $recipe)
    {
        $this->recipe = $recipe;
        $this->brewings = Brewing::where('recipe_id', $this->recipe->id)->get();
    }

    public function render()
    {
        return view('brewings.index')->layout('layouts.app');
    }

    public function delete(Brewing $brewing)
    {
        $this->brewingSteps = BrewingStep::where('brewing_id', $brewing->id)->delete();
        $brewing->delete();
    }

}
