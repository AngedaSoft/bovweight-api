<?php

namespace App\Http\Requests\Finca;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFincaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'provincia' => ['sometimes', 'required', 'string', 'max:120'],
            'canton' => ['sometimes', 'required', 'string', 'max:120'],
            'distrito' => ['sometimes', 'required', 'string', 'max:120'],
        ];
    }
}
