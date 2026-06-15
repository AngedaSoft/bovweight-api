<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finca;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'finca_id' => ['required', 'integer', 'exists:fincas,id'],
        ]);

        $fincaId = (int) $request->input('finca_id');
        $finca = Finca::findOrFail($fincaId);

        // OJO ESTE DETALLE DE SEGURIDAD: El ganadero autenticado solo puede ver el dashboard de su propia finca
        Gate::authorize('view', $finca);

        $metricas = $this->dashboardService->obtenerMetricasFinca($fincaId);

        // CORRECCIÓN: Retornamos las métricas directamente sin envoltorios extras para mantener la consistencia
        return response()->json($metricas);
    }
}