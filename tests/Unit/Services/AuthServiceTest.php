<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService();
    }

    public function test_registrar_crea_usuario_con_contrasena_hasheada(): void
    {
        $user = $this->service->registrar([
            'nombre_completo' => 'Juan Tester',
            'correo' => 'juan@test.cr',
            'contrasena' => 'Secreto123',
        ]);

        $this->assertTrue($user->exists);
        $this->assertSame('juan@test.cr', $user->correo);
        $this->assertSame('propietario', $user->rol);
        $this->assertSame('activo', $user->estado);
        $this->assertTrue(Hash::check('Secreto123', $user->contrasena_hash));
    }

    public function test_registrar_respeta_rol_explicito(): void
    {
        $user = $this->service->registrar([
            'nombre_completo' => 'Vet',
            'correo' => 'vet@test.cr',
            'contrasena' => 'Secreto123',
            'rol' => 'veterinario',
        ]);

        $this->assertSame('veterinario', $user->rol);
    }

    public function test_autenticar_devuelve_usuario_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'correo' => 'a@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
            'estado' => 'activo',
        ]);

        $autenticado = $this->service->autenticar('a@test.cr', 'Secreto123');

        $this->assertSame($user->id, $autenticado->id);
        $this->assertNotNull($autenticado->ultimo_acceso, 'ultimo_acceso debe actualizarse al loguear');
    }

    public function test_autenticar_lanza_si_correo_no_existe(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->autenticar('nadie@test.cr', 'lo-que-sea');
    }

    public function test_autenticar_lanza_si_contrasena_incorrecta(): void
    {
        User::factory()->create([
            'correo' => 'a@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->autenticar('a@test.cr', 'mala');
    }

    public function test_autenticar_lanza_si_usuario_inactivo(): void
    {
        User::factory()->create([
            'correo' => 'a@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
            'estado' => 'inactivo',
        ]);

        $this->expectException(ValidationException::class);
        $this->service->autenticar('a@test.cr', 'Secreto123');
    }

    public function test_emitir_token_crea_token_con_expiracion(): void
    {
        $user = User::factory()->create();

        $token = $this->service->emitirToken($user, 'mobile-app');

        $this->assertNotEmpty($token->plainTextToken);
        $this->assertNotNull($token->accessToken->expires_at);
        $this->assertTrue($token->accessToken->expires_at->isFuture());
    }

    public function test_actualizar_perfil_modifica_campos_permitidos(): void
    {
        $user = User::factory()->create(['nombre_completo' => 'Viejo', 'correo' => 'v@test.cr']);

        $actualizado = $this->service->actualizarPerfil($user, [
            'nombre_completo' => 'Nuevo',
            'correo' => 'n@test.cr',
        ]);

        $this->assertSame('Nuevo', $actualizado->nombre_completo);
        $this->assertSame('n@test.cr', $actualizado->correo);
    }

    public function test_cambiar_contrasena_actualiza_hash_con_actual_valida(): void
    {
        $user = User::factory()->create([
            'contrasena_hash' => Hash::make('Vieja12345'),
        ]);

        $resultado = $this->service->cambiarContrasena($user, 'Vieja12345', 'Nueva12345');

        $this->assertTrue($resultado);
        $this->assertTrue(Hash::check('Nueva12345', $user->fresh()->contrasena_hash));
    }

    public function test_cambiar_contrasena_falla_si_actual_es_incorrecta(): void
    {
        $user = User::factory()->create([
            'contrasena_hash' => Hash::make('Vieja12345'),
        ]);

        $resultado = $this->service->cambiarContrasena($user, 'NoEsLaMia', 'Nueva12345');

        $this->assertFalse($resultado);
        $this->assertTrue(Hash::check('Vieja12345', $user->fresh()->contrasena_hash));
    }

    public function test_actualizar_avatar_guarda_archivo_y_borra_anterior(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar_url' => 'avatars/old.jpg']);
        Storage::disk('public')->put('avatars/old.jpg', 'fake-content');

        $nuevo = UploadedFile::fake()->image('nuevo.jpg', 200, 200);
        $actualizado = $this->service->actualizarAvatar($user, $nuevo);

        $this->assertNotNull($actualizado->avatar_url);
        $this->assertStringStartsWith("avatars/{$user->id}/", $actualizado->avatar_url);
        Storage::disk('public')->assertExists($actualizado->avatar_url);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }
}
