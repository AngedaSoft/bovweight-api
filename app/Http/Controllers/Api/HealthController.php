<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(
        private readonly HealthService $healthService,
    ) {
    }

    /**
     * GET /api/health
     *
     * Endpoint publico de health-check. Verifica que el servidor responde
     * y que la conexion a la BD esta activa. Util para monitoreo y para
     * que la app movil valide conectividad antes de ejecutar operaciones.
     */
    public function __invoke(): JsonResponse
    {
        $check = $this->healthService->check();

        $statusCode = $check['status'] === 'ok' ? 200 : 503;

        return response()->json($check, $statusCode);
    }
}
