<?php

namespace Tests\Unit\Models;

use App\Models\AccesoCompartido;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoCompartidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_esta_vigente_devuelve_true_si_activo_y_sin_fecha_fin(): void
    {
        $a = AccesoCompartido::factory()->create([
            'activo' => true,
            'fecha_fin' => null,
        ]);

        $this->assertTrue($a->estaVigente());
    }

    public function test_esta_vigente_devuelve_false_si_inactivo(): void
    {
        $a = AccesoCompartido::factory()->inactivo()->create();

        $this->assertFalse($a->estaVigente());
    }

    public function test_esta_vigente_devuelve_false_si_fecha_fin_pasada(): void
    {
        $a = AccesoCompartido::factory()->vencido()->create();

        $this->assertFalse($a->estaVigente());
    }

    public function test_permite_edicion_solo_si_tipo_es_edicion_y_esta_vigente(): void
    {
        $lectura = AccesoCompartido::factory()->create();
        $edicion = AccesoCompartido::factory()->edicion()->create();
        $edicionVencido = AccesoCompartido::factory()->edicion()->vencido()->create();

        $this->assertFalse($lectura->permiteEdicion());
        $this->assertTrue($edicion->permiteEdicion());
        $this->assertFalse($edicionVencido->permiteEdicion(),
            'un acceso de edicion ya vencido no debe seguir habilitando edicion');
    }
}
