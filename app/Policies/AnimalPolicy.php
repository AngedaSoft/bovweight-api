<?php

namespace App\Policies;

use App\Models\AccesoCompartido;
use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    public function view(User $user, Animal $animal): bool
    {
        $finca = $animal->finca;
        if ($finca === null) {
            return false;
        }
        if ($finca->propietario_id === $user->id) {
            return true;
        }
        return $finca->permiteAccesoA($user, AccesoCompartido::TIPO_LECTURA);
    }

    /**
     * Editar un animal: el propietario siempre puede; los vet con
     * `edicion` tambien pueden registrar cambios sobre el ganado.
     */
    public function update(User $user, Animal $animal): bool
    {
        $finca = $animal->finca;
        if ($finca === null) {
            return false;
        }
        if ($finca->propietario_id === $user->id) {
            return true;
        }
        return $finca->permiteAccesoA($user, AccesoCompartido::TIPO_EDICION);
    }

    public function delete(User $user, Animal $animal): bool
    {
        return $animal->finca?->propietario_id === $user->id;
    }
}
