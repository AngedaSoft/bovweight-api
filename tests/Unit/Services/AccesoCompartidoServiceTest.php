<?php

namespace Tests\Unit\Services;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;
use App\Services\AccesoCompartido\AccesoCompartidoService;
use App\Services\AccesoCompartido\NoSePuedeCompartirConAdminException;
use App\Services\AccesoCompartido\NoSePuedeCompartirConPropietarioException;
use App\Services\AccesoCompartido\UsuarioNoExisteParaCompartirException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoCompartidoServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccesoCompartidoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AccesoCompartidoService();
    }

    public function test_otorgar_crea_un_acceso_para_un_veterinario_existente(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $vet = User::factory()->veterinario()->create(['correo' => 'vet@test.cr']);

        $acceso = $this->service->otorgar($finca, 'vet@test.cr', AccesoCompartido::TIPO_LECTURA);

        $this->assertSame($vet->id, $acceso->usuario_id);
        $this->assertSame($finca->id, $acceso->finca_id);
        $this->assertSame('lectura', $acceso->tipo_acceso);
        $this->assertTrue($acceso->activo);
    }

    public function test_otorgar_actualiza_acceso_existente_en_vez_de_duplicar(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $vet = User::factory()->veterinario()->create(['correo' => 'vet@test.cr']);

        $this->service->otorgar($finca, 'vet@test.cr', AccesoCompartido::TIPO_LECTURA);
        $segundo = $this->service->otorgar($finca, 'vet@test.cr', AccesoCompartido::TIPO_EDICION);

        $this->assertSame(1, AccesoCompartido::where('finca_id', $finca->id)->count());
        $this->assertSame('edicion', $segundo->tipo_acceso);
    }

    public function test_otorgar_lanza_si_el_correo_no_existe(): void
    {
        $finca = Finca::factory()->create();

        $this->expectException(UsuarioNoExisteParaCompartirException::class);
        $this->service->otorgar($finca, 'nadie@test.cr', AccesoCompartido::TIPO_LECTURA);
    }

    public function test_otorgar_lanza_si_se_intenta_compartir_con_el_propietario(): void
    {
        $propietario = User::factory()->create(['correo' => 'yo@test.cr']);
        $finca = Finca::factory()->for($propietario, 'propietario')->create();

        $this->expectException(NoSePuedeCompartirConPropietarioException::class);
        $this->service->otorgar($finca, 'yo@test.cr', AccesoCompartido::TIPO_LECTURA);
    }

    public function test_otorgar_lanza_si_se_intenta_compartir_con_un_administrador(): void
    {
        $finca = Finca::factory()->create();
        User::factory()->administrador()->create(['correo' => 'admin@test.cr']);

        $this->expectException(NoSePuedeCompartirConAdminException::class);
        $this->service->otorgar($finca, 'admin@test.cr', AccesoCompartido::TIPO_LECTURA);
    }

    public function test_listar_para_finca_devuelve_todos_los_accesos(): void
    {
        $finca = Finca::factory()->create();
        AccesoCompartido::factory()->count(3)->for($finca)->create();

        $lista = $this->service->listarParaFinca($finca);

        $this->assertCount(3, $lista);
        $this->assertNotNull($lista->first()->usuario, 'debe traer el usuario eager-loaded');
    }

    public function test_listar_mis_accesos_devuelve_solo_los_vigentes_del_usuario(): void
    {
        $vet = User::factory()->veterinario()->create();
        // 1 vigente
        AccesoCompartido::factory()->for($vet, 'usuario')->create();
        // 1 inactivo
        AccesoCompartido::factory()->for($vet, 'usuario')->inactivo()->create();
        // 1 vencido
        AccesoCompartido::factory()->for($vet, 'usuario')->vencido()->create();
        // 1 de otro usuario
        AccesoCompartido::factory()->create();

        $mios = $this->service->listarMisAccesos($vet);

        $this->assertCount(1, $mios);
    }

    public function test_revocar_elimina_el_acceso(): void
    {
        $acceso = AccesoCompartido::factory()->create();

        $resultado = $this->service->revocar($acceso);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('acceso_compartido', ['id' => $acceso->id]);
    }
}
