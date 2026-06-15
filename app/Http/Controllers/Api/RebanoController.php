<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rebano\StoreRebanoRequest;
use App\Http\Requests\Rebano\UpdateRebanoRequest;
use App\Http\Resources\RebanoResource;
use App\Models\Rebano;
use App\Models\Finca;
use App\Services\Rebano\RebanoService;
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RebanoController extends Controller
{
    // Inyectamos el servicio mediante el constructor de forma limpia
    public function __construct(protected RebanoService $rebanoService) {}

    /**
     * GET /api/rebanos
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Rebano::class);
        
        // CORRECCIÓN: Se pasa el usuario autenticado por parámetro, eliminando el auth() global del Service
        return RebanoResource::collection($this->rebanoService->listarTodos($request->user()));
    }

    /**
     * GET /api/fincas/{fincaId}/rebanos
     */
    public function porFinca(int $fincaId): AnonymousResourceCollection
    {
        $finca = Finca::findOrFail($fincaId);
        Gate::authorize('view', $finca); // Verifica que el usuario tenga acceso a la finca

        return RebanoResource::collection($this->rebanoService->listarPorFinca($fincaId));
    }

    /**
     * POST /api/rebanos
     */
    public function store(StoreRebanoRequest $request): RebanoResource
    {
        Gate::authorize('create', Rebano::class);
        $rebano = $this->rebanoService->crear($request->validated());
        return new RebanoResource($rebano);
    }

    /**
     * GET /api/rebanos/{rebano}
     */
    public function show(Rebano $rebano): RebanoResource
    {
        Gate::authorize('view', $rebano);
        return new RebanoResource($rebano->loadCount('animales'));
    }

    /**
     * PATCH/PUT /api/rebanos/{rebano}
     */
    public function update(UpdateRebanoRequest $request, Rebano $rebano): RebanoResource
    {
        Gate::authorize('update', $rebano);
        $actualizado = $this->rebanoService->actualizar($rebano, $request->validated());
        return new RebanoResource($actualizado);
    }

    /**
     * DELETE /api/rebanos/{rebano}
     */
    public function destroy(Rebano $rebano): Response
    {
        Gate::authorize('delete', $rebano);
        $this->rebanoService->eliminar($rebano);
        return response()->noContent();
    }
}