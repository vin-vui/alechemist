<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBrewingRequest extends FormRequest
{
    public function authorize(): Response
    {
        // Parité web (CreateBrewingController) : ability "view" sur la recette
        return Gate::inspect('view', $this->route('recipe'));
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
