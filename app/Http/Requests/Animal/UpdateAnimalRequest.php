<?php

namespace App\Http\Requests\Animal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $animalId = $this->route('animal')?->id;

        return [
            'finca_id'  => ['sometimes', 'integer', 'exists:fincas,id'],
            'rebano_id' => ['sometimes', 'nullable', 'integer', 'exists:rebanos,id'],
            'raza_id'   => ['sometimes', 'nullable', 'integer', 'exists:razas,id'],
            'arete_senasa' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('animales', 'arete_senasa')->ignore($animalId),
            ],
            'nombre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sexo' => ['sometimes', 'in:macho,hembra'],
            'estado' => ['sometimes', 'in:activo,inactivo_vendido,inactivo_muerto,inactivo_traslado,inactivo_transferido'],
            'motivo_inactivacion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fecha_nacimiento_aprox' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'fecha_inactivacion' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
