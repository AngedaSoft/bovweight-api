<?php

namespace App\Services\AccesoCompartido;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Orquesta el otorgamiento y revocacion de acceso compartido a fincas.
 *
 * Reglas de negocio:
 *  - El propietario de una finca invita por correo a un usuario existente.
 *  - El usuario invitado debe tener rol `veterinario` (o `propietario`).
 *  - Solo puede haber un acceso activo por par (finca, usuario): si ya existe,
 *    se actualiza la fila en lugar de duplicarla.
 *  - Un veterinario puede listar las fincas a las que tiene acceso.
 */
class AccesoCompartidoService
{
    public function listarParaFinca(Finca $finca): Collection
    {
        return $finca->accesosCompartidos()
            ->with('usuario:id,nombre_completo,correo,rol')
            ->orderByDesc('created_at')
            ->get();
    }

    public function listarMisAccesos(User $usuario): Collection
    {
        return AccesoCompartido::query()
            ->where('usuario_id', $usuario->id)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>', now());
            })
            ->with('finca:id,nombre,provincia,canton,distrito,propietario_id')
            ->orderByDesc('created_at')
            ->get();
    }

    public function otorgar(Finca $finca, string $correoUsuario, string $tipoAcceso, ?\DateTimeInterface $fechaFin = null): AccesoCompartido
    {
        $usuario = User::where('correo', $correoUsuario)->first();
        if ($usuario === null) {
            throw new UsuarioNoExisteParaCompartirException(
                "No existe ningun usuario registrado con el correo '{$correoUsuario}'."
            );
        }

        if ($usuario->id === $finca->propietario_id) {
            throw new NoSePuedeCompartirConPropietarioException(
                'El propietario ya tiene acceso completo a su propia finca.'
            );
        }

        if ($usuario->rol === 'administrador') {
            throw new NoSePuedeCompartirConAdminException(
                'Los administradores ya pueden ver todas las fincas; no se requiere acceso explicito.'
            );
        }

        $acceso = AccesoCompartido::updateOrCreate(
            ['finca_id' => $finca->id, 'usuario_id' => $usuario->id],
            [
                'fecha_inicio' => now(),
                'fecha_fin' => $fechaFin,
                'tipo_acceso' => $tipoAcceso,
                'activo' => true,
            ],
        );

        return $acceso->load('usuario:id,nombre_completo,correo,rol');
    }

    public function revocar(AccesoCompartido $acceso): bool
    {
        return $acceso->delete() === true;
    }
}


class UsuarioNoExisteParaCompartirException extends RuntimeException {}
class NoSePuedeCompartirConPropietarioException extends RuntimeException {}
class NoSePuedeCompartirConAdminException extends RuntimeException {}
