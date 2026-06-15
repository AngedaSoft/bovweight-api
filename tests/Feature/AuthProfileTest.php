<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualizar_perfil_modifica_nombre_y_correo(): void
    {
        $user = User::factory()->create([
            'nombre_completo' => 'Nombre Viejo',
            'correo' => 'viejo@test.cr',
        ]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/auth/me', [
            'nombre_completo' => 'Nombre Nuevo',
            'correo' => 'nuevo@test.cr',
        ])->assertOk()
            ->assertJsonPath('user.nombre_completo', 'Nombre Nuevo')
            ->assertJsonPath('user.correo', 'nuevo@test.cr');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'correo' => 'nuevo@test.cr',
        ]);
    }

    public function test_actualizar_perfil_rechaza_correo_duplicado_de_otro_usuario(): void
    {
        User::factory()->create(['correo' => 'ocupado@test.cr']);
        $user = User::factory()->create(['correo' => 'mio@test.cr']);
        Sanctum::actingAs($user);

        $this->patchJson('/api/auth/me', ['correo' => 'ocupado@test.cr'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('correo');
    }

    public function test_actualizar_perfil_permite_mantener_mi_propio_correo(): void
    {
        $user = User::factory()->create(['correo' => 'mio@test.cr']);
        Sanctum::actingAs($user);

        $this->patchJson('/api/auth/me', [
            'correo' => 'mio@test.cr',
            'nombre_completo' => 'Solo cambio el nombre',
        ])->assertOk();
    }

    public function test_actualizar_perfil_requiere_autenticacion(): void
    {
        $this->patchJson('/api/auth/me', ['nombre_completo' => 'X'])->assertStatus(401);
    }

    public function test_cambiar_contrasena_con_actual_valida_funciona(): void
    {
        $user = User::factory()->create([
            'contrasena_hash' => Hash::make('Vieja12345'),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/cambiar-contrasena', [
            'contrasena_actual' => 'Vieja12345',
            'nueva_contrasena' => 'Nueva12345',
            'nueva_contrasena_confirmation' => 'Nueva12345',
        ])->assertOk();

        $this->assertTrue(Hash::check('Nueva12345', $user->fresh()->contrasena_hash));
    }

    public function test_cambiar_contrasena_rechaza_actual_incorrecta(): void
    {
        $user = User::factory()->create([
            'contrasena_hash' => Hash::make('Vieja12345'),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/cambiar-contrasena', [
            'contrasena_actual' => 'EquivocadaXY',
            'nueva_contrasena' => 'Nueva12345',
            'nueva_contrasena_confirmation' => 'Nueva12345',
        ])->assertStatus(422);
    }

    public function test_cambiar_contrasena_rechaza_confirmacion_incorrecta(): void
    {
        $user = User::factory()->create([
            'contrasena_hash' => Hash::make('Vieja12345'),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/cambiar-contrasena', [
            'contrasena_actual' => 'Vieja12345',
            'nueva_contrasena' => 'Nueva12345',
            'nueva_contrasena_confirmation' => 'Distinta12345',
        ])->assertStatus(422)->assertJsonValidationErrors('nueva_contrasena');
    }

    public function test_subir_avatar_guarda_archivo_y_actualiza_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $archivo = UploadedFile::fake()->image('yo.jpg', 200, 200);

        $this->postJson('/api/auth/avatar', ['avatar' => $archivo])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $userActualizado = $user->fresh();
        $this->assertNotNull($userActualizado->avatar_url);
        Storage::disk('public')->assertExists($userActualizado->avatar_url);
    }

    public function test_subir_avatar_rechaza_archivo_no_imagen(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/auth/avatar', [
            'avatar' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('avatar');
    }

    public function test_subir_avatar_rechaza_archivo_demasiado_pesado(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/auth/avatar', [
            'avatar' => UploadedFile::fake()->image('gigante.jpg', 200, 200)->size(3000),
        ])->assertStatus(422)->assertJsonValidationErrors('avatar');
    }
}
