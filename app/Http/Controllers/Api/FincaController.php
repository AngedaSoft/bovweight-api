<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finca\StoreFincaRequest;
use App\Http\Requests\Finca\UpdateFincaRequest;
use App\Http\Resources\FincaResource;
use App\Models\Finca;
use App\Services\Finca\FincaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FincaController extends Controller
{
    public function __construct(private readonly FincaService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return FincaResource::collection($this->service->listarParaUsuario($request->user()));
    }

    public function store(StoreFincaRequest $request): JsonResponse
    {
        $finca = $this->service->crearParaUsuario($request->user(), $request->validated());
        return response()->json(new FincaResource($finca), 201);
    }

    public function show(Request $request, Finca $finca): FincaResource
    {
        $this->authorize('view', $finca);
        return new FincaResource($finca->loadCount(['animales', 'rebanos']));
    }

    public function update(UpdateFincaRequest $request, Finca $finca): FincaResource
    {
        $this->authorize('update', $finca);
        return new FincaResource($this->service->actualizar($finca, $request->validated()));
    }

    public function destroy(Request $request, Finca $finca): JsonResponse
    {
        $this->authorize('delete', $finca);
        $this->service->eliminar($finca);
        return response()->json(null, 204);
    }
}
