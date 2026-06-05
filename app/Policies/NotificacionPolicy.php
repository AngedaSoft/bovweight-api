<?php

namespace App\Policies;

use App\Models\Notificacion;
use App\Models\User;

class NotificacionPolicy
{
    /**
     * Intercepta todas las revisiones. Si es el correo de administración, concede el permiso de inmediato.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Corrección del campo estricto de usuario (correo en lugar de email)
        if ($user->correo === 'admin@bovweight.local') {
            return true;
        }

        return null; // Continúa con las reglas de abajo si no es admin
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, Notificacion $notificacion): bool
    {
        return $user->id == $notificacion->usuario_id;
    }
}