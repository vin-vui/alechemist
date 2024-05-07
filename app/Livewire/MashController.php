<?php

namespace App\Livewire;

use App\Models\BrewingStep;
use App\Models\Brewing;
use Livewire\Component;

class MashController extends Component
{
    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->stepsCount = BrewingStep::where('type', 'Mash')->where('brewing_id', $this->brewing->id)->count();
        $this->checkStep = BrewingStep::where('type', 'Mash')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
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
        $this->brewing->current_step = 'boil';
        $this->brewing->save();
        return redirect()->route('boil', [$this->recipe, $this->brewing]);
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

    public function note()
    {
        return redirect()->route('note', [$this->recipe, $this->brewing]);
    }

    public function render()
    {
        $this->steps = BrewingStep::where('type', 'Mash')->where('brewing_id', $this->brewing->id)->get();

        return view('steps.mash')->layout('layouts.app');
    }
}
