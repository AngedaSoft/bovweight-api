<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_helpers_de_rol_devuelven_true_solo_para_el_rol_correspondiente(): void
    {
        $propietario = new User(['rol' => 'propietario']);
        $vet = new User(['rol' => 'veterinario']);
        $admin = new User(['rol' => 'administrador']);

        $this->assertTrue($propietario->esPropietario());
        $this->assertFalse($propietario->esVeterinario());
        $this->assertFalse($propietario->esAdministrador());

        $this->assertTrue($vet->esVeterinario());
        $this->assertFalse($vet->esPropietario());
        $this->assertFalse($vet->esAdministrador());

        $this->assertTrue($admin->esAdministrador());
        $this->assertFalse($admin->esPropietario());
        $this->assertFalse($admin->esVeterinario());
    }

    public function test_get_auth_password_devuelve_el_hash_de_la_contrasena(): void
    {
        // El cast `hashed` siempre re-hashea al asignar. Para testear que el
        // getter apunta a la columna correcta sin tocar el cast, usamos
        // setRawAttributes que evita los mutators.
        $user = new User();
        $user->setRawAttributes(['contrasena_hash' => 'valor-crudo-en-bd']);

        $this->assertSame('valor-crudo-en-bd', $user->getAuthPassword());
    }

    public function test_get_email_for_verification_devuelve_el_correo(): void
    {
        $user = new User(['correo' => 'x@test.cr']);

        $this->assertSame('x@test.cr', $user->getEmailForVerification());
    }

    public function test_contrasena_hash_y_remember_token_estan_ocultos_en_array(): void
    {
        $user = new User([
            'nombre_completo' => 'Y',
            'correo' => 'y@test.cr',
            'contrasena_hash' => 'secreto',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('contrasena_hash', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
