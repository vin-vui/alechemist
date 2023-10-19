<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;

class NoteController extends Component
{
    public $recipe, $note, $brewing;
    public function mount(Brewing $brewing)
    {
        $this->brewing = $brewing;
        $this->note = $brewing->note;
        $this->recipe = Recipe::find($brewing->recipe_id);
    }

    public function update(Brewing $brewing)
    {
        $validated = $this->validate([
            'note' => 'required',
        ]);

        Brewing::updateOrCreate(['id' => $this->brewing->id], $validated);

        if ($this->brewing->current_step == null){
            return redirect()->route('preparation', ['recipe'=> $this->recipe, 'brewing'=> $this->brewing]);
        } else {
            return redirect()->route($this->brewing->current_step, ['recipe'=> $this->recipe, 'brewing'=> $this->brewing]);
        }
    }
    public function render()
    {
        return view('_partials.note')->layout('layouts.app');
    }
}
