<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Brewing;
use Livewire\Component;

class BoilController extends Component
{
    public $recipe, $steps;

    public function mount(Brewing $brewing)
    {
        $this->brewing = Brewing::find($brewing->id);
        $this->recipe = $brewing;
        $this->steps = Step::where('type', 'Boil')->where('recipe_id', $this->recipe->id)->get();
    }

    public function render()
    {
        return view('steps.boil')->layout('layouts.app');
    }
}
