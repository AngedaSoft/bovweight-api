<?php

namespace App\Policies;

use App\Models\AccesoCompartido;
use App\Models\Pesaje;
use App\Models\User;

class PesajePolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    public function view(User $user, Pesaje $pesaje): bool
    {
        $finca = $pesaje->animal?->finca;
        if ($finca === null) {
            return false;
        }
        if ($finca->propietario_id === $user->id) {
            return true;
        }
        return $finca->permiteAccesoA($user, AccesoCompartido::TIPO_LECTURA);
    }

    /**
     * Corregir un pesaje requiere acceso de edicion. El propietario siempre puede;
     * un veterinario invitado solo si su acceso es de tipo `edicion`.
     */
    public function corregir(User $user, Pesaje $pesaje): bool
    {
        $finca = $pesaje->animal?->finca;
        if ($finca === null) {
            return false;
        }
        if ($finca->propietario_id === $user->id) {
            return true;
        }
        return $finca->permiteAccesoA($user, AccesoCompartido::TIPO_EDICION);
    }
}
