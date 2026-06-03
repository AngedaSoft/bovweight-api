<?php

namespace App\Services\Rebano;

use App\Models\Rebano;
use Illuminate\Database\Eloquent\Collection;

class RebanoService
{
    public function listarTodos(): Collection
    {
        // rafa, aqui es donde trae los rebaños vinculados a las fincas del usuario autenticado
        return Rebano::whereHas('finca', function ($query) {
            $query->where('propietario_id', auth()->id());
        })->withCount('animales')->get();
    }

    public function listarPorFinca(int $fincaId): Collection
    {
        return Rebano::where('finca_id', $fincaId)
            ->withCount('animales')
            ->get();
    }

    public function crear(array $data): Rebano
    {
        if (empty($data['fecha_creacion'])) {
            $data['fecha_creacion'] = now()->format('Y-m-d');
        }
        return Rebano::create($data);
    }

    public function actualizar(Rebano $rebano, array $data): Rebano
    {
        $rebano->update($data);
        return $rebano;
    }

    public function eliminar(Rebano $rebano): bool
    {
        return $rebano->delete();
    }
}