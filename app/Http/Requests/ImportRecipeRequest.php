<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // toute personne authentifiée importe dans SON compte
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xml|max:2048',
        ];
    }
}
