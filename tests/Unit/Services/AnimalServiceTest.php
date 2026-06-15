<?php

namespace Tests\Unit\Services;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use App\Services\Animal\AnimalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnimalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnimalService();
    }

    public function test_listar_solo_devuelve_animales_de_fincas_del_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $raza = Raza::factory()->create();

        Animal::factory()->for(Finca::factory()->for($user, 'propietario'))->for($raza)->count(2)->create();
        Animal::factory()->for(Finca::factory()->for($otro, 'propietario'))->for($raza)->count(3)->create();

        $animales = $this->service->listar($user);

        $this->assertSame(2, $animales->total());
    }

    public function test_listar_aplica_filtro_de_estado(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        Animal::factory()->for($finca)->for($raza)->count(2)->create(['estado' => 'activo']);
        Animal::factory()->for($finca)->for($raza)->create(['estado' => 'inactivo_vendido']);

        $activos = $this->service->listar($user, ['estado' => 'activo']);
        $vendidos = $this->service->listar($user, ['estado' => 'inactivo_vendido']);

        $this->assertSame(2, $activos->total());
        $this->assertSame(1, $vendidos->total());
    }

    public function test_listar_busca_por_arete(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        Animal::factory()->for($finca)->for($raza)->create(['arete_senasa' => 'CRI-AAA-111']);
        Animal::factory()->for($finca)->for($raza)->create(['arete_senasa' => 'CRI-BBB-222']);

        $resultado = $this->service->listar($user, ['arete' => 'AAA']);

        $this->assertSame(1, $resultado->total());
        $this->assertSame('CRI-AAA-111', $resultado->first()->arete_senasa);
    }

    public function test_crear_lanza_si_finca_es_de_otro_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();

        $this->expectException(AuthorizationException::class);
        $this->service->crearParaUsuario($user, [
            'finca_id' => $fincaAjena->id,
            'raza_id' => $raza->id,
            'arete_senasa' => 'CRI-X',
            'sexo' => 'macho',
        ]);
    }

    public function test_crear_funciona_si_usuario_es_admin_aunque_finca_sea_ajena(): void
    {
        $admin = User::factory()->administrador()->create();
        $otro = User::factory()->create();
        $finca = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();

        $animal = $this->service->crearParaUsuario($admin, [
            'finca_id' => $finca->id,
            'raza_id' => $raza->id,
            'arete_senasa' => 'CRI-ADMIN-1',
            'sexo' => 'hembra',
        ]);

        $this->assertTrue($animal->exists);
        $this->assertSame('activo', $animal->estado);
    }
}
