<?php

namespace App\Policies;

use App\Models\Finca;
use App\Models\User;

class FincaPolicy
{
    /**
     * Administradores ven todo. Esta regla simple se evalua primero en `before`.
     */
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    public function view(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }

    public function update(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }

    public function delete(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }
}
