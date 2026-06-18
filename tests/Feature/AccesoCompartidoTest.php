<?php

namespace Tests\Feature;

use App\Models\AccesoCompartido;
use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccesoCompartidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_invita_a_un_veterinario_existente(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        User::factory()->veterinario()->create(['correo' => 'vet@test.cr']);

        Sanctum::actingAs($propietario);

        $this->postJson('/api/acceso-compartido', [
            'finca_id' => $finca->id,
            'correo_usuario' => 'vet@test.cr',
            'tipo_acceso' => 'lectura',
        ])->assertCreated()
            ->assertJsonPath('tipo_acceso', 'lectura')
            ->assertJsonPath('finca_id', $finca->id);

        $this->assertDatabaseHas('acceso_compartido', [
            'finca_id' => $finca->id,
            'tipo_acceso' => 'lectura',
            'activo' => true,
        ]);
    }

    public function test_invitar_a_un_correo_inexistente_devuelve_422_con_codigo(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        Sanctum::actingAs($propietario);

        $this->postJson('/api/acceso-compartido', [
            'finca_id' => $finca->id,
            'correo_usuario' => 'nadie@test.cr',
            'tipo_acceso' => 'lectura',
        ])->assertStatus(422)
            ->assertJsonPath('codigo', 'USUARIO_NO_EXISTE');
    }

    public function test_no_propietario_no_puede_invitar_a_finca_ajena(): void
    {
        $duenoOriginal = User::factory()->create();
        $finca = Finca::factory()->for($duenoOriginal, 'propietario')->create();
        $intruso = User::factory()->create();
        User::factory()->veterinario()->create(['correo' => 'vet@test.cr']);

        Sanctum::actingAs($intruso);

        $this->postJson('/api/acceso-compartido', [
            'finca_id' => $finca->id,
            'correo_usuario' => 'vet@test.cr',
            'tipo_acceso' => 'lectura',
        ])->assertForbidden();
    }

    public function test_veterinario_ve_finca_compartida_en_su_listado(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create(['nombre' => 'Hacienda Esmeralda']);
        $vet = User::factory()->veterinario()->create();
        AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->create();

        Sanctum::actingAs($vet);

        $this->getJson('/api/fincas')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Hacienda Esmeralda']);
    }

    public function test_veterinario_no_ve_fincas_no_compartidas(): void
    {
        $propietario = User::factory()->create();
        Finca::factory()->for($propietario, 'propietario')->create(['nombre' => 'Privada']);
        $vet = User::factory()->veterinario()->create();

        Sanctum::actingAs($vet);

        $this->getJson('/api/fincas')
            ->assertOk()
            ->assertJsonMissing(['nombre' => 'Privada']);
    }

    public function test_veterinario_con_acceso_vencido_no_ve_la_finca(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create(['nombre' => 'Finca temporal']);
        $vet = User::factory()->veterinario()->create();
        AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->vencido()->create();

        Sanctum::actingAs($vet);

        $this->getJson('/api/fincas')
            ->assertOk()
            ->assertJsonMissing(['nombre' => 'Finca temporal']);
    }

    public function test_veterinario_lectura_puede_ver_animales_pero_no_corregir_pesajes(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $pesaje = Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $propietario->id, 'fecha' => now(),
            'peso_estimado_kg' => 400, 'tipo' => 'ia',
        ]);

        $vet = User::factory()->veterinario()->create();
        AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->create(); // lectura

        Sanctum::actingAs($vet);

        // Puede listar animales compartidos
        $this->getJson('/api/animales')->assertOk()->assertJsonFragment([
            'arete_senasa' => $animal->arete_senasa,
        ]);

        // Puede ver el pesaje
        $this->getJson("/api/pesajes/{$pesaje->id}")->assertOk();

        // Pero NO puede corregir porque tiene solo lectura
        $this->patchJson("/api/pesajes/{$pesaje->id}/correccion", [
            'peso_corregido_kg' => 420,
            'motivo' => 'Pesado en bascula',
        ])->assertForbidden();
    }

    public function test_veterinario_edicion_si_puede_corregir_pesajes(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $pesaje = Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $propietario->id, 'fecha' => now(),
            'peso_estimado_kg' => 400, 'tipo' => 'ia',
        ]);

        $vet = User::factory()->veterinario()->create();
        AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->edicion()->create();

        Sanctum::actingAs($vet);

        $this->patchJson("/api/pesajes/{$pesaje->id}/correccion", [
            'peso_corregido_kg' => 420.5,
            'motivo' => 'Pesado en bascula',
        ])->assertOk();

        $this->assertDatabaseHas('pesajes', [
            'id' => $pesaje->id,
            'fue_corregido' => true,
        ]);
    }

    public function test_mis_fincas_devuelve_solo_fincas_compartidas_vigentes(): void
    {
        $vet = User::factory()->veterinario()->create();
        $fincaA = Finca::factory()->create();
        $fincaB = Finca::factory()->create();
        $fincaC = Finca::factory()->create();

        AccesoCompartido::factory()->for($fincaA)->for($vet, 'usuario')->create();
        AccesoCompartido::factory()->for($fincaB)->for($vet, 'usuario')->vencido()->create();
        AccesoCompartido::factory()->for($fincaC)->create(); // no es el vet

        Sanctum::actingAs($vet);

        $this->getJson('/api/acceso-compartido/mis-fincas')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_propietario_revoca_acceso(): void
    {
        $propietario = User::factory()->create();
        $finca = Finca::factory()->for($propietario, 'propietario')->create();
        $vet = User::factory()->veterinario()->create();
        $acceso = AccesoCompartido::factory()->for($finca)->for($vet, 'usuario')->create();

        Sanctum::actingAs($propietario);

        $this->deleteJson("/api/acceso-compartido/{$acceso->id}")->assertNoContent();
        $this->assertDatabaseMissing('acceso_compartido', ['id' => $acceso->id]);
    }

    public function test_admin_ve_todos_los_accesos_compartidos_de_una_finca(): void
    {
        $admin = User::factory()->administrador()->create();
        $finca = Finca::factory()->create();
        AccesoCompartido::factory()->count(3)->for($finca)->create();

        Sanctum::actingAs($admin);

        $this->getJson("/api/fincas/{$finca->id}/acceso-compartido")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
