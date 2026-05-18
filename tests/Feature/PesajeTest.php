<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PesajeTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_puede_corregir_su_pesaje_y_se_guarda_historial(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $pesaje = Pesaje::create([
            'animal_id' => $animal->id,
            'usuario_id' => $user->id,
            'fecha' => now(),
            'peso_estimado_kg' => 400,
            'tipo' => 'ia',
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/pesajes/{$pesaje->id}/correccion", [
            'peso_corregido_kg' => 420.5,
            'motivo' => 'Pesaje en bascula: 420.5 kg',
        ])->assertOk()->assertJsonPath('data.peso_final_kg', 420.5);

        $this->assertDatabaseHas('correcciones_peso', [
            'pesaje_id' => $pesaje->id,
            'peso_corregido_kg' => 420.5,
        ]);
    }

    public function test_no_se_corrige_pesaje_ajeno(): void
    {
        $otro = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animalAjeno = Animal::factory()->for($fincaAjena)->for($raza)->create();
        $pesajeAjeno = Pesaje::create([
            'animal_id' => $animalAjeno->id,
            'usuario_id' => $otro->id,
            'fecha' => now(),
            'peso_estimado_kg' => 400,
            'tipo' => 'ia',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/pesajes/{$pesajeAjeno->id}/correccion", [
            'peso_corregido_kg' => 410,
            'motivo' => 'Intento ajeno',
        ])->assertForbidden();
    }
}
