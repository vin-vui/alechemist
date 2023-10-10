<?php

namespace App\Livewire;

use App\Models\Brewing;
use App\Models\Brewing_Step;
use Livewire\Component;

class PreparationController extends Component
{
    public $recipe, $steps, $brewing;

    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->steps = Brewing_Step::where('type', 'Preparation')
        ->where('brewing_id', $this->brewing->id)
        ->orderBy('unit', 'DESC')
        ->get();
    }



    public function render()
    {
        return view('steps.preparation')->layout('layouts.app');
    }
}
