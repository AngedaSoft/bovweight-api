<?php

namespace App\Policies;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;

class AccesoCompartidoPolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    /**
     * Solo el propietario de la finca puede otorgar acceso.
     */
    public function create(User $user, ?Finca $finca = null): bool
    {
        if ($finca === null) {
            return false;
        }
        return $finca->propietario_id === $user->id;
    }

    /**
     * El propietario o el propio invitado pueden ver el registro de acceso.
     */
    public function view(User $user, AccesoCompartido $acceso): bool
    {
        if ($user->id === $acceso->usuario_id) {
            return true;
        }
        return $acceso->finca?->propietario_id === $user->id;
    }

    /**
     * Solo el propietario de la finca puede revocar el acceso.
     */
    public function delete(User $user, AccesoCompartido $acceso): bool
    {
        return $acceso->finca?->propietario_id === $user->id;
    }
}
