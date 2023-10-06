<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Brewing;
use Livewire\Component;

class YeastController extends Component
{
    public $recipe, $steps;

    public function mount(Brewing $brewing)
    {
        $this->brewing = Brewing::find($brewing->id);
        $this->recipe = $brewing;
        $this->steps = Step::where('type', 'Yeast')->where('recipe_id', $this->recipe->id)->get();
    }

    public function render()
    {
        return view('steps.yeast')->layout('layouts.app');
    }
}
