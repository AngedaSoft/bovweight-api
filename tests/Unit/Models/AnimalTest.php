<?php

namespace Tests\Unit\Models;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    use RefreshDatabase;

    public function test_animal_pertenece_a_finca_y_raza(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();

        $this->assertSame($finca->id, $animal->finca->id);
        $this->assertSame($raza->id, $animal->raza->id);
    }

    public function test_ultimo_pesaje_devuelve_el_de_fecha_mas_reciente(): void
    {
        $finca = Finca::factory()->create();
        $raza = Raza::factory()->create();
        $animal = Animal::factory()->for($finca)->for($raza)->create();
        $user = User::factory()->create();

        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id,
            'fecha' => now()->subDays(10), 'peso_estimado_kg' => 100, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id,
            'fecha' => now(), 'peso_estimado_kg' => 200, 'tipo' => 'manual',
        ]);
        Pesaje::create([
            'animal_id' => $animal->id, 'usuario_id' => $user->id,
            'fecha' => now()->subDays(5), 'peso_estimado_kg' => 150, 'tipo' => 'manual',
        ]);

        $ultimo = $animal->ultimoPesaje;

        $this->assertEqualsWithDelta(200.0, (float) $ultimo->peso_estimado_kg, 0.01);
    }

    public function test_ultimo_pesaje_es_null_si_no_hay_pesajes(): void
    {
        $animal = Animal::factory()->for(Finca::factory())->for(Raza::factory())->create();

        $this->assertNull($animal->ultimoPesaje);
    }
}
