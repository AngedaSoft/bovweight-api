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

        $response->assertStatus(201) // Created
            ->assertJsonPath('data.nombre', 'Lote Engorde Novillos');
            
        $this->assertDatabaseHas('rebanos', ['nombre' => 'Lote Engorde Novillos']);
    }

    public function test_no_se_puede_crear_rebano_con_proposito_invalido()
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->create(['propietario_id' => $user->id]);

        $payload = [
            'finca_id' => $finca->id,
            'nombre' => 'Rebaño Invalido',
            'proposito' => 'leche_premium', // No existe en el enum de la migración
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/rebanos', $payload);

        $response->assertStatus(422) // Validation Error
            ->assertJsonValidationErrors(['proposito']);
    }
}