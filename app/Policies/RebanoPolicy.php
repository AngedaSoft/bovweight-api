<?php

namespace App\Policies;

use App\Models\Rebano;
use App\Models\User;
use App\Models\Finca;

class RebanoPolicy
{
    /**
     * Corre antes de cualquier otro método. Si es admin, aprueba el acceso de una vez.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Se cambió $user->email por $user->correo
        if ($user->correo === 'admin@bovweight.local') {
            return true;
        }

        return null; // Si no es admin, continúa con las reglas de abajo
    }

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id;
    }

    public function create(User $user): bool
    {
        $fincaId = request()->input('finca_id') ?? request()->json('finca_id');
        
        if ($fincaId) {
            $finca = Finca::find($fincaId);
            return $finca && $user->id === $finca->propietario_id;
        }
        
        return true;
    }

    public function update(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id;
    }

    public function delete(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id;
    }
}