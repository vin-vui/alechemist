<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class PreparationController extends Component
{
    public $recipe, $steps, $brewing;

    public function mount(Brewing $brewing)
    {
        $this->brewing = Brewing::find($brewing->id);
        $this->recipe = $brewing->recipe_id;
        $this->steps = Step::where('type', 'Preparation')->where('recipe_id', $this->recipe)->get();
    }



    public function render()
    {
        return view('steps.preparation')->layout('layouts.app');
    }
}
