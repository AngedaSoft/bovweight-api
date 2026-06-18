<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finca;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    /**
     * GET /api/dashboard/peso?finca_id=...  (finca_id opcional)
     */
    public function pesoTotal(Request $request): JsonResponse
    {
        $request->validate([
            'finca_id' => ['nullable', 'integer', 'exists:fincas,id'],
        ]);

        $fincaId = $request->filled('finca_id') ? $request->integer('finca_id') : null;

        if ($fincaId !== null) {
            $finca = \App\Models\Finca::findOrFail($fincaId);
            $this->authorize('view', $finca);
        }

        return response()->json(
            $this->dashboardService->obtenerPesoTotal($request->user(), $fincaId)
        );
    }

    /**
     * GET /api/dashboard?finca_id=...
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'finca_id' => ['required', 'integer', 'exists:fincas,id'],
        ]);

        $finca = Finca::findOrFail($request->integer('finca_id'));

        // Un ganadero solo puede consultar metricas de sus propias fincas.
        // FincaPolicy.before() concede acceso global al administrador.
        $this->authorize('view', $finca);

        return response()->json(
            $this->dashboardService->obtenerMetricasFinca($finca->id)
        );
    }
}
