<?php

namespace Tests\Feature;

use App\Models\Finca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FincaTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_crea_y_lista_sus_fincas(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/fincas', [
            'nombre' => 'Mi Finca',
            'provincia' => 'Guanacaste',
            'canton' => 'Liberia',
            'distrito' => 'Liberia',
        ])->assertCreated()->assertJsonPath('nombre', 'Mi Finca');

        $this->getJson('/api/fincas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Mi Finca');
    }

    public function test_propietario_no_puede_ver_finca_ajena(): void
    {
        $otroUsuario = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otroUsuario, 'propietario')->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/fincas/{$fincaAjena->id}")->assertForbidden();
    }

    public function test_admin_ve_todas_las_fincas(): void
    {
        Finca::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->administrador()->create());

        $this->getJson('/api/fincas')->assertOk()->assertJsonCount(3, 'data');
    }
}
