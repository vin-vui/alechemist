<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('recipe'));
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
