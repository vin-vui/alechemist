<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Brewing;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NoteController extends Component
{
    use AuthorizesRequests;

    public $recipe, $note, $brewing;

    public function mount(Recipe $recipe, Brewing $brewing)
    {
        abort_unless($brewing->recipe_id === $recipe->id, 404);
        $this->authorize('view', $recipe);

        $this->recipe = $recipe;
        $this->brewing = $brewing;
        $this->note = $brewing->note;
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

    public function back()
    {
        return redirect()->route($this->brewing->current_step, ['recipe'=> $this->recipe, 'brewing'=> $this->brewing]);
    }

    public function render()
    {
        return view('_partials.note')->layout('layouts.app');
    }

    public function delete()
    {
        $this->brewing->delete();
        return redirect()->route('recipes.index');
    }
}
