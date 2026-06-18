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

    public function crearParaUsuario(User $propietario, array $datos): Finca
    {
        return Finca::create([
            'propietario_id' => $propietario->id,
            'nombre' => $datos['nombre'],
            'provincia' => $datos['provincia'],
            'canton' => $datos['canton'],
            'distrito' => $datos['distrito'],
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

    /**
     * Filtra fincas visibles al usuario:
     *  - administrador: todas
     *  - propietario: las suyas
     *  - veterinario (u otro rol) con AccesoCompartido vigente: las que tiene compartidas
     */
    private function consultaParaUsuario(User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return Finca::query();
        }
        return Finca::query()->where(function ($q) use ($usuario) {
            $q->where('propietario_id', $usuario->id)
              ->orWhereHas('accesosVigentes', fn ($sub) => $sub->where('usuario_id', $usuario->id));
        });
    }
}
