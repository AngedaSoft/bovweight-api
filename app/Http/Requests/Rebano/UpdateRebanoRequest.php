<?php

namespace App\Http\Requests\Rebano;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRebanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'proposito' => ['sometimes', 'required', Rule::in(['terneros', 'vacas_ordeno', 'engorde', 'cria', 'otro'])],
            'fecha_creacion' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
        ];
    }
}