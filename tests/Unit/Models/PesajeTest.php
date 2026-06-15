<?php

namespace Tests\Unit\Models;

use App\Models\Pesaje;
use Tests\TestCase;

class PesajeTest extends TestCase
{
    public function test_peso_final_usa_estimado_si_no_hay_correccion(): void
    {
        $pesaje = new Pesaje([
            'peso_estimado_kg' => 410.5,
            'fue_corregido' => false,
        ]);

        $this->assertEqualsWithDelta(410.5, $pesaje->pesoFinal(), 0.01);
    }

    public function test_peso_final_usa_corregido_si_existe_y_fue_corregido(): void
    {
        $pesaje = new Pesaje([
            'peso_estimado_kg' => 410.5,
            'peso_corregido_kg' => 425.0,
            'fue_corregido' => true,
        ]);

        $this->assertEqualsWithDelta(425.0, $pesaje->pesoFinal(), 0.01);
    }

    public function test_peso_final_cae_al_estimado_si_corregido_es_null_aun_con_flag_true(): void
    {
        $pesaje = new Pesaje([
            'peso_estimado_kg' => 410.5,
            'peso_corregido_kg' => null,
            'fue_corregido' => true,
        ]);

        $this->assertEqualsWithDelta(410.5, $pesaje->pesoFinal(), 0.01);
    }

    public function test_peso_final_devuelve_float_aunque_columna_sea_decimal_string(): void
    {
        $pesaje = new Pesaje([
            'peso_estimado_kg' => '410.50',
            'fue_corregido' => false,
        ]);

        $this->assertIsFloat($pesaje->pesoFinal());
    }
}
