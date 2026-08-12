<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrewingStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Remonte au propriétaire via brewing → recipe (BrewingPolicy::update)
        return $this->user()->can('update', $this->route('brewingStep')->brewing);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|boolean',
        ];
    }
}
