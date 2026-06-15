<?php

namespace App\Services\Notificacion;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificacionService
{
    public function listarParaUsuario(User $user): Collection
    {
        return Notificacion::where('usuario_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function marcarComoLeida(Notificacion $notificacion): Notificacion
    {
        $notificacion->update(['leida' => true]); 
        return $notificacion;
    }

    public function marcarTodasComoLeidas(User $user): int
    {
        return Notificacion::where('usuario_id', $user->id)
            ->where('leida', false) 
            ->update(['leida' => true]); 
    }
}