<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rebano\StoreRebanoRequest;
use App\Http\Requests\Rebano\UpdateRebanoRequest;
use App\Http\Resources\RebanoResource;
use App\Models\Rebano;
use App\Models\Finca;
use App\Services\Rebano\RebanoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RebanoController extends Controller
{
    // Inyectamos el servicio mediante el constructor de forma limpia
    public function __construct(protected RebanoService $rebanoService) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Rebano::class);
        return RebanoResource::collection($this->rebanoService->listarTodos());
    }

    public function porFinca(int $fincaId): AnonymousResourceCollection
    {
        $finca = Finca::findOrFail($fincaId);
        Gate::authorize('view', $finca); // Verifica que el usuario tenga acceso a la finca

        return RebanoResource::collection($this->rebanoService->listarPorFinca($fincaId));
    }

    public function store(StoreRebanoRequest $request): RebanoResource
    {
        Gate::authorize('create', Rebano::class);
        $rebano = $this->rebanoService->crear($request->validated());
        return new RebanoResource($rebano);
    }

    public function show(Rebano $rebano): RebanoResource
    {
        Gate::authorize('view', $rebano);
        return new RebanoResource($rebano->loadCount('animales'));
    }

    public function update(UpdateRebanoRequest $request, Rebano $rebano): RebanoResource
    {
        Gate::authorize('update', $rebano);
        $actualizado = $this->rebanoService->actualizar($rebano, $request->validated());
        return new RebanoResource($actualizado);
    }

    public function destroy(Rebano $rebano): Response
    {
        Gate::authorize('delete', $rebano);
        $this->rebanoService->eliminar($rebano);
        return response()->noContent();
    }
}