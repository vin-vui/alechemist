<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Brewing;
use Livewire\Component;
use App\Models\BrewingStep;

class BoilController extends Component
{
    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount, $boilTime;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->stepsCount = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->count();
        $this->checkStep = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
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
        return redirect()->route('yeast', [$this->recipe, $this->brewing]);
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

    public function startChrono()
    {
        $this->brewing->boil_start = now();
        $this->brewing->save();
    }

    public function render()
    {
        $this->steps = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->get();

        return view('steps.boil')->layout('layouts.app');
    }
}
