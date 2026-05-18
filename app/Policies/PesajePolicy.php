<?php

namespace App\Policies;

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
        return $pesaje->animal?->finca?->propietario_id === $user->id;
    }

    public function corregir(User $user, Pesaje $pesaje): bool
    {
        return $pesaje->animal?->finca?->propietario_id === $user->id;
    }
}
