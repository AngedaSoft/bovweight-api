<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RebanoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'finca_id' => $this->finca_id,
            'nombre' => $this->nombre,
            'proposito' => $this->proposito,
            'fecha_creacion' => $this->fecha_creacion ? $this->fecha_creacion->format('Y-m-d') : null,
            'animales_count' => $this->whenCounted('animales'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}