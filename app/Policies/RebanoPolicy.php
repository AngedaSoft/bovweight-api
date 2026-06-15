<?php

namespace App\Policies;

use App\Models\Finca;
use App\Models\Rebano;
use App\Models\User;

/**
 * Politica de autorizacion para Rebano.
 *
 * Sigue el mismo patron que FincaPolicy / AnimalPolicy / PesajePolicy:
 *  - `before()` da acceso total a administradores.
 *  - Las reglas finas solo verifican propiedad de la finca asociada.
 *  - No se toca `request()` ni ningun input directamente; el controller
 *    es quien resuelve la entidad y se la pasa al policy.
 */
class RebanoPolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Rebano $rebano): bool
    {
        return $rebano->finca?->propietario_id === $user->id;
    }

    /**
     * Para crear un rebano se requiere la Finca destino. El controller
     * invoca `$this->authorize('create', [Rebano::class, $finca])` para
     * que aqui evaluemos pertenencia sin tocar el request.
     */
    public function create(User $user, ?Finca $finca = null): bool
    {
        if ($finca === null) {
            return false;
        }
        return $finca->propietario_id === $user->id;
    }

    public function update(User $user, Rebano $rebano): bool
    {
        return $rebano->finca?->propietario_id === $user->id;
    }

    public function delete(User $user, Rebano $rebano): bool
    {
        return $rebano->finca?->propietario_id === $user->id;
    }
}
