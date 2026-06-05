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

        // Ajustado al formato plano consistente solicitado {finca_id: ...}
        $response->assertStatus(200)
            ->assertJson([
                'finca_id' => $finca->id,
                'total_cabezas' => 1,
                'peso_total_acumulado_kg' => 450,
            ]);
    }

    public function test_usuario_no_puede_ver_dashboard_de_finca_ajena()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $fincaAjena = Finca::create([
            'propietario_id' => $user2->id,
            'nombre' => 'Finca Ajena',
            'provincia' => 'Guanacaste',
            'canton' => 'Liberia',
            'distrito' => 'Liberia',
            'fecha_creacion' => now()->format('Y-m-d')
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/dashboard?finca_id={$fincaAjena->id}");

        // Retorna 403 Forbidden por políticas de seguridad
        $response->assertStatus(403);
    }
}