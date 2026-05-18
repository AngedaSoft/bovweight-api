<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FincaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'provincia' => $this->provincia,
            'canton' => $this->canton,
            'distrito' => $this->distrito,
            'fecha_creacion' => $this->fecha_creacion?->toDateString(),
            'propietario_id' => $this->propietario_id,
            'total_animales' => $this->whenCounted('animales'),
            'total_rebanos' => $this->whenCounted('rebanos'),
        ];
    }
}
