<?php

namespace App\Services\Rebano;

use App\Models\Rebano;
use App\Models\User; // <- CORRECCIÓN: Importación para el tipado del parámetro
use Illuminate\Database\Eloquent\Collection;

class RebanoService
{
    /**
     * CORRECCIÓN: Ahora recibe el usuario por parámetro ($user) 
     * y elimina la dependencia directa de auth()->id() de acuerdo a la revisión.
     */
    public function listarTodos(User $user): Collection
    {
        return Rebano::whereHas('finca', function ($query) use ($user) {
            $query->where('propietario_id', $user->id);
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