<?php

namespace App\Policies;

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
        if ($user->esVeterinario()) {
            return $user->fincasAsignadas()->where('fincas.id', $animal->finca_id)->exists();
        }

        return $animal->finca?->propietario_id === $user->id;
    }

    public function update(User $user, Animal $animal): bool
    {
        return $animal->finca?->propietario_id === $user->id;
    }

    public function delete(User $user, Animal $animal): bool
    {
        return $animal->finca?->propietario_id === $user->id;
    }
}
