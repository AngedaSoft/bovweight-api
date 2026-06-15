<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\RebanoResource;
use App\Models\Rebano;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class RebanoResourceTest extends TestCase
{
    public function test_serializa_campos_basicos_del_rebano(): void
    {
        $rebano = new Rebano([
            'finca_id' => 7,
            'nombre' => 'Lote A',
            'proposito' => 'engorde',
            'fecha_creacion' => Carbon::parse('2026-05-01'),
        ]);
        $rebano->id = 11;
        $rebano->created_at = Carbon::parse('2026-05-01 10:00:00');
        $rebano->updated_at = Carbon::parse('2026-05-01 10:00:00');

        $payload = (new RebanoResource($rebano))->toArray(new Request());

        $this->assertSame(11, $payload['id']);
        $this->assertSame(7, $payload['finca_id']);
        $this->assertSame('Lote A', $payload['nombre']);
        $this->assertSame('engorde', $payload['proposito']);
    }

    public function test_fecha_creacion_se_formatea_como_iso_date_y_no_como_y_m_dat_roto(): void
    {
        $rebano = new Rebano([
            'finca_id' => 1, 'nombre' => 'X', 'proposito' => 'cria',
            'fecha_creacion' => Carbon::parse('2026-05-01'),
        ]);
        $rebano->id = 1;
        $rebano->created_at = Carbon::parse('2026-05-01');
        $rebano->updated_at = Carbon::parse('2026-05-01');

        $payload = (new RebanoResource($rebano))->toArray(new Request());

        // Verifica explicitamente que NO aparece el bug "Y-m-dat" -> "2026-05-01pm30"
        $this->assertSame('2026-05-01', $payload['fecha_creacion']);
        $this->assertDoesNotMatchRegularExpression('/(am|pm)\d+/', $payload['fecha_creacion']);
    }

    public function test_fecha_creacion_es_null_si_no_existe(): void
    {
        $rebano = new Rebano(['finca_id' => 1, 'nombre' => 'X', 'proposito' => 'cria']);
        $rebano->id = 1;
        $rebano->created_at = Carbon::now();
        $rebano->updated_at = Carbon::now();

        $payload = (new RebanoResource($rebano))->toArray(new Request());

        $this->assertNull($payload['fecha_creacion']);
    }
}
