<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use App\Models\Brewing_Step;
use Livewire\Component;

class BrewingController extends Component
{
    public $recipe, $brewingSteps;

    public function mount (Recipe $recipe)
    {
        $this->recipe = $recipe;
    }


    public function delete(Brewing $brewing)
    {
        $this->brewingSteps = Brewing_Step::where('brewing_id', $brewing->id)->delete();
        $brewing->delete();

    }

    public function render()
    {
        return view('brewings.index', [
            'brewings' => Brewing::where('recipe_id', $this->recipe->id)
            ->get()
        ])->layout('layouts.app');
    }
}
