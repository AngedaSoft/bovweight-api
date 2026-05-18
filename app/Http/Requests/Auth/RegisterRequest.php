<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'min:2', 'max:255'],
            'correo' => ['required', 'email', 'max:255', 'unique:users,correo'],
            'contrasena' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol' => ['sometimes', 'in:propietario,veterinario'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.unique' => 'Ya existe una cuenta registrada con ese correo.',
            'contrasena.confirmed' => 'La confirmacion de contrasena no coincide.',
        ];
    }
}
