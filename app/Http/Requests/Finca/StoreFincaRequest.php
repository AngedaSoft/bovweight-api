<?php

namespace App\Http\Requests\Finca;

use Illuminate\Foundation\Http\FormRequest;

class StoreFincaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nombre'         => ['required', 'string', 'max:255'],
            'provincia'      => ['required', 'string', 'max:120'],
            'canton'         => ['required', 'string', 'max:120'],
            'distrito'       => ['required', 'string', 'max:120'],
            'propietario_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
