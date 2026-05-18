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
            ], 502);
        }

        return new PesajeResource($pesaje);
    }
}
