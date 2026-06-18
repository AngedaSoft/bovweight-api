<?php

namespace App\Http\Requests\AccesoCompartido;

use App\Models\AccesoCompartido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OtorgarAccesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'finca_id' => ['required', 'integer', 'exists:fincas,id'],
            'correo_usuario' => ['required', 'email', 'max:255'],
            'tipo_acceso' => [
                'required',
                Rule::in([AccesoCompartido::TIPO_LECTURA, AccesoCompartido::TIPO_EDICION]),
            ],
            'fecha_fin' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo_usuario.email' => 'Debe ser un correo valido del veterinario o usuario invitado.',
            'tipo_acceso.in' => 'tipo_acceso debe ser "lectura" o "edicion".',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior al momento actual.',
        ];
    }
}
