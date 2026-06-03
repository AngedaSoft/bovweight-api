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
        
        // Usamos los campos estrictos de tu migración real
        $notificacion = Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'ALERTA',
            'mensaje' => 'El animal CR-01 requiere pesaje.',
            'leida' => false
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/notificaciones');

        // Validamos el éxito de la estructura del sobre y campos reales
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'tipo' => 'ALERTA',
                'mensaje' => 'El animal CR-01 requiere pesaje.',
                'leida' => false
            ]);
    }

    public function test_usuario_no_puede_leer_notificaciones_ajenas()
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