<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_crea_usuario_y_devuelve_token(): void
    {
        $payload = [
            'nombre_completo' => 'Juan Tester',
            'correo' => 'juan@test.cr',
            'contrasena' => 'Secreto123',
            'contrasena_confirmation' => 'Secreto123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'expires_at', 'user' => ['id', 'correo', 'rol']]);

        $this->assertDatabaseHas('users', ['correo' => 'juan@test.cr', 'rol' => 'propietario']);
    }

    public function test_register_rechaza_correo_duplicado(): void
    {
        User::factory()->create([
            'correo' => 'duplicado@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
            'nombre_completo' => 'Existente',
            'rol' => 'propietario',
            'estado' => 'activo',
            'fecha_registro' => now()->toDateString(),
        ]);

        $this->postJson('/api/auth/register', [
            'nombre_completo' => 'Otro',
            'correo' => 'duplicado@test.cr',
            'contrasena' => 'Secreto123',
            'contrasena_confirmation' => 'Secreto123',
        ])->assertStatus(422)->assertJsonValidationErrors('correo');
    }

    public function test_login_devuelve_token_con_credenciales_correctas(): void
    {
        User::factory()->create([
            'correo' => 'login@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
            'nombre_completo' => 'Login Test',
            'rol' => 'propietario',
            'estado' => 'activo',
            'fecha_registro' => now()->toDateString(),
        ]);

        $this->postJson('/api/auth/login', [
            'correo' => 'login@test.cr',
            'contrasena' => 'Secreto123',
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rechaza_credenciales_incorrectas(): void
    {
        User::factory()->create([
            'correo' => 'login@test.cr',
            'contrasena_hash' => Hash::make('Secreto123'),
            'nombre_completo' => 'Login Test',
            'rol' => 'propietario',
            'estado' => 'activo',
            'fecha_registro' => now()->toDateString(),
        ]);

        $this->postJson('/api/auth/login', [
            'correo' => 'login@test.cr',
            'contrasena' => 'Incorrecta',
        ])->assertStatus(422);
    }

    public function test_endpoint_me_exige_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
