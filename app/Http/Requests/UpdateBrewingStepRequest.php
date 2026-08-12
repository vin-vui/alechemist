<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateBrewingStepRequest extends FormRequest
{
    public function authorize(): Response
    {
        // Remonte au propriétaire via brewing → recipe (BrewingPolicy::update)
        return Gate::inspect('update', $this->route('brewingStep')->brewing);
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
