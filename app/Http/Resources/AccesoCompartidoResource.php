<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccesoCompartidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'finca_id' => $this->finca_id,
            'usuario_id' => $this->usuario_id,
            'tipo_acceso' => $this->tipo_acceso,
            'activo' => (bool) $this->activo,
            'vigente' => $this->estaVigente(),
            'fecha_inicio' => $this->fecha_inicio?->toIso8601String(),
            'fecha_fin' => $this->fecha_fin?->toIso8601String(),
            'usuario' => $this->whenLoaded('usuario', fn () => [
                'id' => $this->usuario->id,
                'nombre_completo' => $this->usuario->nombre_completo,
                'correo' => $this->usuario->correo,
                'rol' => $this->usuario->rol,
            ]),
            'finca' => $this->whenLoaded('finca', fn () => [
                'id' => $this->finca->id,
                'nombre' => $this->finca->nombre,
                'provincia' => $this->finca->provincia,
                'canton' => $this->finca->canton,
                'distrito' => $this->finca->distrito,
            ]),
        ];
    }
}
