<?php

namespace App\Livewire;

use App\Models\Brewing_Step;
use App\Models\Brewing;
use Livewire\Component;

class BoilController extends Component
{
    public $recipe, $steps, $brewing;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->steps = Brewing_Step::where('type', 'Boil')->where('brewing_id', $this->brewing->id)->get();
    }

    public function render()
    {
        return view('steps.boil')->layout('layouts.app');
    }
}
