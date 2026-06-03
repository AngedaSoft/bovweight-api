<?php

namespace App\Http\Requests\Notificacion;

use Illuminate\Foundation\Http\FormRequest;

class MarkAllReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // El endpoint POST /api/notificaciones/leer-todas no requiere parámetros
        ];
    }
}