<?php

namespace App\Livewire;

use App\Models\Brewing;
use App\Models\BrewingStep;
use Livewire\Component;

class PreparationController extends Component
{
    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->stepsCount = BrewingStep::where('type', 'Preparation')->where('brewing_id', $this->brewing->id)->count();
        $this->checkStep = BrewingStep::where('type', 'Preparation')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
        $this->allChecked();
    }


    public function allChecked()
    {
        if ($this->checkStep === $this->stepsCount) {
            $this->allChecked = true;
        } else {
            $this->allChecked = false;
        }
    }

    public function next()
    {
        $this->brewing->current_step = 'mash';
        $this->brewing->save();

        return redirect()->route('mash', [$this->recipe, $this->brewing]);
    }

    public function statusChange(BrewingStep $step)
    {
        $step->status = !$step->status;
        $step->save();

        if($step->status) {
            $this->checkStep += 1;
        } else {
            $this->checkStep -= 1;
        }

        $this->allChecked();
    }


    public function render()
    {
        $this->steps = BrewingStep::where('type', 'Preparation')
        ->where('brewing_id', $this->brewing->id)
        ->orderBy('unit', 'DESC')
        ->get();

        return view('steps.preparation')->layout('layouts.app');
    }
}
