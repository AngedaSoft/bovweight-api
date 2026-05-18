<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estimacion\CrearEstimacionRequest;
use App\Http\Resources\PesajeResource;
use App\Models\Animal;
use App\Services\Estimacion\EstimacionService;
use App\Services\Ml\MlEstimacionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimacionController extends Controller
{
    public function __construct(private readonly EstimacionService $service)
    {
    }

    public function store(CrearEstimacionRequest $request): JsonResource|JsonResponse
    {
        $animal = Animal::with(['finca:id,propietario_id', 'raza'])->findOrFail(
            $request->validated()['animal_id']
        );

        try {
            $pesaje = $this->service->estimar(
                usuario: $request->user(),
                animal: $animal,
                datos: $request->validated(),
                imagen: $request->file('imagen'),
            );
        } catch (MlEstimacionException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'codigo' => $e->codigo,
                'detalles' => $e->detalles,
            ], $this->statusParaCodigoMl($e->codigo));
        }

        return new PesajeResource($pesaje);
    }

    /**
     * Mapea los codigos de error del microservicio ML al status HTTP correcto.
     *
     * - 422 cuando es un problema de la entrada del usuario (sin medidas, sin
     *   raza asignada, deteccion fallida).
     * - 502 solo cuando el servicio remoto no esta disponible o responde mal.
     */
    private function statusParaCodigoMl(?string $codigo): int
    {
        return match ($codigo) {
            'ENTRADA_INSUFICIENTE',
            'DETECCION_FALLIDA',
            'RAZA_REQUERIDA' => 422,
            default => 502,
        };
    }
}
