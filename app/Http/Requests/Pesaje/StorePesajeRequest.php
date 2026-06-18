<?php

namespace App\Http\Requests\Pesaje;

use Illuminate\Foundation\Http\FormRequest;

class StorePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'peso_estimado_kg'         => ['required', 'numeric', 'min:1', 'max:2000'],
            'rango_confianza_kg'       => ['nullable', 'numeric', 'min:0'],
            'tipo'                     => ['nullable', 'in:ia,manual'],
            'modelo_ia_version'        => ['nullable', 'string', 'max:50'],
            'tiempo_procesamiento_seg' => ['nullable', 'integer', 'min:0'],
            'formula_zootecnica'       => ['nullable', 'string', 'max:2000'],
            'es_offline'               => ['sometimes', 'boolean'],
            'perimetro_toracico_cm'    => ['nullable', 'numeric', 'min:30', 'max:300'],
            'largo_cuerpo_cm'          => ['nullable', 'numeric', 'min:30', 'max:300'],
            'fecha_medicion'           => ['nullable', 'date'],
        ];
    }
}
