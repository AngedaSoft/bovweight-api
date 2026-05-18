<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'finca_id' => $this->finca_id,
            'rebano_id' => $this->rebano_id,
            'raza_id' => $this->raza_id,
            'arete_senasa' => $this->arete_senasa,
            'nombre' => $this->nombre,
            'sexo' => $this->sexo,
            'estado' => $this->estado,
            'fecha_nacimiento_aprox' => $this->fecha_nacimiento_aprox?->toDateString(),
            'fecha_asignacion_arete' => $this->fecha_asignacion_arete?->toDateString(),
            'motivo_inactivacion' => $this->motivo_inactivacion,
            'fecha_inactivacion' => $this->fecha_inactivacion?->toDateString(),
            'raza' => $this->whenLoaded('raza', fn () => [
                'id' => $this->raza->id,
                'nombre' => $this->raza->nombre,
            ]),
            'ultimo_pesaje' => $this->whenLoaded('ultimoPesaje', function () {
                if (! $this->ultimoPesaje) {
                    return null;
                }
                return [
                    'id' => $this->ultimoPesaje->id,
                    'fecha' => $this->ultimoPesaje->fecha?->toIso8601String(),
                    'peso_kg' => $this->ultimoPesaje->pesoFinal(),
                ];
            }),
        ];
    }
}
