<?php

namespace App\Livewire;

use App\Models\Brewing;
use Livewire\Component;
use Illuminate\Http\Request;

class CreateBrewingController extends Component
{
    public $name, $type, $date_start, $note, $brewing_id;

    public function update(Brewing $brewing)
    {
        $this->name = $brewing->name;
        $this->type = $brewing->type;
        $this->date_start = $brewing->date_start;
        $this->note = $brewing->note;
        $this->brewing_id = $brewing->id;

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required',
            'note' => 'required',
            'date_start' => 'required',
        ]);

        $this->name = $request->name;
        $this->type = $request->type;
        $this->date_start = $request->date_start;
        $this->note = $request->note;


       Brewing::updateOrCreate(['id' => $this->brewing_id], $request);

    }
    public function render()
    {
        return view('brewings.create')->layout('layouts.app');
    }
}
