<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class MashController extends Component
{
    public $recipe, $steps, $brewing;

    public function mount(Brewing $brewing)
    {
        $this->brewing = Brewing::find($brewing->id);
        $this->recipe = $brewing->recipe_id;
        $this->steps = Step::where('type', 'Mash')->where('recipe_id', $this->recipe)->get();
    }

    public function render()
    {
        return view('steps.mash')->layout('layouts.app');
    }
}
