<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class CompletedController extends Component
{
    public $recipe, $brewing, $recipeStep;
    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->recipeSteps = Step::where('recipe_id', $this->recipe)->get();
    }

    public function note()
    {
        return redirect()->route('note', [$this->recipe, $this->brewing]);
    }

    public function render()
    {
        return view('brewings.completed')->layout('layouts.app');
    }
}
