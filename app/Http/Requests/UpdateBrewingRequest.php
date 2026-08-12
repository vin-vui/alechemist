<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrewingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('brewing'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => 'sometimes|nullable|string',
            'boil_time' => 'sometimes|integer|min:1',
            'current_step' => 'sometimes|in:preparation,mash,boil,yeast,ferment,completed',
        ];
    }
}
