<?php

namespace App\Http\Requests\Estimacion;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CrearEstimacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'integer', 'exists:animales,id'],
            'imagen' => ['nullable', 'file', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'perimetro_toracico_cm' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'largo_cuerpo_cm' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'es_offline' => ['sometimes', 'boolean'],
            'fecha_medicion' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $tieneImagen = $this->hasFile('imagen');
            $tieneMedidas = $this->filled('perimetro_toracico_cm') && $this->filled('largo_cuerpo_cm');

            if (! $tieneImagen && ! $tieneMedidas) {
                $v->errors()->add('imagen', 'Debe enviar una imagen o las medidas perimetro_toracico_cm y largo_cuerpo_cm.');
            }
        });
    }
}
