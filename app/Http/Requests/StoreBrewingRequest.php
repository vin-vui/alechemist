<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrewingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Parité web (CreateBrewingController) : ability "view" sur la recette
        return $this->user()->can('view', $this->route('recipe'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'date_start' => 'required|date',
        ];
    }
}
