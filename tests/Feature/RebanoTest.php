<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Finca;
use App\Models\Rebano;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RebanoTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_puede_crear_un_rebano_en_su_finca()
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->create(['propietario_id' => $user->id]);

        $payload = [
            'finca_id' => $finca->id,
            'nombre' => 'Lote Engorde Novillos',
            'proposito' => 'engorde',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/rebanos', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.nombre', 'Lote Engorde Novillos');
            
        $this->assertDatabaseHas('rebanos', ['nombre' => 'Lote Engorde Novillos']);
    }

    public function test_usuario_no_puede_crear_rebano_en_finca_ajena()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $fincaAjena = Finca::factory()->create(['propietario_id' => $user2->id]);

        $payload = [
            'finca_id' => $fincaAjena->id,
            'nombre' => 'Rebaño Intruso',
            'proposito' => 'engorde',
        ];

        $response = $this->actingAs($user1, 'sanctum')
            ->postJson('/api/rebanos', $payload);

        // La política 'create' del RebanoPolicy frena la acción con 403
        $response->assertStatus(403);
    }

    public function test_usuario_no_puede_ver_rebanos_de_fincas_ajenas()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $fincaAjena = Finca::factory()->create(['propietario_id' => $user2->id]);
        
        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/fincas/{$fincaAjena->id}/rebanos");

        $response->assertStatus(403);
    }

    public function test_no_se_puede_crear_rebano_con_proposito_invalido()
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->create(['propietario_id' => $user->id]);

        $payload = [
            'finca_id' => $finca->id,
            'nombre' => 'Rebaño Invalido',
            'proposito' => 'leche_premium',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/rebanos', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['proposito']);
    }
}