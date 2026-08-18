<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateBrewingRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('brewing'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => 'sometimes|nullable|string',
            'boil_time' => 'sometimes|integer|min:1',
            // Mesure faite dans la cuve, distincte de la densité visée par la recette.
            'before_boil_density' => 'sometimes|nullable|numeric',
            'current_step' => 'sometimes|in:preparation,mash,boil,yeast,ferment,completed',
        ];
    }
}
