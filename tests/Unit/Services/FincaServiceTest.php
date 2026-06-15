<?php

namespace Tests\Unit\Services;

use App\Models\Finca;
use App\Models\User;
use App\Services\Finca\FincaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FincaServiceTest extends TestCase
{
    use RefreshDatabase;

    private FincaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FincaService();
    }

    public function test_listar_propietario_solo_ve_sus_propias_fincas(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        Finca::factory()->for($user, 'propietario')->count(2)->create();
        Finca::factory()->for($otro, 'propietario')->count(3)->create();

        $fincas = $this->service->listarParaUsuario($user);

        $this->assertCount(2, $fincas);
        $this->assertTrue($fincas->every(fn ($f) => $f->propietario_id === $user->id));
    }

    public function test_listar_admin_ve_todas_las_fincas(): void
    {
        $admin = User::factory()->administrador()->create();
        Finca::factory()->count(4)->create();

        $fincas = $this->service->listarParaUsuario($admin);

        $this->assertCount(4, $fincas);
    }

    public function test_listar_incluye_conteos_de_animales_y_rebanos(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();

        $fincas = $this->service->listarParaUsuario($user);

        $this->assertNotNull($fincas->first()->animales_count);
        $this->assertNotNull($fincas->first()->rebanos_count);
    }

    public function test_crear_asigna_propietario_y_fecha(): void
    {
        $user = User::factory()->create();

        $finca = $this->service->crearParaUsuario($user, [
            'nombre' => 'Mi Finca',
            'provincia' => 'Guanacaste',
            'canton' => 'Liberia',
            'distrito' => 'Liberia',
        ]);

        $this->assertSame($user->id, $finca->propietario_id);
        $this->assertNotNull($finca->fecha_creacion);
    }

    public function test_actualizar_modifica_campos_y_devuelve_modelo_fresco(): void
    {
        $finca = Finca::factory()->create(['nombre' => 'Vieja']);

        $actualizada = $this->service->actualizar($finca, ['nombre' => 'Nueva']);

        $this->assertSame('Nueva', $actualizada->nombre);
        $this->assertDatabaseHas('fincas', ['id' => $finca->id, 'nombre' => 'Nueva']);
    }

    public function test_eliminar_borra_la_finca(): void
    {
        $finca = Finca::factory()->create();

        $this->service->eliminar($finca);

        $this->assertDatabaseMissing('fincas', ['id' => $finca->id]);
    }
}
