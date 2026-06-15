<?php

namespace Tests\Unit\Policies;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use App\Policies\AnimalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AnimalPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AnimalPolicy();
    }

    public function test_propietario_de_la_finca_puede_administrar_el_animal(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $animal = Animal::factory()->for($finca)->for(Raza::factory())->create();
        $animal->load('finca');

        $this->assertTrue($this->policy->view($user, $animal));
        $this->assertTrue($this->policy->update($user, $animal));
        $this->assertTrue($this->policy->delete($user, $animal));
    }

    public function test_usuario_ajeno_no_puede_administrar_el_animal(): void
    {
        $dueno = User::factory()->create();
        $finca = Finca::factory()->for($dueno, 'propietario')->create();
        $animal = Animal::factory()->for($finca)->for(Raza::factory())->create();
        $animal->load('finca');
        $intruso = User::factory()->create();

        $this->assertFalse($this->policy->view($intruso, $animal));
        $this->assertFalse($this->policy->update($intruso, $animal));
        $this->assertFalse($this->policy->delete($intruso, $animal));
    }

    public function test_admin_concede_acceso_via_before(): void
    {
        $admin = User::factory()->administrador()->create();
        $this->assertTrue($this->policy->before($admin));
    }
}
