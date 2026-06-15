<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_listar_sus_notificaciones()
    {
        $user = User::factory()->create();
        
        Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'ALERTA',
            'mensaje' => 'El animal CR-01 requiere pesaje.',
            'leida' => false
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/notificaciones');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'tipo' => 'ALERTA',
                'mensaje' => 'El animal CR-01 requiere pesaje.',
                'leida' => false
            ]);
    }

    public function test_usuario_puede_marcar_leida_su_propia_notificacion()
    {
        // Forzamos que el ID creado sea tratado explícitamente igual
        $user = User::factory()->create();
        
        $notificacion = Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'ALERTA',
            'mensaje' => 'Alerta propia.',
            'leida' => false
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/notificaciones/{$notificacion->id}/leer");

        // Evaluamos el estatus de la petición
        $response->assertStatus(200);
        
        // CORRECCIÓN: Casteamos a booleano el valor fresco de la BD para evitar fallos de tipos en SQLite
        $this->assertTrue((bool) $notificacion->fresh()->leida);
    }

    public function test_usuario_no_puede_read_notificaciones_ajenas()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $notificacionAjena = Notificacion::create([
            'usuario_id' => $user2->id,
            'tipo' => 'ALERTA',
            'mensaje' => 'No deberías ver esto.',
            'leida' => false
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->patchJson("/api/notificaciones/{$notificacionAjena->id}/leer");

        $response->assertStatus(403); 
    }
}