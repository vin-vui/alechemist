<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use App\Models\BrewingStep;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class BrewingController extends Component
{
    use AuthorizesRequests;

    public $recipe, $brewings, $brewingSteps;

    public function mount(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $this->recipe = $recipe;
        $this->brewings = Brewing::where('recipe_id', $this->recipe->id)->get();
    }

    public function render()
    {
        return view('brewings.index')->layout('layouts.app');
    }

    public function delete(Brewing $brewing)
    {
        abort_unless($brewing->recipe_id === $this->recipe->id, 403);

        BrewingStep::where('brewing_id', $brewing->id)->delete();
        $brewing->delete();
    }

}
