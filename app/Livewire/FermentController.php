<?php

namespace App\Livewire;

use App\Models\BrewingStep;
use App\Models\Brewing;
use Livewire\Component;

class FermentController extends Component
{
    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount, $fermentTime;


    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;




        $this->stepsCount = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])
        ->where('brewing_id', $this->brewing->id)->count();

        $this->checkStep = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])
        ->where('brewing_id', $this->brewing->id)
        ->where('status', true)->count();

        $this->fermentTime = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])
        ->where('brewing_id', $this->brewing->id)
        ->sum('time');


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
        return redirect()->route('brewing.index', [$this->recipe, $this->brewing]);
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
        $this->brewing->ferment_start = now();
        $this->brewing->save();
    }

    public function note()
    {
        return redirect()->route('note', [$this->recipe, $this->brewing]);
    }


    public function render()
    {
        $this->steps = BrewingStep::whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle'])
        ->where('brewing_id', $this->brewing->id)->get();
        // $this->steps = BrewingStep::where('type', 'Primary')
        // ->orWhere('type', 'Secondary')
        // ->orWhere('type', 'Tertiary')
        // ->orWhere('type', 'Bottle')
        // ->where('brewing_id', $this->brewing->id)->get();

        return view('steps.ferment')->layout('layouts.app');
    }
}
