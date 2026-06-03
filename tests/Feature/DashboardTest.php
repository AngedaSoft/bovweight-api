<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Finca;
use App\Models\Animal;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_obtener_metricas_del_dashboard()
    {
        $user = User::factory()->create();
        
        $finca = Finca::create([
            'propietario_id' => $user->id,
            'nombre' => 'Finca Guanacaste',
            'provincia' => 'Guanacaste',
            'canton' => 'Liberia',
            'distrito' => 'Liberia',
            'fecha_creacion' => now()->format('Y-m-d')
        ]);

        $animal = Animal::create([
            'finca_id' => $finca->id,
            'arete_senasa' => 'CR-12345-XYZ',
            'nombre' => 'Toro Relámpago',
            'sexo' => 'macho',
            'estado' => 'activo'
        ]);

        // Insertando un pesaje controlado usando DB nativo
        DB::table('pesajes')->insert([
            'animal_id' => $animal->id,
            'usuario_id' => $user->id,
            'fecha' => now()->subDays(5)->toDateTimeString(),
            'peso_estimado_kg' => 450.00,
            'tipo' => 'manual',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/dashboard?finca_id={$finca->id}");

        // Aserción corregida para evitar el error estricto de tipos con decimales vacíos
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'finca_id' => $finca->id,
                    'total_cabezas' => 1,
                    'peso_total_acumulado_kg' => 450,
                ]
            ]);
    }
}