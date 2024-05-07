<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class RecipeDetailsController extends Component
{
    public $recipe, $inProgressCount;
    public $isOpen = false;
    public $before_boil_density;

    public function mount(Recipe $recipe)
    {
        $this->recipe = $recipe;
        $this->inProgressCount = Brewing::where('recipe_id', $this->recipe->id)->count();
    }

    public function render()
    {
        return view('recipes.recipe')->layout('layouts.app');
    }

    public function update()
    {
        $this->before_boil_density = $this->recipe->before_boil_density;
        $this->openModal();
    }

    public function edit()
    {
        $recipe = $this->recipe;
        $recipe->before_boil_density = $this->before_boil_density;
        $recipe->save();

        $this->closeModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function back()
    {
        return redirect()->route('recipes.index');
    }

    public function delete(Recipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('recipes.index');
    }

}