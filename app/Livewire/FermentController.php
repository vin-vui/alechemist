<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Brewing;
use Livewire\Component;

class FermentController extends Component
{
    public $recipe, $steps;

    public function mount(Brewing $brewing)
    {
        $this->brewing = Brewing::find($brewing->id);
        $this->recipe = $brewing;
        $this->steps = Step::where('type', 'Primary')
        ->orWhere('type', 'Secondary')
        ->orWhere('type', 'Tertiary')
        ->orWhere('type', 'Bottle')
        ->where('recipe_id', $this->recipe->id)->get();
    }
    public function render()
    {
        return view('steps.ferment')->layout('layouts.app');
    }
}
