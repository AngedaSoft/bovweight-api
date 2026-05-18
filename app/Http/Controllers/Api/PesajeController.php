<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pesaje\CorregirPesajeRequest;
use App\Http\Resources\PesajeResource;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Services\Pesaje\PesajeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PesajeController extends Controller
{
    public function __construct(private readonly PesajeService $service)
    {
    }

    public function porAnimal(Request $request, Animal $animal): AnonymousResourceCollection
    {
        $this->authorize('view', $animal);
        return PesajeResource::collection(
            $this->service->listarPorAnimal($request->user(), $animal, (int) $request->input('per_page', 20))
        );
    }

    public function show(Pesaje $pesaje): PesajeResource
    {
        $this->authorize('view', $pesaje);
        return new PesajeResource($pesaje->load('fotografias'));
    }

    public function corregir(CorregirPesajeRequest $request, Pesaje $pesaje): PesajeResource
    {
        $this->authorize('corregir', $pesaje);
        $actualizado = $this->service->corregir(
            $request->user(),
            $pesaje,
            (float) $request->input('peso_corregido_kg'),
            (string) $request->input('motivo'),
        );
        return new PesajeResource($actualizado);
    }
}
