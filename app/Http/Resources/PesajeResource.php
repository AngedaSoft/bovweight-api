<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesajeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'animal_id' => $this->animal_id,
            'fecha' => $this->fecha?->toIso8601String(),
            'tipo' => $this->tipo,
            'peso_estimado_kg' => (float) $this->peso_estimado_kg,
            'rango_confianza_kg' => $this->rango_confianza_kg !== null ? (float) $this->rango_confianza_kg : null,
            'fue_corregido' => (bool) $this->fue_corregido,
            'peso_corregido_kg' => $this->peso_corregido_kg !== null ? (float) $this->peso_corregido_kg : null,
            'peso_final_kg' => $this->pesoFinal(),
            'modelo_ia_version' => $this->modelo_ia_version,
            'tiempo_procesamiento_seg' => $this->tiempo_procesamiento_seg,
            'estado_procesamiento' => $this->estado_procesamiento,
            'formula_zootecnica' => $this->formula_zootecnica,
            'es_offline' => (bool) $this->es_offline,
            'perimetro_toracico_cm' => $this->perimetro_toracico_cm !== null ? (float) $this->perimetro_toracico_cm : null,
            'largo_cuerpo_cm' => $this->largo_cuerpo_cm !== null ? (float) $this->largo_cuerpo_cm : null,
            'fotografias' => FotografiaResource::collection($this->whenLoaded('fotografias')),
            'animal' => $this->whenLoaded('animal', fn () => [
                'id'           => $this->animal->id,
                'nombre'       => $this->animal->nombre,
                'arete_senasa' => $this->animal->arete_senasa,
                'sexo'         => $this->animal->sexo,
                'raza'         => $this->animal->raza
                    ? ['id' => $this->animal->raza->id, 'nombre' => $this->animal->raza->nombre]
                    : null,
                'finca'        => $this->animal->finca
                    ? ['id' => $this->animal->finca->id, 'nombre' => $this->animal->finca->nombre]
                    : null,
            ]),
        ];
    }
}
