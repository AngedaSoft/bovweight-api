<?php

namespace Tests\Unit\Policies;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use App\Policies\PesajePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesajePolicyTest extends TestCase
{
    use RefreshDatabase;

    private PesajePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PesajePolicy();
    }

    private function pesajeDe(User $user): Pesaje
    {
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $animal = Animal::factory()->for($finca)->for(Raza::factory())->create();
        $p = Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id,
            'fecha' => now(), 'peso_estimado_kg' => 400, 'tipo' => 'ia',
        ]);
        return $p->load('animal.finca');
    }

    public function test_propietario_puede_ver_y_corregir_su_pesaje(): void
    {
        $user = User::factory()->create();
        $pesaje = $this->pesajeDe($user);

        $this->assertTrue($this->policy->view($user, $pesaje));
        $this->assertTrue($this->policy->corregir($user, $pesaje));
    }

    public function test_usuario_ajeno_no_puede_ver_ni_corregir(): void
    {
        $dueno = User::factory()->create();
        $pesaje = $this->pesajeDe($dueno);
        $intruso = User::factory()->create();

        $this->assertFalse($this->policy->view($intruso, $pesaje));
        $this->assertFalse($this->policy->corregir($intruso, $pesaje));
    }

    public function test_admin_concede_acceso_via_before(): void
    {
        $admin = User::factory()->administrador()->create();
        $this->assertTrue($this->policy->before($admin));
    }
}
