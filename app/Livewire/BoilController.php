<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use App\Models\BrewingStep;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BoilController extends Component
{
    use AuthorizesRequests;

    public $recipe, $steps, $brewing, $checkStep, $allChecked, $stepsCount, $boilTime, $boilStart, $boilEnd, $newBoilTime;
    public $isOpen = false;

    public function mount(Recipe $recipe, Brewing $brewing)
    {
        abort_unless($brewing->recipe_id === $recipe->id, 404);
        $this->authorize('view', $recipe);

        $this->recipe = $recipe;
        $this->brewing = $brewing;
        $this->stepsCount = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->count();
        $this->checkStep = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->where('status', true)->count();
        $this->steps = BrewingStep::whereIn('type', ['Boil', 'Aroma'])->where('brewing_id', $this->brewing->id)->get();
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
        abort_unless($step->brewing_id === $this->brewing->id, 403);

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

    public function modifyBoilTime()
    {
        if ($this->newBoilTime > 0) {
            $this->brewing->boil_time = intval($this->newBoilTime);
            $this->brewing->save();
        } else {
            $this->newBoilTime = $this->brewing->boil_time;
        }

        $this->closeModal();
    }

    public function openModal()
    {
        $this->newBoilTime = $this->brewing->boil_time;
        $this->isOpen = !$this->isOpen;
    }

    public function closeModal()
    {
        $this->brewing->boil_time = intval($this->newBoilTime);
        $this->isOpen = false;
    }

    public function note()
    {
        return redirect()->route('note', [$this->recipe, $this->brewing]);
    }

    public function render()
    {
        return view('steps.boil')->layout('layouts.app');
    }
}
