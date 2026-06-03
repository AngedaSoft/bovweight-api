<?php

namespace App\Http\Requests\Rebano;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRebanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; //  esto es controlado por policy, para que sepas rafa
    }

    public function rules(): array
    {
        return [
            'finca_id' => ['required', 'exists:fincas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'proposito' => ['required', Rule::in(['terneros', 'vacas_ordeno', 'engorde', 'cria', 'otro'])],
            'fecha_creacion' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}