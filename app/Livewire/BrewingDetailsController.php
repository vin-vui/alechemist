<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class BrewingDetailsController extends Component
{
    public $brewing, $recipe, $steps;

    public function mount($brewingId)
    {

    $this->brewing = Brewing::find($brewingId);
    $this->recipe = Recipe::where('id', $brewingId)->get();
    $this->steps = Step::whereRelation('Recipe', 'id', $this->recipe['0']['id'])->get();
    }

    public function render()
    {
        return view('steps.preparation')->layout('layouts.app');
    }
}
