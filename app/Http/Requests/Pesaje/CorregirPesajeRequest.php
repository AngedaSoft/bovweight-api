<?php

namespace App\Http\Requests\Pesaje;

use Illuminate\Foundation\Http\FormRequest;

class CorregirPesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'peso_corregido_kg' => ['required', 'numeric', 'min:1', 'max:2000'],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }
}
