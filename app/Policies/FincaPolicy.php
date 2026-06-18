<?php

namespace App\Policies;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;

class FincaPolicy
{
    /**
     * Administradores ven todo.
     */
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    /**
     * Pueden ver una finca:
     *  - su propietario
     *  - veterinarios u otros usuarios con AccesoCompartido vigente sobre ella
     */
    public function view(User $user, Finca $finca): bool
    {
        if ($finca->propietario_id === $user->id) {
            return true;
        }
        return $finca->permiteAccesoA($user, AccesoCompartido::TIPO_LECTURA);
    }

    /**
     * Modificar la finca (datos, ubicacion) es estrictamente del propietario.
     * Un veterinario con `edicion` puede gestionar el contenido (animales,
     * pesajes), no las atributos de la finca en si.
     */
    public function update(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }

    public function delete(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }
}
