<?php

namespace Tests\Unit\Services;

use App\Models\Finca;
use App\Models\Rebano;
use App\Models\User;
use App\Services\Rebano\RebanoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RebanoServiceTest extends TestCase
{
    use RefreshDatabase;

    private RebanoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RebanoService();
    }

    public function test_listar_todos_solo_devuelve_rebanos_de_fincas_del_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $fincaMia = Finca::factory()->for($user, 'propietario')->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();

        Rebano::factory()->count(2)->for($fincaMia)->create();
        Rebano::factory()->for($fincaAjena)->create();

        $rebanos = $this->service->listarTodos($user);

        $this->assertCount(2, $rebanos);
        $this->assertTrue($rebanos->every(fn ($r) => $r->finca_id === $fincaMia->id));
    }

    public function test_listar_por_finca_devuelve_solo_los_de_esa_finca_con_conteo(): void
    {
        $finca1 = Finca::factory()->create();
        $finca2 = Finca::factory()->create();
        Rebano::factory()->count(2)->for($finca1)->create();
        Rebano::factory()->for($finca2)->create();

        $rebanos = $this->service->listarPorFinca($finca1->id);

        $this->assertCount(2, $rebanos);
        $this->assertNotNull($rebanos->first()->animales_count);
    }

    public function test_crear_asigna_fecha_creacion_si_no_se_provee(): void
    {
        $finca = Finca::factory()->create();

        $rebano = $this->service->crear([
            'finca_id' => $finca->id,
            'nombre' => 'Lote A',
            'proposito' => 'engorde',
        ]);

        $this->assertSame('Lote A', $rebano->nombre);
        $this->assertNotNull($rebano->fecha_creacion);
    }

    public function test_actualizar_modifica_atributos_del_rebano(): void
    {
        $rebano = Rebano::factory()->create(['proposito' => 'engorde']);

        $actualizado = $this->service->actualizar($rebano, ['proposito' => 'cria']);

        $this->assertSame('cria', $actualizado->proposito);
    }

    public function test_eliminar_borra_el_rebano(): void
    {
        $rebano = Rebano::factory()->create();

        $this->assertTrue($this->service->eliminar($rebano));
        $this->assertDatabaseMissing('rebanos', ['id' => $rebano->id]);
    }
}
