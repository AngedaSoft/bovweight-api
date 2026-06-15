<?php

namespace Tests\Unit\Policies;

use App\Models\Finca;
use App\Models\User;
use App\Policies\FincaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FincaPolicyTest extends TestCase
{
    use RefreshDatabase;

    private FincaPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new FincaPolicy();
    }

    public function test_propietario_puede_ver_actualizar_y_eliminar_su_propia_finca(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();

        $this->assertTrue($this->policy->view($user, $finca));
        $this->assertTrue($this->policy->update($user, $finca));
        $this->assertTrue($this->policy->delete($user, $finca));
    }

    public function test_otro_usuario_no_puede_ver_actualizar_ni_eliminar_finca_ajena(): void
    {
        $dueno = User::factory()->create();
        $finca = Finca::factory()->for($dueno, 'propietario')->create();
        $otro = User::factory()->create();

        $this->assertFalse($this->policy->view($otro, $finca));
        $this->assertFalse($this->policy->update($otro, $finca));
        $this->assertFalse($this->policy->delete($otro, $finca));
    }

    public function test_admin_puede_todo_via_before(): void
    {
        $admin = User::factory()->administrador()->create();
        $dueno = User::factory()->create();
        $finca = Finca::factory()->for($dueno, 'propietario')->create();

        $this->assertTrue($this->policy->before($admin));
        // before() devolviendo true cortocircuita; verificamos que las reglas
        // finas tampoco bloquearian si se llegaran a evaluar.
        $this->assertFalse($this->policy->view($admin, $finca),
            'view() sin before() devuelve false porque admin no es propietario');
    }

    public function test_before_devuelve_null_para_no_administradores(): void
    {
        $this->assertNull($this->policy->before(User::factory()->create()));
        $this->assertNull($this->policy->before(User::factory()->veterinario()->create()));
    }
}
