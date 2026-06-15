<?php

namespace App\Policies;

use App\Models\Notificacion;
use App\Models\User;

class NotificacionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdministrador() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, Notificacion $notificacion): bool
    {
        // El cast `usuario_id => integer` en el modelo Notificacion asegura
        // que la comparacion estricta funcione tambien con SQLite (driver de tests).
        return $user->id === $notificacion->usuario_id;
    }
}
