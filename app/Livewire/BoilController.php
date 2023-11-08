<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Brewing;
use Livewire\Component;
use App\Models\BrewingStep;

class BoilController extends Component
{
    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount, $boilTime, $boilStart, $boilEnd, $newBoilTime;
    public $isOpen = false;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->stepsCount = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->count();
        // SELECT COUNT(*) FROM brewing_steps WHERE type = 'Boil' AND brewing_id = [$this->brewing->id];

        $this->checkStep = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->where('status', true)->count();
        // SELECT COUNT(*) FROM brewing_steps WHERE type = 'Boil'AND brewing_id = [$this->brewing->id] AND status = 1;

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
        $this->brewing->current_step = 'yeast';
        $this->brewing->save();
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

    public function endTime()
    {
        $boilStart = Carbon::create($this->brewing->boil_start);
        $boilEnd = $boilStart->addMinutes($this->brewing->boil_time);
        return now() > $boilEnd;
    }

    public function modifyBoilTime(Brewing $brewing)
    {
        $brewing->boil_time = $this->newBoilTime;
        $brewing->save();

        $this->closeModal();
    }

    public function openModal()
    {
        $this->newBoilTime = $this->brewing->boil_time;
        $this->isOpen = true;
    }
    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function note()
    {
        return redirect()->route('note', [$this->recipe, $this->brewing]);
    }

    public function render()
    {
        $this->steps = BrewingStep::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->get();
        // SELECT * FROM brewing_steps WHERE type = 'Boil' AND brewing_id = $this->brewing->id

        return view('steps.boil')->layout('layouts.app');
    }
}
