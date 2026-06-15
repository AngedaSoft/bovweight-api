<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'avatar_url' => $this->resolverAvatarUrl(),
            'fecha_registro' => $this->fecha_registro?->toDateString(),
            'ultimo_acceso' => $this->ultimo_acceso?->toIso8601String(),
        ];
    }

    private function resolverAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }
        if (str_starts_with($this->avatar_url, 'http')) {
            return $this->avatar_url;
        }
        return Storage::disk('public')->url($this->avatar_url);
    }
}
