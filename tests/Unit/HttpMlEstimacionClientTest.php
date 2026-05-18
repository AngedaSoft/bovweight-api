<?php

namespace Tests\Unit;

use App\Services\Ml\HttpMlEstimacionClient;
use App\Services\Ml\MlEstimacionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpMlEstimacionClientTest extends TestCase
{
    public function test_estima_devuelve_payload_decodificado(): void
    {
        Http::fake([
            '*/api/v1/estimaciones' => Http::response([
                'peso_estimado_kg' => 412,
                'metodo' => 'IA_VISUAL',
            ], 200),
        ]);

        $cliente = new HttpMlEstimacionClient(
            app(HttpFactory::class),
            baseUrl: 'http://ml-fake',
            apiKey: null,
            timeoutSeg: 5,
        );

        $resultado = $cliente->estimar(['raza' => 'BRAHMAN', 'perimetro_toracico_cm' => 175, 'largo_cuerpo_cm' => 140]);

        $this->assertSame(412, $resultado['peso_estimado_kg']);
    }

    public function test_lanza_excepcion_si_el_servicio_falla(): void
    {
        Http::fake([
            '*/api/v1/estimaciones' => Http::response(['error' => 'fallo', 'codigo' => 'X'], 500),
        ]);

        $cliente = new HttpMlEstimacionClient(
            app(HttpFactory::class),
            baseUrl: 'http://ml-fake',
            apiKey: null,
            timeoutSeg: 5,
        );

        $this->expectException(MlEstimacionException::class);
        $cliente->estimar(['raza' => 'BRAHMAN', 'perimetro_toracico_cm' => 175, 'largo_cuerpo_cm' => 140]);
    }
}
