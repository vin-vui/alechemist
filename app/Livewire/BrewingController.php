<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class BrewingController extends Component
{

    public function render()
    {
        return view('brewings.index', [
            'brewings' => Brewing::all()
        ])->layout('layouts.app');
    }
}
