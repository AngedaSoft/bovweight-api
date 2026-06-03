<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo, 
            'mensaje' => $this->mensaje,
            'payload' => $this->payload, // Inclui el JSON contextual por si Gerald/Ángeles lo ocupan
            'leida' => (bool) $this->leida, 
            'fecha_envio' => $this->fecha_envio ? $this->fecha_envio->toIso8601String() : null,
            'fecha_creacion' => $this->created_at->toIso8601String(),
        ];
    }
}