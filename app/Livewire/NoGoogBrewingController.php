<?php

namespace App\Livewire;

use App\Models\Step;
use App\Models\Recipe;
use Livewire\Component;

class NoGoodBrewingController extends Component
{
    public $recipe, $steps;

    public function mount(Recipe $recipe)
    {
        // $this->recipe = $recipe;
        // $this->steps = $recipe->steps;
        // $this->steps = Step::where('type', 'Preparation')->where('recipe_id', $this->recipe->id)->get();
    }
    public function render()
    {
        return view('brewings.index')->layout('layouts.app');
    }
}
    // public $recipe;
    // Public $steps = [];
    // public $currentStep = 0;
    // public $currentStepType = 'Preparation';

    // public function mount(Recipe $recipe)
    // {
    //     $this->recipe = $recipe;
    //     $this->steps = $recipe->steps;
    // }

    // public function loadSteps()
    // {
    //     $this->steps = Step::where('recipe_id', $this->recipe->id)
    //         ->where('type', $this->currentStepType)
    //         ->get();
    // }

    // public function toggleNextStep()
    // {
    //     $this->currentStep++;

    //     if ($this->currentStep >= count($this->steps)) {

    //         $this->currentStep = 0;
    //         switch ($this->currentStepType) {
    //             case 'Preparation':
    //                 $this->currentStepType = 'Mash';
    //                 break;
    //             case 'Mash':
    //                 $this->currentStepType = 'Boil';
    //                 break;
    //             case 'Boil':
    //                 $this->currentStepType = 'Yeast';
    //                 break;
    //             case 'Yeast':
    //                 $this->currentStepType = 'Primary';
    //                 break;
    //             case 'Primary':
    //                 $this->currentStepType = 'Secondary';
    //                 break;
    //             case 'Secondary':
    //                 $this->currentStepType = 'Bottle';
    //                 break;
    //             default:
    //                 break;
    //         }
    //         $this->loadSteps();
    //     }
    // }

