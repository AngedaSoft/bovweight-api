<?php

namespace App\Http\Requests\Notificacion;

use Illuminate\Foundation\Http\FormRequest;

class ReadNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Retornamos true aquí porque la autorización fina de propiedad 
        // la ejecuta la NotificacionPolicy mediante el método Gate::authorize()
        return true; 
    }

    public function rules(): array
    {
        return [
            // No requiere reglas en el body ya que el ID viaja seguro por la URL binding
        ];
    }
}