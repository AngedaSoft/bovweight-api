<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FotografiaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ruta_archivo' => $this->ruta_archivo,
            'url' => $this->resolverUrl(),
            'fecha_captura' => $this->fecha_captura?->toIso8601String(),
            'resolucion' => $this->resolucion,
            'es_valida' => (bool) $this->es_valida,
            'motivo_invalidez' => $this->motivo_invalidez,
        ];
    }

    private function resolverUrl(): ?string
    {
        if (! $this->ruta_archivo) {
            return null;
        }
        if (str_starts_with($this->ruta_archivo, 'http')) {
            return $this->ruta_archivo;
        }
        return Storage::disk('public')->url($this->ruta_archivo);
    }
}
