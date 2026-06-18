<?php

namespace App\Services\Finca;

use App\Models\Finca;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Orquesta operaciones sobre fincas y aplica las reglas de visibilidad por rol.
 *
 * SRP: solo se preocupa por fincas. Las reglas de autorizacion granular viven
 * en FincaPolicy; aqui solo se filtran las consultas para listados.
 */
class FincaService
{
    public function listarParaUsuario(User $usuario): Collection
    {
        return $this->consultaParaUsuario($usuario)
            ->withCount(['animales', 'rebanos'])
            ->orderBy('nombre')
            ->get();
    }

    public function crearParaUsuario(User $creador, array $datos): Finca
    {
        $propietarioId = ($creador->esAdministrador() && isset($datos['propietario_id']))
            ? $datos['propietario_id']
            : $creador->id;

        return Finca::create([
            'propietario_id' => $propietarioId,
            'nombre'         => $datos['nombre'],
            'provincia'      => $datos['provincia'],
            'canton'         => $datos['canton'],
            'distrito'       => $datos['distrito'],
            'fecha_creacion' => now()->toDateString(),
        ]);
    }

    public function actualizar(Finca $finca, array $datos): Finca
    {
        $finca->fill($datos)->save();
        return $finca->fresh();
    }

    public function eliminar(Finca $finca): void
    {
        $finca->delete();
    }

    private function consultaParaUsuario(User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return Finca::query();
        }

        if ($usuario->esVeterinario()) {
            $ids = $usuario->fincasAsignadas()->pluck('fincas.id');
            return Finca::query()->whereIn('id', $ids);
        }

        return Finca::query()->where('propietario_id', $usuario->id);
    }
}
