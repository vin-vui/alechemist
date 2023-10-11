<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use App\Models\BrewingStep;

class CreateBrewingController extends Component
{
    public $name, $date_start, $note, $brewing_id, $recipe, $steps, $brewingStep;

    public function mount (Recipe $recipe)
    {
        $this->recipe = $recipe;
        $this->steps = $recipe->steps;
        $this->date_start = Carbon::today()->format('Y-m-d');
    }

    public function store()
    {
        // champ requis dans ma vue brewing.create
        $validated = $this->validate([
            'name' => 'required|max:255',
            'note' => 'nullable',
            'date_start' => 'required',
        ]);
        // dans ma variable $brewing, j'utilise ma fonction newBrewing qui insère mes données dans ma db, avec les valeur requise et celles qui
        // proviennent de ma recette, et je retourne ma varible $brewing
        $brewing = $this->newBrewing($validated);
        // J'utilise ma fontion bewingSteps pour insérer mes differentes étapes dans mon nouveau brassin depuis ma recette
        $this->brewingSteps($brewing);

        return redirect()->route('brewing.index', $this->recipe);
    }

    private function newBrewing($validated)
    {
        $brewing = new Brewing;
        $brewing->name = $validated['name'];
        $brewing->note = $validated['note'];
        $brewing->date_start = $validated['date_start'];
        $brewing->volume = $this->recipe->volume;
        $brewing->alcohol = $this->recipe->alcohol;
        $brewing->initial_density = $this->recipe->initial_density;
        $brewing->final_density = $this->recipe->final_density;
        $brewing->boil_time = $this->recipe->boil_time;
        $brewing->before_boil_density = $this->recipe->before_boil_density;
        $brewing->carbonation = $this->recipe->carbonation;
        $brewing->recipe_id = $this->recipe->id;

        $brewing->save();
        return $brewing;
    }

    private function brewingSteps($brewing)
    {
        foreach ($this->steps as $step) {
            $brewingStep = new BrewingStep;
            $brewingStep->quantity = $step->quantity;
            $brewingStep->unit = $step->unit;
            $brewingStep->field = $step->field;
            $brewingStep->date = $step->date;
            $brewingStep->time = $step->time;
            $brewingStep->type = $step->type;
            $brewingStep->brewing_id = $brewing->id;
            $brewingStep->save();
        }
    }

    public function render()
    {
        return view('brewings.create')->layout('layouts.app');
    }
}
