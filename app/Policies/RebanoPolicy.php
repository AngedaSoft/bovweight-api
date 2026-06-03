<?php

namespace App\Policies;

use App\Models\Rebano;
use App\Models\User;
use App\Models\Finca;

class RebanoPolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id || $user->email === 'admin@bovweight.local';
    }

    public function create(User $user): bool
    {
        /* En esta parte se puede verificar si el usuario es dueño de la finca enviada en el request, 
        si necesitan hacer cambios me dicen para explicarles*/
        /* Tambien soporta capturar el campo tanto en
         form-data como en JSON crudo (peticiones de test) */
        $fincaId = request()->input('finca_id') ?? request()->json('finca_id');
    
    if ($fincaId) {
        $finca = \App\Models\Finca::find($fincaId);
        return $finca && ($user->id === $finca->propietario_id || $user->email === 'admin@bovweight.local');
    }
    
    return true;
}
      

    public function update(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id || $user->email === 'admin@bovweight.local';
    }

    public function delete(User $user, Rebano $rebano): bool
    {
        return $user->id === $rebano->finca->propietario_id || $user->email === 'admin@bovweight.local';
    }
}