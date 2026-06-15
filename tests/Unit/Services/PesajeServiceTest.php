<?php

namespace Tests\Unit\Services;

use App\Models\Animal;
use App\Models\CorreccionPeso;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use App\Services\Pesaje\PesajeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesajeServiceTest extends TestCase
{
    use RefreshDatabase;

    private PesajeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PesajeService();
    }

    public function test_listar_por_animal_devuelve_solo_pesajes_del_propietario(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();

        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id, 'fecha' => now()->subDays(2),
            'peso_estimado_kg' => 410, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id, 'fecha' => now(),
            'peso_estimado_kg' => 420, 'tipo' => 'manual',
        ]);

        $pesajes = $this->service->listarPorAnimal($user, $animal);

        $this->assertSame(2, $pesajes->total());
        $this->assertEqualsWithDelta(420.0, (float) $pesajes->first()->peso_estimado_kg, 0.01);
    }

    public function test_listar_por_animal_no_devuelve_pesajes_de_otra_finca(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $fincaAjena = Finca::factory()->for($otro, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animalAjeno = Animal::factory()->for($fincaAjena)->for($raza)->create();
        Pesaje::create([
            'animal_id' => $animalAjeno->id, 'usuario_id' => $otro->id, 'fecha' => now(),
            'peso_estimado_kg' => 500, 'tipo' => 'ia',
        ]);

        $pesajes = $this->service->listarPorAnimal($user, $animalAjeno);

        $this->assertSame(0, $pesajes->total());
    }

    public function test_corregir_actualiza_pesaje_y_guarda_correccion_en_historial(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $pesaje = Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id, 'fecha' => now(),
            'peso_estimado_kg' => 400, 'tipo' => 'ia',
        ]);

        $actualizado = $this->service->corregir($user, $pesaje, 420.5, 'Pesado en bascula');

        $this->assertTrue((bool) $actualizado->fue_corregido);
        $this->assertEqualsWithDelta(420.5, (float) $actualizado->peso_corregido_kg, 0.01);
        $this->assertSame(1, CorreccionPeso::where('pesaje_id', $pesaje->id)->count());
    }

    public function test_corregir_guarda_peso_original_correcto_si_pesaje_ya_estaba_corregido(): void
    {
        $user = User::factory()->create();
        $finca = Finca::factory()->for($user, 'propietario')->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $pesaje = Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id, 'fecha' => now(),
            'peso_estimado_kg' => 400, 'peso_corregido_kg' => 410, 'fue_corregido' => true,
            'tipo' => 'ia',
        ]);

        $this->service->corregir($user, $pesaje, 415, 'Re-correccion');

        $correccion = CorreccionPeso::where('pesaje_id', $pesaje->id)->first();
        $this->assertEqualsWithDelta(410.0, (float) $correccion->peso_original_kg, 0.01,
            'el peso_original_kg debe ser el ultimo peso corregido, no el estimado');
    }
}
