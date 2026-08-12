<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('recipe'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'before_boil_density' => 'sometimes|nullable|numeric',
        ];
    }
}
