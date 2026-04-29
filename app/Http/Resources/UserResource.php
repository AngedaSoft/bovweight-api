<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_completo' => $this->nombre_completo,
            'correo' => $this->correo,
            'rol' => $this->rol,
            'estado' => $this->estado,
            'fecha_registro' => $this->fecha_registro?->toDateString(),
            'ultimo_acceso' => $this->ultimo_acceso?->toIso8601String(),
        ];
    }
}
