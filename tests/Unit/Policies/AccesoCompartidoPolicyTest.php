<?php

namespace Tests\Unit\Policies;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;
use App\Policies\AccesoCompartidoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoCompartidoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AccesoCompartidoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AccesoCompartidoPolicy();
    }

    public function test_solo_el_propietario_de_la_finca_puede_crear_un_acceso(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $otro = User::factory()->create();

        $this->assertTrue($this->policy->create($propietario, $finca));
        $this->assertFalse($this->policy->create($otro, $finca));
        $this->assertFalse($this->policy->create($propietario, null));
    }

    public function test_ver_lo_puede_hacer_el_propietario_o_el_invitado(): void
    {
        $propietario = User::factory()->create();
        $vet = User::factory()->veterinario()->create();
        $intruso = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $acceso = AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->create()
            ->load('finca');

        $this->assertTrue($this->policy->view($propietario, $acceso));
        $this->assertTrue($this->policy->view($vet, $acceso));
        $this->assertFalse($this->policy->view($intruso, $acceso));
    }

    public function test_solo_el_propietario_puede_revocar(): void
    {
        $propietario = User::factory()->create();
        $vet = User::factory()->veterinario()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $acceso = AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->create()
            ->load('finca');

        $this->assertTrue($this->policy->delete($propietario, $acceso));
        $this->assertFalse($this->policy->delete($vet, $acceso),
            'el propio invitado no puede auto-revocarse desde esta policy');
    }

    public function test_admin_concede_acceso_via_before(): void
    {
        $admin = User::factory()->administrador()->create();
        $this->assertTrue($this->policy->before($admin));
    }
}
