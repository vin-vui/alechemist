<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use App\Models\BrewingStep;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompletedController extends Component
{
    use AuthorizesRequests;

    public $recipe, $brewing, $recipeSteps;

    public function mount(Recipe $recipe, Brewing $brewing)
    {
        abort_unless($brewing->recipe_id === $recipe->id, 404);
        $this->authorize('view', $recipe);

        $this->recipe = $recipe;
        $this->brewing = $brewing;
        $this->recipeSteps = Step::where('recipe_id', $recipe->id)->get();
    }

    public function delete(Brewing $brewing)
    {
        $this->brewingSteps = BrewingStep::where('brewing_id', $brewing->id)->delete();
        $brewing->delete();

        return redirect()->route('brewing.index', $this->recipe);

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
