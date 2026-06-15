<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'nombre_completo' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'correo' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'correo')->ignore($userId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.unique' => 'Ya existe una cuenta registrada con ese correo.',
            'nombre_completo.min' => 'El nombre debe tener al menos 2 caracteres.',
        ];
    }
}
