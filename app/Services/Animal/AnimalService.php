<?php

namespace App\Services\Animal;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class AnimalService
{
    public function listar(User $usuario, array $filtros = [], int $porPagina = 25): LengthAwarePaginator
    {
        $query = $this->consultaParaUsuario($usuario)
            ->with(['raza', 'ultimoPesaje', 'imagenPrincipal']);

        if (isset($filtros['finca_id'])) {
            $query->where('finca_id', $filtros['finca_id']);
        }
        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (isset($filtros['arete'])) {
            $query->where('arete_senasa', 'like', '%' . $filtros['arete'] . '%');
        }

        return $query->orderBy('arete_senasa')->paginate($porPagina);
    }

    public function crearParaUsuario(User $usuario, array $datos): Animal
    {
        $finca = Finca::findOrFail($datos['finca_id']);
        if (! $usuario->esAdministrador() && $finca->propietario_id !== $usuario->id) {
            throw new AuthorizationException('No puede crear animales en fincas ajenas.');
        }

        return Animal::create([
            'finca_id' => $finca->id,
            'rebano_id' => $datos['rebano_id'] ?? null,
            'raza_id' => $datos['raza_id'] ?? null,
            'arete_senasa' => $datos['arete_senasa'],
            'fecha_asignacion_arete' => $datos['fecha_asignacion_arete'] ?? null,
            'nombre' => $datos['nombre'] ?? null,
            'fecha_nacimiento_aprox' => $datos['fecha_nacimiento_aprox'] ?? null,
            'sexo' => $datos['sexo'],
            'estado' => 'activo',
        ]);
    }

    public function actualizar(Animal $animal, array $datos): Animal
    {
        $animal->fill($datos)->save();
        return $animal->fresh(['raza', 'ultimoPesaje']);
    }

    public function eliminar(Animal $animal): void
    {
        $animal->delete();
    }

    private function consultaParaUsuario(User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return Animal::query();
        }

        if ($usuario->esVeterinario()) {
            $fincaIds = $usuario->fincasAsignadas()->pluck('fincas.id');
            return Animal::query()->whereIn('finca_id', $fincaIds);
        }

        return Animal::query()->whereHas('finca', fn ($q) => $q->where('propietario_id', $usuario->id));
    }
}
