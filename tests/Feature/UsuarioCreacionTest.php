<?php

namespace Tests\Feature;

use App\Mail\CredencialesUsuarioMailable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsuarioCreacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crea_usuario_y_se_envia_correo_con_credenciales(): void
    {
        Mail::fake();

        $admin = User::factory()->administrador()->create();
        Sanctum::actingAs($admin);

        $payload = [
            'nombre_completo' => 'Maria Veterinaria',
            'correo' => 'maria.vet@test.cr',
            'rol' => 'veterinario',
        ];

        $response = $this->postJson('/api/usuarios', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.correo', 'maria.vet@test.cr')
            ->assertJsonPath('data.rol', 'veterinario')
            ->assertJsonPath('correo_enviado', true);

        $this->assertDatabaseHas('users', [
            'correo' => 'maria.vet@test.cr',
            'rol' => 'veterinario',
            'estado' => 'activo',
        ]);

        Mail::assertSent(CredencialesUsuarioMailable::class, function ($mail) {
            return $mail->hasTo('maria.vet@test.cr')
                && $mail->usuario->correo === 'maria.vet@test.cr'
                && strlen($mail->contrasenaTextoPlano) >= 8;
        });
    }

    public function test_crear_usuario_no_recibe_contrasena_del_admin(): void
    {
        Mail::fake();
        Sanctum::actingAs(User::factory()->administrador()->create());

        // Aunque admin la mande, el backend la ignora — la genera automaticamente.
        $this->postJson('/api/usuarios', [
            'nombre_completo' => 'X',
            'correo' => 'x@test.cr',
            'rol' => 'propietario',
            'contrasena' => '__IGNORADA__',
        ])->assertCreated();

        $u = User::where('correo', 'x@test.cr')->first();
        $this->assertFalse(
            Hash::check('__IGNORADA__', $u->contrasena_hash),
            'La contrasena enviada por el admin NO debe ser usada; el backend la genera'
        );
    }

    public function test_no_admin_no_puede_crear_usuarios(): void
    {
        Mail::fake();
        Sanctum::actingAs(User::factory()->create()); // rol propietario por default

        $this->postJson('/api/usuarios', [
            'nombre_completo' => 'X',
            'correo' => 'x@test.cr',
            'rol' => 'veterinario',
        ])->assertStatus(403);

        Mail::assertNothingSent();
    }

    public function test_crear_usuario_con_correo_duplicado_falla(): void
    {
        Mail::fake();
        User::factory()->create(['correo' => 'ya-existe@test.cr']);
        Sanctum::actingAs(User::factory()->administrador()->create());

        $this->postJson('/api/usuarios', [
            'nombre_completo' => 'X',
            'correo' => 'ya-existe@test.cr',
            'rol' => 'propietario',
        ])->assertStatus(422)->assertJsonValidationErrors('correo');

        Mail::assertNothingSent();
    }

    public function test_crear_usuario_con_rol_invalido_falla(): void
    {
        Mail::fake();
        Sanctum::actingAs(User::factory()->administrador()->create());

        $this->postJson('/api/usuarios', [
            'nombre_completo' => 'X',
            'correo' => 'x@test.cr',
            'rol' => 'administrador',  // no permitido: admin solo crea propietario/veterinario
        ])->assertStatus(422)->assertJsonValidationErrors('rol');

        Mail::assertNothingSent();
    }

    public function test_actualizar_usuario_con_contrasena_envia_mail_con_la_nueva(): void
    {
        Mail::fake();
        $admin = User::factory()->administrador()->create();
        $usuario = User::factory()->create();
        Sanctum::actingAs($admin);

        $this->putJson("/api/usuarios/{$usuario->id}", [
            'contrasena' => 'NuevaPwd123',
        ])->assertOk();

        Mail::assertSent(CredencialesUsuarioMailable::class, function ($mail) use ($usuario) {
            return $mail->hasTo($usuario->correo)
                && $mail->contrasenaTextoPlano === 'NuevaPwd123';
        });
    }

    public function test_reset_de_contrasena_genera_nueva_y_envia_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->administrador()->create();
        $usuario = User::factory()->create();
        $hashOriginal = $usuario->contrasena_hash;
        Sanctum::actingAs($admin);

        $this->putJson("/api/usuarios/{$usuario->id}", [
            'reenviar_contrasena' => true,
        ])->assertOk();

        $this->assertNotSame($hashOriginal, $usuario->fresh()->contrasena_hash);
        Mail::assertSent(CredencialesUsuarioMailable::class);
    }
}
