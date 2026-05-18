<?php

namespace App\Http\Requests\Animal;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'finca_id' => ['required', 'integer', 'exists:fincas,id'],
            'rebano_id' => ['nullable', 'integer', 'exists:rebanos,id'],
            'raza_id' => ['nullable', 'integer', 'exists:razas,id'],
            'arete_senasa' => ['required', 'string', 'max:50', 'unique:animales,arete_senasa'],
            'fecha_asignacion_arete' => ['nullable', 'date'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento_aprox' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo' => ['required', 'in:macho,hembra'],
        ];
    }
}
