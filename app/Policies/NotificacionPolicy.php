<?php

namespace App\Policies;

use App\Models\Notificacion;
use App\Models\User;

class NotificacionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, Notificacion $notificacion): bool
    {
        return $user->id === $notificacion->usuario_id;
    }
}