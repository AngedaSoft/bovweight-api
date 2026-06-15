<?php

namespace Tests\Unit\Policies;

use App\Models\Finca;
use App\Models\Rebano;
use App\Models\User;
use App\Policies\RebanoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RebanoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RebanoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RebanoPolicy();
    }

    public function test_propietario_de_la_finca_puede_ver_actualizar_y_eliminar(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $rebano = Rebano::factory()->for($finca)->create()->load('finca');

        $this->assertTrue($this->policy->view($user, $rebano));
        $this->assertTrue($this->policy->update($user, $rebano));
        $this->assertTrue($this->policy->delete($user, $rebano));
    }

    public function test_no_propietario_no_puede_ver_actualizar_ni_eliminar(): void
    {
        $dueno = User::factory()->create();
        $finca = Finca::factory()->for($dueno, 'propietario')->create();
        $rebano = Rebano::factory()->for($finca)->create()->load('finca');
        $intruso = User::factory()->create();

        $this->assertFalse($this->policy->view($intruso, $rebano));
        $this->assertFalse($this->policy->update($intruso, $rebano));
        $this->assertFalse($this->policy->delete($intruso, $rebano));
    }

    public function test_create_requiere_finca_y_evalua_pertenencia(): void
    {
        $user = User::factory()->create();
        $fincaPropia = Finca::factory()->for($user, 'propietario')->create();
        $fincaAjena = Finca::factory()->create();

        $this->assertTrue($this->policy->create($user, $fincaPropia));
        $this->assertFalse($this->policy->create($user, $fincaAjena));
        $this->assertFalse(
            $this->policy->create($user, null),
            'sin finca el create debe rechazar para evitar bypass via authorize()'
        );
    }

    public function test_admin_concede_acceso_via_before(): void
    {
        $admin = User::factory()->administrador()->create();
        $this->assertTrue($this->policy->before($admin));
    }
}
