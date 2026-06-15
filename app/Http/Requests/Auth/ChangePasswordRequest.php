<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'contrasena_actual' => ['required', 'string'],
            'nueva_contrasena' => [
                'required', 'string', 'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'contrasena_actual.required' => 'Debe ingresar su contrasena actual.',
            'nueva_contrasena.confirmed' => 'La confirmacion de la nueva contrasena no coincide.',
            'nueva_contrasena.min' => 'La nueva contrasena debe tener al menos 8 caracteres.',
        ];
    }
}
