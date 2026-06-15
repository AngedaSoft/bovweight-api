<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\FincaResource;
use App\Models\Finca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Tests\TestCase;

class FincaResourceTest extends TestCase
{
    public function test_serializa_campos_principales(): void
    {
        $finca = new Finca([
            'propietario_id' => 7,
            'nombre' => 'Hacienda',
            'provincia' => 'Guanacaste',
            'canton' => 'Liberia',
            'distrito' => 'Liberia',
            'fecha_creacion' => Carbon::parse('2026-05-01'),
        ]);
        $finca->id = 3;

        $payload = (new FincaResource($finca))->toArray(new Request());

        $this->assertSame(3, $payload['id']);
        $this->assertSame('Hacienda', $payload['nombre']);
        $this->assertSame('Guanacaste', $payload['provincia']);
        $this->assertSame('2026-05-01', $payload['fecha_creacion']);
        $this->assertSame(7, $payload['propietario_id']);
    }

    public function test_total_animales_es_missing_value_cuando_no_se_cargo_count(): void
    {
        $finca = new Finca(['nombre' => 'X', 'provincia' => 'X', 'canton' => 'X', 'distrito' => 'X']);
        $finca->id = 1;

        $payload = (new FincaResource($finca))->toArray(new Request());

        // `whenCounted` devuelve MissingValue cuando el `withCount` no se aplico;
        // al serializar JSON con response()->json() ese campo se omite.
        $this->assertInstanceOf(MissingValue::class, $payload['total_animales']);
        $this->assertInstanceOf(MissingValue::class, $payload['total_rebanos']);
    }
}
