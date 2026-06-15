<?php

namespace Tests\Unit\Services;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    public function test_finca_vacia_devuelve_ceros(): void
    {
        $finca = Finca::factory()->create();

        $metricas = $this->service->obtenerMetricasFinca($finca->id);

        $this->assertSame($finca->id, $metricas['finca_id']);
        $this->assertSame(0, $metricas['total_cabezas']);
        $this->assertSame(0.0, (float) $metricas['peso_total_acumulado_kg']);
        $this->assertSame(0.0, (float) $metricas['gpd_promedio_finca_kg']);
        $this->assertCount(0, $metricas['alertas']);
    }

    public function test_solo_cuenta_animales_activos(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        Animal::factory()->for($finca)->for($raza)->count(3)->create(['estado' => 'activo']);
        Animal::factory()->for($finca)->for($raza)->count(2)->create(['estado' => 'inactivo_vendido']);

        $this->assertSame(3, $this->service->obtenerMetricasFinca($finca->id)['total_cabezas']);
    }

    public function test_peso_total_suma_ultimo_peso_de_cada_animal_activo(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        $a1 = Animal::factory()->for($finca)->for($raza)->create(['estado' => 'activo']);
        $a2 = Animal::factory()->for($finca)->for($raza)->create(['estado' => 'activo']);

        Pesaje::create([
            'animal_id' => $a1->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now()->subDays(30), 'peso_estimado_kg' => 100, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $a1->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now(), 'peso_estimado_kg' => 150, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $a2->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now(), 'peso_estimado_kg' => 200, 'tipo' => 'manual',
        ]);

        $metricas = $this->service->obtenerMetricasFinca($finca->id);

        // Ultimo peso de a1 = 150, ultimo peso de a2 = 200. Total = 350.
        $this->assertSame(350.0, (float) $metricas['peso_total_acumulado_kg']);
    }

    public function test_gpd_es_positivo_si_animal_gana_peso(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create(['estado' => 'activo']);

        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now()->subDays(10), 'peso_estimado_kg' => 100, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now(), 'peso_estimado_kg' => 120, 'tipo' => 'manual',
        ]);

        $gpd = (float) $this->service->obtenerMetricasFinca($finca->id)['gpd_promedio_finca_kg'];
        // 20 kg en 10 dias = 2.0 kg/dia
        $this->assertEqualsWithDelta(2.0, $gpd, 0.01);
    }

    public function test_gpd_es_negativo_si_animal_pierde_peso(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create(['estado' => 'activo']);

        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now()->subDays(5), 'peso_estimado_kg' => 200, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => User::factory()->create()->id,
            'fecha' => now(), 'peso_estimado_kg' => 180, 'tipo' => 'manual',
        ]);

        $gpd = (float) $this->service->obtenerMetricasFinca($finca->id)['gpd_promedio_finca_kg'];
        // Perdio 20 kg en 5 dias = -4.0 kg/dia. Confirma el fix del bug del MIN/MAX.
        $this->assertEqualsWithDelta(-4.0, $gpd, 0.01,
            'El GPD debe usar primer y ultimo peso cronologico, no min/max numericos');
    }

    public function test_alertas_top_5_animales_sin_pesaje_reciente(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        Animal::factory()->for($finca)->for($raza)->count(7)->create(['estado' => 'activo']);

        $alertas = $this->service->obtenerMetricasFinca($finca->id)['alertas'];

        $this->assertCount(5, $alertas);
        $this->assertSame('PESAJE_PENDIENTE', $alertas->first()['tipo']);
    }
}
