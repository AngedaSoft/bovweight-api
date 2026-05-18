<?php

namespace App\Services\Ml;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;

/**
 * Implementacion concreta del cliente ML sobre HTTP.
 *
 * Aislado para que en tests podamos inyectar otra implementacion del
 * contrato `MlEstimacionClient`.
 */
class HttpMlEstimacionClient implements MlEstimacionClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly int $timeoutSeg,
    ) {
    }

    public function estimar(array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/v1/estimaciones';

        $pending = $this->http
            ->timeout($this->timeoutSeg)
            ->acceptJson();

        if ($this->apiKey) {
            $pending = $pending->withHeaders(['X-API-Key' => $this->apiKey]);
        }

        $request = $pending->asMultipart();

        $partes = [];
        foreach (['raza', 'perimetro_toracico_cm', 'largo_cuerpo_cm'] as $campo) {
            if (! empty($payload[$campo])) {
                $partes[] = ['name' => $campo, 'contents' => (string) $payload[$campo]];
            }
        }
        if (! empty($payload['imagen'])) {
            $partes[] = [
                'name' => 'imagen',
                'contents' => fopen($payload['imagen'], 'r'),
                'filename' => basename($payload['imagen']),
            ];
        }

        try {
            $response = $request->post($url, $partes);
        } catch (ConnectionException $e) {
            throw new MlEstimacionException('No se pudo contactar el servicio de estimacion.', 'ML_UNREACHABLE');
        }

        if ($response->failed()) {
            $body = $response->json() ?? [];
            throw new MlEstimacionException(
                $body['error'] ?? 'Respuesta invalida del servicio ML.',
                $body['codigo'] ?? 'ML_ERROR',
                $body,
            );
        }

        return $response->json();
    }
}
